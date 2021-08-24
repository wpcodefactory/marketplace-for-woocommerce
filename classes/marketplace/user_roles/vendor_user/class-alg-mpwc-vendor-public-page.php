<?php
/**
 * Marketplace for WooCommerce - Vendor public page
 *
 * @version 1.2.6
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Public_Page' ) ) {

	class Alg_MPWC_Vendor_Public_Page {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			if ( ! is_admin() ) {
				add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 99 );
				add_action( 'template_redirect', array( $this, 'template_redirect' ) );
				add_filter( 'document_title_parts', array( $this, 'document_title_parts' ) );
				add_action( 'woocommerce_archive_description', array( $this, 'output_vendor_header' ) );
				add_filter( 'woocommerce_page_title', array( $this, 'page_title' ) );
				add_filter( 'woocommerce_get_breadcrumb', array( $this, 'change_breadcrumb' ) );
			}
			add_action( 'init', array( $this, 'rewrite_rules' ) );
		}

		/**
		 * Adds the vendor on breadcrumb
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @param $crumbs
		 *
		 * @return array
		 */
		public function change_breadcrumb( $crumbs ) {
			global $wp_query;

			// Checks if vendor is valid
			if ( ! isset( $wp_query->query['vendor_valid'] ) || ! $wp_query->query['vendor_valid'] ) {
				return $crumbs;
			}

			$fields              = new Alg_MPWC_Vendor_Admin_Fields();
			$vendor_query_string = get_query_var( Alg_MPWC_Query_Vars::VENDOR );
			if ( is_numeric( $vendor_query_string ) ) {
				$vendor = get_user_by( 'id', $vendor_query_string );
			} else {
				$vendor = get_user_by( 'slug', $vendor_query_string );
			}

			$vendor_label = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_ROLE_LABEL, 'Marketplace vendor' ) );

			$store_title = sanitize_text_field( get_user_meta( $vendor->ID, $fields->meta_store_title, true ) );
			$title = $store_title ? esc_html( $store_title ) : esc_html( $vendor->data->display_name );

			$crumbs[] = array( $vendor_label);
			$crumbs[] = array( $title );
			return $crumbs;
		}

		/**
		 * Adds the vendor on page title
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $title
		 *
		 * @return string
		 */
		public function page_title( $title ){
			global $wp_query;

			// Checks if vendor is valid
			if ( ! isset( $wp_query->query['vendor_valid'] ) || ! $wp_query->query['vendor_valid'] ) {
				return $title;
			}

			// User fields
			$fields = new Alg_MPWC_Vendor_Admin_Fields();
			$vendor_query_string = get_query_var( Alg_MPWC_Query_Vars::VENDOR );
			if ( is_numeric( $vendor_query_string ) ) {
				$vendor = get_user_by( 'id', $vendor_query_string );
			} else {
				$vendor = get_user_by( 'slug', $vendor_query_string );
			}

			$store_title = sanitize_text_field( get_user_meta( $vendor->ID, $fields->meta_store_title, true ) );
			$title = $store_title ? esc_html( $store_title ) : esc_html( $vendor->data->display_name );

			return $title;
		}

		/**
		 * Outputs the vendor header
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public function output_vendor_header(){
			global $wp_query;

			// Checks if vendor is valid
			if ( ! isset( $wp_query->query['vendor_valid'] ) || ! $wp_query->query['vendor_valid'] ) {
				return;
			}

			wc_get_template( 'marketplace-vendor-header.php' );
		}

		/**
		 * Gets the public page url
		 *
		 * @version 1.0.4
		 * @since   1.0.0
		 */
		public static function get_public_page_url( $user_id ) {
			if ( get_option( 'permalink_structure' ) ) {
				$vendor_slug = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_PUBLIC_PAGE_SLUG, 'marketplace-vendor' ) );
				$user        = get_user_by( 'id', $user_id );
				$vendor      = $user->data->user_nicename;
				return get_home_url() . '/' . $vendor_slug . '/' . $vendor;
			} else {
				return add_query_arg( array(
					Alg_MPWC_Query_Vars::VENDOR             => $user_id,
					Alg_MPWC_Query_Vars::VENDOR_PUBLIC_PAGE => '1',
					'post_type'                             => 'product'
				), get_home_url() . '/' );
			}
		}

		/**
		 * Changes window page title
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function document_title_parts( $title ) {

			global $wp_query;

			$vendor = get_query_var( Alg_MPWC_Query_Vars::VENDOR );

			// Checks for alg_mpwc_vendor query var
			if ( empty( $vendor ) ) {
				return $title;
			}

			// Checks if vendor is valid
			if ( ! isset( $wp_query->query['vendor_valid'] ) || ! $wp_query->query['vendor_valid'] ) {
				return $title;
			}

			// Gets user
			if ( is_numeric( $vendor ) ) {
				$user = get_user_by( 'id', $vendor );
			} else {
				$user = get_user_by( 'slug', $vendor );
			}

			$fields = new Alg_MPWC_Vendor_Admin_Fields();
			$page_title = sanitize_text_field( get_user_meta( $user->ID, $fields->meta_store_title, true ) );
			$title['vendor_name'] = $page_title ? $page_title : $user->data->display_name;

			return $title;
		}

		/**
		 * Redirects to profile page with pretty permalink
		 *
		 * @version 1.0.2
		 * @since   1.0.0
		 */
		public function template_redirect() {
			global $wp_query;
			if (
				! isset( $_GET[ Alg_MPWC_Query_Vars::VENDOR ] ) ||
				empty( $_GET[ Alg_MPWC_Query_Vars::VENDOR ] ) ||
				! isset( $_GET[ Alg_MPWC_Query_Vars::VENDOR_PUBLIC_PAGE ] ) ||
				empty( $_GET[ Alg_MPWC_Query_Vars::VENDOR_PUBLIC_PAGE ] )
			) {
				return;
			}

			if ( ! get_option( 'permalink_structure' ) ) {
				return;
			}

			/*
			if ( is_shop() ) {
				return;
			}
			*/

			$vendor_from_query_string = sanitize_text_field( $wp_query->get( Alg_MPWC_Query_Vars::VENDOR ) );

			$vendor_slug = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_PUBLIC_PAGE_SLUG, 'marketplace-vendor' ) );
			if ( is_numeric( $vendor_from_query_string ) ) {
				$user = get_user_by( 'id', $vendor_from_query_string );
				if ( ! $user ) {
					return;
				}
				$vendor = $user->data->user_nicename;
			} else {
				$vendor = $vendor_from_query_string;
			}

			wp_redirect( home_url( '/' . $vendor_slug . '/' . $vendor ) );
			exit();
		}

		/**
		 * Saves the rewrite rules.
		 *
		 * Called by method Alg_MPWC_Admin::save_rewrite_rules()
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public static function rewrite_rules() {
			$vendor_slug = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_PUBLIC_PAGE_SLUG, 'marketplace-vendor' ) );
			add_rewrite_rule(
				'^' . $vendor_slug . '/([^/]*)(/page/([0-9]+)?)?/?$',
				'index.php?' . Alg_MPWC_Query_Vars::VENDOR . '=$matches[1]&paged=$matches[3]&post_type=product&' . Alg_MPWC_Query_Vars::VENDOR_PUBLIC_PAGE . '=1',
				'top'
			);
		}

		/**
		 * Setups the query for profile page
		 *
		 * @version 1.2.6
		 * @since   1.0.0
		 */
		public function pre_get_posts( $query ) {
			$vendor_qv = $query->get( 'alg_mpwc_vendor' );
			$public_page = $query->get( 'alg_mpwc_public_page' );
			if (
				empty( $vendor_qv ) ||
				empty( $public_page )
			) {
				return;
			}

			$query->query['vendor_valid'] = false;

			// Gets the vendor slug
			$vendor = $vendor_qv;

			// Gets user
			if ( is_numeric( $vendor ) ) {
				$user = get_user_by( 'id', $vendor );
			} else {
				$user = get_user_by( 'slug', $vendor );
			}

			// Checks if user is vendor
			if ( ! $user || ! in_array( Alg_MPWC_Vendor_Role::ROLE_VENDOR, $user->roles ) ) {
				$query->set_404();
				return;
			}

			$query = apply_filters( 'alg_mpwc_public_page_query', $query, $user->ID );
			if ( $query->is_404 ) {
				return;
			}

			// Sets on query that this vendor is valid
			$query->query['vendor_valid'] = true;

			// Shows only the vendor products
			$query->set( 'author', $user->ID );
		}

	}
}