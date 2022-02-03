<?php
/**
 * Marketplace for WooCommerce - Vendors Section Settings
 *
 * @version 1.5.1
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_MPWC_Settings_Vendor' ) ) :

class Alg_MPWC_Settings_Vendor extends Alg_MPWC_Settings_Section {

	const OPTION_ROLE_LABEL                       = 'alg_mpwc_opt_vendor_role_label';
	const OPTION_PUBLIC_PAGE_SLUG                 = 'alg_mpwc_opt_public_page_slug';
	const OPTION_PUBLIC_PAGE_LOGO                 = 'alg_mpwc_opt_public_page_logo';
	const OPTION_PUBLIC_PAGE_SHOP_LINK_LABEL      = 'alg_mpwc_opt_public_page_shop_link_label';
	const OPTION_CAPABILITIES_PUBLISH_PRODUCTS    = 'alg_mpwc_opt_vendor_caps_publish_products';
	const OPTION_CAPABILITIES_DELETE_PRODUCTS     = 'alg_mpwc_opt_vendor_caps_delete_products';
	const OPTION_CAPABILITIES_UPLOAD_FILES        = 'alg_mpwc_opt_vendor_caps_upload_files';
	const OPTION_CAPABILITIES_VIEW_ORDERS         = 'alg_mpwc_opt_vendor_caps_view_orders';
	const OPTION_CAPABILITIES_ENTER_ADMIN         = 'alg_mpwc_opt_vendor_caps_enter_admin';
	const OPTION_HIDE_VENDOR_WP_INFO              = 'alg_mpwc_opt_hide_vendor_wp_info';

	const OPTION_INCLUDE_TAXES                    = 'alg_mpwc_opt_commissions_include_taxes';
	const OPTION_REGISTRY_AUTOMATIC_APPROVAL      = 'alg_mpwc_opt_registry_automatic_approval';
	const OPTION_REGISTRY_CHECKBOX_TEXT           = 'alg_mpwc_opt_registry_checkbox_text';
	const OPTION_PRODUCT_TAB_TEXT                 = 'alg_mpwc_opt_vendor_product_tab_text';
	const OPTION_PRODUCT_TAB_PRIORITY             = 'alg_mpwc_opt_vendor_product_tab_priority';
	const OPTION_PRODUCT_TAB_ENABLE               = 'alg_mpwc_opt_vendor_product_tab_enable';
	const OPTION_AUTHORSHIP_PRODUCT_LOOP          = 'alg_mpwc_opt_authorship_product_loop';
	const OPTION_PRODUCT_LOOP_VENDOR_INFO_CONTENT = 'alg_mpwc_product_loop_vendor_info';
	const OPTION_REDIRECT_TO_ADMIN                = 'alg_mpwc_opt_redirect_to_admin';

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct( $handle_autoload = true ) {
		$this->id   = 'vendors';
		$this->desc = __( 'Vendors', 'marketplace-for-woocommerce' );
		parent::__construct( $handle_autoload );
	}

	/**
	 * Gets an example of a profile page url.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @param   bool $use_pretty_permalinks
	 * @return  string
	 */
	function get_profile_page_url_ex( $use_pretty_permalinks = true ) {
		return ( $use_pretty_permalinks ?
			'<code>' . get_home_url() . '/' . sanitize_text_field( get_option( self::OPTION_PUBLIC_PAGE_SLUG, 'marketplace-vendor' ) ) . '/vendor-user-name</code>' :
			'<code>' . get_home_url() . '/?alg_mpwc_vendor=1&alg_mpwc_public_page=1&post_type=product' . '</code>' );
	}

	/**
	 * get_settings.
	 *
	 * @version 1.5.1
	 * @since   1.0.0
	 *
	 * @todo    [next] (desc) remove "If it does not work on the first attempt, please go to Permalink Settings and save changes."
	 */
	function get_settings( $settings = null ) {
		$new_settings = array(

			array(
				'title'       => __( 'Vendors Options', 'marketplace-for-woocommerce' ),
				'type'        => 'title',
				'desc'        => __( 'General options regarding vendors.', 'marketplace-for-woocommerce' ),
				'id'          => 'alg_mpwc_vendors_opt',
			),
			array(
				'title'       => __( 'Vendor label', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Vendor role label.', 'marketplace-for-woocommerce' ),
				'id'          => self::OPTION_ROLE_LABEL,
				'default'     => __( 'Marketplace vendor', 'marketplace-for-woocommerce' ),
				'placeholder' => __( 'Marketplace vendor', 'marketplace-for-woocommerce' ),
				'type'        => 'text',
			),
			array(
				'title'       => __( 'Hide WordPress info', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Hide info about Wordpress on vendor\'s admin dashboard', 'marketplace-for-woocommerce' ),
				'desc_tip'    => __( 'Hides WordPress logo, footer text "Thank you for creating with WordPress" and disables update notifications.', 'marketplace-for-woocommerce' ),
				'id'          => self::OPTION_HIDE_VENDOR_WP_INFO,
				'default'     => 'yes',
				'type'        => 'checkbox',
			),
			array(
				'title'       => __( 'Redirect to admin on login', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Redirect vendors to admin after login', 'marketplace-for-woocommerce' ),
				'id'          => self::OPTION_REDIRECT_TO_ADMIN,
				'default'     => 'yes',
				'type'        => 'checkbox',
			),
			array(
				'type'        => 'sectionend',
				'id'          => 'alg_mpwc_vendors_opt',
			),
			array(
				'title' => __( 'Product Loop Info', 'marketplace-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => __( 'Some info that can be displayed on vendor\'s product loop about the vendor itself.', 'marketplace-for-woocommerce' ) . '<br />' .
					sprintf( __( 'You can use the %s filter to setup where the loop info will be displayed.', 'marketplace-for-woocommerce' ), '<a href="'.$this->generate_faq_question_url('What are the filters available?').'" target="_blank"><code>alg_mpwc_loop_vendor_info_hook</code></a>' ),
				'id'    => 'alg_mpwc_product_loop_vendor_info_options',
			),
			array(
				'title'       => __( 'Product loop info', 'marketplace-for-woocommerce' ),
				'desc'        => __( "Display info on vendor's product loop.", 'marketplace-for-woocommerce' ),
				'id'          => self::OPTION_AUTHORSHIP_PRODUCT_LOOP,
				'default'     => 'yes',
				'type'        => 'checkbox',
			),
			array(
				'title'        => __( 'Info\'s content', 'marketplace-for-woocommerce' ),
				'desc'         => __( 'Template variables:', 'marketplace-for-woocommerce' ) . ' ' . alg_marketplace_for_wc()->convert_array_to_string( array( 'store_url', 'store_title' ), array( 'item_template' => '<code>%{value}%</code>' ) ) . '<br />' .
					__( 'Shortcodes:', 'marketplace-for-woocommerce' ) . ' ' . alg_marketplace_for_wc()->convert_array_to_string( array( '<a href="'.$this->generate_faq_question_url('What are the shortcodes available?').'" target="_blank">[alg_mpwc_vendor_img]</a>' ), array( 'item_template' => '<code>{value}</code>' ) ),
				'desc_tip'     => __( "Info displayed on vendor products.", 'marketplace-for-woocommerce' ),
				'id'           => self::OPTION_PRODUCT_LOOP_VENDOR_INFO_CONTENT,
				'default'      => '<div class="alg-mpwc-product-author"><a href="%store_url%">By %store_title%</a></div>',
				'type'         => 'textarea',
				'css'          => 'height:150px;',
			),
			array(
				'type'        => 'sectionend',
				'id'          => 'alg_mpwc_product_loop_vendor_info_options',
			),
			// Registration
			array(
				'title'       => __( 'Registration', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Options regarding vendors registration.', 'marketplace-for-woocommerce' ) . ' ' .
					'<strong>' . __( 'Note:', 'marketplace-for-woocommerce' ) . ' ' . '</strong>' . sprintf( __( "It's required to enable %s option in %s.", 'marketplace-for-woocommerce' ),
						'<strong>' . __( 'Allow customers to create an account on the "My account" page', 'woocommerce' ) . '</strong>',
						'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=account' ) . '">' .
							__( 'WooCommerce', 'woocommerce' ) .' > ' . __( 'Settings', 'woocommerce' ) . ' > ' . __( 'Accounts &amp; Privacy', 'woocommerce' ) . '</a>' ),
				'type'        => 'title',
				'id'          => 'alg_mpwc_registry_opt',
			),
			array(
				'title'       => __( 'Automatic approval', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Allow', 'marketplace-for-woocommerce' ),
				'desc_tip'    => __( 'Allows users to be automatically approved as vendors on registration, bypassing the pending status.', 'marketplace-for-woocommerce' ),
				'id'          => self::OPTION_REGISTRY_AUTOMATIC_APPROVAL,
				'default'     => 'no',
				'type'        => 'checkbox',
			),
			array(
				'title'       => __( 'Checkbox text', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'The text displayed to users that want to become vendors.', 'marketplace-for-woocommerce' ),
				'desc_tip'    => __( 'Leave empty to disable the checkbox.', 'marketplace-for-woocommerce' ),
				'id'          => self::OPTION_REGISTRY_CHECKBOX_TEXT,
				'default'     => __( 'Apply for becoming a vendor', 'marketplace-for-woocommerce' ),
				'class'       => 'regular-input',
				'type'        => 'text',
			),
			array(
				'type'        => 'sectionend',
				'id'          => 'alg_mpwc_registry_opt',
			),

			// Product Tab
			array(
				'title'       => __( 'Product Tab', 'marketplace-for-woocommerce' ),
				'type'        => 'title',
				'desc'        => __( "Tab displayed on vendor's product pages with some info about vendor themselves.", 'marketplace-for-woocommerce' ),
				'id'          => 'alg_mpwc_product_tab',
			),
			array(
				'title'       => __( 'Product tab', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Enable', 'marketplace-for-woocommerce' ),
				'desc_tip'    => __( 'Enables the product tab.', 'marketplace-for-woocommerce' ),
				'id'          => self::OPTION_PRODUCT_TAB_ENABLE,
				'default'     => 'yes',
				'type'        => 'checkbox',
			),
			array(
				'title'       => __( 'Priority', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Controls the tab position. A lower priority will make it appear before other tabs.', 'marketplace-for-woocommerce' ),
				'id'          => self::OPTION_PRODUCT_TAB_PRIORITY,
				'default'     => 40,
				'type'        => 'number',
			),
			array(
				'title'       => __( 'Label', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Tab title.', 'marketplace-for-woocommerce' ),
				'id'          => self::OPTION_PRODUCT_TAB_TEXT,
				'default'     => __( 'Vendor', 'marketplace-for-woocommerce' ),
				'type'        => 'text',
			),
			array(
				'title'       => __( 'Content', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Tab content.', 'marketplace-for-woocommerce' ) . '<br>' .
					sprintf( __( 'Available placeholders: %s.', 'marketplace-for-woocommerce' ), '<code>' . implode( '</code>, <code>', array(
							'%image%',
							'%image_link%',
							'%title%',
							'%formatted_title%',
							'%description%',
							'%public_page_url%',
							'%public_page_link%',
							'%formatted_public_page_link%',
							'%vendor_id%',
						) ) . '</code>' ) . '<br>' .
					sprintf( __( 'You can also use shortcodes here, e.g. %s.', 'marketplace-for-woocommerce' ), '<code>[vendor_rating]</code>' ),
				'id'          => 'alg_mpwc_opt_vendor_product_tab_content',
				'default'     => '%image_link%' . PHP_EOL . PHP_EOL . '%formatted_title%' . PHP_EOL . PHP_EOL . '%description%' . PHP_EOL . PHP_EOL . '%formatted_public_page_link%',
				'type'        => 'textarea',
				'css'         => 'height:150px;',
			),
			array(
				'type'        => 'sectionend',
				'id'          => 'alg_mpwc_product_tab',
			),

			// Public page
			array(
				'title'       => __( 'Public Page', 'marketplace-for-woocommerce' ),
				'desc'        => __( "The public page that displays vendor's info and its products.", 'marketplace-for-woocommerce' ),
				'type'        => 'title',
				'id'          => 'alg_mpwc_vendors_pp_opt',
			),
			array(
				'title'       => __( 'Page slug', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Slug for the vendor public page.', 'marketplace-for-woocommerce' ) . ' ' .
					__( 'E.g.:', 'marketplace-for-woocommerce' ) . '<br>' .
						'- ' . $this->get_profile_page_url_ex() . '<br>' .
						'- ' . $this->get_profile_page_url_ex( false ) . '<br>' .
						'<span style="color:#999">' .
							sprintf( __( 'If it does not work on the first attempt, please go to <a href="%s">Permalink Settings</a> and save changes.', 'marketplace-for-woocommerce' ),
								admin_url( 'options-permalink.php' ) ) .
						'</span>',
				'id'          => self::OPTION_PUBLIC_PAGE_SLUG,
				'default'     => __( 'marketplace-vendor', 'marketplace-for-woocommerce' ),
				'placeholder' => __( 'marketplace-vendor', 'marketplace-for-woocommerce' ),
				'type'        => 'text',
			),
			array(
				'title'       => __( 'Shop link label', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Text pointing to shop page showing results filtered by vendor.', 'marketplace-for-woocommerce' ),
				'desc_tip'    => __( "Leave it empty if you don't want to show it.", 'marketplace-for-woocommerce' ),
				'id'          => self::OPTION_PUBLIC_PAGE_SHOP_LINK_LABEL,
				'default'     => __( "See all vendor's products", 'marketplace-for-woocommerce' ),
				'type'        => 'text',
			),
			array(
				'title'       => __( 'Logo', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Show', 'marketplace-for-woocommerce' ),
				'desc_tip'    => __( "Shows vendor's logo.", 'marketplace-for-woocommerce' ),
				'id'          => self::OPTION_PUBLIC_PAGE_LOGO,
				'default'     => 'yes',
				'type'        => 'checkbox',
			),
			array(
				'title'       => __( 'Rating', 'marketplace-for-woocommerce' ),
				'desc'        => __( 'Show', 'marketplace-for-woocommerce' ),
				'desc_tip'    => __( "Shows vendor's rating.", 'marketplace-for-woocommerce' ) . '<br>' .
					sprintf( __( 'Alternatively you can use shortcode: %s', 'marketplace-for-woocommerce' ),
						'<code>' . esc_html( '[vendor_rating]<div class="alg-mpwc-vendor-rating">%rating_html%</div>[/vendor_rating]' ) . '</code>' ),
				'id'          => 'alg_mpwc_opt_public_page_rating',
				'default'     => 'no',
				'type'        => 'checkbox',
			),
			array(
				'desc'        => __( 'Template.', 'marketplace-for-woocommerce' ) . '<br>' .
					sprintf( __( 'Available placeholders: %s.', 'marketplace-for-woocommerce' ), '<code>' . implode( '</code>, <code>', array(
							'%rating_html%',
							'%rating%',
							'%count%',
							'%rated_products%',
							'%total_products%',
							'%vendor_id%',
						) ) . '</code>' ),
				'id'          => 'alg_mpwc_opt_public_page_rating_template',
				'default'     => '<div class="alg-mpwc-vendor-rating">%rating_html%</div>',
				'type'        => 'textarea',
			),
			array(
				'type'        => 'sectionend',
				'id'          => 'alg_mpwc_vendors_pp_opt',
			),

			// Capabilities
			array(
				'title' => __( 'Capabilities', 'marketplace-for-woocommerce' ),
				'desc'  => __( 'What vendors will be allowed to do', 'marketplace-for-woocommerce' ),
				'type'  => 'title',
				'id'    => 'alg_mpwc_vendors_caps_opt',
			),
			array(
				'title'   => __( 'Access the dashboard', 'marketplace-for-woocommerce' ),
				'desc'    => __( 'Allow vendors to access the admin dashboard', 'marketplace-for-woocommerce' ),
				'id'      => self::OPTION_CAPABILITIES_ENTER_ADMIN,
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Publish products', 'marketplace-for-woocommerce' ),
				'desc'    => __( 'Allow vendors to publish products automatically, bypassing the pending status', 'marketplace-for-woocommerce' ),
				'id'      => self::OPTION_CAPABILITIES_PUBLISH_PRODUCTS,
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Delete products', 'marketplace-for-woocommerce' ),
				'desc'    => __( 'Allow vendors to delete products', 'marketplace-for-woocommerce' ) . ' ' .
				             __( 'Be careful with possible 404 pages.', 'marketplace-for-woocommerce' ),
				'id'      => self::OPTION_CAPABILITIES_DELETE_PRODUCTS,
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Upload files', 'marketplace-for-woocommerce' ),
				'desc'    => __( 'Allow vendors to upload files', 'marketplace-for-woocommerce' ),
				'id'      => self::OPTION_CAPABILITIES_UPLOAD_FILES,
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'View orders', 'marketplace-for-woocommerce' ),
				'desc'    => __( 'Allow vendors to view orders', 'marketplace-for-woocommerce' ),
				'id'      => self::OPTION_CAPABILITIES_VIEW_ORDERS,
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'type'        => 'sectionend',
				'id'          => 'alg_mpwc_vendors_caps_opt',
			),

		);

		return parent::get_settings( array_merge( $settings, $new_settings ) );
	}

}

endif;
