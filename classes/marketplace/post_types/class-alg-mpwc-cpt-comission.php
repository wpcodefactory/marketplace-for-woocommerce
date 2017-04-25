<?php
/**
 * Marketplace for WooCommerce - Comission custom post type
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_CPT_Comission' ) ) {
	class Alg_MPWC_CPT_Comission {

		protected $labels;
		protected $args;
		public $id = 'alg_mpwc_comission';

		function __construct() {
			$labels = array(
				'name'               => __( 'Comissions', 'marketplace-for-woocommerce' ),
				'singular_name'      => __( 'Comission', 'marketplace-for-woocommerce' ),
				'menu_name'          => __( 'Comissions', 'marketplace-for-woocommerce' ),
				'name_admin_bar'     => __( 'Comission', 'marketplace-for-woocommerce' ),
				'add_new'            => __( 'Add New', 'marketplace-for-woocommerce' ),
				'add_new_item'       => __( 'Add New Comission', 'marketplace-for-woocommerce' ),
				'new_item'           => __( 'New Comission', 'marketplace-for-woocommerce' ),
				'edit_item'          => __( 'Edit Comission', 'marketplace-for-woocommerce' ),
				'view_item'          => __( 'View Comission', 'marketplace-for-woocommerce' ),
				'all_items'          => __( 'All Comissions', 'marketplace-for-woocommerce' ),
				'search_items'       => __( 'Search Comissions', 'marketplace-for-woocommerce' ),
				'parent_item_colon'  => __( 'Parent Comissions:', 'marketplace-for-woocommerce' ),
				'not_found'          => __( 'No Comissions found.', 'marketplace-for-woocommerce' ),
				'not_found_in_trash' => __( 'No Comissions found in Trash.', 'marketplace-for-woocommerce' ),
			);

			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Description.', 'marketplace-for-woocommerce' ),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => false,
				'rewrite'            => array( 'slug' => 'comission' ),
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title' ),
			);

			$this->labels = $labels;
			$this->args   = $args;
		}

		public function register() {
			$args = $this->args;
			register_post_type( $this->id, $args );
		}


	}
}