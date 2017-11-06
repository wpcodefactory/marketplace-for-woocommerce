<?php
/**
 * Marketplace for WooCommerce - Commission creator
 *
 * @version 1.0.5
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
			$comission_creator           = new Alg_MPWC_CPT_Commission_Creator();
			$commissions_creation_status = $this->commission_manager->automatic_creation;
			if ( is_array( $commissions_creation_status ) ) {
				foreach ( $commissions_creation_status as $order_status ) {
					$status = str_replace( 'wc-', '', $order_status );
					$action = "woocommerce_order_status_{$status}";

					if ( ! has_action( $action, array( $this, 'create_commission_automatically' ) ) ) {
						add_action( $action, array( $this, 'create_commission_automatically' ), 10 );
					}
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
					'item'      => $item,
					'vendor_id' => $vendor_id,
				) );
				$products_by_vendor[ $vendor_id ] = $comission_data;
			}

			return $products_by_vendor;
		}

		/**
		 * Creates commission automatically
		 *
		 * @version 1.0.5
		 * @since   1.0.0
		 */
		public function create_commission_automatically( $order_id ) {
			$status_tax = new Alg_MPWC_Commission_Status_Tax();
			$status_tax->setup();
			$status_unpaid_term = get_term_by( 'slug', 'unpaid', $status_tax->id );
			$user_fields        = new Alg_MPWC_Vendor_Admin_Fields();

			// Only creates commissions automatically if the corresponding order has not been processed yet
			$comissions_evaluated = filter_var( get_post_meta( $order_id, Alg_MPWC_Post_Metas::ORDER_COMISSIONS_EVALUATED, true ), FILTER_VALIDATE_BOOLEAN );
			if ( $comissions_evaluated ) {
				return;
			}

			// An array of products from an order filtered by vendors
			$products_by_vendor = $this->get_order_items_filtered_by_vendor( $order_id );

			// Gets commission base and balue
			$commission_fixed_value = $this->commission_manager->commission_fixed_value;
			$commission_percentage_value = $this->commission_manager->commission_percentage_value;

			// Gets order currency
			$order_currency = get_post_meta($order_id, '_order_currency', true);
			$commission_convert_value = 1;
			//$commission_convert_value = function_exists( 'alg_get_currency_exchange_rate' ) ? alg_get_currency_exchange_rate($order_currency) : 1;

			foreach ( $products_by_vendor as $comissions ) {

				// Sets comission vars
				$subtotal        = 0;
				$product_ids     = array();
				$order_id        = '';
				$title_arr       = array();
				$vendor_id       = '';
				$comission_value = 0;
				foreach ( $comissions as $comission ) {
					/* @var WC_Order_Item_Product $item */
					$item          = $comission['item'];
					$post          = get_post( $item->get_product_id() );
					$vendor_id     = $post->post_author;
					$subtotal      += $item->get_subtotal();
					$product_ids[] = $item->get_product_id();
					$title_arr[]   = $post->post_title;
					$order_id      = $item->get_order_id();
				}

				// Override commission values
				$commission_fixed_value_override      = (float) get_user_meta( $vendor_id, $user_fields->meta_commission_fixed_value, true );
				$commission_percentage_value_override = (float) get_user_meta( $vendor_id, $user_fields->meta_commission_percentage_value, true );
				$commission_fixed_value               = $commission_fixed_value_override || $commission_fixed_value_override === 0 ? $commission_fixed_value_override : $commission_fixed_value;
				$commission_percentage_value          = $commission_percentage_value_override || $commission_percentage_value_override === 0 ? $commission_percentage_value_override : $commission_percentage_value;

				// Sets comission title
				$title = implode( ', ', $title_arr );
				$title = __( 'Commission', 'marketplace-for-woocommerce' ) . ' - ' . $title;
				//$title .= ' (' . sprintf( __( 'Order %s' ), $order_id ) . ')';

				$commission_value_final = 0;
				$commission_value_final += $commission_fixed_value;
				$commission_value_final += $subtotal * ( (float) $commission_percentage_value / 100 );
				$commission_value_final *= $commission_convert_value;

				// Creates comission post type programmatically
				$insert_post_response = wp_insert_post( array(
					'post_author' => $vendor_id,
					'post_title'  => $title,
					'post_type'   => $this->commission_manager->id,
					'post_status' => 'publish',
					'meta_input' => array(
						Alg_MPWC_Post_Metas::COMMISSION_AUTHOR_ID        => $vendor_id,
						Alg_MPWC_Post_Metas::COMMISSION_ORDER_ID         => $order_id,
						Alg_MPWC_Post_Metas::COMMISSION_PRODUCT_IDS      => $product_ids,
						Alg_MPWC_Post_Metas::COMMISSION_FIXED_VALUE      => $commission_fixed_value,
						Alg_MPWC_Post_Metas::COMMISSION_PERCENTAGE_VALUE => $commission_percentage_value,
						Alg_MPWC_Post_Metas::COMMISSION_FINAL_VALUE      => $commission_value_final,
						Alg_MPWC_Post_Metas::COMMISSION_CURRENCY         => get_woocommerce_currency(),
					)
				) );

				wp_set_object_terms( $insert_post_response, array( $status_unpaid_term->term_id ), $status_tax->id );

				do_action('alg_mpwc_insert_commission', $insert_post_response, $vendor_id, $order_id, $commission_value_final);

				// Associate related commissions to main order
				add_post_meta( $order_id, Alg_MPWC_Post_Metas::ORDER_RELATED_COMISSIONS, $insert_post_response );
			}

			update_post_meta( $order_id, Alg_MPWC_Post_Metas::ORDER_COMISSIONS_EVALUATED, true );
		}
	}
}