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

		const QUERY_VARS_VENDOR = 'alg_mpwc_vendor';

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
			add_action( 'template_include', array( $this, 'template_include' ) );
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
			add_action( 'template_redirect', array($this,'template_redirect' ));
			add_filter('document_title_parts',array($this,'document_title_parts'));
		}

		/**
		 * Changes page title
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function document_title_parts($title){

			global $wp_query;

			$vendor = get_query_var(self::QUERY_VARS_VENDOR);

			// Checks for alg_mpwc_vendor query var
			if ( empty( $vendor ) ) {
				return $title;
			}

			// Checks if vendor is valid
			if ( ! isset( $wp_query->query['vendor_valid'] ) || ! $wp_query->query['vendor_valid'] ) {
				return $title;
			}

			$user = get_user_by( 'slug', $vendor );
			$title['tagline']=sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_ROLE_LABEL, 'Marketplace vendor' ) );
			$title['vendor_name']=$user->data->display_name;

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
			if ( !isset( $_GET[ self::QUERY_VARS_VENDOR ] ) || empty( $_GET[ self::QUERY_VARS_VENDOR ] ) ) {
				return;
			}

			if ( !get_option('permalink_structure') ) {
				return;
			}

			wp_redirect( home_url( '/marketplace-vendor/' . $wp_query->get( self::QUERY_VARS_VENDOR ) ) );
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
				'^'.$vendor_slug.'/([^/]*)?$',
				'index.php?'.self::QUERY_VARS_VENDOR.'=$matches[1]',
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
			if ( ! $query->query || ! isset( $query->query[ self::QUERY_VARS_VENDOR ] ) ) {
				return;
			}

			// Gets the vendor slug
			$vendor = sanitize_text_field( $query->query[ self::QUERY_VARS_VENDOR ]);

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
			$vars[] = self::QUERY_VARS_VENDOR;

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

			$vendor = get_query_var(self::QUERY_VARS_VENDOR);

			// Checks for alg_mpwc_vendor query var
			if ( empty( $vendor ) ) {
				return $template;
			}

			// Checks if vendor is valid
			if ( ! isset( $wp_query->query['vendor_valid'] ) || ! $wp_query->query['vendor_valid'] ) {
				return $template;
			}

			$user = get_user_by( 'slug', $vendor );

			set_query_var('vendor_user',$user);

			// Gets the template
			$template = Alg_MPWC_Frontend::get_template( 'vendor-profile.php' );

			return $template;
		}


	}
}