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

		protected $labels;
		protected $args;
		public $id = 'alg_mpwc_commission';

		/**
		 * Setups the post type
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function setup(){
			$this->set_args();
			$this->handle_automatic_creation();
		}

		/**
		 * Handles automatic commissions creation
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		protected function handle_automatic_creation() {
			$commissions_creation = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_COMMISSIONS_AUTOMATIC_CREATION ) );
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
				add_action( $action, array( $this, 'create_commission_autommatically' ), 10, 1 );
			}
		}

		/**
		 * Creates commission autommatically
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function create_commission_autommatically($order_id){
			$order = wc_get_order($order_id);
			$order->get_items();
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