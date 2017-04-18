<?php
/*
Plugin Name: Marketplace for WooCommerce
Description: Let users sell on your store
Version: 1.0.0
Author: Algoritmika Ltd
Copyright: © 2017 Algoritmika Ltd.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: marketplace-for-woocommerce
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'alg_marketplace_for_wc' ) ) {
	/**
	 * Returns the main instance of Alg_MP_WC_Core to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_MP_WC_Core
	 */
	function alg_marketplace_for_wc() {
		$marketplace = Alg_MP_WC_Core::get_instance();
		$marketplace->set_args( array(
			'plugin_file_path' => __FILE__,
			'translation'      => array(
				'slug' => 'marketplace-for-woocommerce',
			),
		) );

		return $marketplace;
	}
}

// Starts the plugin
add_action( 'plugins_loaded', 'alg_mp_wc_start_plugin' );
if ( ! function_exists( 'alg_mp_wc_start_plugin' ) ) {
	/**
	 * Starts the plugin
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_mp_wc_start_plugin() {

		// Includes composer dependencies and autoloads classes
		require __DIR__ . '/vendor/autoload.php';

		// Initializes the plugin
		$marketplace = alg_marketplace_for_wc();
		$marketplace->init();

	}
}