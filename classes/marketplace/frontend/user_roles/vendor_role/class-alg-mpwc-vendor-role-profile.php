<?php
/**
 * Marketplace for WooCommerce - Profile manager for vendor role
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Role_Profile' ) ) {

	class Alg_MPWC_Vendor_Role_Profile {

		//const QUERY_VARS_VENDOR = 'alg_mpwc_vendor';

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
			add_action( 'template_include', array( $this, 'template_include' ) );
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 99 );
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
			add_filter( 'document_title_parts', array( $this, 'document_title_parts' ) );
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

			$user                 = get_user_by( 'slug', $vendor );
			$title['tagline']     = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_ROLE_LABEL, 'Marketplace vendor' ) );
			$title['vendor_name'] = $user->data->display_name;

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

			$vendor_slug = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_PROFILE_PAGE_SLUG, 'marketplace-vendor' ) );
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
			$vendor_slug = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_PROFILE_PAGE_SLUG, 'marketplace-vendor' ) );
			add_rewrite_rule(
				'^' . $vendor_slug . '/([^/]*)?$',
				'index.php?' . Alg_MPWC_Query_Vars::VENDOR . '=$matches[1]',
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

			// Checks for alg_mpwc_vendor query var
			if ( ! $query->query || ! isset( $query->query[ Alg_MPWC_Query_Vars::VENDOR ] ) ) {
				return;
			}

			// Checks if there is only vendor_valid and query_vars_vendor on query
			if ( count( $query->query ) > 2 ) {
				return;
			}

			// Gets the vendor slug
			$vendor = sanitize_text_field( $query->query[ Alg_MPWC_Query_Vars::VENDOR ] );

			// Checks if user is vendor
			$user = get_user_by( 'slug', $vendor );
			if ( ! $user || ! in_array( Alg_MPWC_Vendor_Role_Manager_Adm::ROLE_VENDOR, $user->roles ) ) {
				$query->set_404();
				return;
			}

			// Sets on query that this vendor is valid
			$query->query['vendor_valid'] = true;

			// Puts WooCommerce products from vendor id on query
			$query->set( 'post_type', 'product' );
			$query->set( 'author', $user->ID );
		}

		/**
		 * Add query vars
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_query_vars( $vars ) {
			$vars[] = Alg_MPWC_Query_Vars::VENDOR;

			return $vars;
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

			// Gets the template
			$template = Alg_MPWC_Frontend::get_template( 'vendor-profile.php' );

			return $template;
		}


	}
}