<?php
/**
 * Marketplace for WooCommerce - Settings Section
 *
 * @version 1.5.7
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_MPWC_Settings_Section' ) ) :

	class Alg_MPWC_Settings_Section {

		/**
		 * Id.
		 *
		 * @since 1.5.7
		 */
		protected $id;

		/**
		 * Desc.
		 *
		 * @since 1.5.7
		 */
		protected $desc;

		protected $settings;
		protected $handle_autoload = true;

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct( $handle_autoload = true ) {
			$this->handle_autoload = $handle_autoload;
			if ( $this->handle_autoload ) {
				$this->get_settings( array() );
				$this->handle_autoload();
			}
			add_filter( 'woocommerce_get_sections_alg_mpwc', array( $this, 'settings_section' ) );
			add_filter( 'woocommerce_get_settings_alg_mpwc_' . $this->id, array(
				$this,
				'get_settings',
			), PHP_INT_MAX );
		}

		/**
		 * generate_faq_question_url.
		 *
		 * @version 1.4.8
		 * @since   1.0.0
		 *
		 * @param $question
		 *
		 * @return string
		 */
		function generate_faq_question_url( $question ) {
			return 'https://wordpress.org/plugins/marketplace-for-woocommerce/#' . rawurlencode( strtolower( $question ) );
		}

		/**
		 * get_settings.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function get_settings( $settings = array() ) {
			$this->settings = $settings;

			return $this->settings;
		}

		/**
		 * handle_autoload.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function handle_autoload() {
			foreach ( $this->settings as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					$autoload = isset( $value['autoload'] ) ? ( bool ) $value['autoload'] : true;
					add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
				}
			}
		}

		/**
		 * settings_section.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function settings_section( $sections ) {
			$sections[ $this->id ] = $this->desc;

			return $sections;
		}

	}

endif;
