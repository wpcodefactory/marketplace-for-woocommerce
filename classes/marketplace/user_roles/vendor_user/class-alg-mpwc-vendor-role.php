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
			"upload_files"              => true,
			'level_0'                   => true,
			'edit_alg_mpwc_commissions' => true,
		);

		private static $order_caps = array(
			"edit_published_shop_orders" => true,
			"edit_shop_orders"           => true,
			"delete_shop_orders"         => true,
			"read_shop_orders"           => true,
			'create_shop_orders'         => false,
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

				// Limits the vendor user to see only his own posts, media, etc
				add_filter( 'pre_get_posts', array( $this, 'limit_access_to_own_posts_only' ) );

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

				add_filter( 'alg_mpwc_show_total_commissions_value', array( $this, 'show_total_commissions_value' ) );
			}

			// Redirects user to dashboard instead of the profile page
			add_action( 'wp_login', array( $this, 'redirect_to_dashboard_after_login' ), 10, 2 );
		}

		/**
		 * Show total commissions value if current user is a vendor
		 *
		 * @param $show
		 *
		 * @return bool
		 */
		public function show_total_commissions_value($show){
			if ( current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ) {
				$show=true;
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

			$args = array(
				'display_name' => $vendor_label,
				'caps'         => wp_parse_args( array(
					'publish_products' => $caps_publish_products,
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

			if ( isset( $query->query['post_type'] ) && $query->query['post_type'] != $commission_cpt->id ) {
				$query->set( 'author', $user_id );
				add_filter( 'views_edit-' . $query->query['post_type'] . '', array(
					$this,
					'views_filter_for_own_posts',
				) );
			} else {
				unset( $query->query['author'] );
				unset( $query->query_vars['author'] );
				$query->set( 'meta_query', array(
					array(
						'key'     => Alg_MPWC_Post_Metas::COMMISSION_AUTHOR_ID,
						'value'   => array( $user_id ),
						'compare' => 'IN',
					),
				) );
				add_filter( 'views_edit-' . $query->query['post_type'] . '', array(
					$this,
					'views_filter_for_own_posts',
				) );
			}


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

			foreach ( $new_views as $view => $name ) {

				if ( $post_type != $commission_cpt->id ) {
					$query = array(
						'author'    => $author,
						'post_type' => $post_type,
					);
				} else {
					$user_id = get_current_user_id();
					$query   = array(
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
				}

				if ( $view == 'all' ) {
					$query['all_posts'] = 1;
					$class              = ( get_query_var( 'all_posts' ) == 1 || get_query_var( 'post_status' ) == '' ) ? ' class="current"' : '';
					$url_query_var      = 'all_posts=1';

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