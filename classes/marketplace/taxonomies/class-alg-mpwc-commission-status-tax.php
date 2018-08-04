<?php
/**
 * Marketplace for WooCommerce - Commission status taxonomy
 *
 * @version 1.2.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Commission_Status_Tax' ) ) {
	class Alg_MPWC_Commission_Status_Tax {

		public $id = 'alg_mpwc_c_status_tax';

		// Taxonomy args
		protected $labels;
		protected $args;

		public static $terms_arr = array(
			array(
				'slug'  => 'paid',
				'label' => 'Paid',
			),
			array(
				'slug'  => 'unpaid',
				'label' => 'Unpaid',
			),
			array(
				'slug'  => 'need-refund',
				'label' => 'Need Refund',
			),
			array(
				'slug'  => 'refunded',
				'label' => 'Refunded',
			),
			array(
				'slug'  => 'reserved',
				'label' => 'Reserved',
			),
		);

		/**
		 * Setups the post type
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function setup() {
			$this->set_args();
		}

		/**
		 * Creates initial status terms for commission taxonomy.
		 *
		 * Called when the plugin is enabled on Alg_MPWC_Core::on_plugin_activation()
		 *
		 * @version 1.1.2
		 * @since   1.0.0
		 */
		public function create_initial_status() {
			$terms = self::$terms_arr;
			foreach ( $terms as $term ) {
				if ( term_exists( $term['slug'], $this->id ) == null ) {
					$this->add_term( $term['slug'] );
				}
			}
			/*if ( term_exists( 'paid', $this->id ) == null ) {
				$response = wp_insert_term(
					__( 'Paid', 'marketplace-for-woocommerce' ),
					$this->id,
					array(
						'slug' => 'paid',
					)
				);
			}

			if ( term_exists( 'unpaid', $this->id ) == null ) {
				$response = wp_insert_term(
					__( 'Unpaid', 'marketplace-for-woocommerce' ),
					$this->id,
					array(
						'slug' => 'unpaid',
					)
				);
			}

			if ( term_exists( 'need-refund', $this->id ) == null ) {
				$response = wp_insert_term(
					__( 'Need refund', 'marketplace-for-woocommerce' ),
					$this->id,
					array(
						'slug' => 'need-refund',
					)
				);
			}

			if ( term_exists( 'refunded', $this->id ) == null ) {
				$response = wp_insert_term(
					__( 'Refunded', 'marketplace-for-woocommerce' ),
					$this->id,
					array(
						'slug' => 'refunded',
					)
				);
			}*/
		}

		/**
		 * Gets a term
		 *
		 * @version 1.1.2
		 * @since   1.1.2
		 *
		 * @param $term_slug 'refunded' | 'need-refund' | 'paid' | 'unpaid'
		 *
		 * @return array|WP_Error
		 */
		public function add_term( $term_slug ) {
			$terms = self::$terms_arr;

			$term  = wp_list_filter( $terms, array( 'slug' => $term_slug ) );
			$label = reset( $term )['label'];

			return wp_insert_term(
				__( $label, 'marketplace-for-woocommerce' ),
				$this->id,
				array(
					'slug' => $term_slug,
				)
			);
		}

		/**
		 * Gets a term
		 *
		 * @version 1.1.2
		 * @since   1.1.2
		 *
		 * @param $term_slug 'refunded' | 'need-refund' | 'paid' | 'unpaid'
		 * @param bool $smart_creation
		 *
		 * @return array|false|WP_Term
		 */
		public function get_term( $term_slug, $smart_creation = true ) {
			if ( term_exists( $term_slug, $this->id ) == null ) {
				if ( $smart_creation ) {
					$this->add_term( $term_slug );
				}
			}

			if ( term_exists( $term_slug, $this->id ) != null ) {
				return get_term_by( 'slug', $term_slug, $this->id );
			}
		}

		/**
		 * Setups the arguments for creating the taxonomy
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function set_args() {
			// Add new taxonomy, make it hierarchical (like categories)
			$labels = array(
				'name'              => __( 'Status', 'marketplace-for-woocommerce' ),
				'singular_name'     => __( 'Status', 'marketplace-for-woocommerce' ),
				'search_items'      => __( 'Search Status', 'marketplace-for-woocommerce' ),
				'all_items'         => __( 'All Status', 'marketplace-for-woocommerce' ),
				'parent_item'       => __( 'Parent Status', 'marketplace-for-woocommerce' ),
				'parent_item_colon' => __( 'Parent Status:', 'marketplace-for-woocommerce' ),
				'edit_item'         => __( 'Edit Status', 'marketplace-for-woocommerce' ),
				'update_item'       => __( 'Update Status', 'marketplace-for-woocommerce' ),
				'add_new_item'      => __( 'Add New Status', 'marketplace-for-woocommerce' ),
				'new_item_name'     => __( 'New Status Name', 'marketplace-for-woocommerce' ),
				'menu_name'         => __( 'Status', 'marketplace-for-woocommerce' ),
			);

			$args = array(
				'hierarchical'       => true,
				'labels'             => $labels,
				'show_ui'            => true,
				'show_admin_column'  => false,
				'show_in_quick_edit' => false,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'status' ),
			);

			$this->labels = $labels;
			$this->args   = $args;
		}

		/**
		 * Registers the taxonomy
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function register() {
			$args           = $this->args;
			$commission_cpt = new Alg_MPWC_CPT_Commission();
			register_taxonomy( $this->id, $commission_cpt->id, $args );
		}
	}
}