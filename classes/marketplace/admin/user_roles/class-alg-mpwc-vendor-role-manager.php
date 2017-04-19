<?php
/**
 * Marketplace for WooCommerce - Vendor role
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Role_Manager' ) ) {

	class Alg_MPWC_Vendor_Role_Manager {

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
		);

		/**
		 * Initializes the vendor role manager
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public function init() {

			// Allows the vendor user to access wp-admin
			add_filter( 'woocommerce_prevent_admin_access', array( $this, 'allow_admin_access' ) );

			// Limits the vendor user to see only his own posts, media, etc
			add_filter( 'pre_get_posts', array( $this, 'limit_access_to_own_posts_only' ) );

			// Changes role options based on admin settings
			$id      = 'alg_mpwc';
			$section = 'vendors';
			add_action( "woocommerce_update_options_{$id}_{$section}", array( $this, 'change_role_options' ) );
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
			} else {
				$user_id = get_current_user_id();
				$query->set( 'author', $user_id );

				// Fixes the post count
				add_filter( 'views_edit-product', array( $this, 'views_filter_for_own_posts' ) );
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
			$post_type = get_query_var( 'post_type' );
			$author    = get_current_user_id();

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
				$query = array(
					'author'    => $author,
					'post_type' => $post_type,
				);

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

			if ( get_role( self::ROLE_VENDOR ) ) {
				remove_role( self::ROLE_VENDOR );
			}

			add_role( self::ROLE_VENDOR, sanitize_text_field( $args['display_name'] ), $args['caps'] );
		}
	}
}