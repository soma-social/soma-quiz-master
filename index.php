<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}
/*
Plugin Name: Soma Quiz Master
Plugin URI: https://somasocial.com
Description: This plugin provides an easy way to add quizzes to a WordPress website.
Author: kos [Soma Social]
Author URI: https://wp-kitten.me
Version: 1.0
Text Domain: soma-quiz-master
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

require_once( dirname( __FILE__ ) . '/plugin-constants.php' );

/*
 * Autoload files from {pluginDir}/src directory
 */
spl_autoload_register( function ( $className ) {
    $baseDirPath = SQM_DIR . '/src/classes';
    if ( !class_exists( $className ) ) {
        $classPath = "{$baseDirPath}/{$className}.php";
        if ( is_file( $classPath ) ) {
            require_once( $classPath );
        }
    }
} );

$sqmPlugin = SomaQuizMaster::getInstance();
$sqmDB = new SQM_DB();
new SQM_Ajax();

add_action( 'plugins_loaded', 'sqm_load_textdomain' );

/**
 * SQM admin notice for minimum WordPress version.
 * Warning when the site doesn't have the minimum required WordPress version.
 * @return void
 */
function sqm_notice_wp_version( $wpVersion )
{
    /* translators: %s: WordPress version */
    $message = sprintf( esc_html__( 'Soma Quiz Master requires WordPress version %s+. Because you are using an earlier version, the plugin is currently NOT RUNNING.', 'soma-quiz-master' ), SQM_MIN_WP_VERSION );
    $html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
    echo wp_kses_post( $html_message );
}

/**
 * SQM admin notice for minimum PHP version.
 * Warning when the site doesn't have the minimum required PHP version.
 * @return void
 */
function sqm_notice_php_version()
{
    /* translators: %s: PHP version */
    $message = sprintf( esc_html__( 'Soma Quiz Master requires PHP version %s+, plugin is currently NOT RUNNING.', 'soma-quiz-master' ), SQM_MIN_PHP_VERSION );
    $html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
    echo wp_kses_post( $html_message );
}

function sqm_load_textdomain()
{
    load_plugin_textdomain( 'soma-quiz-master', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}

if ( !$sqmPlugin->isValidPhpVersion() ) {
    add_action( 'admin_notices', 'sqm_notice_php_version' );
}
elseif ( !$sqmPlugin->isValidWpVersion() ) {
    add_action( 'admin_notices', 'sqm_notice_wp_version' );
}
else {
    require_once( SQM_DIR . '/plugin-init.php' );
}

register_activation_hook( __FILE__, 'sqm_plugin_create_tables' );
register_uninstall_hook( __FILE__, 'sqm_plugin_drop_tables' );
//#! [:: DEBUG MODE ONLY]
//register_deactivation_hook( __FILE__, 'sqm_plugin_drop_tables' );

function sqm_plugin_create_tables()
{
    global $sqmDB;
    if ( !$sqmDB ) {
        require_once( SQM_DIR . '/src/classes/SQM_DB.php' );
        $sqmDB = new SQM_DB();
    }
    $sqmDB->createTables();
}

function sqm_plugin_drop_tables()
{
    global $sqmDB;
    if ( !$sqmDB ) {
        require_once( SQM_DIR . '/src/classes/SQM_DB.php' );
        $sqmDB = new SQM_DB();
    }
    $sqmDB->dropTables();
}
