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

		public function get_html() {
			$users_with_role = implode( ",", get_users( array(
				'fields' => 'id',
				'role'   => Alg_MPWC_Vendor_Role_Manager_Adm::ROLE_VENDOR,
			) ) );

			$return_str = '';

			$return_str .= wp_dropdown_users( array(
				'show_option_none' => __( 'Select a vendor', 'marketplace-for-woocommerce' ),
				'class'            => 'alg-mpwc-vendor-products-filter',
				//'show'=>'display_name_with_login',
				'selected'         => isset( $_GET[ Alg_MPWC_Query_Vars::VENDOR ] ) ? $_GET[ Alg_MPWC_Query_Vars::VENDOR ] : - 1,
				'include_selected' => true,
				'include'          => $users_with_role,
				'echo'             => false,
			) );

			$shop_page_id = wc_get_page_id( 'shop' );

			$return_str .= '<form class="alg-mpwc-vendor-products-filter-form">';
			$return_str .= '<input type="hidden" name="page_id" value="' . $shop_page_id . '">';
			$return_str .= '<input type="hidden" class="alg-mpwc-vendor-slug-input" name="' . Alg_MPWC_Query_Vars::VENDOR . '" value="">';
			$return_str .= '</form>';
			$return_str .= '<style>.alg-mpwc-vendor-products-filter{width:100%}</style>';


			return $return_str;
		}

		public function enqueue_scripts() {
			$js = "
				jQuery(document).ready(function($){
					$('.alg-mpwc-vendor-products-filter').change(function(){
						var val = $(this).val();
						$(this).parent().find('.alg-mpwc-vendor-slug-input').attr('value',val);
						$(this).parent().find('.alg-mpwc-vendor-products-filter-form').submit();
					});
				});
			";
			wp_add_inline_script( 'jquery-migrate', $js );
		}

		public function setup() {
			add_action( 'woocommerce_product_query', array( $this, 'woocommerce_product_query' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		public function woocommerce_product_query( $query ) {
			if ( ! $query->query || ! isset( $query->query['post_type'] ) ) {
				return;
			}

			if ( $query->query['post_type'] != 'product' || ! isset( $query->query[ Alg_MPWC_Query_Vars::VENDOR ] ) ) {
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
			if ( ! $user || ! in_array( Alg_MPWC_Vendor_Role_Manager_Adm::ROLE_VENDOR, $user->roles ) ) {
				return;
			}

			$query->set( 'author', $user_id );
		}
	}
}