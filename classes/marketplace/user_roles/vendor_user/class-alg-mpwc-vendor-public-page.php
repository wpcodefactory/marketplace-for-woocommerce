<?php
/**
 * Marketplace for WooCommerce - Vendor public page
 *
 * @version 1.0.0
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
				add_action( 'template_include', array( $this, 'template_include' ) );
				add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 99 );
				add_action( 'template_redirect', array( $this, 'template_redirect' ) );
				add_filter( 'document_title_parts', array( $this, 'document_title_parts' ) );
				add_filter( 'body_class', array( $this, 'body_class' ) );
			}
			add_action( 'init', array( $this, 'rewrite_rules' ) );
		}

		/**
		 * Changes body class.
		 *
		 * If is on public page and it has a paged param, removes home class
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @param $classes
		 *
		 * @return mixed
		 */
		public function body_class( $classes ) {

			$paged = get_query_var( 'paged' );
			if ( ! $paged ) {
				return $classes;
			}
			$vendor = get_query_var( Alg_MPWC_Query_Vars::VENDOR );
			if ( $vendor ) {
				$home = array_search( 'home', $classes );
				if ( false !== $home ) {
					unset( $classes[ $home ] );
				}

			}
			return $classes;
		}

		/**
		 * Gets the public page url
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public static function get_public_page_url( $user_id ) {
			return add_query_arg( array(
				Alg_MPWC_Query_Vars::VENDOR => $user_id,
			), get_home_url() . '/' );
		}

		/**
		 * Changes page title
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

			$title['tagline']     = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_ROLE_LABEL, 'Marketplace vendor' ) );

			$fields = new Alg_MPWC_Vendor_Admin_Fields();
			$page_title = sanitize_text_field( get_user_meta( $user->ID, $fields->meta_public_page_title, true ) );
			$title['vendor_name'] = $page_title ? $page_title : $user->data->display_name;

			return $title;
		}

		/**
		 * Redirects to profile page with pretty permalink
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function template_redirect() {
			global $wp_query;
			if ( ! isset( $_GET[ Alg_MPWC_Query_Vars::VENDOR ] ) || empty( $_GET[ Alg_MPWC_Query_Vars::VENDOR ] ) ) {
				return;
			}

			if ( ! get_option( 'permalink_structure' ) ) {
				return;
			}

			if ( is_shop() ) {
				return;
			}

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
				'index.php?' . Alg_MPWC_Query_Vars::VENDOR . '=$matches[1]&paged=$matches[3]',
				'top'
			);
		}

		/**
		 * Setups the query for profile page
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function pre_get_posts( $query ) {
			$query->query['vendor_valid'] = false;
			$vendor_qv = get_query_var( Alg_MPWC_Query_Vars::VENDOR );

			// Checks for alg_mpwc_vendor query var
			if ( ! $query->query || ! $vendor_qv ) {
				return;
			}

			if(is_shop()){
				return;
			}

			// Checks if there is only vendor_valid and query_vars_vendor on query
			/*if ( count( $query->query ) > 2 ) {
				return;
			}*/

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

			// Adds WooCommerce products from vendor id on query
			$query->set( 'post_type', 'product' );
			$query->set( 'author', $user->ID );

			//error_log(print_r($query,true));
		}

		/**
		 * Gets the template vendor-profle.php
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function template_include( $template ) {
			global $wp_query;

			$vendor = get_query_var( Alg_MPWC_Query_Vars::VENDOR );

			// Checks for alg_mpwc_vendor query var
			if ( empty( $vendor ) ) {
				return $template;
			}

			// Checks if vendor is valid
			if ( ! isset( $wp_query->query['vendor_valid'] ) || ! $wp_query->query['vendor_valid'] ) {
				return $template;
			}

			$user = get_user_by( 'slug', $vendor );

			set_query_var( 'vendor_user', $user );

			$template_from_admin_settings = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_PUBLIC_PAGE_TEMPLATE ) );

			$template = Alg_MPWC_Core::get_template( $template_from_admin_settings );

			return $template;
		}


	}
}