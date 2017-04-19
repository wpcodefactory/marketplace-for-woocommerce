<?php
/**
 * Marketplace for WooCommerce - Core Class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Core' ) ) {

	class Alg_MPWC_Core extends Alg_WP_Plugin {

		protected $vendor_role_manager;

		/**
		 * Initializes the plugin.
		 *
		 * Should be called after the set_args() method
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $args
		 */
		public function init() {
			parent::init();

			// Init admin part
			if ( is_admin() ) {
				$this->init_admin();
			}
		}

		/**
		 * Initializes admin part
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function init_admin() {

			// Settings pages
			$this->handle_settings_pages();

			// Manages roles
			$this->manage_roles();
		}

		/**
		 * Manages roles
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		protected function manage_roles(){
			// Manages the vendor role
			$role_manager = $this->get_vendor_role_manager();
			$role_manager->init();

			// Manages the shop manager role
			$shop_manager = new Alg_MPWC_Shop_Manager_Role();
			$shop_manager->setup();
		}

		/**
		 * Called when plugin is enabled
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public static function on_plugin_activation() {
			parent::on_plugin_activation();

			// Adds the vendor role
			Alg_MPWC_Vendor_Role_Manager::add_vendor_role();
		}

		/**
		 * Gets the Marketplace vendor role manager
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public function get_vendor_role_manager() {
			if ( ! $this->vendor_role_manager ) {
				$this->vendor_role_manager = new Alg_MPWC_Vendor_Role_Manager();
			}

			return $this->vendor_role_manager;
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