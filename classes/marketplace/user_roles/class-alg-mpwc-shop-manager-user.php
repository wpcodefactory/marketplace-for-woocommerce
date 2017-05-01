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
		const CAP_MANAGE_WOOCOMMERCE='manage_woocommerce';

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		function __construct() {
			add_filter( 'manage_product_posts_columns', array( $this, 'add_author_column' ) );
			add_filter( 'alg_mpwc_show_commissions_by_vendor_filter', array( $this, 'show_commissions_by_vendor_filter' ) );
			add_filter( 'alg_mpwc_show_total_commissions_value', array( $this, 'show_total_commissions_value' ) );
		}

		/**
		 * Show total commissions value if current user is a shop manager and a vendor is selected
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @param $show
		 *
		 * @return bool
		 */
		public function show_total_commissions_value($show){
			if ( ! current_user_can( self::CAP_MANAGE_WOOCOMMERCE ) ) {
				return $show;
			}

			$vendor_query_vars = get_query_var( Alg_MPWC_Query_Vars::VENDOR );
			if ( $vendor_query_vars ) {
				$show=true;
			}

			return $show;
		}

		/**
		 * Shows the dropdowns that filters commissions by vendor user.
		 */
		public function show_commissions_by_vendor_filter($show){
			if ( ! current_user_can( self::CAP_MANAGE_WOOCOMMERCE ) ) {
				return $show;
			}

			$show=true;

			return $show;
		}

		/**
		 * Adds an author column on products page
		 *
		 * @param $columns
		 *
		 * @return array
		 */
		function add_author_column( $columns ) {
			if ( ! current_user_can( self::CAP_MANAGE_WOOCOMMERCE ) ) {
				return $columns;
			}

			return array_merge( $columns, array( 'author' => __( 'Author', 'marketplace-for-woocommerce' ) ) );
		}

	}
}