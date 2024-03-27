<?php
/*
Plugin Name: Marketplace for WooCommerce
Plugin URI: https://wordpress.org/plugins/marketplace-for-woocommerce/
Description: Let users sell on your store.
Version: 1.5.7
Author: WPFactory
Author URI: https://wpfactory.com
Text Domain: marketplace-for-woocommerce
Domain Path: /langs
WC requires at least: 6.4
WC tested up to: 8.7
*/

defined( 'ABSPATH' ) || exit;

defined( 'ALG_WC_MARKETPLACE_VERSION' ) || define( 'ALG_WC_MARKETPLACE_VERSION', '1.5.7' );

/**
 * Check for active plugins.
 */
$is_wc_active = ( in_array( 'woocommerce/woocommerce.php', (array) get_option( 'active_plugins', array() ), true ) ||
	( is_multisite() && array_key_exists( 'woocommerce/woocommerce.php', (array) get_site_option( 'active_sitewide_plugins', array() ) ) ) );
if ( ! $is_wc_active ) {
	return;
}

if ( ! function_exists( 'alg_marketplace_for_wc' ) ) {
	/**
	 * Returns the main instance of Alg_MP_WC_Core to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
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

/**
 * Version update.
 *
 * @version 1.3.6
 * @since   1.3.6
 */
if ( get_option( 'alg_wc_marketplace_version', '' ) !== ALG_WC_MARKETPLACE_VERSION ) {
	add_action( 'init', 'alg_mpwc_version_updated' );
}

if ( ! function_exists( 'alg_mpwc_version_updated' ) ) {
	/**
	 * alg_mpwc_version_updated.
	 *
	 * @version 1.4.3
	 * @since   1.3.6
	 */
	function alg_mpwc_version_updated() {
		update_option( 'alg_wc_marketplace_version', ALG_WC_MARKETPLACE_VERSION );
		Alg_MPWC_Core::activate_plugin( false );
	}
}

/**
 * Starts the plugin.
 */
add_action( 'plugins_loaded', 'alg_mpwc_start_plugin' );
if ( ! function_exists( 'alg_mpwc_start_plugin' ) ) {
	/**
	 * Starts the plugin.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @todo    [next] (dev) use `function_exists( 'WC' )` instead of `is_plugin_active()`?
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

/**
 * Handles activation, installation and uninstall hooks.
 */
alg_mpwc_register_hooks();
