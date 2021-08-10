<?php
/*
Plugin Name: Marketplace for WooCommerce
Plugin URI: https://wordpress.org/plugins/marketplace-for-woocommerce/
Description: Let users sell on your store.
Version: 1.3.5
Author: Algoritmika Ltd
Author URI: https://algoritmika.com
Text Domain: marketplace-for-woocommerce
Domain Path: /langs
WC requires at least: 3.0.0
WC tested up to: 5.5
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Handle is_plugin_active function
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// Check for active plugins
if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	return;
}

if ( ! function_exists( 'alg_marketplace_for_wc' ) ) {
	/**
	 * Returns the main instance of Alg_MP_WC_Core to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_MPWC_Core
	 */
	function alg_marketplace_for_wc() {
		$marketplace = Alg_MPWC_Core::get_instance();
		$marketplace->set_args( array(
			'plugin_file_path' => __FILE__,
			'action_links'     => array( array( 'url' => admin_url( 'admin.php?page=wc-settings&tab=alg_mpwc' ), 'text' => __( 'Settings', 'woocommerce' ) ) ),
			'translation'      => array( 'text_domain' => 'marketplace-for-woocommerce' ),
		) );
		return $marketplace;
	}
}

// Starts the plugin
add_action( 'plugins_loaded', 'alg_mpwc_start_plugin' );
if ( ! function_exists( 'alg_mpwc_start_plugin' ) ) {
	/**
	 * Starts the plugin.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function alg_mpwc_start_plugin() {
		// Initializes the plugin
		$marketplace = alg_marketplace_for_wc();
		$marketplace->init();
	}
}

if ( ! function_exists( 'alg_mpwc_register_hooks' ) ) {
	/**
	 * Handles activation, installation and uninstall hooks.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_mpwc_register_hooks() {

		// Includes composer dependencies and autoloads classes
		require __DIR__ . '/vendor/autoload.php';

		// When plugin is enabled
		register_activation_hook( __FILE__, array( 'Alg_MPWC_Core', 'on_plugin_activation' ) );
	}
}

// Handles activation, installation and uninstall hooks
alg_mpwc_register_hooks();
