<?php
/**
 * Marketplace for WooCommerce - Marketplace tab
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Marketplace_Tab' ) ) {

	class Alg_MPWC_Vendor_Marketplace_Tab {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			//add_action( 'init', array( $this, 'bbloomer_add_premium_support_endpoint' ) );
			//add_filter( 'query_vars', array( $this, 'bbloomer_premium_support_query_vars', 0 ) );
			//add_filter( 'woocommerce_account_menu_items', array($this,'bbloomer_add_premium_support_link_my_account') );
			//add_action( 'woocommerce_account_marketplace_endpoint', array($this,'bbloomer_premium_support_content') );
		}

		function bbloomer_add_premium_support_endpoint() {
			add_rewrite_endpoint( 'marketplace', EP_ROOT | EP_PAGES );
		}

		function bbloomer_premium_support_query_vars( $vars ) {
			$vars[] = 'marketplace';
			return $vars;
		}

		function bbloomer_add_premium_support_link_my_account( $items ) {
			$items['marketplace'] = 'Marketplace';
			return $items;
		}

		function bbloomer_premium_support_content() {
			echo '<h3>Premium WooCommerce Support</h3><p>Welcome to the WooCommerce support area. As a premium customer, you can submit a ticket should you have any WooCommerce issues with your website, snippets or customization. <i>Please contact your theme/plugin developer for theme/plugin-related support.</i></p>';
			echo do_shortcode( ' /* your shortcode here */ ' );
		}
	}
}