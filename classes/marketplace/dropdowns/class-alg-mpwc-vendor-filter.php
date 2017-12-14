<?php
/**
 * Marketplace for WooCommerce - Vendor filter
 *
 * @version 1.1.1
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Filter' ) ) {

	class Alg_MPWC_Vendor_Filter {

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
		public function get_html( $params = null ) {

			// Creates custom params
			$params = wp_parse_args( $params, array(
				'get_dropdown_only' => false,
			) );

			// User fields
			$user_fields = new Alg_MPWC_Vendor_Admin_Fields();

			$get_users_args = apply_filters( 'alg_mpwc_vendors_dropdown_get_users_args', array(
				'fields'   => 'id',
				'role__in' => array( Alg_MPWC_Vendor_Role::ROLE_VENDOR ),
			) );

			// Gets vendor users that aren't blocked
			$users_with_role = get_users($get_users_args);

			// Gets vendor value from query_string
			$return_str        = '';
			$vendor_query_vars = get_query_var( Alg_MPWC_Query_Vars::VENDOR );
			if ( $vendor_query_vars ) {
				if ( ! is_numeric( $vendor_query_vars ) ) {
					$user              = get_user_by( 'slug', $vendor_query_vars );
					$vendor_query_vars = $user->ID;
				}
			} else {
				$vendor_query_vars = - 1;
			}

			// Setups dropdown params
			$args = array(
				'show_option_none' => __( 'Select a vendor', 'marketplace-for-woocommerce' ),
				'class'            => 'alg-mpwc-vendor-filter',
				'name'             => Alg_MPWC_Query_Vars::VENDOR,
				'selected'         => $vendor_query_vars,
				'include_selected' => true,
				'echo'             => false,
			);
			if ( is_array( $users_with_role ) && count( $users_with_role ) > 0 ) {
				$args['include'] = $users_with_role;
			}

			// Creates the HTML
			if ( ! $params['get_dropdown_only'] ) {
				$return_str   .= '<form class="alg-mpwc-vendor-filter-form">';
				$return_str   .= is_array( $users_with_role ) && count( $users_with_role ) > 0 ? wp_dropdown_users( $args ) : '';
				$return_str   .= '<input type="hidden" name="post_type" value="product">';
				$return_str   .= '</form>';
				$return_str   .= '<style>.alg-mpwc-vendor-filter{width:100%}</style>';
			} else {
				$return_str = is_array( $users_with_role ) && count( $users_with_role ) > 0 ? wp_dropdown_users( $args ) : '';
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
					if($('.alg-mpwc-vendor-filter').length){
						$('.alg-mpwc-vendor-filter').change(function(){
							var val = $(this).val();							
							$(this).parent().submit();							
						});
					}
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
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999 );
		}

		/**
		 * Filters things from a specific vendor.
		 *
		 * If post type is commissions, filters by meta_query _alg_mpwc_author_id. Else filters by author id
		 *
		 * @version 1.1.1
		 * @since   1.0.0
		 */
		public function filter( $query ) {
			if ( ! $query->query || ! isset( $query->query['post_type'] ) ) {
				return;
			}

			//if ( $query->query['post_type'] != 'product' || ! isset( $query->query[ Alg_MPWC_Query_Vars::VENDOR ] ) ) {
			if ( ! isset( $query->query[ Alg_MPWC_Query_Vars::VENDOR ] ) ) {
				return;
			}

			// Gets the vendor slug
			$vendor  = sanitize_text_field( $query->query[ Alg_MPWC_Query_Vars::VENDOR ] );
			$user_id = null;

			// Cancels if user doesn't exist
			if ( is_numeric( $vendor ) ) {
				$user = get_user_by( 'id', $vendor );
				if ( ! $user ) {
					return;
				}
				$user_id = $vendor;
			} else {
				$user = get_user_by( 'slug', $vendor );
				if ( ! $user ) {
					return;
				}
				$user_id = $user->ID;
			}

			// Cancels if user is not a vendor
			if ( ! $user || ! in_array( Alg_MPWC_Vendor_Role::ROLE_VENDOR, $user->roles ) ) {
				return;
			}

			$allow_user = apply_filters( 'alg_mpwc_vendors_dropdown_allow_user', true, $user_id );
			if ( ! $allow_user ) {
				return;
			}

			// Sets query params
			$commission = new Alg_MPWC_CPT_Commission();
			if ( $query->query['post_type'] == $commission->id ) {
				$meta_query = $query->get('meta_query');
				if(!is_array($meta_query)){
					$meta_query = array();
				}
				$meta_query[] = array(
					'key'     => Alg_MPWC_Post_Metas::COMMISSION_AUTHOR_ID,
					'value'   => array( $user_id ),
					'compare' => 'IN',
				);
				$query->set( 'meta_query', $meta_query );
			} else {
				$query->set( 'author', $user_id );
			}

		}
	}
}