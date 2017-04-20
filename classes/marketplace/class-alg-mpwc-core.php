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
				$this->init_admin();
			} else {
				if ( filter_var( get_option( Alg_MPWC_Settings_General::OPTION_ENABLE_PLUGIN ), FILTER_VALIDATE_BOOLEAN ) ) {
					$this->init_frontend();
				}
			}

			add_action( 'widgets_init', array( $this, 'create_widgets' ) );
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
		 * Initializes admin part
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function init_admin() {
			new Alg_MPWC_Admin();
		}

		/**
		 * Initializes frontend part
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function init_frontend() {
			new Alg_MPWC_Frontend();
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
			Alg_MPWC_Vendor_Role_Manager_Adm::add_vendor_role();
		}


	}
}