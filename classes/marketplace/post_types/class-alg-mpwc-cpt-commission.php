<?php
/**
 * Marketplace for WooCommerce - Commission custom post type
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_CPT_Commission' ) ) {
	class Alg_MPWC_CPT_Commission {

		// Custom post type args
		protected $labels;
		protected $args;
		public $id = 'alg_mpwc_commission';

		/**
		 * Manages the creation of commissions
		 *
		 * @var Alg_MPWC_CPT_Commission_Creator
		 */
		public $comission_creator;

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
		public function setup() {
			$this->set_args();
			$this->get_values_from_admin();
			$this->handle_automatic_creation();
			add_action( 'cmb2_admin_init', array( $this, 'handle_admin_settings' ) );
			add_action('admin_init',array($this,'remove_add_new_from_menu'));
		}

		/**
		 * Removes add new from menu
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function remove_add_new_from_menu(){
			global $submenu;
			unset($submenu['edit.php?post_type='.$this->id.''][10]);
		}

		/**
		 * Creates values from admin
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function handle_admin_settings() {
			$admin_settings = new Alg_MPWC_CPT_Commission_Admin_Settings();
			$admin_settings->set_args( $this );
			$admin_settings->add_commission_details_cmb();
			$admin_settings->add_commission_status_cmb();
		}

		/**
		 * Gets values from admin
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		protected function get_values_from_admin() {
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
			$commissions_creator     = new Alg_MPWC_CPT_Commission_Creator();
			$this->comission_creator = $commissions_creator;
			$commissions_creator->set_args( $this );
			$commissions_creator->handle_automatic_creation();
		}

		/**
		 * Setups the arguments for creating the post type
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function set_args() {
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
				'menu_icon'=>'dashicons-cart',
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