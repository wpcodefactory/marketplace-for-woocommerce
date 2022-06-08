<?php
/**
 * Marketplace for WooCommerce - Commission manager.
 *
 * @version 1.5.3
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_CPT_Commission_Manager' ) ) {
	class Alg_MPWC_CPT_Commission_Manager {

		/**
		 * @var Alg_MPWC_CPT_Commission
		 */
		private $commission_cpt;

		/**
		 * Set arguments
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function set_args( Alg_MPWC_CPT_Commission $commission_cpt ) {
			$this->commission_cpt = $commission_cpt;
		}

		/**
		 * Handles automatic commissions creation
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function handle_automatic_creation() {
			$comission_creator           = new Alg_MPWC_CPT_Commission_Manager();
			$commissions_creation_status = $this->commission_cpt->automatic_creation;
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
		 * Handles automatic refund
		 *
		 * @version 1.1.2
		 * @since   1.1.2
		 */
		public function handle_automatic_refund() {
			$refund_status = $this->commission_cpt->refund_status;
			if ( is_array( $refund_status ) ) {
				foreach ( $refund_status as $order_status ) {
					$status = str_replace( 'wc-', '', $order_status );
					$action = "woocommerce_order_status_{$status}";
					if ( ! has_action( $action, array( $this, 'automatically_set_commission_as_need_refund' ) ) ) {
						add_action( $action, array( $this, 'automatically_set_commission_as_need_refund' ), 10 );
					}
				}
			}
		}

		/**
		 * Automatically set commission as "Need refund" or "Refunded"
		 *
		 * @version 1.2.0
		 * @since   1.1.2
		 * @param   $order_id
		 */
		public function automatically_set_commission_as_need_refund( $order_id ) {
			$commissions = get_post_meta( $order_id, Alg_MPWC_Post_Metas::ORDER_RELATED_COMISSIONS, false );
			if ( is_array( $commissions ) ) {
				$status_tax = new Alg_MPWC_Commission_Status_Tax();
				foreach ( $commissions as $commission_id ) {
					$current_terms = wp_get_object_terms( $commission_id, $status_tax->id, array( 'fields' => 'slugs' ) );
					wp_set_object_terms( $commission_id, ( in_array( 'paid', $current_terms ) ? 'need-refund' : 'refunded' ), $status_tax->id );
				}
			}
		}

		/**
		 * Creates a array of products from an order filtered by vendors
		 *
		 * @version 1.4.4
		 * @since   1.0.0
		 *
		 * @param $order_id
		 *
		 * @return array
		 */
		protected function get_order_items_separated_by_vendor( $order_id ) {
			$order              = wc_get_order( $order_id );
			$products_by_vendor = array();

			/* @var WC_Order_Item_Product $item */
			foreach ( $order->get_items() as $item ) {
				$post      = get_post( $item->get_product_id() );
				$vendor_id = $post->post_author;
				if ( ! Alg_MPWC_Vendor_Role::is_user_vendor( $vendor_id ) ) {
					continue;
				}
				$subtotal       = (float) $item->get_total( 'edit' );
				$quantity       = $item->get_quantity();
				$product_id     = $item->get_product_id();
				$order_id       = $item->get_order_id();
				$comission_data = isset( $products_by_vendor[ $vendor_id ] ) ? $products_by_vendor[ $vendor_id ] : array();
				array_push( $comission_data, $item );
				$products_by_vendor[ $vendor_id ] = $comission_data;
			}

			return $products_by_vendor;
		}

		/**
		 * Gets order items from a specific vendor
		 *
		 * @version 1.1.0
		 * @since   1.1.0
		 */
		protected function get_order_items_by_vendor( $order_id, $vendor_id ) {
			$order_items_separated_by_vendor = $this->get_order_items_separated_by_vendor( $order_id );

			return $order_items_separated_by_vendor[ $vendor_id ];
		}

		/**
		 * Gets updated commission info
		 *
		 * @version 1.4.4
		 * @since   1.1.0
		 *
		 * @param        $commission_id
		 *
		 * @param string $based_on 'global_settings' | 'commission'
		 *
		 * @return array
		 */
		public function get_updated_commission_info( $commission_id, $based_on = 'global_settings' ) {
			// Gets commission base and value
			$commission_fixed_value      = $this->commission_cpt->commission_fixed_value;
			$commission_percentage_value = $this->commission_cpt->commission_percentage_value;

			$order_id  = filter_var( get_post_meta( $commission_id, Alg_MPWC_Post_Metas::COMMISSION_ORDER_ID, true ), FILTER_SANITIZE_NUMBER_INT );
			$vendor_id = filter_var( get_post_meta( $commission_id, Alg_MPWC_Post_Metas::COMMISSION_AUTHOR_ID, true ), FILTER_SANITIZE_NUMBER_INT );

			$user_fields = new Alg_MPWC_Vendor_Admin_Fields();

			$order_items = $this->get_order_items_by_vendor( $order_id, $vendor_id );

			$subtotal = 0;

			foreach ( $order_items as $order_item ) {
				$subtotal += (float) $order_item->get_total( 'edit' );
			}

			// Override commission values
			$commission_fixed_value_override      = (float) get_user_meta( $vendor_id, $user_fields->meta_commission_fixed_value, true );
			$commission_percentage_value_override = (float) get_user_meta( $vendor_id, $user_fields->meta_commission_percentage_value, true );
			$commission_fixed_value               = $commission_fixed_value_override || $commission_fixed_value_override === 0 ? $commission_fixed_value_override : $commission_fixed_value;
			$commission_fixed_value               = apply_filters( 'alg_mpwc_commission_fixed_value', $commission_fixed_value, $order_id );
			$commission_percentage_value          = $commission_percentage_value_override || $commission_percentage_value_override === 0 ? $commission_percentage_value_override : $commission_percentage_value;

			if ( $based_on == 'commission' ) {
				$commission_fixed_value      = get_post_meta( $commission_id, Alg_MPWC_Post_Metas::COMMISSION_FIXED_VALUE, true );
				$commission_percentage_value = get_post_meta( $commission_id, Alg_MPWC_Post_Metas::COMMISSION_PERCENTAGE_VALUE, true );
			}

			$commission_value_final = 0;
			$commission_value_final += $commission_fixed_value;
			$commission_value_final += $subtotal * ( (float) $commission_percentage_value / 100 );

			return array(
				'final_value'      => $commission_value_final,
				'fixed_value'      => $commission_fixed_value,
				'percentage_value' => $commission_percentage_value
			);
		}

		/**
		 * Updates commission values (Final value, fixed value and percentage value)
		 *
		 * @version 1.1.0
		 * @since   1.1.0
		 */
		public function update_commission_values( $commission_id, $new_values ) {
			if ( isset( $new_values['final_value'] ) ) {
				update_post_meta( $commission_id, Alg_MPWC_Post_Metas::COMMISSION_FINAL_VALUE, $new_values['final_value'] );
			}

			if ( isset( $new_values['fixed_value'] ) ) {
				update_post_meta( $commission_id, Alg_MPWC_Post_Metas::COMMISSION_FIXED_VALUE, $new_values['fixed_value'] );
			}

			if ( isset( $new_values['percentage_value'] ) ) {
				update_post_meta( $commission_id, Alg_MPWC_Post_Metas::COMMISSION_PERCENTAGE_VALUE, $new_values['percentage_value'] );
			}
		}

		/**
		 * Creates commission
		 *
		 * @version 1.3.0
		 * @since   1.2.2
		 *
		 * @param $order_id
		 * @param $vendor_id
		 * @param $title
		 * @param array $product_ids
		 * @param int $subtotal
		 */
		public function create_commission( $order_id, $vendor_id, $title, $product_ids = array(), $subtotal = 0 ) {
			$user_fields                 = new Alg_MPWC_Vendor_Admin_Fields();
			$commission_fixed_value      = $this->commission_cpt->commission_fixed_value;
			$commission_percentage_value = $this->commission_cpt->commission_percentage_value;
			$default_status              = get_option( Alg_MPWC_Settings_General::OPTION_COMMISSIONS_DEFAULT_STATUS, 'unpaid' );
			$status_tax                  = new Alg_MPWC_Commission_Status_Tax();
			$status_tax->setup();

			// Override commission values
			$commission_fixed_value_override      = (float) get_user_meta( $vendor_id, $user_fields->meta_commission_fixed_value, true );
			$commission_percentage_value_override = (float) get_user_meta( $vendor_id, $user_fields->meta_commission_percentage_value, true );
			$commission_fixed_value               = $commission_fixed_value_override || $commission_fixed_value_override === 0 ? $commission_fixed_value_override : $commission_fixed_value;
			$commission_fixed_value               = apply_filters( 'alg_mpwc_commission_fixed_value', $commission_fixed_value, $order_id );
			$commission_percentage_value          = $commission_percentage_value_override || $commission_percentage_value_override === 0 ? $commission_percentage_value_override : $commission_percentage_value;

			$commission_value_final = 0;
			$commission_value_final += $commission_fixed_value;
			$commission_value_final += $subtotal * ( (float) $commission_percentage_value / 100 );

			if ( 0 == $commission_value_final && 'no' === get_option( 'alg_mpwc_opt_commissions_create_zero', 'yes' ) ) {
				return false;
			}

			// Creates comission post type programmatically
			$insert_post_response = wp_insert_post( array(
				'post_author' => $vendor_id,
				'post_title'  => $title,
				'post_type'   => $this->commission_cpt->id,
				'post_status' => 'publish',
				'meta_input'  => array(
					Alg_MPWC_Post_Metas::COMMISSION_AUTHOR_ID        => $vendor_id,
					Alg_MPWC_Post_Metas::COMMISSION_ORDER_ID         => $order_id,
					Alg_MPWC_Post_Metas::COMMISSION_PRODUCT_IDS      => $product_ids,
					Alg_MPWC_Post_Metas::COMMISSION_FIXED_VALUE      => $commission_fixed_value,
					Alg_MPWC_Post_Metas::COMMISSION_PERCENTAGE_VALUE => $commission_percentage_value,
					Alg_MPWC_Post_Metas::COMMISSION_FINAL_VALUE      => $commission_value_final,
					Alg_MPWC_Post_Metas::COMMISSION_CURRENCY         => get_woocommerce_currency(),
				)
			) );

			wp_set_object_terms( $insert_post_response, $default_status, $status_tax->id );

			do_action( 'alg_mpwc_insert_commission', $insert_post_response, $vendor_id, $order_id, $commission_value_final );

			// Associate related commissions to main order
			add_post_meta( $order_id, Alg_MPWC_Post_Metas::ORDER_RELATED_COMISSIONS, $insert_post_response );
		}

		/**
		 * Creates commissions grouping by author
		 *
		 * @version 1.4.4
		 * @since   1.2.2
		 * @param $order_id
		 */
		public function create_commission_grouping_by_author( $order_id ) {
			// An array of products from an order filtered by vendors
			$products_by_vendor = $this->get_order_items_separated_by_vendor( $order_id );

			foreach ( $products_by_vendor as $key => $order_items ) {
				// Sets commission vars
				$subtotal    = 0;
				$product_ids = array();
				$title_arr   = array();
				$vendor_id   = 0;

				/* @var WC_Order_Item_Product $order_item */
				foreach ( $order_items as $order_item ) {
					$post          = get_post( $order_item->get_product_id() );
					$subtotal      += (float) $order_item->get_total('edit');
					$product_ids[] = (string) $order_item->get_product_id();
					$title_arr[]   = $post->post_title;
					$vendor_id     = $post->post_author;
				}

				// Sets comission title
				$title = implode( ', ', $title_arr );
				$title = __( 'Commission', 'marketplace-for-woocommerce' ) . ' - ' . $title;

				$this->create_commission( $order_id, $vendor_id, $title, $product_ids, $subtotal );
			}
		}

		/**
		 * Gets commissions query.
		 *
		 * @version 1.5.3
		 * @since   1.2.3
		 * @param array $args
		 *
		 * @return bool|WP_Query
		 */
		public function get_commissions_query( $args = array() ) {
			$can_run_query = true;
			$args          = wp_parse_args( $args, array(
				'vendor_id'      => '',
				'order_id'       => '',
				'post_type'      => $this->commission_cpt->id,
				'posts_per_page' => - 1
			) );
			if ( ! empty( $args['order_id'] ) && is_int( $args['order_id'] ) ) {
				$args['meta_query'][] = array(
					'key'   => '_alg_mpwc_order_id',
					'value' => $args['order_id'],
				);
			} else {
				$can_run_query = false;
			}
			if ( ! empty( $args['vendor_id'] ) && is_int( $args['vendor_id'] ) ) {
				$args['meta_query'][] = array(
					'key'   => '_alg_mpwc_author_id',
					'value' => $args['vendor_id'],
				);
			} else {
				$can_run_query = false;
			}
			if ( $can_run_query ) {
				return new \WP_Query( $args );
			} else {
				return false;
			}
		}

		/**
		 * Creates email table from commission query
		 *
		 * @version 1.5.1
		 * @since   1.2.3
		 *
		 * @param $the_query
		 *
		 * @return string
		 */
		public function create_email_table_from_commissions_query( $the_query ) {
			$message = '';
			// The Loop
			if ( $the_query->have_posts() ) {
				$message .= '<table class="td" cellspacing="0" border="1">';
				$message .= '
				<thead>
					<tr>
						<th class="td" scope="col" >' . __( "Product", "woocommerce" ) . '</th>
						<th class="td" scope="col" >' . __( "Commission Value", "marketplace-for-woocommerce" ) . '</th>
					</tr>
				</thead>
				<tbody>
				';
				$total   = 0;
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$commission_value = get_post_meta( get_the_ID(), '_alg_mpwc_comission_final_value', true );
					$currency         = get_post_meta( get_the_ID(), '_alg_mpwc_currency', true );
					$total            += $commission_value;
					$product_ids      = get_post_meta( get_the_ID(), '_alg_mpwc_product_ids', true );
					$product_names    = array();
					foreach ( $product_ids as $id ) {
						$product         = wc_get_product( $id );
						$product_names[] = is_a( $product, 'WC_Product' ) ? $product->get_formatted_name() : 'NA';
					}
					$message .= '
					<tr>
						<td class="td" scope="row" >' . implode( ', ', $product_names ) . '</td>
						<td class="td" scope="row" >' . wc_price( $commission_value, array( 'currency' => $currency ) ) . '</td>
					</tr>
					';
				}
				$message .= '
					<tr>
						<th class="td" scope="row" >' . __( "Total", "woocommerce" ) . '</th>
						<td class="td" scope="row" >' . wc_price( $total,array( 'currency' => $currency ) ) . '</td>
					</tr>
				';
				$message .= '</tbody></table>';
				wp_reset_postdata();
			}

			return $message;
		}

		/**
		 * Sends commission email to vendors.
		 *
		 * @version 1.5.3
		 * @since   1.2.3
		 * @param   $order_id
		 */
		public function send_commission_email_to_vendors( $order_id ) {
			$order                    = wc_get_order( $order_id );
			$mail_enable              = get_option( Alg_MPWC_Settings_General::OPTION_COMMISSIONS_EMAIL_ENABLE, 'no' );
			$commission_email_message = get_option( Alg_MPWC_Settings_General::OPTION_COMMISSIONS_EMAIL_MESSAGE, __( 'You have a new sale on {site_title} from {order_date}', 'marketplace-for-woocommerce' ) );
			$subject                  = get_option( Alg_MPWC_Settings_General::OPTION_COMMISSIONS_EMAIL_SUBJECT, __( 'You have a new sale on {site_title} from {order_date}', 'marketplace-for-woocommerce' ) );
			$subject                  = Alg_MPWC_Email::replace_template_variables( $subject, $order );
			$commission_email_message = Alg_MPWC_Email::replace_template_variables( $commission_email_message, $order );
			if ( $mail_enable === 'no' ) {
				return;
			}
			$products_by_vendor = $this->get_order_items_separated_by_vendor( $order_id );
			foreach ( $products_by_vendor as $key => $order_items ) {
				$vendor_id         = $key;
				$vendor_user       = get_user_by( 'ID', $vendor_id );
				$commissions_query = $this->get_commissions_query( array(
					'vendor_id' => $vendor_id,
					'order_id'  => $order_id
				) );
				$table = false !== $commissions_query ? $this->create_email_table_from_commissions_query( $commissions_query ) : '';
				if (
					! empty( $vendor_user ) &&
					is_a( $order, 'WC_Order' ) &&
					apply_filters( 'alg_mpwc_send_commission_notification_email', true, array(
						'vendor_user' => $vendor_user,
						'order_id'    => $order_id
					) ) &&
					! empty( $table )
				) {
					$final_message    = ! empty( $commission_email_message ) ? '<p>' . $commission_email_message . '</p>' . $table : $table;
					$complete_message = Alg_MPWC_Email::wrap_in_wc_email_template( $final_message, $subject );
					wc_mail( apply_filters( 'alg_mpwc_commission_notification_email_to', $vendor_user->user_email, array(
						'order_id' => $order_id,
						'user'     => $vendor_user,
						'subject'  => $subject
					) ), $subject, $complete_message );
				}
			}
		}

		/**
		 * Need to create commission from order item?
		 *
		 * @version 1.2.3
		 * @since   1.2.3
		 *
		 * @param WC_Order_Item_Product $item
		 *
		 * @return bool
		 */
		public function need_to_create_commission_from_order_item( WC_Order_Item_Product $item ) {
			$post    = get_post( $item->get_product_id() );
			$user_id = $post->post_author;
			return Alg_MPWC_Vendor_Role::is_user_vendor( $user_id );
		}

		/**
		 * Need to create commission from order?
		 *
		 * @version 1.2.3
		 * @since   1.2.3
		 *
		 * @param $order_id
		 *
		 * @return bool
		 */
		public function need_to_create_commission_from_order( $order_id ) {
			$order = wc_get_order( $order_id );

			/* @var WC_Order_Item_Product $item */
			foreach ( $order->get_items() as $item ) {
				if ( $this->need_to_create_commission_from_order_item( $item ) ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Creates commissions without grouping by author
		 *
		 * @version 1.4.4
		 * @since   1.2.2
		 *
		 * @param $order_id
		 * @param bool $quantity_separates
		 */
		public function create_commission_without_grouping_by_author( $order_id, $quantity_separates = false ) {
			// An array of products from an order filtered by vendors

			$order = wc_get_order( $order_id );

			/* @var WC_Order_Item_Product $order_item */
			foreach ( $order->get_items() as $order_item ) {
				$post = get_post( $order_item->get_product_id() );
				if ( ! $this->need_to_create_commission_from_order_item( $order_item ) ) {
					continue;
				}
				$vendor_id   = $post->post_author;
				$subtotal    = (float) $order_item->get_total( 'edit' );
				$product_ids = array( (string) $order_item->get_product_id() );
				$title       = __( 'Commission', 'marketplace-for-woocommerce' ) . ' - ' . $post->post_title;
				$repetitions = $quantity_separates ? $order_item->get_quantity() : 1;
				if ( $quantity_separates ) {
					$product  = $order_item->get_product();
					$subtotal = $product->get_price();
				}
				for ( $i = 0; $i < $repetitions; $i ++ ) {
					$this->create_commission( $order_id, $vendor_id, $title, $product_ids, $subtotal );
				}
			}
		}

		/**
		 * Creates commission automatically
		 *
		 * @version 1.2.3
		 * @since   1.0.0
		 */
		public function create_commission_automatically( $order_id ) {
			// Only creates commissions automatically if the corresponding order has not been processed yet
			$comissions_evaluated = filter_var( get_post_meta( $order_id, Alg_MPWC_Post_Metas::ORDER_COMISSIONS_EVALUATED, true ), FILTER_VALIDATE_BOOLEAN );
			if ( $comissions_evaluated ) {
				return;
			}

			$group_by_author = get_option( Alg_MPWC_Settings_General::OPTION_COMMISSIONS_GROUP_BY_AUTHORS, 'yes' );
			$quantity_separates = get_option( Alg_MPWC_Settings_General::OPTION_COMMISSIONS_QUANTITY_SEPARATES, 'no' );
			if ( 'yes' === $group_by_author && $quantity_separates === 'no' ) {
				$this->create_commission_grouping_by_author( $order_id );
			} else {
				$this->create_commission_without_grouping_by_author( $order_id, filter_var( $quantity_separates, FILTER_VALIDATE_BOOLEAN ) );
			}

			update_post_meta( $order_id, Alg_MPWC_Post_Metas::ORDER_COMISSIONS_EVALUATED, true );
			do_action( 'alg_mpwc_after_insert_commission', $order_id );
		}
	}
}