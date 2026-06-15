<?php
/**
 * Plugin Name:       Elkollen (Ampy)
 * Plugin URI:        https://ampy.se/
 * Description:       Elkollen — lead magnet where a homeowner picks an electrical job and gets a GREEN/YELLOW/RED verdict with a legal source. Renders in Bricks via the shortcode [elkollen] (or [behorighetskollen]). UI copy is Swedish by design.
 * Version:           5.7.6
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Ampy
 * License:           GPL-2.0-or-later
 * Text Domain:       ampy-bk
 *
 * IMPORTANT FOR THE DEVELOPER:
 *  - AMPY_BK_VERSION below is the cache-busting string for CSS/JS. BUMP it on
 *    every change to assets/*.css or assets/*.js, otherwise visitors may get
 *    stale files from cache. Keep it in sync with "version" in the data file.
 *  - All copy, all rules, all links live in data/behorighetskollen-data.json.
 *    Never edit text in PHP/JS/CSS; edit the data file.
 *  - LAUNCH GATE: a certified electrician (auktoriserad elinstallatör) must sign
 *    off on the job matrix before public launch. See meta._pending_verification.
 *  - Full docs: HANDOVER.md (for the implementation agent) and CHECKLIST.md (human).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'AMPY_BK_VERSION',  '5.7.6' );
define( 'AMPY_BK_FILE',     __FILE__ );
define( 'AMPY_BK_DIR',      plugin_dir_path( __FILE__ ) );
define( 'AMPY_BK_URL',      plugin_dir_url( __FILE__ ) );
define( 'AMPY_BK_DATA',     AMPY_BK_DIR . 'data/behorighetskollen-data.json' );

require_once AMPY_BK_DIR . 'includes/render.php';

// Inline lead endpoint (REST). USED by the in-tool lead form (the verdict CTA
// "Få kostnadsfri rådgivning" opens an on-page form that POSTs here instead of
// linking out to /offert/). See HANDOVER.md, section "The lead flow".
require_once AMPY_BK_DIR . 'includes/lead-endpoint.php';

/**
 * Load + cache the data file. Single source of truth — never hardcode rules.
 *
 * @return array|null
 */
function ampy_bk_get_data() {
    static $cached = null;
    if ( $cached !== null ) return $cached;
    if ( ! file_exists( AMPY_BK_DATA ) ) return null;
    $raw = file_get_contents( AMPY_BK_DATA );
    $data = json_decode( $raw, true );
    if ( json_last_error() !== JSON_ERROR_NONE ) return null;
    $cached = $data;
    return $cached;
}

/**
 * Enqueue assets only when the shortcode is on the page (no global bloat).
 */
function ampy_bk_register_assets() {
    wp_register_style(
        'ampy-bk',
        AMPY_BK_URL . 'assets/behorighetskollen.css',
        array(),
        AMPY_BK_VERSION
    );

    wp_register_script(
        'ampy-bk',
        AMPY_BK_URL . 'assets/behorighetskollen.js',
        array(),
        AMPY_BK_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'ampy_bk_register_assets' );

/**
 * Shortcode: [behorighetskollen jobb="<job_id>"]
 *
 *  - jobb="" (default): full grid + flow
 *  - jobb="golvvarme": preselects that job; same plugin, focused per service page.
 *
 * This is the SEO lever — same tool serves 20+ URLs, focused for each.
 */
function ampy_bk_shortcode( $atts = array() ) {
    $atts = shortcode_atts( array( 'jobb' => '', 'layout' => '' ), $atts, 'behorighetskollen' );
    // layout="hero" → compact split-hero variant (search + chips + disclosure).
    // Empty = default centered/embedded behavior. Allowlist only.
    $layout = in_array( $atts['layout'], array( 'hero' ), true ) ? $atts['layout'] : '';
    $data = ampy_bk_get_data();
    if ( ! $data ) {
        return '<p>Behörighetskollen kunde inte laddas (saknad eller skadad datafil).</p>';
    }

    wp_enqueue_style( 'ampy-bk' );
    wp_enqueue_script( 'ampy-bk' );

    // Inject the data + REST endpoint info to JS — avoids a second HTTP round-trip.
    wp_localize_script( 'ampy-bk', 'AmpyBK', array(
        'data'      => $data,
        'restUrl'   => esc_url_raw( rest_url( 'ampy-bk/v1/lead' ) ),
        'restNonce' => wp_create_nonce( 'wp_rest' ),
    ) );

    return ampy_bk_render_mount( $atts['jobb'], $data, $layout );
}
add_shortcode( 'behorighetskollen', 'ampy_bk_shortcode' );
add_shortcode( 'elkollen', 'ampy_bk_shortcode' ); // alias: same tool, newer name

/**
 * Dynamic OG meta when ?jobb=... is in the URL.
 *
 * Looks for a static per-job OG image at assets/og/<id>.png. If missing, falls
 * back to a per-verdict image (assets/og/<verdict>.png), so sharing on
 * Reddit/Facebook always shows something better than a generic link.
 *
 * The designer drops in:
 *   assets/og/green.png · assets/og/yellow.png · assets/og/red.png  (1200×630)
 * and optionally a per-job override as assets/og/<id>.png.
 */
function ampy_bk_dynamic_og() {
    if ( empty( $_GET['jobb'] ) ) return;
    $data = ampy_bk_get_data();
    if ( ! $data ) return;
    $job_id = sanitize_key( wp_unslash( $_GET['jobb'] ) );
    foreach ( $data['jobs'] as $j ) {
        if ( $j['id'] !== $job_id ) continue;

        $brand = isset( $data['meta']['product_name'] ) ? $data['meta']['product_name'] : 'Elkollen';
        $title = sprintf( 'Får jag göra %s själv? | %s', mb_strtolower( $j['label'] ), $brand );
        $desc  = isset( $j['summary'] ) ? wp_strip_all_tags( $j['summary'] ) : wp_strip_all_tags( $j['why_text'] );

        // Resolve OG image: per-job override → per-verdict fallback → none.
        $og_url = '';
        $verdict_key = $j['type'] === 'fixed' ? $j['default_verdict'] : 'yellow';
        $candidates = array(
            AMPY_BK_DIR . 'assets/og/' . $j['id'] . '.png'        => AMPY_BK_URL . 'assets/og/' . $j['id'] . '.png',
            AMPY_BK_DIR . 'assets/og/' . $verdict_key . '.png'    => AMPY_BK_URL . 'assets/og/' . $verdict_key . '.png',
        );
        foreach ( $candidates as $path => $url ) {
            if ( file_exists( $path ) ) { $og_url = $url; break; }
        }

        echo "\n<meta property=\"og:title\" content=\"" . esc_attr( $title ) . "\" />\n";
        echo "<meta property=\"og:description\" content=\"" . esc_attr( $desc ) . "\" />\n";
        if ( $og_url ) {
            echo "<meta property=\"og:image\" content=\"" . esc_url( $og_url ) . "\" />\n";
            echo "<meta name=\"twitter:image\" content=\"" . esc_url( $og_url ) . "\" />\n";
        }
        echo "<meta name=\"twitter:card\" content=\"summary_large_image\" />\n";
        echo "<meta name=\"twitter:title\" content=\"" . esc_attr( $title ) . "\" />\n";
        echo "<meta name=\"twitter:description\" content=\"" . esc_attr( $desc ) . "\" />\n";
        return;
    }
}
add_action( 'wp_head', 'ampy_bk_dynamic_og' );
