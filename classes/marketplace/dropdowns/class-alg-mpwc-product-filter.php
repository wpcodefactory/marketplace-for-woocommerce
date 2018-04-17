<?php
/**
 * Marketplace for WooCommerce - Product filter
 *
 * @version 1.1.6
 * @since   1.0.6
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Product_Filter' ) ) {

	class Alg_MPWC_Product_Filter {

		/**
		 * Constructor
		 *
		 * @version 1.0.6
		 * @since   1.0.6
		 */
		function __construct() {

		}

		/**
		 * Creates the dropdown html.
		 *
		 * Besides filtering vendor products, redirects to shop page.
		 *
		 * @version 1.0.6
		 * @since   1.0.6
		 */
		public function html( $params = null ) {

			wp_enqueue_script( 'wc-enhanced-select' );

			//return $str;
			$query_string_product = null;
			if ( isset( $_REQUEST['alg_mpwc_product'] ) ) {
				$query_string_product = filter_var( $_REQUEST['alg_mpwc_product'], FILTER_SANITIZE_NUMBER_INT );
			}
			$selected_val = array();
			if ( ! empty( $query_string_product ) ) {
				$posts = get_posts( array(
					'post__in'  => array( $query_string_product ),
					'post_type' => 'product'
				) );
				foreach ( $posts as $post ) {
					$selected_val[ $post->ID ] = $post->post_title;
				}
			}

			?>
            <select class="alg-mpwc-product-search wc-product-search " id="alg_mpwc_product"
                    name="alg_mpwc_product"
                    data-allow_clear="true"
                    data-placeholder="Search for a product…" data-action="woocommerce_json_search_products"
                    aria-hidden="true">
				<?php if ( count( $selected_val ) > 0 ) { ?>
                    <option value="<?php echo key( $selected_val ); ?>"
                            selected="selected"><?php echo $selected_val[ key( $selected_val ) ]; ?></option>
				<?php } ?>
            </select>
            <script>
                jQuery(document.body).trigger('wc-enhanced-select-init');
            </script>
            <style>
                .select2-container--default .select2-selection--single {
                    border-color: #ddd !important;
                    border-radius: 0 !important;
                }

                .select2-container {
                    margin-top: 1px;
                    min-width: 200px;
                }
            </style>
			<?php
		}

		/**
		 * Filters things from a specific vendor.
		 *
		 * If post type is commissions, filters by meta_query _alg_mpwc_author_id. Else filters by author id
		 *
		 * @version 1.1.6
		 * @since   1.0.6
		 */
		public static function filter( $query ) {
			$screen = null;
			if ( function_exists( 'get_current_screen' ) ) {
				$screen = get_current_screen();
			}
			$query_string_product_id = null;
			if ( isset( $_REQUEST['alg_mpwc_product'] ) ) {
				$query_string_product_id = filter_var( $_REQUEST['alg_mpwc_product'], FILTER_SANITIZE_NUMBER_INT );
			}

			if (
				! $query->is_main_query() ||
				! is_admin() ||
				! $screen ||
				$screen->id != 'edit-alg_mpwc_commission' ||
				empty( $query_string_product_id )
			) {
				return;
			}

			$meta_query = $query->get( 'meta_query' );
			if ( ! is_array( $meta_query ) ) {
				$meta_query = array();
			}
			$meta_query[] = array(
				'key'     => Alg_MPWC_Post_Metas::COMMISSION_PRODUCT_IDS,
				'value'   => '\:\"?' . $query_string_product_id . '"?\;',
				'compare' => 'REGEXP',
			);
			$query->set( 'meta_query', $meta_query );
		}
	}
}