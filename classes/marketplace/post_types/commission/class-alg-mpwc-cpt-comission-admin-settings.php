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
		 * Set arguments
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function set_args( Alg_MPWC_CPT_Commission $commission_manager ) {
			$this->commission_manager = $commission_manager;
		}

		public function add_cmb() {

			$cmb_demo = new_cmb2_box( array(
				'id'           => 'alg_mpwc_commissions_details_cmb',
				'title'        => esc_html__( 'Details', 'marketplace-for-woocommerce' ),
				'object_types' => array( $this->commission_manager->id ), // Post type
			) );

			$cmb_demo->add_field( array(
				'name'   => esc_html__( 'Order ID', 'marketplace-for-woocommerce' ),
				'desc'   => esc_html__( 'Commission order id', 'marketplace-for-woocommerce' ),
				'id'     => Alg_MPWC_Post_Metas::COMMISSION_ORDER_ID,
				'type'   => 'text',
				'attributes' => array(
					'type'=>'number'
				),
				'column' => true, // Display field value in the admin post-listing columns
			) );

			$cmb_demo->add_field( array(
				'name'       => esc_html__( 'Value', 'marketplace-for-woocommerce' ).' ('.get_woocommerce_currency().')',
				'desc'       => esc_html__( 'Commission value', 'marketplace-for-woocommerce' ).' ('.get_woocommerce_currency_symbol().')',
				'id'         => Alg_MPWC_Post_Metas::COMMISSION_VALUE,
				'type'       => 'text',
				'attributes' => array(
					'step'=>'0.001',
					'type'=>'number'
				)
				//'attributes'=>array('class'=>'short wc_input_price')
				// 'column'          => true, // Display field value in the admin post-listing columns
			) );

			//woocommerce_wp_text_input

			/*
			const COMMISSION_PRODUCT_IDS = 'alg_mpwc_product_ids';
			const COMMISSION_AUTHOR_ID   = 'alg_mpwc_author_id';
			const COMMISSION_ORDER_ID    = 'alg_mpwc_order_id';
			const COMMISSION_VALUE       = 'alg_mpwc_comission_value';
			*/
		}


	}
}