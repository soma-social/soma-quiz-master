<?php if ( !defined( 'ABSPATH' ) ) {
    return;
}

global $sqmPlugin;
if ( !$sqmPlugin ) {
    $sqmPlugin = SomaQuizMaster::getInstance();
}
$sqmPlugin->initHooks();

$sqmState = SQM_State::getInstance();

require_once( SQM_DIR . '/plugin-listeners.php' );
