<?php
/**
 * Marketplace for WooCommerce - Core Class
 *
 * @version 1.2.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Core' ) ) {

	class Alg_MPWC_Core extends Alg_WP_Plugin {

		/**
		 * @var Alg_MPWC_CPT_Commission_Recalculator_Bkg_Process
		 */
		public static $bkg_process_commission_recalculator;

		/**
		 * Initializes the plugin.
		 *
		 * Should be called after the set_args() method
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 *
		 * @param array $args
		 */
		public function init() {
			parent::init();

			self::$bkg_process_commission_recalculator = new Alg_MPWC_CPT_Commission_Recalculator_Bkg_Process();

			// Init admin part
			if ( is_admin() ) {
				$this->init_admin_settings();
			}

			if ( filter_var( get_option( Alg_MPWC_Settings_General::OPTION_ENABLE_PLUGIN ), FILTER_VALIDATE_BOOLEAN ) ) {
				$this->setup_plugin();
			}
		}

		/**
		 * Creates widgets
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function create_widgets() {
			register_widget( 'Alg_MPWC_Vendor_Products_Filter_Widget' );
		}

		/**
		 * Initializes admin settings
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function init_admin_settings() {
			new Alg_MPWC_Admin_Settings();
		}

		/**
		 * Setups the plugin
		 *
		 * @version 1.1.9
		 * @since   1.0.0
		 */
		public function setup_plugin() {
			new Alg_MPWC_Vendor_User();
			new Alg_MPWC_Shop_Manager_User();
			add_action( 'widgets_init', array( $this, 'create_widgets' ) );
			add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
			add_action( 'init', array( $this, 'manage_post_types' ), 3 );
			add_action( 'init', array( $this, 'manage_taxonomies' ), 0 );
			add_filter( 'woocommerce_locate_template', array( $this, 'woocommerce_locate_template' ), 10, 3 );
			add_filter( 'woocommerce_locate_core_template', array( $this, 'woocommerce_locate_template' ), 10, 3 );
			add_action( 'save_post_product', array( $this, 'fix_variations_authorship' ), 10, 3 );
			add_action( 'save_post_product', array( $this, 'fix_empty_variation_product_price' ), 10, 3 );
		}

		/**
		 * Fixes variation product price
		 *
		 * When the product is saved, all the variations prices are saved to main product '_price' meta
		 *
		 * @version 1.2.0
		 * @since   1.1.9
		 * @param $post_id
		 * @param $post
		 * @param $update
		 */
		public function fix_empty_variation_product_price( $post_id, $post, $update ) {
			global $woocommerce;
			$product = wc_get_product( $post_id );
			if (
				! $product ||
				! $product->is_type( 'variable' )
				// || empty( get_post_meta( $post_id, '_price', false ) )
			) {
				return;
			}

			$prices = array();
			foreach ( $product->get_available_variations() as $variation_values ) {
				if ( ! empty( $variation_values['display_price'] ) ) {
					$prices[] = $variation_values['display_price'];
				}
				if ( ! empty( $variation_values['display_regular_price'] ) ) {
					$prices[] = $variation_values['display_regular_price'];
				}
			}
			if ( ! empty( $prices ) ) {
				$min_max_prices[] = min( $prices );
				$min_max_prices[] = max( $prices );
				delete_post_meta( $post_id, '_price' );
				foreach ( $min_max_prices as $price ) {
					add_post_meta( $post_id, '_price', $price );
				}
			}
		}

		/**
		 * Set the variations to correct author
		 *
		 * When the product author changes, the variations don't. It fixes that
		 *
		 * @version 1.1.3
		 * @since   1.1.3
		 * @param $post_id
		 * @param $post
		 * @param $update
		 */
		public function fix_variations_authorship( $post_id, $post, $update ) {
			global $woocommerce;
			$product = wc_get_product( $post_id );
			if (
				! $product ||
				! is_a( $product, 'WC_Product_Variable' )
			) {

				return;
			}

			$user      = wp_get_current_user();
			$the_query = new WP_Query( array(
				'post_type'      => 'product_variation',
				'posts_per_page' => - 1,
				'post_parent'    => $post_id,
				'author__not_in' => array( $post->post_author )
			) );

			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					wp_update_post( array(
						'ID'          => get_the_ID(),
						'post_author' => $post->post_author,
					) );
				}
				wp_reset_postdata();
			}
		}

		/**
		 * Override woocommerce locate template
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $template
		 * @param $template_name
		 * @param $template_path
		 *
		 * @return string
		 */
		public function woocommerce_locate_template( $template, $template_name, $template_path ) {
			if ( strpos( $template_name, 'marketplace' ) !== false ) {

				$template_path = 'woocommerce';
				$marketplace   = alg_marketplace_for_wc();
				$default_path  = $marketplace->dir . 'templates' . DIRECTORY_SEPARATOR;
				$template      = locate_template(
					array(
						trailingslashit( $template_path ) . $template_name,
						$template_name,
					)
				);

				// Get default template/
				if ( ! $template || WC_TEMPLATE_DEBUG_MODE ) {
					$template = $default_path . $template_name;
				}
			}
			return $template;
		}

		/**
		 * Creates taxonomies
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function manage_taxonomies() {
			$tax = new Alg_MPWC_Commission_Status_Tax();
			$tax->setup();
			$tax->register();
		}

		/**
		 * Creates custom post types
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function manage_post_types() {
			$cpt = new Alg_MPWC_CPT_Commission();
			$cpt->setup();
			$cpt->register();
		}

		/**
		 * Creates dashboard widgets
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_dashboard_widgets() {
			new Alg_MPWC_Dashboard_Widgets();
		}

		/**
		 * Called when plugin is enabled
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public static function on_plugin_activation() {
			parent::on_plugin_activation();

			// Adds the vendor role
			Alg_MPWC_Vendor_Role::add_vendor_role();
			Alg_MPWC_CPT_Commission::gives_all_caps_to_roles();

			// Creates commission status
			$tax = new Alg_MPWC_Commission_Status_Tax();
			$tax->set_args();
			$tax->register();
			$tax->create_initial_status();

			flush_rewrite_rules();
		}
	}
}
