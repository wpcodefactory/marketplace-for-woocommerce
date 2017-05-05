<?php
/**
 * Marketplace for WooCommerce - Vendor user
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_User' ) ) {

	class Alg_MPWC_Vendor_User {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			// Manages the public page of the vendor user
			new Alg_MPWC_Vendor_Public_Page();

			// Manages vendor user (Create role, manages access)
			$vendor_role = new Alg_MPWC_Vendor_Role();
			$vendor_role->init();

			// Creates vendor admin fields
			add_action( 'cmb2_admin_init', array( $this, 'add_admin_fields' ) );

			// Setups the block vendor option
			new Alg_MPWC_Vendor_Block_Option();
		}

		/**
		 * Creates vendor admin fields
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_admin_fields() {
			$admin_fields = new Alg_MPWC_Vendor_Admin_Fields();
			$admin_fields->add_fields();
			$admin_fields->setup_custom_css();
		}
	}
}