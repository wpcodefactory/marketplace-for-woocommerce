<?php
/**
 * Marketplace for WooCommerce - Marketplace tab
 *
 * @version 1.4.6
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Marketplace_Tab' ) ) {

	class Alg_MPWC_Vendor_Marketplace_Tab {

		/**
		 * Custom endpoint name.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @var string
		 */
		public static $endpoint = 'marketplace';

		/**
		 * Plugin actions.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			// Actions used to insert a new endpoint in the WordPress.
			add_action( 'init', array( $this, 'add_endpoints' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			// Change the My Accout page title.
			add_filter( 'the_title', array( $this, 'endpoint_title' ) );
			// Inserting your new tab/page into the My Account page.
			add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
			add_action( 'woocommerce_account_' . self::$endpoint . '_endpoint', array( $this, 'endpoint_content' ) );
		}

		/**
		 * Register new endpoint to use inside My Account page.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
		 */
		public function add_endpoints() {
			add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
		}

		/**
		 * Add new query var.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $vars
		 *
		 * @return array
		 */
		public function add_query_vars( $vars ) {
			$vars[] = self::$endpoint;

			return $vars;
		}

		/**
		 * Set endpoint title.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param string $title
		 *
		 * @return string
		 */
		public function endpoint_title( $title ) {
			global $wp_query;
			$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );
			if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
				// New page title.
				$title = __( 'Marketplace', 'marketplace-for-woocommerce' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}

			return $title;
		}

		/**
		 * Insert the new endpoint into the My Account menu.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $items
		 *
		 * @return array
		 */
		public function new_menu_items( $items ) {
			if ( ! current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				return $items;
			}

			// Remove the logout menu item.
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );
			// Insert your custom endpoint.
			$items[ self::$endpoint ] = __( 'Marketplace', 'marketplace-for-woocommerce' );
			// Insert back the logout item.
			$items['customer-logout'] = $logout;

			return $items;
		}

		/**
		 * Endpoint HTML content.
		 *
		 * @version 1.4.6
		 * @since   1.0.0
		 */
		public function endpoint_content() {
			$current_user    = wp_get_current_user();
			$current_section = ( isset( $_GET['section'] ) ? $_GET['section'] : apply_filters( 'alg_mpwc_vendor_marketplace_tab_default_section', 'dashboard' ) );
			$sections        = apply_filters( 'alg_mpwc_vendor_marketplace_tab_sections', array(
				'dashboard' => __( 'Dashboard', 'marketplace-for-woocommerce' ),
			) );
			if ( count( $sections ) > 1 ) {
				$links = array();
				foreach ( $sections as $section_id => $section_title ) {
					$is_active = ( $section_id === $current_section );
					$link      = apply_filters( 'alg_mpwc_vendor_marketplace_tab_section_link', add_query_arg( 'section', $section_id, wc_get_endpoint_url( 'marketplace' ) ), $section_id, $current_user );
					$links[]   = '<li style="display: inline;"' . ( $is_active ? ' class="alg_mpwc_vendor_marketplace_tab_section_active"' : '' ) . '>' .
					             '<a' . ( $is_active ? ' style="color: black; font-weight: bold;" class="alg_mpwc_vendor_marketplace_tab_section_active"' : '' ) . ' href="' .
					             $link . '">' . $section_title . '</a>' .
					             '</li>';
				}
				echo '<ul class="alg_mpwc_vendor_marketplace_tab_section" style="list-style-type: none; margin: 0; padding: 0;">' . implode( ' | ', $links ) . '</ul>' .
				     '<br class="clear">';
			}
			if ( 'dashboard' === $current_section ) {
				do_action( 'alg_mpwc_vendor_marketplace_tab_before_dashboard_section_content', $current_user );
				$dashboard_content = array();
				if ( 'yes' === get_option( Alg_MPWC_Settings_Vendor::OPTION_CAPABILITIES_ENTER_ADMIN, 'yes' ) ) {
					$dashboard_content['admin_dashboard'] = __( 'Manage your Marketplace through the <a href="{{admin_url}}"><span style="text-decoration: underline">admin dashboard</span></a>', 'marketplace-for-woocommerce' );
				}
				$dashboard_content['public_page'] = sprintf( __( 'See your <a href="{{vendor_public_page}}">public page</a>', 'marketplace-for-woocommerce' ) );
				$dashboard_content                = array_map( function ( $value ) {
					$from_to = array(
						'{{admin_url}}'          => admin_url(),
						'{{vendor_public_page}}' => Alg_MPWC_Vendor_Public_Page::get_public_page_url( wp_get_current_user()->ID ),
					);
					return str_replace( array_keys( $from_to ), $from_to, $value );
				}, $dashboard_content );
				$dashboard_content                = apply_filters( 'alg_mpwc_vendor_marketplace_tab_dashboard_section_content', $dashboard_content );
				if ( ! empty( $dashboard_content ) ) {
					echo '<ul class="alg_mpwc_vendor_marketplace_tab_dashboard_section_content"><li>' . implode( '</li><li>', $dashboard_content ) . '</li></ul>';
				}
				do_action( 'alg_mpwc_vendor_marketplace_tab_after_dashboard_section_content', $current_user );
			} else {
				do_action( 'alg_mpwc_vendor_marketplace_tab_section_content', $current_section, $current_user );
			}
		}
	}
}