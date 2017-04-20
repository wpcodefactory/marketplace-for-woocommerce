<?php
/**
 * Marketplace for WooCommerce - Frontend
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Frontend' ) ) {

	class Alg_MPWC_Frontend {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			new Alg_MPWC_Vendor_Role_Manager();
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