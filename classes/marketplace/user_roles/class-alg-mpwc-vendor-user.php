<?php
/**
 * Marketplace for WooCommerce - Vendor user
 *
 * @version 1.4.8
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_User' ) ) :

class Alg_MPWC_Vendor_User {

	/**
	 * Constructor.
	 *
	 * @version 1.4.9
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
		add_action( apply_filters( 'alg_mpwc_loop_vendor_info_hook', 'woocommerce_after_shop_loop_item' ), array( $this, 'display_product_author_on_loop' ), apply_filters( 'alg_mpwc_loop_vendor_info_hook_priority', 9 ) );

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
	 */
	public function create_vendor_tab_template() {
		global $post;

		// User
		$user                       = get_user_by( 'ID', $post->post_author );

		// Public page url
		$public_page_url            = Alg_MPWC_Vendor_Public_Page::get_public_page_url( $post->post_author );

		// User fields
		$fields                     = new Alg_MPWC_Vendor_Admin_Fields();

		// Logo
		$logo_id                    = filter_var( get_user_meta( $user->ID, $fields->meta_logo . '_id', true ), FILTER_VALIDATE_INT );
		$image                      = ( $logo_id ? wp_get_attachment_image( $logo_id, 'full', false, array( 'style' => 'max-width:38%;float:left;margin:0 15px 0 0' ) ) : '' );
		$image_link                 = ( $image ? '<a href="' . $public_page_url . '">' . $image . '</a>' : '' );

		// Title
		$store_title                = sanitize_text_field( get_user_meta( $user->ID, $fields->meta_store_title, true ) );
		$title                      = ( $store_title ? $store_title : $user->display_name );
		$formatted_title            = '<h2 style="display:inline">' . $title . '</h2>';

		// Description
		$description                = sanitize_text_field( get_user_meta( $user->ID, $fields->meta_description, true ) );
		$description                = ( $description ? apply_filters( 'the_content', $description ) : '' );

		// See all products
		$public_page_link           = '<a href="' . $public_page_url . '">' . sprintf( __( 'Go to %s', 'marketplace-for-woocommerce' ), $title ) . '</a>';
		$formatted_public_page_link = '<div class="alg-mpwc-product-author">' . $public_page_link . '</div>';

		// Output
		$placeholders = array(
			'%image%'                      => $image,
			'%image_link%'                 => $image_link,
			'%title%'                      => $title,
			'%formatted_title%'            => $formatted_title,
			'%description%'                => $description,
			'%public_page_url%'            => $public_page_url,
			'%public_page_link%'           => $public_page_link,
			'%formatted_public_page_link%' => $formatted_public_page_link,
			'%vendor_id%'                  => $user->ID,
		);
		$template = get_option( 'alg_mpwc_opt_vendor_product_tab_content',
			'%image_link%' . PHP_EOL . PHP_EOL . '%formatted_title%' . PHP_EOL . PHP_EOL . '%description%' . PHP_EOL . PHP_EOL . '%formatted_public_page_link%' );
		echo do_shortcode( str_replace( array_keys( $placeholders ), $placeholders, $template) );
	}

	/**
	 * Displays the product's author on product loop.
	 *
	 * @version 1.4.8
	 * @since   1.0.0
	 */
	public function display_product_author_on_loop() {
		global $post;
		$user                    = get_user_by( 'ID', $post->post_author );
		$authorship_link_enabled = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_AUTHORSHIP_PRODUCT_LOOP, true ), FILTER_VALIDATE_BOOLEAN );
		if (
			! $user ||
			! in_array( Alg_MPWC_Vendor_Role::ROLE_VENDOR, $user->roles ) ||
			! $authorship_link_enabled
		) {
			return;
		}
		$fields                = new Alg_MPWC_Vendor_Admin_Fields();
		$user_public_page_url  = Alg_MPWC_Vendor_Public_Page::get_public_page_url( $post->post_author );
		$store_title           = sanitize_text_field( get_user_meta( $user->ID, $fields->meta_store_title, true ) );
		$title                 = $store_title ? $store_title : $user->display_name;
		$array_from_to         = array(
			'%store_url%'   => esc_url( $user_public_page_url ),
			'%store_title%' => esc_html( $title ),
		);
		$prod_loop_vendor_info = get_option( 'alg_mpwc_product_loop_vendor_info', '<div class="alg-mpwc-product-author"><a href="%store_url%">By %store_title%</a></div>' );
		$prod_loop_vendor_info = str_replace( array_keys( $array_from_to ), $array_from_to, $prod_loop_vendor_info );
		echo wp_kses_post( do_shortcode( $prod_loop_vendor_info ) );
	}

	/**
	 * get_vendor_image.
	 *
	 * @version 1.4.7
	 * @since   1.4.7
	 *
	 * @param null $args
	 *
	 * @return string
	 */
	function get_vendor_image( $args = null ) {
		global $post;
		$args = wp_parse_args( $args, array(
			'img_type'    => 'store_logo', //store_logo | gravatar
			'vendor'        => '',
			'vendor_id'     => '',
			'post_id'       => ! empty( $post ) ? $post->ID : '',
			'gravatar_size' => 32,
			'logo_style'    => ''
		) );
		// Get vendor
		$vendor = $args['vendor'];
		if ( ! is_a( $vendor, 'WP_User' ) ) {
			$vendor_id = ! is_numeric( $args['vendor_id'] ) && is_numeric( $args['post_id'] ) ? get_post_field( 'post_author', intval( $args['post_id'] ) ) : $args['vendor_id'];
			$vendor = is_numeric( $vendor_id ) ? get_user_by( 'id', intval( $vendor_id ) ) : ( is_numeric( $args['post_id'] ) ? get_post_field( 'post_author', intval( $args['post_id'] ) ) : $vendor );
		}
		if ( ! is_a( $vendor, 'WP_User' ) ) {
			return '';
		}
		$fields = new Alg_MPWC_Vendor_Admin_Fields();
		$image  = '';
		if ( 'store_logo' === $args['img_type'] ) {
			$logo_id = filter_var( get_user_meta( $vendor->ID, $fields->meta_logo . '_id', true ), FILTER_VALIDATE_INT );
			if ( $logo_id ) {
				$image = wp_get_attachment_image( $logo_id, 'full', false, array( 'style' => sanitize_text_field( $args['logo_style'] ) ) );
			}
		} elseif ( 'gravatar' === $args['img_type'] ) {
			$image = get_avatar( $vendor, intval( $args['gravatar_size'] ) );
		}
		return $image;
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
