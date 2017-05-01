<?php
/**
 * Marketplace for WooCommerce - Frontend
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Products_Filter' ) ) {

	class Alg_MPWC_Vendor_Products_Filter {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {

		}

		/**
		 * Creates the dropdown html.
		 *
		 * Besides filtering vendor products, redirects to shop page.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function get_html($params=null) {
			$params = wp_parse_args($params,array(
				'get_dropdown_only'=>false
			));

			$users_with_role = get_users( array(
				'fields' => 'id',
				'role'   => Alg_MPWC_Vendor_Role::ROLE_VENDOR,
			) );

			$return_str = '';

			$vendor_query_vars = get_query_var( Alg_MPWC_Query_Vars::VENDOR );
			if ( $vendor_query_vars ) {
				if ( ! is_numeric( $vendor_query_vars ) ) {
					$user              = get_user_by( 'slug', $vendor_query_vars );
					$vendor_query_vars = $user->ID;
				}
			} else {
				$vendor_query_vars = - 1;
			}

			$args = array(
				'show_option_none' => __( 'Select a vendor', 'marketplace-for-woocommerce' ),
				'class'            => 'alg-mpwc-vendor-products-filter',
				'name'             => Alg_MPWC_Query_Vars::VENDOR,
				'selected'         => $vendor_query_vars,
				'include_selected' => true,
				'echo'             => false,
			);
			if(is_array($users_with_role) && count($users_with_role)>0){
				$args['include'] = $users_with_role;
			}

			if ( ! $params['get_dropdown_only'] ) {
				$shop_page_id = wc_get_page_id( 'shop' );
				$return_str .= '<form class="alg-mpwc-vendor-products-filter-form">';
				$return_str .= wp_dropdown_users($args);
				$return_str .= '<input type="hidden" name="page_id" value="' . $shop_page_id . '">';
				$return_str .= '</form>';
				$return_str .= '<style>.alg-mpwc-vendor-products-filter{width:100%}</style>';
			}else{
				$return_str = wp_dropdown_users($args);
			}


			return $return_str;
		}

		/**
		 * Enqueues dropdown scripts
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function enqueue_scripts() {
			$js = "				
				jQuery(document).ready(function($){
					$('.alg-mpwc-vendor-products-filter').change(function(){
						var val = $(this).val();
						if(val!='-1'){
							$(this).parent().submit();
						}
					});
				});
			";
			wp_add_inline_script( 'jquery-migrate', $js );
		}

		/**
		 * Setups the dropdown
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function setup() {
			add_action( 'pre_get_posts', array( $this, 'filter' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ),999 );
		}

		/**
		 * Filters things from a specific vendor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function filter( $query ) {
			if ( ! $query->query || ! isset( $query->query['post_type'] ) ) {
				return $query;
			}

			//if ( $query->query['post_type'] != 'product' || ! isset( $query->query[ Alg_MPWC_Query_Vars::VENDOR ] ) ) {
			if ( ! isset( $query->query[ Alg_MPWC_Query_Vars::VENDOR ] ) ) {
				return $query;
			}

			// Gets the vendor slug
			$vendor  = sanitize_text_field( $query->query[ Alg_MPWC_Query_Vars::VENDOR ] );
			$user_id = null;

			// Cancels if user doesn't exist
			if ( is_numeric( $vendor ) ) {
				$user = get_user_by( 'id', $vendor );
				if ( ! $user ) {
					return $query;
				}
				$user_id = $vendor;
			} else {
				$user = get_user_by( 'slug', $vendor );
				if ( ! $user ) {
					return $query;
				}
				$user_id = $user->ID;
			}

			// Cancels if user is not a vendor
			if ( ! $user || ! in_array( Alg_MPWC_Vendor_Role::ROLE_VENDOR, $user->roles ) ) {
				return $query;
			}

			$query->set( 'author', $user_id );
		}
	}
}