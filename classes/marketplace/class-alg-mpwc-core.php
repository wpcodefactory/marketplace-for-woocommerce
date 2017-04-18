<?php
/**
 * Marketplace for WooCommerce  - Core Class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Core' ) ) {

	class Alg_MPWC_Core extends Alg_WP_Plugin {

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
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		}

		/**
		 * Add settings tab to WooCommerce settings.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function add_woocommerce_settings_tab( $settings ) {


			$settings[] = new Alg_MP_WC_Settings_Page();

			return $settings;
		}
	}
}