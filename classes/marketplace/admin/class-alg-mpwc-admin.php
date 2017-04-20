<?php
/**
 * Marketplace for WooCommerce - Admin
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Admin' ) ) {

	class Alg_MPWC_Admin {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			// Settings pages
			$this->handle_settings_pages();

			// Manages roles
			$this->manage_roles();

			// Save rewrite rules
			add_action('init',array($this,'save_rewrite_rules'));
		}

		public function save_rewrite_rules(){
			Alg_MPWC_Vendor_Role_Profile::rewrite_rules();
		}

		/**
		 * Handles settings pages
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		protected function handle_settings_pages() {
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			new Alg_MPWC_Settings_General();
			new Alg_MPWC_Settings_Vendor();
		}

		/**
		 * Manages roles
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		protected function manage_roles() {
			// Manages the vendor role
			$role_manager = new Alg_MPWC_Vendor_Role_Manager_Adm();
			$role_manager->init();

			// Manages the shop manager role
			$shop_manager = new Alg_MPWC_Shop_Manager_Role_Adm();
			$shop_manager->setup();
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
	}
}