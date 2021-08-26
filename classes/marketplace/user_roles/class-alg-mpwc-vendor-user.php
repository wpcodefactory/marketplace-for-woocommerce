<?php
/**
 * Marketplace for WooCommerce - Vendor user
 *
 * @version 1.4.2
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_User' ) ) :

class Alg_MPWC_Vendor_User {

	/**
	 * Constructor.
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
		add_action( 'cmb2_init', array( $this, 'add_admin_fields' ) );

		// Setups the block vendor option
		new Alg_MPWC_Vendor_Block_Option();

		// Setups vendor registry
		new Alg_MPWC_Vendor_Registry();

		// Setups marketplace tab on my account page
		new Alg_MPWC_Vendor_Marketplace_Tab();

		// Adds (By vendor name) on product loop
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_product_author_on_loop' ), 9 );

		// Product tab
		add_filter( 'woocommerce_product_tabs', array( $this, 'add_tab_on_product' ) );

		// Add pending vendor email class
		add_filter( 'woocommerce_email_classes', array( $this, 'add_pending_vendor_email' ) );
	}

	/**
	 * Adds pending vendor email class.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function add_pending_vendor_email( $email_classes ) {
		$email_classes['WC_Vendor_Registration_Email'] = new Alg_MPWC_Vendor_Registration_Email();
		return $email_classes;
	}

	/**
	 * Create a tab on product page displaying some info about the vendor.
	 *
	 * @version 1.0.2
	 * @since   1.0.0
	 */
	public function add_tab_on_product( $tabs ) {
		global $post;
		$user                = get_user_by( 'ID', $post->post_author );
		$product_tab_enabled = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_PRODUCT_TAB_ENABLE, true ), FILTER_VALIDATE_BOOLEAN );

		if (
			! $product_tab_enabled ||
			! $user ||
			! in_array( Alg_MPWC_Vendor_Role::ROLE_VENDOR, $user->roles )
		) {
			return $tabs;
		}

		$product_tab_priority = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_PRODUCT_TAB_PRIORITY, 40 ), FILTER_SANITIZE_NUMBER_INT );
		$product_tab_text     = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_PRODUCT_TAB_TEXT, __( 'Vendor', 'marketplace-for-woocommerce' )));

		$tabs['alg_mpwc_vendor'] = array(
			'title'    => $product_tab_text,
			'priority' => $product_tab_priority,
			'callback' => array( $this, 'create_vendor_tab_template' ),
		);

		return $tabs;
	}

	/**
	 * The html for vendor tab on product.
	 *
	 * @version 1.4.2
	 * @since   1.0.0
	 *
	 * @todo    [now] (feature) customizable content || rating: add options: enable/disable, content
	 * @todo    [now] (dev) class `alg_mpwc_vendor_rating` to `alg-mpwc-vendor-rating`
	 */
	public function create_vendor_tab_template() {
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
			echo '<a href="' . $user_public_page_url . '">' . $image . '</a>';
		}

		// Title
		$store_title = sanitize_text_field( get_user_meta( $user->ID, $fields->meta_store_title, true ) );
		$title = $store_title ? $store_title : $user->display_name;
		echo '<h2 style="display:inline">' . $title . '</h2>';

		// Description
		$description = sanitize_text_field( get_user_meta( $user->ID, $fields->meta_description, true ) );
		echo $description ? apply_filters( 'the_content', $description ) : '';

		// See all products
		echo '<div class="alg-mpwc-product-author"><a href="' . $user_public_page_url . '">' . sprintf( __( 'Go to %s', 'marketplace-for-woocommerce' ), $title ) . '</a></div>';

	}

	/**
	 * Displays the product's author on product loop.
	 *
	 * @version 1.0.2
	 * @since   1.0.0
	 */
	public function display_product_author_on_loop() {
		global $post;
		$user = get_user_by( 'ID', $post->post_author );
		$authorship_link_enabled = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_AUTHORSHIP_PRODUCT_LOOP, true ), FILTER_VALIDATE_BOOLEAN );

		if (
			! $user ||
			! in_array( Alg_MPWC_Vendor_Role::ROLE_VENDOR, $user->roles ) ||
			! $authorship_link_enabled
		) {
			return;
		}

		$fields               = new Alg_MPWC_Vendor_Admin_Fields();
		$user_public_page_url = Alg_MPWC_Vendor_Public_Page::get_public_page_url( $post->post_author );
		$store_title          = sanitize_text_field( get_user_meta( $user->ID, $fields->meta_store_title, true ) );
		$title                = $store_title ? $store_title : $user->display_name;

		echo '<div class="alg-mpwc-product-author"><a href="' . $user_public_page_url . '">By ' . $title . '</a></div>';
	}

	/**
	 * Creates vendor admin fields.
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

endif;
