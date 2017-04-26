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
				$this->init_admin_settings();
			}

			if ( filter_var( get_option( Alg_MPWC_Settings_General::OPTION_ENABLE_PLUGIN ), FILTER_VALIDATE_BOOLEAN ) ) {
				$this->setup_plugin();
			}
		}

		/**
		 * Creates widgets
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function create_widgets() {
			register_widget( 'Alg_MPWC_Vendor_Products_Filter_Widget' );
		}

		/**
		 * Initializes admin settings
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function init_admin_settings() {
            new Alg_MPWC_Admin_Settings();
		}

		/**
		 * Setups the plugin
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function setup_plugin() {
			new Alg_MPWC_Vendor_User();
			new Alg_MPWC_Shop_Manager_User();
			add_action( 'widgets_init', array( $this, 'create_widgets' ) );
			add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
			add_action( 'init', array($this,'create_post_types') );
		}

		/**
		 * Create custom post types
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function create_post_types(){
			$cpt = new Alg_MPWC_CPT_Commission();
			$cpt->setup();
			$cpt->register();
		}

		/**
		 * Create dashboard widgets
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_dashboard_widgets(){
            new Alg_MPWC_Dashboard_Widgets();
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
			Alg_MPWC_Vendor_Role::add_vendor_role();
		}

		/**
		 * Gets the template
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public static function get_template( $template_name = '', $default_path = '', $template_path = 'woocommerce/alg-marketplace' ) {
			$marketplace = alg_marketplace_for_wc();

			if ( ! $default_path ) {
				$default_path = $marketplace->dir . 'templates' . DIRECTORY_SEPARATOR;
			} else {
				$default_path = $marketplace->dir . 'templates' . $default_path;
			}

			return wc_locate_template( $template_name, $template_path, $default_path );
		}

	}
}