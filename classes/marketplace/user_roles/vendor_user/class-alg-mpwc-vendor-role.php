<?php
/**
 * Marketplace for WooCommerce - Vendor role
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Role' ) ) {

	class Alg_MPWC_Vendor_Role {

		const ROLE_VENDOR = 'alg_mpwc_vendor';

		private static $user_caps = array(
			"read"                      => true,
			"edit_product"              => true,
			"read_product"              => true,
			"delete_product"            => true,
			"edit_products"             => true,
			"delete_products"           => true,
			"delete_published_products" => true,
			"edit_published_products"   => true,
			"assign_product_terms"      => true,
			'level_0'                   => true,
			'edit_alg_mpwc_commissions' => true,
		);

		private static $order_caps = array(
			//"read_shop_orders"           => true,
			"edit_shop_orders"        => true,
			'edit_others_shop_orders' => true,
			'read_shop_order'         => true
			//"edit_published_shop_orders" => true,
			//'edit_others_shop_orders'    =>true,
			//"delete_shop_orders"         => true,
			//'create_shop_orders'         => false,
		);

		/**
		 * Initializes the vendor role manager
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public function init() {

			if ( is_admin() ) {
				// Allows the vendor user to access wp-admin
				add_filter( 'woocommerce_prevent_admin_access', array( $this, 'allow_admin_access' ) );

				// Changes role options based on admin settings
				$id      = 'alg_mpwc';
				$section = 'vendors';
				add_action( "woocommerce_update_options_{$id}_{$section}", array( $this, 'change_role_options' ) );

				// Handle dashboard widgets
				add_action( 'admin_init', array( $this, 'remove_dashboard_widgets' ) );
				add_action( 'alg_mpwc_dashboard_widget_alg_mpwc_main_widget', array(
					$this,
					'display_marketplace_widget',
				) );

				// Show total commissions value
				add_filter( 'alg_mpwc_show_total_commissions_value', array( $this, 'show_total_commissions_value' ) );

				// Adds items in marketplace menu
				add_filter( 'register_post_type_args', array( $this, 'add_items_in_marketplace_menu' ), 10, 2 );
			}

			// Limits the vendor user to see only his own posts, media, etc
			add_filter( 'pre_get_posts', array( $this, 'limit_access_to_own_posts_only' ) );

			// Adds vendors related to an order
			add_action( 'save_post', array( $this, 'add_vendors_related_to_an_order' ) );

			// Redirects user to dashboard instead of the profile page
			add_action( 'wp_login', array( $this, 'redirect_to_dashboard_after_login' ), 10, 2 );

			// Add query vars
			add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

			// Manages the order view
			new Alg_MPWC_Vendor_Order_View();
		}

		/**
		 * Adds items in marketplace menu
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $post_id
		 */
		public function add_items_in_marketplace_menu( $args, $post_type ) {
			if ( !current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				return $args;
			}
			$commissions_cpt = new Alg_MPWC_CPT_Commission();
			if ( $post_type == $commissions_cpt->id || $post_type == 'shop_order' ) {
			//if ( $post_type == 'product') {
				$args['show_in_menu'] = 'alg_mpwc_marketplace';
			}

			return $args;
		}

		/**
		 * Adds vendors related to an order
		 *
		 * When an order is created or updated, it saves a post_meta on this order about the related vendors
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $post_id
		 */
		public function add_vendors_related_to_an_order( $post_id ) {
			$post_type = get_post_type( $post_id );

			if ( "shop_order" != $post_type ) {
				return;
			}

			$order = wc_get_order( $post_id );

			$vendors = array();
			foreach ( $order->get_items() as $item ) {
				$post                   = get_post( $item->get_product_id() );
				$vendor_id              = $post->post_author;
				$vendors [ $vendor_id ] = $vendor_id;
			}

			if ( count( $vendors ) > 0 ) {
				delete_post_meta( $post_id, Alg_MPWC_Post_Metas::ORDER_RELATED_VENDOR );
				foreach ( $vendors as $vendor ) {
					add_post_meta( $post_id, Alg_MPWC_Post_Metas::ORDER_RELATED_VENDOR, $vendor );
				}
			}
		}

		/**
		 * Add query vars
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_query_vars( $vars ) {
			$vars[] = Alg_MPWC_Query_Vars::VENDOR;
			return $vars;
		}

		/**
		 * Show total commissions value if current user is a vendor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $show
		 *
		 * @return bool
		 */
		public function show_total_commissions_value( $show ) {
			if ( current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				$show = true;
			}
			return $show;
		}

		/**
		 * Redirects user to dashboard instead of the profile page
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $redirect_to
		 * @param $request
		 *
		 * @return string|void
		 */
		public function redirect_to_dashboard_after_login( $user_login, $user ) {
			if ( in_array( self::ROLE_VENDOR, $user->roles ) ) {
				exit( wp_redirect( admin_url( 'index.php' ) ) );
			}
		}

		/**
		 * Display the marketplace dashboard widget
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $display
		 *
		 * @return bool
		 */
		public function display_marketplace_widget( $display ) {
			if ( current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				$display = true;
			}

			return $display;
		}

		/**
		 * Removes dashboard widgets
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function remove_dashboard_widgets() {
			if ( ! current_user_can( self::ROLE_VENDOR ) ) {
				return;
			}

			remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
		}

		/**
		 * Changes role options based on admin settings
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function change_role_options() {
			$vendor_label          = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_ROLE_LABEL ) );
			$caps_publish_products = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_CAPABILITIES_PUBLISH_PRODUCTS ), FILTER_VALIDATE_BOOLEAN );
			$caps_upload_files     = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_CAPABILITIES_UPLOAD_FILES ), FILTER_VALIDATE_BOOLEAN );

			$args = array(
				'display_name' => $vendor_label,
				'caps'         => wp_parse_args( array(
					'publish_products' => $caps_publish_products,
					'upload_files'     => $caps_upload_files,
				), self::$user_caps ),
			);

			self::add_vendor_role( $args );
		}

		/**
		 * Allows the vendor user to access wp-admin
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function allow_admin_access( $prevent_access ) {
			if ( current_user_can( self::ROLE_VENDOR ) ) {
				$prevent_access = false;
			}

			return $prevent_access;
		}

		/**
		 * Limits the user to see only his own posts, media, etc
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function limit_access_to_own_posts_only( $query ) {
			if ( ! current_user_can( self::ROLE_VENDOR ) ) {
				return $query;
			}

			$commission_cpt = new Alg_MPWC_CPT_Commission();
			$user_id        = get_current_user_id();

			if ( ! isset( $query->query['post_type'] ) ) {
				return $query;
			}

			$post_type = $query->query['post_type'];

			// COMMISSIONS
			if ( $post_type == $commission_cpt->id ) {
				unset( $query->query['author'] );
				unset( $query->query_vars['author'] );
				$query->set( 'meta_query', array(
					array(
						'key'     => Alg_MPWC_Post_Metas::COMMISSION_AUTHOR_ID,
						'value'   => array( $user_id ),
						'compare' => 'IN',
					),
				) );
			} // SHOP ORDERS
			else if ( $post_type == 'shop_order' ) {
				unset( $query->query['author'] );
				unset( $query->query_vars['author'] );
				$query->set( 'meta_query', array(
					array(
						'key'     => Alg_MPWC_Post_Metas::ORDER_RELATED_VENDOR,
						'value'   => array( $user_id ),
						'compare' => 'IN',
					),
				) );
			} // EVERYTHING ELSE
			else {
				$query->set( 'author', $user_id );
			}

			add_filter( 'views_edit-' . $post_type . '', array(
				$this,
				'views_filter_for_own_posts',
			), 999 );

			return $query;
		}

		/**
		 * Fixes the post count
		 *
		 * https://wordpress.stackexchange.com/a/178250/25264
		 *
		 * @return mixed
		 */
		public function views_filter_for_own_posts( $views ) {
			$post_type      = get_query_var( 'post_type' );
			$author         = get_current_user_id();
			$commission_cpt = new Alg_MPWC_CPT_Commission();

			unset( $views['mine'] );

			$new_views = array(
				'all'     => __( 'All' ),
				'publish' => __( 'Published' ),
				'private' => __( 'Private' ),
				'pending' => __( 'Pending Review' ),
				'future'  => __( 'Scheduled' ),
				'draft'   => __( 'Draft' ),
				'trash'   => __( 'Trash' ),
			);

			if ( $post_type == 'shop_order' ) {

				unset( $new_views['publish'] );
				unset( $new_views['private'] );
				unset( $new_views['pending'] );
				unset( $new_views['future'] );
				unset( $new_views['draft'] );

				$order_views = array(
					'wc-processing' => __( 'Processing', 'woocommerce' ),
					'wc-on-hold'    => __( 'On hold', 'woocommerce' ),
					'wc-completed'  => __( 'Completed', 'woocommerce' ),
				);
				$new_views   = array_merge( $new_views, $order_views );
			}

			$user_id = get_current_user_id();

			foreach ( $new_views as $view => $name ) {

				if ( $post_type == $commission_cpt->id ) {
					$query = array(
						'post_type' => $post_type,
						'meta_query',
						array(
							array(
								'key'     => Alg_MPWC_Post_Metas::COMMISSION_AUTHOR_ID,
								'value'   => array( $user_id ),
								'compare' => 'IN',
							),
						),
					);
				} else if ( $post_type == 'shop_order' ) {
					$query = array(
						'post_type' => $post_type,
						'meta_query',
						array(
							array(
								'key'     => Alg_MPWC_Post_Metas::ORDER_RELATED_VENDOR,
								'value'   => array( $user_id ),
								'compare' => 'IN',
							),
						),
					);
				} else {
					$query = array(
						'author'    => $author,
						'post_type' => $post_type,
					);
				}

				if ( $view == 'all' ) {
					$query['all_posts'] = 1;
					$class              = ( get_query_var( 'all_posts' ) == 1 || get_query_var( 'post_status' ) == '' ) ? ' class="current"' : '';
					$url_query_var      = 'all_posts=1';
					if ( $post_type == 'shop_order' ) {
						$query['post_status'] = $view;
					}
				} else {
					$query['post_status'] = $view;
					$class                = ( get_query_var( 'post_status' ) == $view ) ? ' class="current"' : '';
					$url_query_var        = 'post_status=' . $view;
				}

				$result = new WP_Query( $query );

				if ( $result->found_posts > 0 ) {
					$views[ $view ] = sprintf(
						'<a href="%s"' . $class . '>' . __( $name ) . ' <span class="count">(%d)</span></a>',
						admin_url( 'edit.php?' . $url_query_var . '&post_type=' . $post_type ),
						$result->found_posts
					);
				} else {
					unset( $views[ $view ] );
				}
			}

			return $views;
		}

		/**
		 * Creates the marketplace vendor role
		 *
		 * This function is called when the plugin is enabled. Therefore, its called on the method Alg_MPWC_Core::on_plugin_activation()
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public static function add_vendor_role( $args = null ) {
			$args = wp_parse_args( $args, array(
				'caps'         => self::$user_caps,
				'display_name' => __( 'Marketplace vendor', 'marketplace-for-woocommerce' ),
			) );

			$args['caps'] = array_merge( $args['caps'], self::$order_caps );

			if ( get_role( self::ROLE_VENDOR ) ) {
				remove_role( self::ROLE_VENDOR );
			}

			add_role( self::ROLE_VENDOR, sanitize_text_field( $args['display_name'] ), $args['caps'] );
		}
	}
}