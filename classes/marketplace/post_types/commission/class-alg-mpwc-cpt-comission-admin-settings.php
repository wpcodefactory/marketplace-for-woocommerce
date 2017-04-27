<?php
/**
 * Marketplace for WooCommerce - Commission admin settings
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_CPT_Commission_Admin_Settings' ) ) {
	class Alg_MPWC_CPT_Commission_Admin_Settings {
		/**
		 * @var Alg_MPWC_CPT_Commission
		 */
		private $commission_manager;

		/**
		 * Sets arguments
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function set_args( Alg_MPWC_CPT_Commission $commission_manager ) {
			$this->commission_manager = $commission_manager;
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

		/**
		 * Displays the commission value on post edit column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function display_commission_value_column( $field_args, $field ) {
			echo wc_price( $field->escaped_value() );
		}

		public function add_commission_status_cmb() {
			$status_tax = new Alg_MPWC_Commission_Status_Tax();

			$cmb_demo = new_cmb2_box( array(
				'id'           => 'alg_mpwc_commissions_status_cmb',
				'title'        => __( 'Status', 'marketplace-for-woocommerce' ),
				'object_types' => array( $this->commission_manager->id ),
				'context'      => 'side',
				'priority'     => 'low'
			) );

			$cmb_demo->add_field( array(
				'name'             => __( 'Order ID', 'marketplace-for-woocommerce' ),
				'id'               => Alg_MPWC_Post_Metas::COMMISSION_STATUS,
				'show_option_none' => false,
				'type'             => 'taxonomy_radio_inline',
				'taxonomy'         => $status_tax->id,
				'remove_default'   => 'true'
				//'column'     => array( 'position' => 2 ),
			) );
		}

		/**
		 * Adds the commission details CMB
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_commission_details_cmb() {

			$cmb_demo = new_cmb2_box( array(
				'id'           => 'alg_mpwc_commissions_details_cmb',
				'title'        => __( 'Details', 'marketplace-for-woocommerce' ),
				'object_types' => array( $this->commission_manager->id ),
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
			) );

			$cmb_demo->add_field( array(
				'name'       => __( 'Value', 'marketplace-for-woocommerce' ) . ' (' . get_woocommerce_currency() . ')',
				'desc'       => __( 'Commission value', 'marketplace-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
				'id'         => Alg_MPWC_Post_Metas::COMMISSION_VALUE,
				'type'       => 'text',
				'attributes' => array(
					'step'  => '0.001',
					'type'  => 'number',
					'style' => 'width: 99%',
				),
				'column'     => array( 'position' => 3 ),
				'display_cb' => array( $this, 'display_commission_value_column' ),
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
				'column'     => array( 'position' => 4 ),
			) );

			$cmb_demo->add_field( array(
				'name'       => __( 'Related products', 'marketplace-for-woocommerce' ),
				'id'         => Alg_MPWC_Post_Metas::COMMISSION_PRODUCT_IDS,
				'type'       => 'pw_multiselect',
				'options_cb' => array( $this, 'get_products' ),
				'attributes' => array(
					'style' => 'width: 99%',
				),
			) );

		}

		/**
		 * Displays the vendor column
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function display_vendor_column($field_args, $field){
			echo get_userdata($field->escaped_value())->display_name;
		}


	}
}