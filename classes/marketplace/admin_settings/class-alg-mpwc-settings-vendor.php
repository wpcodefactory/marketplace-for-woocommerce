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
		const OPTION_PROFILE_PAGE_SLUG             = 'alg_mpwc_opt_profile_page_slug';
		const OPTION_CAPABILITIES_PUBLISH_PRODUCTS = 'alg_mpwc_opt_vendor_caps_publish_posts';
		const OPTION_COMISSIONS_BASE               = 'alg_mpwc_opt_comissions_base';

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
			$user = wp_get_current_user();
			return '<strong>' . get_home_url() . '/' . sanitize_text_field( get_option( self::OPTION_PROFILE_PAGE_SLUG, 'marketplace-vendor' ) ) . '/' . $user->data->user_nicename . '</strong>';
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
					'title'       => __( 'Profile slug', 'marketplace-for-woocommerce' ),
					'desc'        => sprintf( __( 'Slug for the individual vendor page. E.g: %s', 'marketplace-for-woocommerce' ), $this->get_profile_page_url_ex() ).'<br />'. '<span style="color:#999">'.sprintf(__( 'If it does not work on the first attempt, please go to <a href="%s">Permalink Settings </a> and save changes', 'marketplace-for-woocommerce' ), admin_url('options-permalink.php') ).'</span>',
					//'desc_tip'    => sprintf(__( 'If it does not work on the first attempt, please go to <a href="%s">Permalink Settings </a> and save changes', 'marketplace-for-woocommerce' ), admin_url('options-permalink.php') ),
					'id'          => self::OPTION_PROFILE_PAGE_SLUG,
					'default'     => __( 'marketplace-vendor', 'marketplace-for-woocommerce' ),
					'placeholder' => __( 'marketplace-vendor', 'marketplace-for-woocommerce' ),
					'type'        => 'text',
				),
				array(
					'type'        => 'sectionend',
					'id'          => 'alg_mpwc_vendors_opt',
				),
				array(
					'title'       => __( 'Capabilities', 'marketplace-for-woocommerce' ),
					'type'        => 'title',
					'id'          => 'alg_mpwc_vendors_caps_opt',
				),
				array(
					'title'       => __( 'Publish products', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Allows the vendors to publish products automatically, bypassing the pending status', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_CAPABILITIES_PUBLISH_PRODUCTS,
					'default'     => 'no',
					'type'        => 'checkbox',
				),
				array(
					'type'        => 'sectionend',
					'id'          => 'alg_mpwc_vendors_caps_opt',
				),
				array(
					'title'       => __( 'Comissions', 'marketplace-for-woocommerce' ),
					'type'        => 'title',
					'id'          => 'alg_mpwc_comissions_opt',
				),
				array(
					'title'       => __( 'Base', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'How vendors will receive their comissions for sales', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_COMISSIONS_BASE,
					'default'     => 'percentage',
					'options'     => array(
						'percentage'  => __( 'By percentage', 'marketplace-for-woocommerce' ),
						'fixed_value' =>sprintf(  __( 'By fixed value (in %s)', 'marketplace-for-woocommerce' ), '<strong>'.get_woocommerce_currency().'</strong>'),
					),
					'type'        => 'select',
					'class'       =>'chosen_select'
				),
				array(
					'title'       => __( 'Value', 'marketplace-for-woocommerce' ),
					'desc'        => __( 'Value that will be transfered to vendors after an order is complete', 'marketplace-for-woocommerce' ),
					'id'          => self::OPTION_COMISSIONS_BASE,
					'default'     => '15',
					'options'     => array( 'percentage' => 'By percentage', 'fixed_value' => 'By fixed value' ),
					'type'        => 'number',
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