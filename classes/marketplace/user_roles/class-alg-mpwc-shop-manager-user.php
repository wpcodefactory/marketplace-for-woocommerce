<?php
/**
 * Marketplace for WooCommerce - Shop manager user manager
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Shop_Manager_User' ) ) {

	class Alg_MPWC_Shop_Manager_User {

		const ROLE_SHOP_MANAGER = 'shop_manager';

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		function __construct() {
			add_filter( 'manage_posts_columns', array( $this, 'add_author_column' ) );
		}

		/**
		 * Adds an author column on products page
		 *
		 * @param $columns
		 *
		 * @return array
		 */
		function add_author_column( $columns ) {
			if ( ! current_user_can( 'edit_others_products' ) ) {
				return $columns;
			}

			return array_merge( $columns, array( 'author' => __( 'Author', 'marketplace-for-woocommerce' ) ) );
		}

	}
}