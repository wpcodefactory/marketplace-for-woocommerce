<?php
/**
 * Marketplace for WooCommerce - Comission custom post type
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_CPT_Commission' ) ) {
	class Alg_MPWC_CPT_Commission {

		// Custom Meta fields
		const META_PRODUCT_IDS     = 'alg_mpwc_product_ids';
		const META_AUTHOR_ID       = 'alg_mpwc_author_id';
		const META_ORDER_ID        = 'alg_mpwc_order_id';
		const META_COMISSION_VALUE = 'alg_mpwc_comission_value';

		// Custom post type args
		protected $labels;
		protected $args;
		public $id = 'alg_mpwc_commission';

		// Comission base type option from admin (percentage, fixed value, so on)
		public $comission_base = 'percentage';

		// Comission value from admin
		public $comission_value = '';

		// Automatic creation option from admin
		public $automatic_creation = 'order_complete';

		/**
		 * Setups the post type
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function setup(){
			$this->set_args();
			$this->get_values_from_admin();
			$this->handle_automatic_creation();
		}

		/**
		 * Gets values from admin
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		protected function get_values_from_admin(){
			$this->comission_base     = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_COMISSIONS_BASE ) );
			$this->comission_value    = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_COMMISSIONS_BASE_VALUE ) );
			$this->automatic_creation = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_COMMISSIONS_AUTOMATIC_CREATION ) );
		}

		/**
		 * Handles automatic commissions creation
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		protected function handle_automatic_creation() {
			$commissions_creation = $this->automatic_creation;
			if ( ! empty( $commissions_creation ) && $commissions_creation != 'none' ) {
				$action = '';
				switch ( $commissions_creation ) {
					case 'order_complete':
						$action = 'woocommerce_order_status_completed';
					break;
					case 'order_processing':
						$action = 'woocommerce_order_status_processing';
					break;
				}
				if ( ! has_action( $action, array( $this, 'create_commission_autommatically' ) ) ) {
					add_action( $action, array( $this, 'create_commission_autommatically' ), 10 );
				}
			}
		}

		/**
		 * Creates a array of products from an order filtered by vendors
		 *
		 * @param $order_id
		 *
		 * @return array
		 */
		protected function get_order_items_filtered_by_vendor( $order_id ) {
			$order              = wc_get_order( $order_id );
			$products_by_vendor = array();

			/* @var WC_Order_Item_Product $item */
			foreach ( $order->get_items() as $item ) {
				$post       = get_post( $item->get_product_id() );
				$vendor_id  = $post->post_author;
				$subtotal   = $item->get_subtotal();
				$quantity   = $item->get_quantity();
				$product_id = $item->get_product_id();
				$order_id   = $item->get_order_id();

				$comission_data = isset( $products_by_vendor[ $vendor_id ] ) ? $products_by_vendor[ $vendor_id ] : array();
				array_push( $comission_data, array(
					'subtotal'      => $subtotal,
					'vendor_id'     => $vendor_id,
					'product_id'    => $product_id,
					'product_title' => $post->post_title,
					'order_id'      => $order_id,
				) );

				$products_by_vendor[ $vendor_id ] = $comission_data;
			}

			return $products_by_vendor;
		}

		/**
		 * Creates commission autommatically
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function create_commission_autommatically( $order_id ) {
			$products_by_vendor = $this->get_order_items_filtered_by_vendor($order_id);

			foreach ( $products_by_vendor as $comissions ) {

				// Sets comission vars
				$subtotal        = 0;
				$vendor_id       = '';
				$product_ids     = array();
				$order_id        = '';
				$title_arr       = array();
				$title           = '';
				$vendor_id       = '';
				$comission_value = 0;
				foreach ( $comissions as $comission ) {
					$vendor_id     = $comission['vendor_id'];
					$subtotal      += $comission['subtotal'];
					$product_ids[] = $comission['product_id'];
					$title_arr[]   = $comission['product_title'];
					$order_id      = $comission['order_id'];
				}

				// Sets comission title
				$title = implode( ', ', $title_arr );
				$title = __( 'Commission', 'marketplace-for-woocommerce' ) . ' - ' . $title;
				$title .= ' (' . sprintf( __( 'Order %s' ), $order_id ) . ')';

				// Calculates comission value
				switch ( $this->comission_base ) {
					case 'percentage':
						$comission_value = $subtotal * ((float)$this->comission_value/100);
					break;
					case 'fixed_value':
						$comission_value = $this->comission_value;
					break;
				}

				// Creates comission post type programmatically
				$insert_post_response = wp_insert_post( array(
					'post_author' => $vendor_id,
					'post_title'  => $title,
					'post_type'   => $this->id,
					'post_status' => 'publish',
					'meta_input'  => array(
						self::META_AUTHOR_ID       => $vendor_id,
						self::META_COMISSION_VALUE => $comission_value,
						self::META_ORDER_ID        => $order_id,
						self::META_PRODUCT_IDS     => $product_ids,
					),
				) );
			}
		}

		/**
		 * Setups the arguments for creating the post type
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function set_args(){
			$labels = array(
				'name'               => __( 'Commissions', 'marketplace-for-woocommerce' ),
				'singular_name'      => __( 'Commission', 'marketplace-for-woocommerce' ),
				'menu_name'          => __( 'Commissions', 'marketplace-for-woocommerce' ),
				'name_admin_bar'     => __( 'Commission', 'marketplace-for-woocommerce' ),
				'add_new'            => __( 'Add New', 'marketplace-for-woocommerce' ),
				'add_new_item'       => __( 'Add New Commission', 'marketplace-for-woocommerce' ),
				'new_item'           => __( 'New Commission', 'marketplace-for-woocommerce' ),
				'edit_item'          => __( 'Edit Commission', 'marketplace-for-woocommerce' ),
				'view_item'          => __( 'View Commission', 'marketplace-for-woocommerce' ),
				'all_items'          => __( 'All Commissions', 'marketplace-for-woocommerce' ),
				'search_items'       => __( 'Search Commissions', 'marketplace-for-woocommerce' ),
				'parent_item_colon'  => __( 'Parent Commissions:', 'marketplace-for-woocommerce' ),
				'not_found'          => __( 'No Commissions found.', 'marketplace-for-woocommerce' ),
				'not_found_in_trash' => __( 'No Commissions found in Trash.', 'marketplace-for-woocommerce' ),
			);

			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Description.', 'marketplace-for-woocommerce' ),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => false,
				'rewrite'            => array( 'slug' => 'commission' ),
				'capability_type'    => 'alg_mpwc_commission',
				/*'capabilities'       => array(
					'create_posts' => 'do_not_allow',
				),*/
				'map_meta_cap'       => true,
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title' ),
			);

			$this->labels = $labels;
			$this->args   = $args;
		}

		/**
		 * Registers the post type
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function register() {
			$args = $this->args;
			register_post_type( $this->id, $args );

			//add_action( 'registered_post_type', array($this,'registered_post_type'),10,2 );
			//$GLOBALS['wp_post_types'][$this->id]->cap->create_posts='test';
			//error_log(print_r($GLOBALS['wp_post_types'][$this->id],true));
		}

		/*public function registered_post_type($post_type, $post_type_object){

		}*/


	}
}