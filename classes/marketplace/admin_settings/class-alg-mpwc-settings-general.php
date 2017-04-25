<?php
/**
 * Marketplace for WooCommerce - General section
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Alg_MPWC_Settings_General' ) ) {

	class Alg_MPWC_Settings_General extends Alg_MPWC_Settings_Section {

		const OPTION_ENABLE_PLUGIN = 'alg_mpwc_opt_enable';

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct( $handle_autoload = true ) {
			$this->id   = '';
			$this->desc = __( 'General', 'marketplace-for-woocommerce' );
			parent::__construct( $handle_autoload );
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
					'title'    => __( 'Marketplace options', 'marketplace-for-woocommerce' ),
					'type'     => 'title',
					'id'       => 'alg_mpwc_opt',
				),
				array(
					'title'    => __( 'Enable Marketplace', 'marketplace-for-woocommerce' ),
					'desc'     => sprintf( __( 'Enable <strong>"%s"</strong> plugin', 'marketplace-for-woocommerce' ), __( 'Marketplace for WooCommerce' ) ),
					'id'       => self::OPTION_ENABLE_PLUGIN,
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'type'     => 'sectionend',
					'id'       => 'alg_mpwc_opt',
				),
			);

			return parent::get_settings( array_merge( $settings, $new_settings ) );
		}
	}
}