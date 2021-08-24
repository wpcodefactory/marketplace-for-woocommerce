<?php
/**
 * Marketplace for WooCommerce - Shop manager user manager
 *
 * @version 1.0.5
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Shop_Manager_User' ) ) {

	class Alg_MPWC_Shop_Manager_User {

		const ROLE_SHOP_MANAGER      = 'shop_manager';
		const CAP_MANAGE_WOOCOMMERCE = 'manage_woocommerce';

		private static $user_caps = array(
			'read'                      => true,
			'edit_product'              => true,
			'read_product'              => true,
			'delete_product'            => true,
			'edit_products'             => true,
			'delete_products'           => true,
			'delete_published_products' => true,
			'edit_published_products'   => true,
			'assign_product_terms'      => true,
			'level_0'                   => true,
			'edit_alg_mpwc_commissions' => true,
			'edit_shop_orders'          => false,
			'edit_others_shop_orders'   => false,
			'read_shop_order'           => false,
		);

		/**
		 * Constructor
		 *
		 * @version 1.0.4
		 * @since   1.0.0
		 *
		 */
		function __construct() {
			add_action( 'init', array( $this, 'add_author_support_for_products' ) );
			add_filter( 'manage_product_posts_columns', array( $this, 'add_author_column' ) );
			add_filter( 'alg_mpwc_show_commissions_by_vendor_filter', array( $this, 'show_commissions_by_vendor_filter' ) );
			add_filter( 'alg_mpwc_show_total_commissions_value', array( $this, 'show_total_commissions_value' ) );

			// Adds vendors to author dropdown
			add_filter( 'wp_dropdown_users_args', array( $this, 'add_vendors_to_author_dropdown' ), 10, 2 );
		}

		/**
		 * Adds vendors to author dropdown
		 *
		 * @version 1.0.5
		 * @since   1.0.1
		 *
		 * @param $args
		 * @param $r
		 *
		 * @return mixed
		 */
		function add_vendors_to_author_dropdown( $args, $r ) {
			global $wp_roles, $post;
			if ( ! current_user_can( self::CAP_MANAGE_WOOCOMMERCE ) ) {
				return $args;
			}

			// Check that this is the correct drop-down.
			if ( $post && $post->post_type ) {
				if ( 'product' === $post->post_type ) {
					$args['who']      = '';
					$args['role__in'] = array( Alg_MPWC_Vendor_Role::ROLE_VENDOR, 'administrator', 'shop_manager' );
				}
			}

			return $args;
		}

		/**
		 * Adds author supports for products
		 *
		 * @version 1.0.1
		 * @since   1.0.1
		 */
		public function add_author_support_for_products(){
			if ( ! current_user_can( self::CAP_MANAGE_WOOCOMMERCE ) ) {
				return;
			}
			add_post_type_support( 'product', 'author' );
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
		public function show_total_commissions_value( $show ){
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
		public function show_commissions_by_vendor_filter( $show ){
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
