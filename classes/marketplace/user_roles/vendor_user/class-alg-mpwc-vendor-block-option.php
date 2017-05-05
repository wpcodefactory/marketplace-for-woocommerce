<?php
/**
 * Marketplace for WooCommerce - Vendor block option
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Block_Option' ) ) {

	class Alg_MPWC_Vendor_Block_Option {
		function __construct() {
			// Hides products of blocked users
			add_action( 'pre_get_posts', array( $this, 'hide_products_of_blocked_users' ) );

			// Dropdown
			add_filter( 'alg_mpwc_vendors_dropdown_allow_user', array(
				$this,
				'make_dropdown_stop_filtering_products',
			), 10, 2 );
			add_filter( 'alg_mpwc_vendors_dropdown_get_users_args', array(
				$this,
				'make_dropdown_hide_blocked_users',
			) );

			// Public page
			add_filter( 'alg_mpwc_public_page_query', array( $this, 'hide_public_page' ), 10, 2 );

			// Sets products from blocked vendors as blocked too
			add_filter( 'alg_mpwc_sanitize_block_vendor_option', array( $this, 'set_products_as_blocked' ), 10, 3 );
		}

		/**
		 * Saves a meta on blocked users products to hide them on loop.
		 *
		 * It only happens when the profile page is saved on admin
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function set_products_as_blocked( $value, $field_args, CMB2_Field $field ) {
			$user_id = $field->object_id();
			if ( ! $user_id ) {
				return $value;
			}

			$query_args = array(
				'post_type'      => 'product',
				'posts_per_page' => - 1,
				'author'         => $user_id,
				'fields'         => 'ids',
			);

			$user_fields    = new Alg_MPWC_Vendor_Admin_Fields();
			$previous_value = filter_var( get_user_meta( $user_id, $user_fields->meta_block_vendor, true ), FILTER_VALIDATE_BOOLEAN );

			if ( $value == 'on' && ! $previous_value ) {
				$the_query = new WP_Query( $query_args );
				if ( $the_query->have_posts() ) {
					foreach ( $the_query->posts as $post_id ) {
						update_post_meta( $post_id, Alg_MPWC_Post_Metas::PRODUCT_BLOCKED, true );
					}
					wp_reset_postdata();
				}
			} else if ( $value != 'on' && $previous_value ) {
				$the_query = new WP_Query( $query_args );
				if ( $the_query->have_posts() ) {
					foreach ( $the_query->posts as $post_id ) {
						delete_post_meta( $post_id, Alg_MPWC_Post_Metas::PRODUCT_BLOCKED );
					}
					wp_reset_postdata();
				}
			}

			return $value;
		}

		/**
		 * Hides public page if vendor is blocked
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $query
		 * @param $user_id
		 *
		 * @return mixed
		 */
		public function hide_public_page( $query, $user_id ) {
			// Checks if user is blocked
			$fields          = new Alg_MPWC_Vendor_Admin_Fields();
			$is_user_blocked = filter_var( get_user_meta( $user_id, $fields->meta_block_vendor, true ), FILTER_VALIDATE_BOOLEAN );
			if ( $is_user_blocked ) {
				$query->set_404();
			}
			return $query;
		}

		/**
		 * Makes the dropdown stop searching for blocked users
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $allow
		 * @param $user_id
		 *
		 * @return bool
		 */
		public function make_dropdown_hide_blocked_users( $args ) {
			// User fields
			$user_fields = new Alg_MPWC_Vendor_Admin_Fields();

			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => $user_fields->meta_block_vendor,
					'value'   => 'off',
					'compare' => '=',
				),
				array(
					'key'     => $user_fields->meta_block_vendor,
					'compare' => 'NOT EXISTS',
				),
			);

			return $args;
		}

		/**
		 * Makes the dropdown stop filtering products of a blocked user
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $allow
		 * @param $user_id
		 *
		 * @return bool
		 */
		public function make_dropdown_stop_filtering_products( $allow, $user_id ) {
			// Checks if user isn't blocked
			$fields = new Alg_MPWC_Vendor_Admin_Fields();
			if ( filter_var( get_user_meta( $user_id, $fields->meta_block_vendor, true ), FILTER_VALIDATE_BOOLEAN ) ) {
				return false;
			}
			return $allow;
		}

		/**
		 * Hide products from being displayed in case its author is blocked
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $query
		 */
		public function hide_products_of_blocked_users( $query ) {
			if ( is_admin() ) {
				return;
			}

			if ( ! $query->is_main_query() ) {
				return;
			}

			if ( $query->get( 'post_type' ) != 'product' ) {
				return;
			}

			$query->set( 'meta_query', array(
				'relation' => 'OR',
				array(
					'key'     => Alg_MPWC_Post_Metas::PRODUCT_BLOCKED,
					'compare' => 'NOT EXISTS',
				),
			) );
		}


	}
}