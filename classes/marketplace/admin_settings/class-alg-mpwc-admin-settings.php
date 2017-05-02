<?php
/**
 * Marketplace for WooCommerce - Admin settings
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Alg_MPWC_Admin_Settings' ) ) {

	class Alg_MPWC_Admin_Settings {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			new Alg_MPWC_Settings_General();
			new Alg_MPWC_Settings_Vendor();
			add_action( 'admin_menu', array( $this, 'create_admin_marketplace_menu' ), 99 );
		}

		/**
		 * Creates a marketplace menu for vendors
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $post_id
		 */
		public function create_admin_marketplace_menu(){
			add_menu_page( 'Marketplace', 'Marketplace', Alg_MPWC_Vendor_Role::ROLE_VENDOR, 'alg_mpwc_marketplace', '','dashicons-cart' );
		}

		/**
		 * Add settings tab to WooCommerce settings.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function add_woocommerce_settings_tab( $settings ) {
			$settings[] = new Alg_MPWC_Settings_Page();

			return $settings;
		}

		/**
		 * Enqueue admin scripts
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function enqueue_admin_scripts( $hook ) {
			if ( $hook != 'woocommerce_page_wc-settings' || ! isset( $_GET['tab'] ) || $_GET['tab'] != 'alg_mpwc' ) {
				return;
			}

			?>
            <style>
                /* Fixes select2 inputs*/
                .woocommerce table.form-table .select2-container {
                    vertical-align: middle !important;
                }
            </style>
			<?php
		}
	}
}