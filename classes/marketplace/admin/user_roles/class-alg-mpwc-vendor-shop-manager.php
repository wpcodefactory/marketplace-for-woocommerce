<?php
/**
 * Marketplace for WooCommerce - Shop manager role
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Shop_Manager_Role' ) ) {

	class Alg_MPWC_Shop_Manager_Role {

		const ROLE_SHOP_MANAGER = 'shop_manager';

		/**
		 * Setups the shop manager role manager
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public function setup() {
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