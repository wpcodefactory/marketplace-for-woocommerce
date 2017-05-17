<?php
/**
 * Marketplace for WooCommerce - Dashboard widgets manager
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Dashboard_Widgets' ) ) {

	class Alg_MPWC_Dashboard_Widgets extends Alg_WP_Plugin {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			$this->handle_marketplace_widget();
		}

		/**
		 * Handles the marketplace dashboard widget
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		protected function handle_marketplace_widget() {


			$widget_id = 'alg_mpwc_main_widget';

			$create_marketplace_main_widget = apply_filters( "alg_mpwc_dashboard_widget_{$widget_id}", false );

			if ( $create_marketplace_main_widget ) {
				wp_add_dashboard_widget(
					$widget_id,         // Widget slug.
					'Marketplace',         // Title.
					array( $this, 'alg_mpwc_main_widget' ) // Display function.
				);
			}
		}

		/**
		 * The html of the marketplace dashboard widget
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function alg_mpwc_main_widget() {
			$user = wp_get_current_user();
			echo __( 'Hello', 'marketplace-for-woocommerce' ) . ' <strong>' . $user->display_name . '</strong>, <br /><br />';
			echo '<ul style="list-style: inside">';
			echo '<li>' . sprintf( __( "Create/edit products by clicking on your left menu <strong><a href='%s'>Products</a></strong>", 'marketplace-for-woocommerce' ), admin_url( 'edit.php?post_type=product' ) ) . '</li>';
			echo '<li>' . sprintf( __( "Edit your profile by clicking on your left menu <strong><a href='%s'>Profile</a></strong>", 'marketplace-for-woocommerce' ), admin_url( 'profile.php' ) ) . '</li>';
			echo '<li>' . sprintf( __( "View your commissions through your left menu <strong><a href='%s'>Commissions</a></strong>", 'marketplace-for-woocommerce' ), admin_url( 'edit.php?post_type=alg_mpwc_commission' ) ) . '</li>';
			echo '<li>' . sprintf( __( "Go to <strong><a href='%s'>My Account</a></strong> page on frontend <strong><a href='%1\$s'>clicking here</a></strong>", 'marketplace-for-woocommerce' ), get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) . '</li>';
			echo '<li>' . sprintf( __( "See your <strong><a href='%s'>Public page</a></strong> on frontend", 'marketplace-for-woocommerce' ), Alg_MPWC_Vendor_Public_Page::get_public_page_url( $user->ID ) ) . '</li>';
			echo '</ul>';
		}

	}
}