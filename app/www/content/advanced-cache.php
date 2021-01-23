<?php 
defined( 'ABSPATH' ) || exit;
define( 'BREEZE_ADVANCED_CACHE', true );
if ( is_admin() ) { return; }
if ( ! @file_exists( '/home/501407.cloudwaysapps.com/ucpeqkwjad/public_html/wp-content/plugins/breeze/breeze.php' ) ) { return; }
$config = '/home/501407.cloudwaysapps.com/ucpeqkwjad/public_html/wp-content/breeze-config/breeze-config.php';
if ( empty( $config ) || ! @file_exists( $config ) ) { return; }
$GLOBALS['breeze_config'] = include $config;
if ( empty( $GLOBALS['breeze_config'] ) || empty( $GLOBALS['breeze_config']['cache_options']['breeze-active'] ) ) { return; }
if ( @file_exists( '/home/501407.cloudwaysapps.com/ucpeqkwjad/public_html/wp-content/plugins/breeze/inc/cache/execute-cache.php' ) ) {
	include_once '/home/501407.cloudwaysapps.com/ucpeqkwjad/public_html/wp-content/plugins/breeze/inc/cache/execute-cache.php';
}
