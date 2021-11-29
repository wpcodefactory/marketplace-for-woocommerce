<?php
/**
 * Marketplace for WooCommerce - Core Class
 *
 * @version 1.4.3
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_MPWC_Core' ) ) :

class Alg_MPWC_Core extends Alg_WP_Plugin {

	/**
	 * vendor_user.
	 *
	 * @version 1.4.7
	 * @since   1.4.7
	 *
	 * @var Alg_MPWC_Vendor_User
	 */
	public $vendor_user;

	/**
	 * @var Alg_MPWC_CPT_Commission_Recalculator_Bkg_Process
	 */
	public static $bkg_process_commission_recalculator;

	/**
	 * Initializes the plugin.
	 *
	 * Should be called after the set_args() method
	 *
	 * @version 1.4.2
	 * @since   1.0.0
	 *
	 * @param   array $args
	 */
	public function init() {
		parent::init();

		self::$bkg_process_commission_recalculator = new Alg_MPWC_CPT_Commission_Recalculator_Bkg_Process();

		// Init admin part
		if ( is_admin() ) {
			$this->init_admin_settings();
			add_action( 'add_meta_boxes', array( $this, 'add_order_meta_box' ) );
		}

		if ( filter_var( get_option( Alg_MPWC_Settings_General::OPTION_ENABLE_PLUGIN ), FILTER_VALIDATE_BOOLEAN ) ) {
			$this->setup_plugin();
		}

		// Shortcodes
		add_shortcode( 'vendor_rating', array( $this, 'vendor_rating_shortcode' ) );
		add_shortcode( 'alg_mpwc_vendor_img', array( $this, 'alg_mpwc_vendor_img' ) );
	}

	/**
	 * alg_mpwc_vendor_img.
	 *
	 * @version 1.4.7
	 * @since   1.4.7
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	function alg_mpwc_vendor_img( $atts ) {
		$atts  = empty( $atts ) ? array() : $atts;
		$atts  = shortcode_atts( $atts, $atts, 'alg_mpwc_vendor_img' );
		$image = $this->vendor_user->get_vendor_image( $atts );
		return $image;
	}

	/**
	 * add_order_meta_box.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	public function add_order_meta_box() {
		if ( get_post_meta( get_the_ID(), '_alg_mpwc_related_commissions', true ) ) {
			add_meta_box( 'alg-wc-mp-order_meta-box', __( 'Related Commissions', 'marketplace-for-woocommerce' ), array( $this, 'create_order_meta_box' ), 'shop_order' );
		}
	}

	/**
	 * create_order_meta_box.
	 *
	 * @version 1.3.3
	 * @since   1.3.0
	 */
	public function create_order_meta_box() {
		$related_commissions = get_post_meta( get_the_ID(), '_alg_mpwc_related_commissions' );
		$rows = array();
		foreach ( $related_commissions as $related_commission_id ) {

			if ( false === get_post_status( $related_commission_id ) ) {
				continue;
			}

			$link             = '<a href="' . admin_url( "post.php?post={$related_commission_id}&action=edit" ) . '">' . '#' . $related_commission_id . '</a>';

			$status           = wp_get_object_terms( $related_commission_id, 'alg_mpwc_c_status_tax', array( 'fields' => 'slugs' ) );

			$author           = get_post_meta( $related_commission_id, '_alg_mpwc_author_id', true );
			$products         = get_post_meta( $related_commission_id, '_alg_mpwc_product_ids', true );
			$fixed_value      = get_post_meta( $related_commission_id, '_alg_mpwc_comission_fixed_value', true );
			$percentage_value = get_post_meta( $related_commission_id, '_alg_mpwc_comission_percentage_value', true );
			$final_value      = get_post_meta( $related_commission_id, '_alg_mpwc_comission_final_value', true );
			$currency         = get_post_meta( $related_commission_id, '_alg_mpwc_currency', true );

			if ( $status ) {
				if ( ! is_array( $status ) ) {
					$status = array( $status );
				}
				$status = implode( ', ', $status );
			}

			if ( $author && ( $user = get_user_by( 'ID', $author ) ) ) {
				$author = '<a href="' . admin_url( "user-edit.php?user_id={$author}" ) . '">' . $user->user_nicename . '</a>';
			}

			if ( $products ) {
				if ( ! is_array( $products ) ) {
					$products = array( $products );
				}
				$products = array_map( array( $this, 'get_product_link' ), $products );
				$products = implode( ', ', $products );
			}

			if ( ! $fixed_value ) {
				$fixed_value = 0;
			}

			if ( ! $percentage_value ) {
				$percentage_value = 0;
			}
			$percentage_value .= '%';

			if ( ! $final_value ) {
				$final_value = 0;
			}
			$final_value = wc_price( $final_value, array( 'currency' => $currency ) );

			$row = array(
				$link,
				$status,
				$author,
				$products,
				$fixed_value,
				$percentage_value,
				$final_value,
			);

			$rows[] = '<td>' . implode( '</td><td>', $row ) . '</td>';
		}
		if ( ! empty( $rows ) ) {
			$headers = array(
				__( 'Commission ID', 'marketplace-for-woocommerce' ),
				__( 'Status', 'marketplace-for-woocommerce' ),
				__( 'Author', 'marketplace-for-woocommerce' ),
				__( 'Product(s)', 'marketplace-for-woocommerce' ),
				__( 'Fixed Value', 'marketplace-for-woocommerce' ),
				__( 'Percentage Value', 'marketplace-for-woocommerce' ),
				__( 'Final Value', 'marketplace-for-woocommerce' ),
			);
			echo '<table class="widefat striped">' .
				'<tr>' . '<th>' . implode( '</th><th>', $headers ) . '</th>' . '</tr>' .
				'<tr>' . implode( '</tr><tr>', $rows ) . '</tr>' .
			'</table>';
		} else {
			echo '<em>' . __( 'No related commissions found.', 'marketplace-for-woocommerce' ) . '</em>';
		}
	}

	/**
	 * get_product_link.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	public function get_product_link( $product_id ) {
		return '<a href="' . admin_url( "post.php?post={$product_id}&action=edit" ) . '">' . get_the_title( $product_id ) . '</a>';
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
	 * Setups the plugin.
	 *
	 * @version 1.4.7
	 * @since   1.0.0
	 */
	public function setup_plugin() {
		$this->vendor_user = new Alg_MPWC_Vendor_User();
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
	 * Fixes variation product price.
	 *
	 * When the product is saved, all the variations prices are saved to main product '_price' meta
	 *
	 * @version 1.2.0
	 * @since   1.1.9
	 *
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
	 * Set the variations to correct author.
	 *
	 * When the product author changes, the variations don't. It fixes that
	 *
	 * @version 1.1.3
	 * @since   1.1.3
	 *
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
	 * Called when plugin is enabled.
	 *
	 * @version 1.4.3
	 * @since   1.0.0
	 */
	public static function on_plugin_activation() {
		parent::on_plugin_activation();
		Alg_MPWC_Core::activate_plugin( true );
	}

	/**
	 * activate_plugin.
	 *
	 * @version 1.4.3
	 * @since   1.4.3
	 */
	public static function activate_plugin( $do_version_update ) {

		// Adds the vendor role
		Alg_MPWC_Vendor_Role::add_vendor_role();
		Alg_MPWC_CPT_Commission::gives_all_caps_to_roles();

		// Creates commission status
		$tax = new Alg_MPWC_Commission_Status_Tax();
		$tax->set_args();
		$tax->register();
		$tax->create_initial_status();

		// Flush rewrite rules
		flush_rewrite_rules();

		// Trigger version update `alg_mpwc_version_updated()`
		if ( $do_version_update ) {
			delete_option( 'alg_wc_marketplace_version' );
		}

	}

	/**
	 * vendor_rating_shortcode.
	 *
	 * @version 1.4.2
	 * @since   1.4.2
	 */
	public function vendor_rating_shortcode( $atts, $content = '' ) {
		$atts = shortcode_atts( array(
				'vendor_id' => 0,
			), $atts, 'vendor_rating' );
		if ( empty( $atts['vendor_id'] ) ) {
			// Try to get `vendor_id` from query, e.g. vendor public page
			$vendor_query_string = get_query_var( Alg_MPWC_Query_Vars::VENDOR );
			if ( ( $vendor = get_user_by( ( is_numeric( $vendor_query_string ) ? 'id' : 'slug' ), $vendor_query_string ) ) ) {
				$atts['vendor_id'] = $vendor->ID;
			} else {
				// Try to get `vendor_id` from post author, e.g. vendor product tab
				global $post;
				if ( ! empty( $post->post_author ) && ( $user = get_user_by( 'ID', $post->post_author ) ) && in_array( Alg_MPWC_Vendor_Role::ROLE_VENDOR, ( array ) $user->roles ) ) {
					$atts['vendor_id'] = $post->post_author;
				}
			}
		}
		if ( empty( $atts['vendor_id'] ) ) {
			// Exiting - no `vendor_id` found
			return '';
		}
		if ( '' === $content ) {
			$content = '<div class="alg-mpwc-vendor-rating">%rating_html%</div>';
		}
		return $this->get_vendor_rating( $atts['vendor_id'], $content );
	}

	/**
	 * get_vendor_rating.
	 *
	 * @version 1.4.2
	 * @since   1.4.0
	 *
	 * @todo    [next] (dev) add option to manually clear the transients?
	 * @todo    [next] (dev) customizable transient expiration
	 * @todo    [next] (dev) pre-calculate in cron
	 * @todo    [next] (dev) move this to some other class
	 * @todo    [next] (desc) `.alg-mpwc-vendor-rating .star-rating { margin: auto; }`
	 */
	public function get_vendor_rating( $vendor_id, $template ) {
		if ( ! ( $rating_data = get_transient( 'alg_mpwc_vendor_rating_' . $vendor_id ) ) ) {
			$rating_data = array( 'rating' => 0, 'count' => 0, 'rated_products' => 0, 'total_products' => 0 );
			foreach ( wc_get_products( array( 'limit' => -1, 'author' => $vendor_id ) ) as $product ) {
				if ( ( $count = $product->get_rating_count() ) > 0 ) {
					$rating_data['count'] += $count;
					$rating_data['rating'] += $count * $product->get_average_rating();
					$rating_data['rated_products']++;
				}
				$rating_data['total_products']++;
			}
			if ( 0 != $rating_data['count'] ) {
				$rating_data['rating'] = round( $rating_data['rating'] / $rating_data['count'], 2 );
			}
			$expiration = 3600;
			set_transient( 'alg_mpwc_vendor_rating_' . $vendor_id, $rating_data, $expiration );
		}
		$placeholders = array(
			'%rating_html%'    => wc_get_rating_html( $rating_data['rating'], $rating_data['count'] ),
			'%rating%'         => $rating_data['rating'],
			'%count%'          => $rating_data['count'],
			'%rated_products%' => $rating_data['rated_products'],
			'%total_products%' => $rating_data['total_products'],
			'%vendor_id%'      => $vendor_id,
		);
		return str_replace( array_keys( $placeholders ), $placeholders, $template );
	}

	/**
	 * converts array to string.
	 *
	 * @version 1.4.7
	 * @since   1.4.7
	 *
	 * @param $arr
	 * @param array $args
	 *
	 * @return string
	 */
	function convert_array_to_string( $arr, $args = array() ) {
		$args            = wp_parse_args( $args, array(
			'glue'          => ', ',
			'item_template' => '{value}' //  {key} and {value} allowed
		) );
		$transformed_arr = array_map( function ( $key, $value ) use ( $args ) {
			$item = str_replace( array( '{key}', '{value}' ), array( $key, $value ), $args['item_template'] );
			return $item;
		}, array_keys( $arr ), $arr );
		return implode( $args['glue'], $transformed_arr );
	}

}

endif;
