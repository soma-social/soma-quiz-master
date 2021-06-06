<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}
define( 'SQM_DIR', wp_normalize_path( untrailingslashit( plugin_dir_path( __FILE__ ) ) ) );
define( 'SQM_URI', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

define( 'SQM_MIN_WP_VERSION', '5.7' );
define( 'SQM_MIN_PHP_VERSION', '7.2' );

define( 'SQM_NONCE_ACTION', 'sqm_security_action' );
define( 'SQM_NONCE_NAME', 'sqm_security' );

define( 'SQM_DATE_FORMAT_DISPLAY', 'd-M-Y' );
