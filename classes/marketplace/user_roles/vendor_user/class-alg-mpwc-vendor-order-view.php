<?php
/**
 * Marketplace for WooCommerce - Vendor's order view
 *
 * @version 1.4.4
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Order_View' ) ) {

	class Alg_MPWC_Vendor_Order_View {

		/**
		 * Alg_MPWC_Vendor_Order_View constructor.
		 *
		 * @version  1.4.4
		 * @since    1.0.0
		 *
		 */
		function __construct() {
			// Cleans the order page doing some CSS and JS stuff
			add_action( 'current_screen', array( $this, 'clean_screen' ) );

			// Remove order metaboxes
			add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 40 );

			// Remove create order button
			add_filter( 'register_post_type_args', array( $this, 'remove_create_order_button' ), 10, 2 );

			// Handle order actions
			add_filter( 'woocommerce_admin_order_actions', array( $this, 'restrict_order_actions' ), 10, 2 );

			// Highlights the marketplace menu on order view
			add_filter( 'parent_file', array( $this, 'highlight_marketplace_menu_on_order_view' ) );
		}

		/**
		 * Highlights the marketplace menu on order view.
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 *
		 * @param $parent_file
		 *
		 * @return string
		 */
		public function highlight_marketplace_menu_on_order_view( $parent_file ) {
			global $submenu_file;

			if ( ! $this->is_read_only_user() ) {
				return $parent_file;
			}

			if ( ! $submenu_file ) {
				return $parent_file;
			}

			if ( $submenu_file == 'edit.php?post_type=shop_order' ) {
				$parent_file = 'alg_mpwc_marketplace';
			}
			return $parent_file;
		}

		/**
		 * Restrict order actions
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 *
		 * @return array
		 * @internal param $post_id
		 */
		public function restrict_order_actions( $actions, $the_order ) {
			if ( current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				$actions = array();
			}
			return $actions;
		}

		/**
		 * Restrict order view
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 *
		 * @return array
		 * @internal param $post_id
		 */
		public function remove_create_order_button( $args, $post_type ) {
			if ( ! current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				return $args;
			}

			if ( $post_type == 'shop_order' ) {
				$args['capabilities'] = array(
					'create_posts' => 'manage_woocommerce',
				);
			}

			return $args;
		}

		/**
		 * Removes order metaboxes
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 *
		 */
		public function remove_meta_boxes() {
			if ( ! $this->is_read_only_user() ) {
				return false;
			}
			remove_meta_box( 'woocommerce-order-actions', 'shop_order', 'side' );
			remove_meta_box( 'woocommerce-order-items', 'shop_order', 'normal' );
			remove_meta_box( 'woocommerce-order-downloads', 'shop_order', 'normal' );
			remove_meta_box( 'postcustom', 'shop_order', 'normal' );
		}

		/**
		 * Checks the user role
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 */
		public function is_read_only_user() {
			if ( ! is_user_logged_in() ) {
				return false;
			}

			return current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR );
		}

		/**
		 * Cleans the order page removing and disabling some elements
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 */
		public function admin_head_js() {
			?>
            <script>
				jQuery(document).ready(function ($) {
					var $edit_address_links = $('a.edit_address');
					var $poststuff_inputs = $('#poststuff input, #poststuff select, #poststuff textarea');
					var $ordernotes_add = $('#woocommerce-order-notes .add_note');
					var $postcustom = $('#postcustom');
					var $downloads = $('#woocommerce-order-downloads');
					var $orderactions = $('#woocommerce-order-actions');
					$edit_address_links.remove();
					$ordernotes_add.remove();
					$postcustom.remove();
					$downloads.remove();
					$orderactions.remove();
					$poststuff_inputs.attr('disabled', 'disabled');
				});
            </script>
			<?php
		}

		/**
		 * Cleans the order page removing some elements
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 */
		public function admin_head_style() {
			?>
            <style>
                a.edit_address,
                #woocommerce-order-notes .add_note,
                #postcustom,
                #woocommerce-order-downloads,
                #woocommerce-order-actions,
                #woocommerce-order-items .refund-items,
                .order_notes .delete_note {
                    display: none;
                }
            </style>
			<?php
		}

		/**
		 * Removes unnecessary script
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 */
		public function admin_print_scripts() {
			// Non-native select fields won't care about our "disabled" attribute.
			// Note: this is only applied on a single order view.
			wp_deregister_script( 'wc-enhanced-select' );
		}

		/**
		 * Cleans the order page doing some CSS and JS stuff
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 *
		 */
		public function clean_screen() {
			if ( ! $this->is_read_only_user() ) {
				return false;
			}

			$screen = get_current_screen();
			if ( ! is_object( $screen ) || $screen->base !== 'post' || $screen->id != 'shop_order' ) {
				return false;
			}

			add_action( 'admin_head', array( $this, 'admin_head_js' ) );
			add_action( 'admin_head', array( $this, 'admin_head_style' ) );
			add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );
		}

	}

}