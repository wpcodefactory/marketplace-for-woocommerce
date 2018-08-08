<?php
/**
 * Marketplace for WooCommerce - Vendor block option
 *
 * @version 1.2.1
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Block_Option' ) ) {

	class Alg_MPWC_Vendor_Block_Option {
		function __construct() {
			// Hides products of blocked users
			add_action( 'pre_get_posts', array( $this, 'hide_blocked_users_products' ) );

			// Dropdown
			add_filter( 'alg_mpwc_vendors_dropdown_allow_user', array( $this, 'make_dropdown_stop_filtering_products' ), 10, 2 );
			add_filter( 'alg_mpwc_vendors_dropdown_get_users_args', array( $this, 'make_dropdown_hide_blocked_users' ) );

			// Public page
			add_filter( 'alg_mpwc_public_page_query', array( $this, 'hide_public_page' ), 10, 2 );

			// Updates blocked vendor option
			add_filter( 'update_user_metadata', array( $this, 'add_blocked_vendor' ), 10, 5 );
			add_filter( 'delete_user_metadata', array( $this, 'unset_blocked_vendor' ), 10, 5 );
		}

		/**
		 * Remove blocked vendor from 'alg_mpwc_blocked_users' option when user meta '_alg_mpwc_blocked' is deleted
		 *
		 * It only happens when the profile page is saved on admin
		 *
		 * @version 1.2.1
		 * @since   1.2.1
		 */
		public function unset_blocked_vendor( $null, $object_id, $meta_key, $meta_value, $prev_value ) {
			if ( '_alg_mpwc_blocked' != $meta_key ) {
				return $null;
			}

			$user_id       = $object_id;
			$blocked_users = get_option( 'alg_mpwc_blocked_users', array() );
			$index         = array_search( $user_id, $blocked_users );
			if ( $index !== false ) {
				unset( $blocked_users[ $index ] );
				update_option( 'alg_mpwc_blocked_users', $blocked_users );
			}

			return $null;
		}

		/**
		 * Adds blocked vendor to 'alg_mpwc_blocked_users' option when user meta '_alg_mpwc_blocked' is true
		 *
		 * It only happens when the profile page is saved on admin
		 *
		 * @version 1.2.1
		 * @since   1.2.1
		 */
		public function add_blocked_vendor( $null, $object_id, $meta_key, $meta_value, $prev_value ) {
			if ( '_alg_mpwc_blocked' != $meta_key ) {
				return $null;
			}
			if ( true === filter_var( $meta_value, FILTER_VALIDATE_BOOLEAN ) ) {
				$user_id         = $object_id;
				$blocked_users   = get_option( 'alg_mpwc_blocked_users', array() );
				$blocked_users[] = $user_id;
				$blocked_users   = array_unique( $blocked_users );
				update_option( 'alg_mpwc_blocked_users', $blocked_users );
			}

			return $null;
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
		 * @version 1.2.1
		 * @since   1.2.1
		 *
		 * @param $query
		 */
		public function hide_blocked_users_products( $query ) {
			if (
				is_admin() ||
				! $query->is_main_query() ||
				$query->get( 'post_type' ) != 'product'
			) {
				return;
			}

			$blocked_users = get_option( 'alg_mpwc_blocked_users', array() );
			if ( empty( $blocked_users ) ) {
				return;
			}

			$author_not_in = $query->get( 'author__not_in' );
			$author_not_in = empty( $author_not_in ) ? array() : $author_not_in;
			$author_not_in = array_merge( $blocked_users, $author_not_in );
			$query->set( 'author__not_in', $author_not_in );
		}

	}
}