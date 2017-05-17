<?php
/**
 * Marketplace for WooCommerce - Vendor user
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_User' ) ) {

	class Alg_MPWC_Vendor_User {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			// Manages the public page of the vendor user
			new Alg_MPWC_Vendor_Public_Page();

			// Manages vendor user (Create role, manages access)
			$vendor_role = new Alg_MPWC_Vendor_Role();
			$vendor_role->init();

			// Creates vendor admin fields
			add_action( 'cmb2_admin_init', array( $this, 'add_admin_fields' ) );

			// Setups the block vendor option
			new Alg_MPWC_Vendor_Block_Option();

			// Setups vendor registry
			new Alg_MPWC_Vendor_Registry();

			// Setups marketplace tab on my account page
			new Alg_MPWC_Vendor_Marketplace_Tab();

			// Adds info on product about the vendor
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_product_author' ), 9 );
			add_filter( 'woocommerce_product_tabs', array( $this, 'add_tab_on_product' ) );
		}

		/**
		 * Create a tab on product page about the vendor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_tab_on_product($tabs) {
			global $post;
			$user = get_user_by( 'ID', $post->post_author );
			if ( ! $user || ! in_array( Alg_MPWC_Vendor_Role::ROLE_VENDOR, $user->roles ) ) {
				return $tabs;
			}
			$tabs['alg_mpwc_vendor'] = array(
				'title'    => __( 'Vendor', 'marketplace-for-woocommerce' ),
				'priority' => 1,
				'callback' => array( $this, 'vendor_tab' ),
			);

			return $tabs;
		}

		/**
		 * The html for vendor tab on product
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function vendor_tab() {
			global $post;

			// User
			$user = get_user_by( 'ID', $post->post_author );

			// Public page url
			$user_public_page_url = Alg_MPWC_Vendor_Public_Page::get_public_page_url( $post->post_author );

			// User fields
			$fields = new Alg_MPWC_Vendor_Admin_Fields();

			// Logo
			$logo_id = filter_var( get_user_meta( $user->ID, $fields->meta_logo . '_id', true ), FILTER_VALIDATE_INT );
			if ( $logo_id ) {
				$image = wp_get_attachment_image( $logo_id, 'full', false, array( 'style' => 'max-width:38%;float:left;margin:0 15px 0 0' ) );

				echo '<a href="'.$user_public_page_url.'">'.$image.'</a>';
			}

			// Title
			$store_title = sanitize_text_field( get_user_meta( $user->ID, $fields->meta_store_title, true ) );
			$title = $store_title ? $store_title : $user->display_name;
			echo '<h2 style="display:inline">'.$title.'</h2>';

			// Description
			$description = sanitize_text_field( get_user_meta( $user->ID, $fields->meta_description, true ) );
			echo $description ? apply_filters( 'the_content', $description ) : '';

			// See all products
			echo '<div class="alg-mpwc-product-author"><a href="' . $user_public_page_url . '">Go to ' . $title . '</a></div>';
		}

		/**
		 * Displays the product's author on product itself
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function display_product_author() {
			global $post;
			$user = get_user_by( 'ID', $post->post_author );

			if ( ! $user || ! in_array( Alg_MPWC_Vendor_Role::ROLE_VENDOR, $user->roles ) ) {
				return;
			}

			$fields               = new Alg_MPWC_Vendor_Admin_Fields();
			$user_public_page_url = Alg_MPWC_Vendor_Public_Page::get_public_page_url( $post->post_author );
			$store_title          = sanitize_text_field( get_user_meta( $user->ID, $fields->meta_store_title, true ) );
			$title                = $store_title ? $store_title : $user->display_name;

			echo '<div class="alg-mpwc-product-author"><a href="' . $user_public_page_url . '">By ' . $title . '</a></div>';
		}

		/**
		 * Creates vendor admin fields
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_admin_fields() {
			$admin_fields = new Alg_MPWC_Vendor_Admin_Fields();
			$admin_fields->add_fields();
			$admin_fields->setup_custom_css();
		}
	}
}