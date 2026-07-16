<?php
/**
 * REST endpoints (ampy-bk/v1):
 *   POST /lead   — the in-tool lead form posts here (see the verdict CTA "Få
 *                  kostnadsfri rådgivning"). ACTIVE — required by the plugin.
 *   GET  /nonce  — returns a FRESH wp_rest nonce. The JS fetches this on form
 *                  open before POSTing, so a stale nonce baked into a
 *                  full-page-cached HTML page never breaks anonymous submits.
 *
 * Protections: fresh-nonce check, honeypot (`webbplats`), per-IP rate limit,
 * server-side validation + sanitization, GDPR consent required. Emails the admin
 * (no DB table by design); on mail failure the payload is written to the PHP
 * error log so a lead is never silently lost.
 *
 * Field names are Swedish (namn, kontakt, telefon, postnummer, samtycke, webbplats).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'rest_api_init', function () {
    // Fresh nonce for the form (uncached GET — survives page caching).
    register_rest_route( 'ampy-bk/v1', '/nonce', array(
        'methods'             => WP_REST_Server::READABLE,
        'permission_callback' => '__return_true',
        'callback'            => function () {
            $response = new WP_REST_Response( array( 'nonce' => wp_create_nonce( 'wp_rest' ) ), 200 );
            // v7.3.7: WP only sends nocache headers on REST for logged-in users,
            // so without this an edge/full-page cache that includes /wp-json/
            // could serve one stale nonce to every anonymous visitor after its
            // 12-24h lifetime -> every lead 403s. no-store removes the class.
            $response->header( 'Cache-Control', 'no-store, max-age=0' );
            return $response;
        },
    ) );

    register_rest_route( 'ampy-bk/v1', '/lead', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'ampy_bk_handle_lead',
        'permission_callback' => function( $request ) {
            $nonce = $request->get_header( 'X-WP-Nonce' );
            return (bool) wp_verify_nonce( $nonce, 'wp_rest' );
        },
        'args' => array(
            'job_id'     => array( 'required' => true, 'type' => 'string' ),
            'verdict'    => array( 'required' => true, 'type' => 'string' ),
            'namn'       => array( 'required' => true, 'type' => 'string' ),
            'kontakt'    => array( 'required' => true, 'type' => 'string' ), // e-post
            'telefon'    => array( 'required' => true, 'type' => 'string' ),
            'postnummer' => array( 'required' => true, 'type' => 'string' ),
            'meddelande' => array( 'required' => false, 'type' => 'string' ),
            'samtycke'   => array( 'required' => true, 'type' => 'boolean' ),
            'webbplats'  => array( 'required' => false, 'type' => 'string' ), // honeypot
        ),
    ) );
} );

function ampy_bk_handle_lead( WP_REST_Request $request ) {
    // 1. Honeypot — if anything is in `webbplats`, it's a bot.
    if ( ! empty( $request->get_param( 'webbplats' ) ) ) {
        // Pretend success so bots don't probe.
        return new WP_REST_Response( array( 'ok' => true ), 200 );
    }

    // 1b. Lightweight per-IP rate limit so the public endpoint can't be used to
    // mail-bomb the admin. NOTE: behind a CDN/proxy (e.g. Cloudflare) REMOTE_ADDR
    // is the edge IP — prefer enforcing this at the edge/WAF and/or reading the
    // real client IP from a trusted forwarded header. Generous threshold to avoid
    // false positives on a shared edge IP.
    $ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
    $rk  = 'ampy_bk_rl_' . md5( $ip );
    $hits = (int) get_transient( $rk );
    if ( $hits >= 15 ) {
        return new WP_Error( 'ampy_bk_rate', 'För många förfrågningar. Försök igen om en stund.', array( 'status' => 429 ) );
    }
    set_transient( $rk, $hits + 1, 10 * MINUTE_IN_SECONDS );

    // 2. Sanitize + validate.
    $job_id     = sanitize_key( $request->get_param( 'job_id' ) );
    $verdict    = sanitize_key( $request->get_param( 'verdict' ) );
    $namn       = sanitize_text_field( $request->get_param( 'namn' ) );
    $kontakt    = sanitize_text_field( $request->get_param( 'kontakt' ) );
    $telefon    = sanitize_text_field( (string) $request->get_param( 'telefon' ) );
    $postnummer = preg_replace( '/[^0-9]/', '', (string) $request->get_param( 'postnummer' ) );
    $meddelande = sanitize_textarea_field( $request->get_param( 'meddelande' ) );
    $samtycke   = (bool) $request->get_param( 'samtycke' );

    if ( ! $namn || ! $kontakt || ! $telefon || ! $postnummer ) {
        return new WP_Error( 'ampy_bk_missing', 'Namn, e-post, telefon och postnummer krävs.', array( 'status' => 400 ) );
    }
    if ( ! $samtycke ) {
        return new WP_Error( 'ampy_bk_consent', 'Vi behöver ditt samtycke för att höra av oss.', array( 'status' => 400 ) );
    }
    if ( ! is_email( $kontakt ) ) {
        return new WP_Error( 'ampy_bk_epost', 'Ange en giltig e-postadress.', array( 'status' => 400 ) );
    }
    if ( ! preg_match( '/^[\d\s\+\-\(\)]{6,}$/', $telefon ) ) {
        return new WP_Error( 'ampy_bk_telefon', 'Ange ett giltigt telefonnummer.', array( 'status' => 400 ) );
    }
    if ( ! preg_match( '/^\d{5}$/', $postnummer ) ) {
        return new WP_Error( 'ampy_bk_postnummer', 'Ange ett giltigt postnummer (5 siffror).', array( 'status' => 400 ) );
    }
    if ( ! in_array( $verdict, array( 'green', 'yellow', 'red' ), true ) ) {
        return new WP_Error( 'ampy_bk_verdict', 'Okänt verdict.', array( 'status' => 400 ) );
    }

    // Verify the job_id exists in data — never trust client.
    $data = ampy_bk_get_data();
    if ( ! $data ) {
        return new WP_Error( 'ampy_bk_data', 'Internt fel — datafil saknas.', array( 'status' => 500 ) );
    }
    $job_label = '';
    foreach ( $data['jobs'] as $j ) {
        if ( $j['id'] === $job_id ) { $job_label = $j['label']; break; }
    }
    if ( ! $job_label ) {
        return new WP_Error( 'ampy_bk_job', 'Okänt jobb.', array( 'status' => 400 ) );
    }

    // 3. Notify Ampy.
    $admin_email = get_option( 'admin_email' );
    $subject = sprintf( '[Behörighetskollen] Lead: %s (%s)', $job_label, strtoupper( $verdict ) );
    $body = sprintf(
        "Ny offertförfrågan via Behörighetskollen\n\n" .
        "Jobb: %s (%s)\nBesked: %s\n\n" .
        "Namn: %s\nE-post: %s\nTelefon: %s\nPostnummer: %s\n\nMeddelande:\n%s\n\n" .
        "IP: %s\nTid: %s",
        $job_label, $job_id, strtoupper( $verdict ),
        $namn, $kontakt, $telefon ?: '–', $postnummer ?: '–',
        $meddelande ?: '–',
        isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '–',
        current_time( 'mysql' )
    );
    $sent = wp_mail( $admin_email, $subject, $body );

    // 4. Persist (optional): store as CPT if registered.
    do_action( 'ampy_bk_lead_received', array(
        'job_id'     => $job_id,
        'verdict'    => $verdict,
        'namn'       => $namn,
        'kontakt'    => $kontakt,
        'telefon'    => $telefon,
        'postnummer' => $postnummer,
        'meddelande' => $meddelande,
    ) );

    if ( ! $sent ) {
        // Don't lose the lead if wp_mail fails (SMTP hiccup, greylisting). Persist
        // a structured line to the error log as a safety net; a CRM/CPT listener on
        // ampy_bk_lead_received (above) is the recommended durable sink.
        error_log( sprintf(
            'AMPY_BK LEAD (mail failed) | jobb=%s verdict=%s | namn=%s | epost=%s | tel=%s | postnr=%s | tid=%s',
            $job_id, $verdict, $namn, $kontakt, $telefon, $postnummer, current_time( 'mysql' )
        ) );
        return new WP_Error( 'ampy_bk_mail', 'Kunde inte skicka mejlet just nu. Ring oss på 010-265 79 79 så hjälper vi dig.', array( 'status' => 500 ) );
    }
    return new WP_REST_Response( array( 'ok' => true, 'message' => 'Tack! Vi hör av oss inom kort.' ), 200 );
}
