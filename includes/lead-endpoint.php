<?php
/**
 * REST endpoint: POST /wp-json/ampy-bk/v1/lead
 *
 * DISABLED BY DEFAULT — not loaded by the plugin (see the commented require in
 * ampy-behorighetskollen.php). The current UI links quote CTAs to /offert/
 * instead. This file is kept ready for a future embedded quote form.
 *
 * Deliberately minimal in scope:
 *  - nonce verification (wp_rest)
 *  - honeypot field (`webbplats`) that must be empty
 *  - server-side validation + sanitization
 *  - GDPR consent required
 *  - emails the admin + (optionally) stores as a CPT
 *
 * Note: the field names in this endpoint are Swedish (namn, kontakt, meddelande,
 * samtycke, webbplats) to match a Swedish-language form. Keep them if you wire it up.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'rest_api_init', function () {
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
            'kontakt'    => array( 'required' => true, 'type' => 'string' ), // e-post (eller telefon)
            'telefon'    => array( 'required' => false, 'type' => 'string' ),
            'postnummer' => array( 'required' => false, 'type' => 'string' ),
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

    // 2. Sanitize + validate.
    $job_id     = sanitize_key( $request->get_param( 'job_id' ) );
    $verdict    = sanitize_key( $request->get_param( 'verdict' ) );
    $namn       = sanitize_text_field( $request->get_param( 'namn' ) );
    $kontakt    = sanitize_text_field( $request->get_param( 'kontakt' ) );
    $telefon    = sanitize_text_field( (string) $request->get_param( 'telefon' ) );
    $postnummer = preg_replace( '/[^0-9]/', '', (string) $request->get_param( 'postnummer' ) );
    $meddelande = sanitize_textarea_field( $request->get_param( 'meddelande' ) );
    $samtycke   = (bool) $request->get_param( 'samtycke' );

    if ( ! $namn || ! $kontakt ) {
        return new WP_Error( 'ampy_bk_missing', 'Namn och kontaktuppgift krävs.', array( 'status' => 400 ) );
    }
    if ( ! $samtycke ) {
        return new WP_Error( 'ampy_bk_consent', 'Vi behöver ditt samtycke för att höra av oss.', array( 'status' => 400 ) );
    }
    $is_email = is_email( $kontakt );
    $is_phone = preg_match( '/^[\d\s\+\-\(\)]{6,}$/', $kontakt );
    if ( ! $is_email && ! $is_phone ) {
        return new WP_Error( 'ampy_bk_kontakt', 'Ange en giltig e-postadress eller telefonnummer.', array( 'status' => 400 ) );
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
        return new WP_Error( 'ampy_bk_mail', 'Kunde inte skicka mejlet just nu. Försök igen om en stund.', array( 'status' => 500 ) );
    }
    return new WP_REST_Response( array( 'ok' => true, 'message' => 'Tack! Vi hör av oss inom kort.' ), 200 );
}
