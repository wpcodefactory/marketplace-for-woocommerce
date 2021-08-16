<?php
/**
 * Marketplace for WooCommerce - WooCommerce admin settings
 *
 * @version 1.3.6
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_MPWC_Settings_Page' ) ) {

	class Alg_MPWC_Settings_Page extends WC_Settings_Page {

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @todo    [next] (dev) add "Reset Settings" sections
		 */
		function __construct() {
			$this->id    = 'alg_mpwc';
			$this->label = __( 'Marketplace', 'marketplace-for-woocommerce' );
			parent::__construct();
		}

		/**
		 * get_settings.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function get_settings() {
			global $current_section;
			return apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() );
		}

		/**
		 * save.
		 *
		 * @version 1.3.6
		 * @since   1.3.6
		 */
		function save() {
			parent::save();
			flush_rewrite_rules();
		}

	}

}
