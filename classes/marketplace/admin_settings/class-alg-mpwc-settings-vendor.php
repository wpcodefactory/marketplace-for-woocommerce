<?php
/**
 * Marketplace for WooCommerce - Vendor role section
 *
 * @version 1.1.2
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Alg_MPWC_Settings_Vendor' ) ) {

	class Alg_MPWC_Settings_Vendor extends Alg_MPWC_Settings_Section {

		const OPTION_ROLE_LABEL                      = 'alg_mpwc_opt_vendor_role_label';
		const OPTION_PUBLIC_PAGE_SLUG                = 'alg_mpwc_opt_public_page_slug';
		const OPTION_PUBLIC_PAGE_LOGO                = 'alg_mpwc_opt_public_page_logo';
		const OPTION_PUBLIC_PAGE_SHOP_LINK_LABEL     = 'alg_mpwc_opt_public_page_shop_link_label';
		const OPTION_CAPABILITIES_PUBLISH_PRODUCTS   = 'alg_mpwc_opt_vendor_caps_publish_products';
		const OPTION_CAPABILITIES_DELETE_PRODUCTS    = 'alg_mpwc_opt_vendor_caps_delete_products';
		const OPTION_CAPABILITIES_UPLOAD_FILES       = 'alg_mpwc_opt_vendor_caps_upload_files';
		const OPTION_CAPABILITIES_VIEW_ORDERS        = 'alg_mpwc_opt_vendor_caps_view_orders';
		const OPTION_HIDE_VENDOR_WP_INFO             = 'alg_mpwc_opt_hide_vendor_wp_info';
		const OPTION_COMMISSIONS_FIXED_VALUE         = 'alg_mpwc_opt_commissions_fixed_value';
		const OPTION_COMMISSIONS_PERCENTAGE_VALUE    = 'alg_mpwc_opt_commissions_percentage_value';
		const OPTION_COMMISSIONS_AUTOMATIC_CREATION  = 'alg_mpwc_opt_commissions_automatic_creation';
		const OPTION_COMMISSIONS_ORDER_REFUND_STATUS = 'alg_mpwc_opt_commissions_order_refund_status';
		const OPTION_INCLUDE_TAXES                   = 'alg_mpwc_opt_commissions_include_taxes';
		const OPTION_REGISTRY_AUTOMATIC_APPROVAL     = 'alg_mpwc_opt_registry_automatic_approval';
		const OPTION_REGISTRY_CHECKBOX_TEXT          = 'alg_mpwc_opt_registry_checkbox_text';
		const OPTION_PRODUCT_TAB_TEXT                = 'alg_mpwc_opt_vendor_product_tab_text';
		const OPTION_PRODUCT_TAB_PRIORITY            = 'alg_mpwc_opt_vendor_product_tab_priority';
		const OPTION_PRODUCT_TAB_ENABLE              = 'alg_mpwc_opt_vendor_product_tab_enable';
		const OPTION_AUTHORSHIP_PRODUCT_LOOP         = 'alg_mpwc_opt_authorship_product_loop';
		const OPTION_REDIRECT_TO_ADMIN               = 'alg_mpwc_opt_redirect_to_admin';

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
		 * Gets an example of a profile page url
		 *
		 * @version 1.0.1
		 * @since   1.0.0
		 *
		 * @param bool $use_pretty_permalinks
		 *
		 * @return string
		 */
		function get_profile_page_url_ex( $use_pretty_permalinks = true ) {
			if ( $use_pretty_permalinks ) {
				return '<strong>' . get_home_url() . '/' . sanitize_text_field( get_option( self::OPTION_PUBLIC_PAGE_SLUG, 'marketplace-vendor' ) ) . '/vendor-user-name</strong>';
			} else {
				return '<strong>'.get_home_url() . '/?alg_mpwc_vendor=1&alg_mpwc_public_page=1&post_type=product'.'</strong>';
			}
		}

		/**
		 * get_settings.
		 *
		 * @version 1.1.2
		 * @since   1.0.0
		 */
		function get_settings( $settings = null ) {
			$new_settings = array(
				array(
					'title'       => __( 'Vendors options', 'marketplace-for-woocommerce' ),
					'type'        => 'title',
					'desc'        => __( 'General options regarding vendors', 'marketplace-for-woocommerce' ),
					'id'          => 'alg_mpwc_vendors_opt',
				),
				array(
					'title'       => __( 'Vendor label', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Vendor role label', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_ROLE_LABEL,
					'default'     => __( 'Marketplace vendor', 'marketplace-for-woocommerce' ),
					'placeholder' => __( 'Marketplace vendor', 'marketplace-for-woocommerce' ),
					'type'        => 'text',
				),
				array(
					'title'       => __( 'Hide WordPress info', 'marketplace-for-woocommerce' ),
					'desc'        => __( "Hides info about Wordpress on vendor's admin dashboard", 'marketplace-for-woocommerce' ),
					'desc_tip'    => __( 'Hides WordPress logo, footer text "Thank you for creating with WordPress" and disables update notifications', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_HIDE_VENDOR_WP_INFO,
					'default'     => 'yes',
					'type'        => 'checkbox',
				),
				array(
					'title'       => __( 'Authorship on product loop', 'marketplace-for-woocommerce' ),
					'desc'        => __( "Displays a link (By vendor) on product loop pointing to vendor's public page", 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_AUTHORSHIP_PRODUCT_LOOP,
					'default'     => 'yes',
					'type'        => 'checkbox',
				),
				array(
					'title'       => __( 'Redirect to admin on login', 'marketplace-for-woocommerce' ),
					'desc'        => __( "Redirects vendors to admin after login", 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_REDIRECT_TO_ADMIN,
					'default'     => 'yes',
					'type'        => 'checkbox',
				),
				array(
					'type'        => 'sectionend',
					'id'          => 'alg_mpwc_vendors_opt',
				),

				// Registration
				array(
					'title'       => __( 'Registration', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Options regarding vendors registration.', 'marketplace-for-woocommerce' ).'<br /><strong>'.__( 'Note: ', 'marketplace-for-woocommerce' ).'</strong>'.sprintf(__( "It's required to enable <strong>Customer registration</strong> on <a href='%s'>WooCommerce > Settings > Accounts</a>", 'marketplace-for-woocommerce' ),admin_url('admin.php?page=wc-settings&tab=account')),
					'type'        => 'title',
					'id'          => 'alg_mpwc_registry_opt',
				),
				array(
					'title'       => __( 'Automatic approval', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Allows users to be automatically approved as vendors on registration, bypassing the pending status', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_REGISTRY_AUTOMATIC_APPROVAL,
					'default'     => 'no',
					'type'        => 'checkbox',
				),
				array(
					'title'       => __( 'Checkbox text', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'The text displayed to users that want to become vendors', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_REGISTRY_CHECKBOX_TEXT,
					'default'     => __('Apply for becoming a vendor', 'marketplace-for-woocommerce' ),
					'class'       => 'regular-input',
					'type'        => 'text',
				),
				array(
					'type'        => 'sectionend',
					'id'          => 'alg_mpwc_registry_opt',
				),

				// Product Tab
				array(
					'title'       => __( 'Product tab', 'marketplace-for-woocommerce' ),
					'type'        => 'title',
					'desc'        => __( "Tab displayed on vendor's product pages with some info about vendor themselves", 'marketplace-for-woocommerce' ),
					'id'          => 'alg_mpwc_product_tab',
				),
				array(
					'title'       => __( 'Enable', 'marketplace-for-woocommerce' ),
					'desc'        => __( "Enables the product tab", 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_PRODUCT_TAB_ENABLE,
					'default'     => 'yes',
					'type'        => 'checkbox',
				),
				array(
					'title'       => __( 'Priority', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Controls the tab position. A lower priority will make it appear before other tabs', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_PRODUCT_TAB_PRIORITY,
					'default'     => 40,
					'type'        => 'number',
				),
				array(
					'title'       => __( 'Label', 'marketplace-for-woocommerce' ),
					'desc'        => __( "Text displayed in the tab", 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_PRODUCT_TAB_TEXT,
					'default'     => __( 'Vendor', 'marketplace-for-woocommerce' ),
					'type'        => 'text',
				),
				array(
					'type'        => 'sectionend',
					'id'          => 'alg_mpwc_product_tab',
				),

				// Public page
				array(
					'title'       => __( 'Public page', 'marketplace-for-woocommerce' ),
					'desc'        => __( "The public page that displays vendor's info and its products", 'marketplace-for-woocommerce' ),
					'type'        => 'title',
					'id'          => 'alg_mpwc_vendors_pp_opt',
				),
				array(
					'title'       => __( 'Page slug', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Slug for the vendor public page.', 'marketplace-for-woocommerce' ).' '.__( 'E.g', 'marketplace-for-woocommerce' ).'<br />- '.$this->get_profile_page_url_ex().'<br />- '.$this->get_profile_page_url_ex(false).'<br />'.'<span style="color:#999">'.sprintf(__( 'If it does not work on the first attempt, please go to <a href="%s">Permalink Settings </a> and save changes', 'marketplace-for-woocommerce' ), admin_url('options-permalink.php') ).'</span>',
					//'desc_tip'    => __( 'Possible public page URL examples:', 'marketplace-for-woocommerce' ), $this->get_profile_page_url_ex().'<br />',
					'id'          => self::OPTION_PUBLIC_PAGE_SLUG,
					'default'     => __( 'marketplace-vendor', 'marketplace-for-woocommerce' ),
					'placeholder' => __( 'marketplace-vendor', 'marketplace-for-woocommerce' ),
					'type'        => 'text',
				),
				array(
					'title'       => __( 'Shop link label', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Text pointing to shop page showing results filtered by vendor', 'marketplace-for-woocommerce' ),
					'desc_tip'    => __( "Leave it empty if you don't want to show it", 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_PUBLIC_PAGE_SHOP_LINK_LABEL,
					'default'     => __( "See all vendor's products", 'marketplace-for-woocommerce' ),
					'type'        => 'text',
				),
				array(
					'title'       => __( 'Logo', 'marketplace-for-woocommerce' ),
					'desc'        => __( "Show vendor's logo", 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_PUBLIC_PAGE_LOGO,
					'default'     => 'yes',
					'type'        => 'checkbox',
				),
				array(
					'type'        => 'sectionend',
					'id'          => 'alg_mpwc_vendors_pp_opt',
				),

				// Capabilities
				array(
					'title'       => __( 'Capabilities', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'What vendors will be allowed to do', 'marketplace-for-woocommerce' ),
					'type'        => 'title',
					'id'          => 'alg_mpwc_vendors_caps_opt',
				),
				array(
					'title'       => __( 'Publish products', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Allows vendors to publish products automatically, bypassing the pending status', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_CAPABILITIES_PUBLISH_PRODUCTS,
					'default'     => 'no',
					'type'        => 'checkbox',
				),
				array(
					'title'       => __( 'Delete products', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Allows vendors to delete products', 'marketplace-for-woocommerce' ),
					'desc_tip'    => __( 'Be careful with possible 404 pages', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_CAPABILITIES_DELETE_PRODUCTS,
					'default'     => 'no',
					'type'        => 'checkbox',
				),
				array(
					'title'       => __( 'Upload files', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Allows vendors to upload files', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_CAPABILITIES_UPLOAD_FILES,
					'default'     => 'yes',
					'type'        => 'checkbox',
				),
				array(
					'title'       => __( 'View orders', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Allows vendors to view orders', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_CAPABILITIES_VIEW_ORDERS,
					'default'     => 'no',
					'type'        => 'checkbox',
				),
				array(
					'type'        => 'sectionend',
					'id'          => 'alg_mpwc_vendors_caps_opt',
				),

				// Commissions
				array(
					'title'       => __( 'Commissions', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Money that need to be transferred to vendors after a sale is made', 'marketplace-for-woocommerce' ),
					'type'        => 'title',
					'id'          => 'alg_mpwc_comissions_opt',
				),
				/*array(
					'title'       => __( 'Include taxes', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Include taxes in commissions', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_INCLUDE_TAXES,
					'default'     => 'no',
					'type'        => 'checkbox',
				),*/
				array(
					'title'       => __( 'Fixed Value', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Fixed value that will be transfered to vendors after a sale is made', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_COMMISSIONS_FIXED_VALUE,
					'default'     => 0,
					'type'        => 'number',
				),
				array(
					'title'       => __( 'Percentage Value', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Percentage value that will be transfered to vendors after a sale is made', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_COMMISSIONS_PERCENTAGE_VALUE,
					'default'     => 80,
					'type'        => 'number',
				),
				array(
					'title'       => __( 'Creation status', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'When orders change to one of these status, correspondent commissions will be automatically created', 'marketplace-for-woocommerce' ),
					'desc_tip'    => __( 'Note 1: Leave it empty if you do not want to create commissions automatically', 'marketplace-for-woocommerce' ) . '<br /><br />' . __( 'Note 2: If you select 2 or more status, commissions will not be created twice, no worries.', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_COMMISSIONS_AUTOMATIC_CREATION,
					'default'     => array('wc-completed'),
					'options'     => wc_get_order_statuses(),
					'type'        => 'multiselect',
					'class'       => 'chosen_select'
				),
				array(
					'title'       => __( 'Refund status', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'When orders change to one of these status, correspondent commissions will be automatically set as "Need Refund"', 'marketplace-for-woocommerce' ),
					'desc_tip'    => __( 'Note 1: Leave it empty if you do not want to set commissions automatically to "Need Refund"', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_COMMISSIONS_ORDER_REFUND_STATUS,
					'default'     => array('wc-refunded','wc-cancelled','wc-failed'),
					'options'     => wc_get_order_statuses(),
					'type'        => 'multiselect',
					'class'       => 'chosen_select'
				),
				array(
					'type'        => 'sectionend',
					'id'          => 'alg_mpwc_comissions_opt',
				),
			);

			return parent::get_settings( array_merge( $settings, $new_settings ) );
		}
	}
}