<?php
/**
 * Marketplace for WooCommerce - Marketplace tab
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Marketplace_Tab' ) ) {

	class Alg_MPWC_Vendor_Marketplace_Tab {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		/*function __construct() {
			//add_action( 'init', array( $this, 'bbloomer_add_premium_support_endpoint' ) );
			//add_filter( 'query_vars', array( $this, 'bbloomer_premium_support_query_vars', 0 ) );
			//add_filter( 'woocommerce_account_menu_items', array($this,'bbloomer_add_premium_support_link_my_account') );
			//add_action( 'woocommerce_account_marketplace_endpoint', array($this,'bbloomer_premium_support_content') );
		}

		function bbloomer_add_premium_support_endpoint() {
			add_rewrite_endpoint( 'marketplace', EP_ROOT | EP_PAGES );
		}

		function bbloomer_premium_support_query_vars( $vars ) {
			$vars[] = 'marketplace';
			return $vars;
		}

		function bbloomer_add_premium_support_link_my_account( $items ) {
			$items['marketplace'] = 'Marketplace';
			return $items;
		}

		function bbloomer_premium_support_content() {
			echo '<h3>Premium WooCommerce Support</h3><p>Welcome to the WooCommerce support area. As a premium customer, you can submit a ticket should you have any WooCommerce issues with your website, snippets or customization. <i>Please contact your theme/plugin developer for theme/plugin-related support.</i></p>';
			echo do_shortcode( ' your shortcode here ' );
		}*/


		/**
		 * Custom endpoint name.
		 *
		 * @var string
		 */
		public static $endpoint = 'marketplace';
		/**
		 * Plugin actions.
		 */
		public function __construct() {
			// Actions used to insert a new endpoint in the WordPress.
			add_action( 'init', array( $this, 'add_endpoints' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			// Change the My Accout page title.
			add_filter( 'the_title', array( $this, 'endpoint_title' ) );
			// Insering your new tab/page into the My Account page.
			add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
			add_action( 'woocommerce_account_' . self::$endpoint .  '_endpoint', array( $this, 'endpoint_content' ) );
		}
		/**
		 * Register new endpoint to use inside My Account page.
		 *
		 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
		 */
		public function add_endpoints() {
			add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
		}
		/**
		 * Add new query var.
		 *
		 * @param array $vars
		 * @return array
		 */
		public function add_query_vars( $vars ) {
			$vars[] = self::$endpoint;
			return $vars;
		}
		/**
		 * Set endpoint title.
		 *
		 * @param string $title
		 * @return string
		 */
		public function endpoint_title( $title ) {
			global $wp_query;
			$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );
			if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
				// New page title.
				$title = __( 'Marketplace', 'woocommerce' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}
			return $title;
		}
		/**
		 * Insert the new endpoint into the My Account menu.
		 *
		 * @param array $items
		 * @return array
		 */
		public function new_menu_items( $items ) {
			// Remove the logout menu item.
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );
			// Insert your custom endpoint.
			$items[ self::$endpoint ] = __( 'Marketplace', 'woocommerce' );
			// Insert back the logout item.
			$items['customer-logout'] = $logout;
			return $items;
		}
		/**
		 * Endpoint HTML content.
		 */
		public function endpoint_content() {
			//wc_get_template( 'myaccount/navigation.php' ); ?>

			<div class="woocommerce-MyAccount-content">
				<a href="<?php echo admin_url()?>">Manage your Marketplace through the <span style="text-decoration: underline">admin dashboard</span></a>
			</div>

			<?php
		}
		/**
		 * Plugin install action.
		 * Flush rewrite rules to make our custom endpoint available.
		 */
		public static function install() {
			flush_rewrite_rules();
		}
	}
}