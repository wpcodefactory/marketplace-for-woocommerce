<?php
/**
 * Marketplace for WooCommerce - Commission creator
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_CPT_Commission_Creator' ) ) {
	class Alg_MPWC_CPT_Commission_Creator {

		/**
		 * @var Alg_MPWC_CPT_Commission
		 */
		private $commission_manager;

		/**
		 * Set arguments
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function set_args( Alg_MPWC_CPT_Commission $commission_manager ) {
			$this->commission_manager = $commission_manager;
		}

		/**
		 * Handles automatic commissions creation
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function handle_automatic_creation() {
			$comission_creator = new Alg_MPWC_CPT_Commission_Creator();

			$commissions_creation = $this->commission_manager->automatic_creation;
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
				if ( ! has_action( $action, array( $this, 'create_commission_automatically' ) ) ) {
					add_action( $action, array( $this, 'create_commission_automatically' ), 10 );
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
		 * Creates commission automatically
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function create_commission_automatically( $order_id ) {
			$status_tax         = new Alg_MPWC_Commission_Status_Tax();
			$status_unpaid_term = get_term_by( 'slug', 'unpaid', $status_tax->id );
			$user_fields = new Alg_MPWC_Vendor_Admin_Fields();

			// Only creates commissions automatically if the corresponding order has not been processed yet
			$comissions_evaluated = filter_var( get_post_meta( $order_id, Alg_MPWC_Post_Metas::ORDER_COMISSIONS_EVALUATED, true ), FILTER_VALIDATE_BOOLEAN );
			if ( $comissions_evaluated ) {
				return;
			}

			// An array of products from an order filtered by vendors
			$products_by_vendor = $this->get_order_items_filtered_by_vendor( $order_id );

			// Gets commission base and balue
			$commission_base = $this->commission_manager->comission_base;
			$commission_value = $this->commission_manager->comission_value;

			foreach ( $products_by_vendor as $comissions ) {

				// Sets comission vars
				$subtotal        = 0;
				$product_ids     = array();
				$order_id        = '';
				$title_arr       = array();
				$vendor_id       = '';
				$comission_value = 0;
				foreach ( $comissions as $comission ) {
					$vendor_id     = $comission['vendor_id'];
					$subtotal      += $comission['subtotal'];
					$product_ids[] = $comission['product_id'];
					$title_arr[]   = $comission['product_title'];
					$order_id      = $comission['order_id'];
				}

				// Override commission base and value
				$commission_base_override = sanitize_text_field(get_user_meta( $vendor_id, $user_fields->meta_commission_base, true ));
				$commission_value_override = (float)get_user_meta( $vendor_id, $user_fields->meta_commission_value, true );
				$commission_base = $commission_base_override ? $commission_base_override : $commission_base;
				$commission_value = $commission_value_override ? $commission_value_override : $commission_value;

				// Sets comission title
				$title = implode( ', ', $title_arr );
				$title = __( 'Commission', 'marketplace-for-woocommerce' ) . ' - ' . $title;
				$title .= ' (' . sprintf( __( 'Order %s' ), $order_id ) . ')';

				// Calculates comission value
				switch ( $commission_base ) {
					case 'percentage':
						$comission_value = $subtotal * ( (float) $commission_value / 100 );
					break;
					case 'fixed_value':
						$comission_value = $this->commission_manager->comission_value;
					break;
				}

				// Creates comission post type programmatically
				$insert_post_response = wp_insert_post( array(
					'post_author' => $vendor_id,
					'post_title'  => $title,
					'post_type'   => $this->commission_manager->id,
					'post_status' => 'publish',
					'meta_input'  => array(
						Alg_MPWC_Post_Metas::COMMISSION_AUTHOR_ID   => $vendor_id,
						Alg_MPWC_Post_Metas::COMMISSION_VALUE       => $comission_value,
						Alg_MPWC_Post_Metas::COMMISSION_ORDER_ID    => $order_id,
						Alg_MPWC_Post_Metas::COMMISSION_PRODUCT_IDS => $product_ids,
					),
					'tax_input'   => array(
						$status_tax->id => array( $status_unpaid_term->term_id ),
					),
				) );
			}

			update_post_meta( $order_id, Alg_MPWC_Post_Metas::ORDER_COMISSIONS_EVALUATED, true );
		}
	}
}