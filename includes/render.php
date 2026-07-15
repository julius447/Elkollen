<?php
/**
 * Server-side render: mount point + crawlable HTML fallback.
 *
 * Search engines (and users without JS) see the job grid as real HTML in a
 * tight, instrument-like layout (the same container as the JS rendering).
 * When JS boots, .ampy-bk__noscript is removed.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function ampy_bk_render_mount( $preselect, $data, $layout = '' ) {
    $preselect  = sanitize_key( $preselect );
    $valid_ids  = array_map( function( $j ) { return $j['id']; }, $data['jobs'] );
    $preselect  = ( $preselect && in_array( $preselect, $valid_ids, true ) ) ? $preselect : '';
    $lead       = isset( $data['meta']['page_lead'] ) ? $data['meta']['page_lead'] : 'Se direkt vilka eljobb du får göra själv.';
    // v7: ONE source line for the crawlable fallback (meta.source_line). The
    // old separate meta.disclaimer <p> duplicated its second sentence and is
    // removed. NOTE: the fallback stays a flat link list — converted jobs keep
    // default_verdict in the data, and these links resolve to the JS question
    // step; the no-JS view never "asks" anything, it just links.
    $source     = isset( $data['meta']['source_line'] ) ? $data['meta']['source_line']
        : 'Källa: Elsäkerhetsverket & Elsäkerhetslagen (2016:732). Vägledning, inte juridisk rådgivning.';
    $hero       = ( $layout === 'hero' );

    ob_start();
    ?>
    <div class="ampy-bk<?php echo $hero ? ' ampy-bk--hero' : ''; ?>"
         data-base-path="<?php echo esc_attr( AMPY_BK_URL ); ?>"
         data-data-url="<?php echo esc_url( AMPY_BK_URL . 'data/behorighetskollen-data.json' ); ?>"
         <?php if ( $hero ) : ?>data-layout="hero"<?php endif; ?>
         <?php if ( $preselect ) : ?>data-preselect-job="<?php echo esc_attr( $preselect ); ?>"<?php endif; ?>>
        <div class="ampy-bk__noscript">
            <div class="ampy-bk__instrument">
                <p class="ampy-bk__tagline"><?php echo esc_html( $lead ); ?></p>
                <ul class="ampy-bk__noscript-grid" role="list">
                    <?php foreach ( $data['jobs'] as $job ) : ?>
                        <li>
                            <a href="?jobb=<?php echo esc_attr( $job['id'] ); ?>">
                                <?php echo esc_html( $job['label'] ); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="ampy-bk__source-line"><?php echo esc_html( $source ); ?></p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
