<?php
/**
 * Marketplace for WooCommerce - Vendor role section
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Alg_MPWC_Settings_Vendor' ) ) {

	class Alg_MPWC_Settings_Vendor extends Alg_MPWC_Settings_Section {

		const OPTION_ROLE_LABEL                    = 'alg_mpwc_opt_vendor_role_label';
		const OPTION_PUBLIC_PAGE_SLUG              = 'alg_mpwc_opt_public_page_slug';
		const OPTION_CAPABILITIES_PUBLISH_PRODUCTS = 'alg_mpwc_opt_vendor_caps_publish_products';
		const OPTION_CAPABILITIES_DELETE_PRODUCTS  = 'alg_mpwc_opt_vendor_caps_delete_products';
		const OPTION_CAPABILITIES_UPLOAD_FILES     = 'alg_mpwc_opt_vendor_caps_upload_files';
		const OPTION_CAPABILITIES_VIEW_ORDERS      = 'alg_mpwc_opt_vendor_caps_view_orders';
		const OPTION_HIDE_VENDOR_WP_INFO           = 'alg_mpwc_opt_hide_vendor_wp_info';

		//const OPTION_COMMISSIONS_BASE               = 'alg_mpwc_opt_commissions_base';
		const OPTION_COMMISSIONS_FIXED_VALUE        = 'alg_mpwc_opt_commissions_fixed_value';
		const OPTION_COMMISSIONS_PERCENTAGE_VALUE   = 'alg_mpwc_opt_commissions_percentage_value';

		const OPTION_COMMISSIONS_AUTOMATIC_CREATION = 'alg_mpwc_opt_commissions_automatic_creation';
		const OPTION_REGISTRY_AUTOMATIC_APPROVAL    = 'alg_mpwc_opt_registry_automatic_approval';
		const OPTION_REGISTRY_CHECKBOX_TEXT         = 'alg_mpwc_opt_registry_checkbox_text';

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
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return string
		 */
		function get_profile_page_url_ex() {
			return '<strong>' . get_home_url() . '/' . sanitize_text_field( get_option( self::OPTION_PUBLIC_PAGE_SLUG, 'marketplace-vendor' ) ) . '/vendor-user-name</strong>';
		}

		/**
		 * get_settings.
		 *
		 * @version 1.0.0
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
					'desc_tip'    => __( 'It hides WordPress logo, footer text "Thank you for creating with WordPress" and disables update notifications', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_HIDE_VENDOR_WP_INFO,
					'default'     => 'yes',
					'type'        => 'checkbox',
				),
				array(
					'type'        => 'sectionend',
					'id'          => 'alg_mpwc_vendors_opt',
				),

				// Commissions
				array(
					'title'       => __( 'Registration', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Options regarding vendors registration', 'marketplace-for-woocommerce' ),
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

				// Public page
				array(
					'title'       => __( 'Public page', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'The public page used to display vendor products', 'marketplace-for-woocommerce' ),
					'type'        => 'title',
					'id'          => 'alg_mpwc_vendors_pp_opt',
				),
				array(
					'title'       => __( 'Page slug', 'marketplace-for-woocommerce' ),
					'desc'        => sprintf( __( 'Slug for the vendor public page. E.g: %s', 'marketplace-for-woocommerce' ), $this->get_profile_page_url_ex() ).'<br />'. '<span style="color:#999">'.sprintf(__( 'If it does not work on the first attempt, please go to <a href="%s">Permalink Settings </a> and save changes', 'marketplace-for-woocommerce' ), admin_url('options-permalink.php') ).'</span>',
					'id'          => self::OPTION_PUBLIC_PAGE_SLUG,
					'default'     => __( 'marketplace-vendor', 'marketplace-for-woocommerce' ),
					'placeholder' => __( 'marketplace-vendor', 'marketplace-for-woocommerce' ),
					'type'        => 'text',
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
					'default'     => 15,
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
					'type'        => 'sectionend',
					'id'          => 'alg_mpwc_comissions_opt',
				),
			);

			return parent::get_settings( array_merge( $settings, $new_settings ) );
		}
	}
}