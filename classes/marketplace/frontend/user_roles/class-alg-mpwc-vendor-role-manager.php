<?php
/**
 * Marketplace for WooCommerce - Vendor role (Frontend)
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Role_Manager' ) ) {

	class Alg_MPWC_Vendor_Role_Manager {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			new Alg_MPWC_Vendor_Role_Profile();
		}
	}
}