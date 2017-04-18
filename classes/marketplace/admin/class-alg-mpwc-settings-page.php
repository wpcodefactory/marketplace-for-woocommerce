<?php
/**
 * WooCommerce admin settings
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Settings_Page' ) ) {

	class Alg_MPWC_Settings_Page extends WC_Settings_Page {
		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			$this->id    = 'alg_mp_wc';
			$this->label = __( 'Marketplace', 'marketplace-for-woocommerce' );
			parent::__construct();
		}

	}

}