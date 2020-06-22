<?php
/**
 * @wordpress-plugin
 * Plugin Name:       TTR66 Core
 * Description:       Components for ttr66.ru
 * Version:           1.0.0
 * Author:            wetterkrank
 * Text Domain:       ttr66
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'TTR66_VERSION', '1.0.0' );

require plugin_dir_path( __FILE__ ) . 'includes/class-ttr66.php';
function run_ttr66() {
	$plugin = new TTR66();
	$plugin->run();
}
run_ttr66();
