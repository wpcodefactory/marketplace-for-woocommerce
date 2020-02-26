<?php
/**
 * Marketplace for WooCommerce - Commission admin settings
 *
 * @version 1.3.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_CPT_Commission_Admin_Settings' ) ) {
	class Alg_MPWC_CPT_Commission_Admin_Settings {
		/**
		 * @var Alg_MPWC_CPT_Commission
		 */
		private $commission_cpt;

		/**
		 * Sets arguments
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function set_args( Alg_MPWC_CPT_Commission $commission_cpt ) {
			$this->commission_cpt = $commission_cpt;
		}

		/**
		 * Converts commission sum bit value
		 *
		 * @version 1.0.4
		 * @since   1.0.4
		 *
		 * @return float
		 */
		public function convert_commission_sum_bit_value( $commission_final_value, $commission_currency, $currency_to ) {
			$exchange_rate          = Alg_MPWCMA_Multicurrency::get_exchange_rate( $commission_currency, $currency_to );
			$commission_final_value *= $exchange_rate;
			return $commission_final_value;
		}

		/**
		 * Adds screen options about totals
		 *
		 * @version 1.1.2
		 * @since   1.1.2
		 *
		 * @param $status
		 * @param $args
		 *
		 * @return string
		 */
		public function add_totals_screen_option( $status, $args ) {
			$return         = $status;
			$commission_cpt = new Alg_MPWC_CPT_Commission();
			if (
				$args->base != 'edit' ||
				$args->id != "edit-{$commission_cpt->id}"
			) {
				return $status;
			}

			$fields = apply_filters( 'mpwc_totals_screen_option_fields', '' );

			$return .= "
            <fieldset class='metabox-prefs'>
            <legend>" . __( 'Totals', 'marketplace-for-woocommerce' ) . "</legend>
            {$fields}
            <br class='clear'>
            </fieldset>
            ";

			return $return;
		}

		/**
		 * Adds refund summing screen option
		 *
		 * @version 1.1.2
		 * @since   1.1.2
		 *
		 * @param $return
		 *
		 * @return string
		 */
		public function add_refund_sum_screen_option( $return ) {
			$user_id               = get_current_user_id();
			$sum_refunded_comm     = !filter_var( get_user_meta( $user_id, 'mpwc_sum_ref_comm_total_val', true ), FILTER_VALIDATE_BOOLEAN );
			$sum_refunded_comm_str = $sum_refunded_comm ? 'checked="checked"' : '';

			$return .= "
            <input {$sum_refunded_comm_str} type='checkbox' name='mpwc_sum_ref_comm_total_val' id='mpwc_sum_ref_comm_total_val' />
            <label for='mpwc_sum_ref_comm_total_val'>Ignore refund commissions</label>
            ";

			return $return;
		}

		/**
		 * Ignore refund commissions on admin
		 *
		 * @version 1.1.3
		 * @since   1.1.3
		 * @param $query
		 */
		public function ignore_refund_commissions( $query ) {
			$screen = null;
			if ( function_exists( 'get_current_screen' ) ) {
				$screen = get_current_screen();
			}

			if (
				! $query->is_main_query() ||
				! is_admin() ||
				! $screen ||
				$screen->id != 'edit-alg_mpwc_commission' ||
				filter_var( get_user_meta( get_current_user_id(), 'mpwc_sum_ref_comm_total_val', true ), FILTER_VALIDATE_BOOLEAN )
			) {
				return;
			}

			$tax_query  = $query->get( 'tax_query' );
			$status_tax = new Alg_MPWC_Commission_Status_Tax();
			if ( ! is_array( $tax_query ) ) {
				$tax_query = array();
			}
			$tax_query[] = array(
				'taxonomy' => $status_tax->id,
				'field'    => 'slug',
				'terms'    => array( 'refunded', 'need-refund' ),
				'operator' => 'NOT IN'
			);
			$query->set( 'tax_query', $tax_query );
		}

		/**
		 * Saves refund summing screen option
		 *
		 * @version 1.1.2
		 * @since   1.1.2
		 * @todo Add nonce
		 * @return string
		 */
		public function save_refund_sum_screen_option() {
			if (
				! is_admin()
				|| empty( $_POST['screen-options-apply'] )
				|| empty($_POST['wp_screen_options'])
				|| $_POST['wp_screen_options']['option'] != 'edit_alg_mpwc_commission_per_page'
				|| ! is_user_logged_in()
			) {
				return;
			}

			$user_id = get_current_user_id();

			if ( empty( $_POST['mpwc_sum_ref_comm_total_val'] ) ) {
				update_user_meta( $user_id, 'mpwc_sum_ref_comm_total_val', 'on' );
			} else {
				delete_user_meta( $user_id, 'mpwc_sum_ref_comm_total_val' );
			}
		}

		/**
		 * Gets vendors
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function get_vendors( $field ) {
			$users_with_role = get_users( array(
				'fields' => array( 'id', 'display_name' ),
				'role'   => Alg_MPWC_Vendor_Role::ROLE_VENDOR,
			) );

			if ( is_array( $users_with_role ) && count( $users_with_role ) > 0 ) {
				return wp_list_pluck( $users_with_role, 'display_name', 'id' );
			} else {
				return array();
			}
		}

		/**
		 * Display the sum of commissions values in edit.php page
		 *
		 * @version 1.1.3
		 * @since   1.0.0
		 *
		 * Called on Alg_MPWC_CPT_Commission::display_total_value_in_edit_columns()
		 * @param $defaults
		 *
		 * @return mixed
		 */
		public function get_total_value_in_edit_columns( $defaults ) {
			global $wp_query;
			$the_query = $wp_query;

			$show_total_commissions_value = apply_filters( 'alg_mpwc_show_total_commissions_value', true );
			if ( ! $show_total_commissions_value ) {
				return $defaults;
			}

			$currency_to = apply_filters( 'alg_mpwc_commission_sum_currency_to', get_woocommerce_currency() );

			// The Loop
			if ( $the_query->have_posts() ) {
				$total_value = 0;
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$commission_final_value = get_post_meta( get_the_ID(), Alg_MPWC_Post_Metas::COMMISSION_FINAL_VALUE, true );
					$commission_currency    = get_post_meta( get_the_ID(), Alg_MPWC_Post_Metas::COMMISSION_CURRENCY, true );
					$commission_final_value = apply_filters( 'alg_mpwc_commission_sum_bit', $commission_final_value, $commission_currency, $currency_to );
					$total_value            += $commission_final_value;
				}

				/* Restore original Post Data */
				wp_reset_postdata();

				$total_value                                             = '<strong>' . wc_price( $total_value, array( 'currency' => $currency_to ) ) . '</strong>';
				$defaults[ Alg_MPWC_Post_Metas::COMMISSION_FINAL_VALUE ] = "Value - {$total_value}";
			}

			return $defaults;
		}

		/**
		 * Gets products
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function get_products( $field ) {
			/* @var CMB2_Field $field */

			$args      = array(
				'posts_per_page' => '-1',
				'post_type'      => 'product',
			);
			$object_id = $field->object_id();
			if ( ! empty( $object_id ) ) {
				$author_id = get_post_meta( (int) $object_id, Alg_MPWC_Post_Metas::COMMISSION_AUTHOR_ID, true );
				if ( ! empty( $author_id ) ) {
					$args['author'] = $author_id;
				}
			}
			$posts = get_posts( $args );

			if ( is_array( $posts ) && count( $posts ) > 0 ) {
				return wp_list_pluck( $posts, 'post_title', 'ID' );
			} else {
				return array();
			}
		}

		public function add_commission_status_cmb() {
			$status_tax = new Alg_MPWC_Commission_Status_Tax();

			$cmb_demo = new_cmb2_box( array(
				'id'           => 'alg_mpwc_commissions_status_cmb',
				'title'        => __( 'Status', 'marketplace-for-woocommerce' ),
				'object_types' => array( $this->commission_cpt->id ),
				'context'      => 'side',
				'priority'     => 'low',
			) );

			$cmb_demo->add_field( array(
				'id'               => Alg_MPWC_Post_Metas::COMMISSION_STATUS,
				'show_option_none' => false,
				'type'             => 'taxonomy_radio_inline',
				'default'          => 'unpaid',
				'taxonomy'         => $status_tax->id,
				'remove_default'   => 'true',
				'display_cb'       => array( $this, 'display_status_column' ),
				'column'           => array( 'position' => 6, 'name' => 'Status' ),
			) );
		}

		public function display_order_id_column( $field_args, $field ) {
			$order = wc_get_order( (int) $field->escaped_value() );
			if ( $order ) {
				echo apply_filters( 'woocommerce_order_number', $order->get_id(), $order );
			}
		}

		/**
		 * Adds the commission details CMB
		 *
		 * @version 1.0.3
		 * @since   1.0.0
		 */
		public function add_commission_details_cmb() {

			$cmb_demo = new_cmb2_box( array(
				'id'           => 'alg_mpwc_commissions_details_cmb',
				'title'        => __( 'Details', 'marketplace-for-woocommerce' ),
				'object_types' => array( $this->commission_cpt->id ),
			) );

			$cmb_demo->add_field( array(
				'name'       => __( 'Order ID', 'marketplace-for-woocommerce' ),
				'desc'       => __( 'Commission order id', 'marketplace-for-woocommerce' ),
				'id'         => Alg_MPWC_Post_Metas::COMMISSION_ORDER_ID,
				'type'       => 'text',
				'attributes' => array(
					'type'  => 'number',
					'style' => 'width: 99%',
				),
				'column'     => array( 'position' => 2 ),
				'display_cb' => array( $this, 'display_order_id_column' ),
			) );

			$cmb_demo->add_field( array(
				'name'       => __( 'Fixed Value', 'marketplace-for-woocommerce' ) . ' (' . get_woocommerce_currency() . ')',
				'desc'       => __( 'Fixed value settled when this commission was created', 'marketplace-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ').'.'<br />'.__( "Note 1: It's registered in shop base currency", 'marketplace-for-woocommerce' ).'<br />'.__( "Note 2: It's converted to currency option to calculate the value field", 'marketplace-for-woocommerce' ),
				'id'         => Alg_MPWC_Post_Metas::COMMISSION_FIXED_VALUE,
				'type'       => 'text',
				'attributes' => array(
					'step'     => '0.001',
					'type'     => 'number',
					'style'    => 'width: 99%',
					//'readonly' => true,
				),
			) );

			$cmb_demo->add_field( array(
				'name'       => __( 'Percentage', 'marketplace-for-woocommerce' ) . ' (%)',
				'desc'       => __( 'Percentage settled when this commission was created', 'marketplace-for-woocommerce' ) . ' (%)',
				'id'         => Alg_MPWC_Post_Metas::COMMISSION_PERCENTAGE_VALUE,
				'type'       => 'text',
				'attributes' => array(
					'step'     => '0.001',
					'type'     => 'number',
					'style'    => 'width: 99%',
					//'readonly' => true,
				),
			) );

			$cmb_demo->add_field( array(
				'name'       => __( 'Value', 'marketplace-for-woocommerce' ),
				'id'         => Alg_MPWC_Post_Metas::COMMISSION_FINAL_VALUE,
				'type'       => 'text',
				'attributes' => array(
					'step'  => '0.001',
					'type'  => 'number',
					'style' => 'width: 99%',
				),
				'after' => array($this,'add_value_description'),
				'column'     => array( 'position' => 3 ),
				'display_cb' => array( $this, 'display_commission_value_column' ),
			) );

			$cmb_demo->add_field( array(
				'name'          => __( 'Deal', 'marketplace-for-woocommerce' ),
				'desc'          => __( 'Combination of fixed value / percentage settled when this commission was created', 'marketplace-for-woocommerce' ) . ' (%)',
				'id'            => Alg_MPWC_Post_Metas::COMMISSION_DEAL,
				'type'          => 'text',
				//'escape_cb'   => false,
				'save_fields'   => false,
				'render_row_cb' => false,
				'attributes'    => array(
					'step'     => '0.001',
					'type'     => 'number',
					'style'    => 'width: 99%',
					'readonly' => true,
				),
				'column'        => array( 'position' => 4 ),
				'display_cb'    => array( $this, 'display_deal_column' ),
			) );

			$cmb_demo->add_field( array(
				'name'       => __( 'Vendor', 'marketplace-for-woocommerce' ),
				'id'         => Alg_MPWC_Post_Metas::COMMISSION_AUTHOR_ID,
				'type'       => 'pw_select',
				'options_cb' => array( $this, 'get_vendors' ),
				'attributes' => array(
					'style' => 'width: 99%',
				),
				'display_cb' => array( $this, 'display_vendor_column' ),
				'column'     => array( 'position' => 5 ),
			) );

			$cmb_demo->add_field( array(
				'name'       => __( 'Related products', 'marketplace-for-woocommerce' ),
				'id'         => Alg_MPWC_Post_Metas::COMMISSION_PRODUCT_IDS,
				'type'       => 'pw_multiselect',
				'options_cb' => array( $this, 'get_products' ),
				'attributes' => array(
					'style' => 'width: 99%',
				),
				'display_cb' => array( $this, 'display_products_column' ),
				'column'     => array( 'position' => 6 ),
			) );

			$cmb_demo->add_field( array(
				'name'       => __( 'Currency', 'marketplace-for-woocommerce' ),
				'id'         => Alg_MPWC_Post_Metas::COMMISSION_CURRENCY,
				'type'       => 'pw_select',
				'options_cb' => array($this,'get_currencies'),
				'default'    => get_woocommerce_currency(),
				'attributes' => array(
					'style'  => 'width: 99%',
				),
				//'display_cb' => array( $this, 'display_products_column' ),
				'column'     => array( 'position' => 7 ),
			) );

		}

		/**
		 * Outputs the value field description
		 *
		 * @version 1.0.3
		 * @since   1.0.0
		 *
		 * @param  object $field_args Current field args
		 * @param  object $field      Current field object
		 */
		public function add_value_description( $field_args, $field ) {
			//$symbol = get_woocommerce_currency_symbol();
			$post_id  = $field->object_id;

			$currency = get_post_meta($post_id, Alg_MPWC_Post_Metas::COMMISSION_CURRENCY, true);
			$currency = $currency ? $currency : get_woocommerce_currency();
			$symbol = get_woocommerce_currency_symbol($currency);

			$currency_str = $currency." ({$symbol})";

			$currency = get_woocommerce_currency();
			echo "<p class='cmb2-metabox-description'>Currency: ".$currency_str."</p>";
		}

		/**
		 * Gets currencies
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public function get_currencies( $field ) {
			$currencies = get_woocommerce_currencies();

			$shop_currency = get_woocommerce_currency();
			$currency      = apply_filters( 'alg_mpwc_commission_currencies', array(
				$shop_currency => $currencies[ $shop_currency ] . ' (' . get_woocommerce_currency_symbol( $shop_currency ) . ')'
			) );

			return $currency;
		}

		/**
		 * Displays the deal settled (percentage + fixed value) when commission was created
		 *
		 * @version 1.0.5
		 * @since   1.0.0
		 *
		 */
		public function display_deal_column( $field_args, $field ) {
			$post_id          = $field->object_id;
			$fixed_value      = get_post_meta( $post_id, Alg_MPWC_Post_Metas::COMMISSION_FIXED_VALUE, true );
			$percentage_value = get_post_meta( $post_id, Alg_MPWC_Post_Metas::COMMISSION_PERCENTAGE_VALUE, true );
			if ( ! empty( $fixed_value ) ) {
				echo wc_price( $fixed_value);
				if ( ! empty( $percentage_value ) ) {
					echo ' + ';
				}
			}

			if ( ! empty( $percentage_value ) ) {
				echo $percentage_value . '%';
			}
		}

		/**
		 * Displays the commission value on post edit column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function display_commission_value_column( $field_args, $field ) {
			$post_id  = $field->object_id;
			$currency = get_post_meta( $post_id, Alg_MPWC_Post_Metas::COMMISSION_CURRENCY, true );
			$currency = $currency ? $currency : get_woocommerce_currency();
			echo wc_price( $field->escaped_value(), array(
				'currency' => $currency
			) );
		}

		/**
		 * Displays the products column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function display_products_column( $field_args, $field ) {
			$values = $field->value;
			wp_reset_postdata();
			if ( is_array( $values ) && count( $values ) > 0 ) {
				$posts       = get_posts( array(
					'post_type'      => 'product',
					'posts_per_page' => - 1,
					'post__in'       => $values,
				) );
				$post_titles = array();
				foreach ( $posts as $post ) {
					$post_titles[] = $post->post_title;
				}
				wp_reset_postdata();
				echo implode( ', ', $post_titles );
			}
		}

		/**
		 * Displays the vendor column
		 *
		 * @version 1.3.0
		 * @since   1.0.0
		 */
		public function display_vendor_column( $field_args, $field ) {
			if ( $field->escaped_value() ) {
				if ( $user_data = get_userdata( $field->escaped_value() ) ) {
					echo $user_data->display_name;
				}
			}
		}

		/**
		 * Displays the status column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function display_status_column( $field_args, $field ) {
			if ( $field->object_id ) {
				$tax   = new Alg_MPWC_Commission_Status_Tax();
				$terms = wp_get_post_terms( $field->object_id, $tax->id, array( 'fields' => 'names' ) );
				echo implode( ', ', $terms );
			}
		}

	}
}
