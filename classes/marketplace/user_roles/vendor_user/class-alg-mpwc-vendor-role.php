<?php
/**
 * Marketplace for WooCommerce - Vendor role
 *
 * @version 1.4.1
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Role' ) ) {

	class Alg_MPWC_Vendor_Role {

		const ROLE_VENDOR          = 'alg_mpwc_vendor';
		const ROLE_VENDOR_PENDING  = 'alg_mpwc_vendor_pending';
		const ROLE_VENDOR_REJECTED = 'alg_mpwc_vendor_rejected';

		/**
		 * Is user vendor?
		 *
		 * @version 1.2.3
		 * @since   1.2.3
		 *
		 * @param $user_id
		 *
		 * @return bool
		 */
		public static function is_user_vendor( $user_id ) {
			$user_meta  = get_userdata( $user_id );
			$user_roles = $user_meta->roles;
			if ( in_array( self::ROLE_VENDOR, $user_roles, true ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Initializes the vendor role manager.
		 *
		 * @version 1.4.1
		 * @since   1.0.0
		 */
		public function init() {

			if ( is_admin() ) {
				// Allows the vendor user to access wp-admin
				add_filter( 'woocommerce_prevent_admin_access', array( $this, 'allow_admin_access' ) );

				// Changes role options based on admin settings
				$id      = 'alg_mpwc';
				$section = 'vendors';
				add_action( "woocommerce_update_options_{$id}_{$section}", array( $this, 'add_vendor_role' ) );

				// Handle dashboard widgets
				add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
				add_action( 'alg_mpwc_dashboard_widget_alg_mpwc_main_widget', array(
					$this,
					'display_marketplace_widget',
				) );

				// Show total commissions value
				add_filter( 'alg_mpwc_show_total_commissions_value', array( $this, 'show_total_commissions_value' ) );

				// Adds items in marketplace menu
				add_filter( 'register_post_type_args', array( $this, 'add_items_in_marketplace_menu' ), 10, 2 );

				// Removes vendor's wordpress dashboard logo
				add_action( 'admin_bar_menu', array( $this, 'remove_wp_info' ), 999 );

				// Removes vendor's wordpress dashboard footer texts
				add_filter( 'admin_footer_text', array($this,'remove_footer_text'), 11 );
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

			// Manages media deleting
			add_filter( 'user_has_cap', array( $this, 'manages_media_deleting' ), 10, 4 );

			// Removes vendor's core updates notifications
			add_action( 'after_setup_theme', array( $this, 'remove_core_updates' ) );

			// Remove WooCommerce menu
			add_action( 'admin_init', array( $this, 'remove_woocommerce_menu' ) );
		}

		/**
		 * remove_woocommerce_menu.
		 *
		 * @version 1.4.1
		 * @since   1.4.1
		 */
		function remove_woocommerce_menu(){
			if ( current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				remove_menu_page( 'woocommerce' );
			}
		}

		/**
		 * Removes vendor's core updates notifications.
		 *
		 * @version 1.2.7
		 * @since   1.0.0
		 */
		public function remove_core_updates() {
			if ( ! current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				return;
			}
			$remove_wp_info = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_HIDE_VENDOR_WP_INFO ), FILTER_VALIDATE_BOOLEAN );
			if ( ! $remove_wp_info ) {
				return;
			}
			add_action( 'init', array( $this, 'remove_wp_version_check_action' ), 2 );
			add_filter( 'pre_option_update_core', '__return_null' );
			add_filter( 'pre_site_transient_update_core', '__return_null' );
		}

		/**
		 * remove_wp_version_check_action.
		 *
		 * @version 1.2.7
		 * @since   1.2.7
		 */
		function remove_wp_version_check_action() {
			remove_action( 'init', 'wp_version_check' );
		}

		/**
		 * Removes vendor's wordpress dashboard footer text.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function remove_footer_text() {
			if ( ! current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				return;
			}
			$remove_wp_info = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_HIDE_VENDOR_WP_INFO ), FILTER_VALIDATE_BOOLEAN );
			if ( ! $remove_wp_info ) {
				return;
			}
			return false;
		}

		/**
		 * Removes vendor's wordpress dashboard logo
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function remove_wp_info( $wp_admin_bar ) {
			if ( ! current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				return;
			}
			$remove_wp_info = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_HIDE_VENDOR_WP_INFO ), FILTER_VALIDATE_BOOLEAN );
			if ( ! $remove_wp_info ) {
				return;
			}
			$wp_admin_bar->remove_node( 'wp-logo' );
		}

		/**
		 * Manages media deleting
		 *
		 * @version 1.3.4
		 * @since   1.0.0
		 *
		 * @param $post_id
		 */
		function manages_media_deleting( $user_caps, $req_cap, $args, $user ) {

			// if not `alg_mpwc_vendor` just return original array
			if ( ! $user || ! in_array( 'alg_mpwc_vendor', ( array ) $user->roles ) ) {
				return $user_caps;
			}

			// if no post is connected with capabilities check just return original array
			if ( empty( $args[2] ) ) {
				return $user_caps;
			}

			$post = get_post( $args[2] );

			if ( $post ) {
				if ( 'attachment' == $post->post_type ) {
					if ( isset( $req_cap[0] ) ) {
						if ( $post->post_author == $user->ID ) {
							$user_caps[ $req_cap[0] ] = true;
							return $user_caps;
						}
					}
				}
			}

			// for any other post type return original capabilities
			return $user_caps;
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
			if ( ! current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				return $args;
			}
			$commissions_cpt = new Alg_MPWC_CPT_Commission();
			if ( $post_type == $commissions_cpt->id || $post_type == 'shop_order' ) {
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
			$vars[] = Alg_MPWC_Query_Vars::VENDOR_PUBLIC_PAGE;
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
		 * @version 1.0.5
		 * @since   1.0.0
		 *
		 * @param $redirect_to
		 * @param $request
		 *
		 * @return string|void
		 */
		public function redirect_to_dashboard_after_login( $user_login, $user ) {
			if ( in_array( self::ROLE_VENDOR, $user->roles ) ) {
				$redirect_to_admin = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_REDIRECT_TO_ADMIN, true ), FILTER_VALIDATE_BOOLEAN );
				if ( $redirect_to_admin ) {
					exit( wp_redirect( admin_url( 'index.php' ) ) );
				}
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
		 * @version 1.0.5
		 * @since   1.0.0
		 */
		function remove_dashboard_widgets() {
			if ( ! current_user_can( self::ROLE_VENDOR ) ) {
				return;
			}

			global $wp_meta_boxes;
			$wp_meta_boxes['dashboard']['normal']['core'] = array();
			$wp_meta_boxes['dashboard']['side']['core'] = array();
		}

		/**
		 * Allows the vendor user to access wp-admin
		 *
		 * @version 1.1.8
		 * @since   1.0.0
		 */
		public function allow_admin_access( $prevent_access ) {
			if (
				current_user_can( self::ROLE_VENDOR ) &&
				get_option( Alg_MPWC_Settings_Vendor::OPTION_CAPABILITIES_ENTER_ADMIN, 'yes' ) === 'yes'
			) {
				$prevent_access = false;
			}

			return $prevent_access;
		}

		/**
		 * Limits the user to see only its own posts, media, etc
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function limit_access_to_own_posts_only( $query ) {
			if ( ! current_user_can( self::ROLE_VENDOR ) ) {
				return $query;
			}
			if ( ! is_admin() ) {
				return $query;
			}
			if( defined( 'DOING_AJAX' ) && DOING_AJAX ){
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
		 * Fixes the post count.
		 *
		 * @see     https://wordpress.stackexchange.com/a/178250/25264
		 *
		 * @return  mixed
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
		 * Creates the marketplace vendor role.
		 *
		 * This function is called when the plugin is enabled. Therefore, it's called on the method Alg_MPWC_Core::on_plugin_activation()
		 *
		 * @version 1.4.0
		 * @since   1.0.0
		 *
		 * @todo    [next] (dev) remove `edit_others_shop_orders`?
		 * @todo    [next] (dev) caps: media: show only vendor's media?
		 * @todo    [next] (dev) remove `$default_user_caps`?
		 */
		public static function add_vendor_role() {

			$default_user_caps = array(
				'publish_products'          => false,
				'upload_files'              => true,
				'read'                      => true,
				'edit_product'              => true,
				'read_product'              => true,
				'delete_product'            => true,
				'edit_products'             => true,
				'delete_products'           => true,
				'delete_published_products' => true,
				'edit_published_products'   => true,
				'assign_product_terms'      => true,
				'level_0'                   => true,
				'edit_alg_mpwc_commissions' => true,
				'edit_shop_orders'          => false,
				'edit_others_shop_orders'   => false,
				'read_shop_order'           => false,
			);

			$vendor_label          = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_ROLE_LABEL, __( 'Marketplace vendor', 'marketplace-for-woocommerce' ) ) );
			$caps_publish_products = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_CAPABILITIES_PUBLISH_PRODUCTS, 'no' ),  FILTER_VALIDATE_BOOLEAN );
			$caps_upload_files     = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_CAPABILITIES_UPLOAD_FILES,     'yes' ), FILTER_VALIDATE_BOOLEAN );
			$view_orders           = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_CAPABILITIES_VIEW_ORDERS,      'no' ),  FILTER_VALIDATE_BOOLEAN );
			$delete_products       = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_CAPABILITIES_DELETE_PRODUCTS,  'no' ),  FILTER_VALIDATE_BOOLEAN );

			$args = array(
				'display_name' => $vendor_label,
				'caps'         => wp_parse_args( array(
					'publish_products'          => $caps_publish_products,
					'upload_files'              => $caps_upload_files,
					'edit_shop_orders'          => $view_orders,
					'edit_others_shop_orders'   => $view_orders,
					'read_shop_order'           => $view_orders,
					'delete_products'           => $delete_products,
					'delete_product'            => $delete_products,
					'delete_published_products' => $delete_products,
				), $default_user_caps ),
			);

			if ( get_role( self::ROLE_VENDOR ) ) {
				remove_role( self::ROLE_VENDOR );
			}

			add_role( self::ROLE_VENDOR, sanitize_text_field( $args['display_name'] ), $args['caps'] );

			self::add_pending_rejected_vendor_roles( $args );
		}

		/**
		 * Creates the marketplace vendor roles for pending and rejected status.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public static function add_pending_rejected_vendor_roles( $args ) {

			if ( get_role( self::ROLE_VENDOR_PENDING ) ) {
				remove_role( self::ROLE_VENDOR_PENDING );
			}

			if ( get_role( self::ROLE_VENDOR_REJECTED ) ) {
				remove_role( self::ROLE_VENDOR_REJECTED );
			}

			$caps = array( 'read' => true );

			add_role( self::ROLE_VENDOR_PENDING,  sanitize_text_field( $args['display_name'] ) . ' (' . __( 'pending', 'marketplace-for-woocommerce' )  . ')', $caps );
			add_role( self::ROLE_VENDOR_REJECTED, sanitize_text_field( $args['display_name'] ) . ' (' . __( 'rejected', 'marketplace-for-woocommerce' ) . ')', $caps );
		}
	}
}
