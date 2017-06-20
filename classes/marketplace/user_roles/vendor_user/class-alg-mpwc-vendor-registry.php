<?php
/**
 * Marketplace for WooCommerce - Vendor registry
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Registry' ) ) {

	class Alg_MPWC_Vendor_Registry {

	    public static $user_registered=false;
	    public static $user_registered_args=array();

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			add_action( 'woocommerce_register_form', array( $this, 'add_apply_for_checkbox' ), 10 );
			add_action( 'woocommerce_edit_account_form', array( $this, 'add_apply_for_checkbox' ), 10 );
			add_action( 'woocommerce_created_customer', array( $this, 'change_user_role_to_vendor' ), 10 );
			add_action( 'woocommerce_save_account_details', array( $this, 'change_user_role_to_vendor' ), 10 );
			add_action( 'shutdown', array( $this, 'trigger_vendor_registration' ), 10, 1 );
		}

		/**
		 * Trigger vendor registration hook
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function trigger_vendor_registration() {
			if ( self::$user_registered ) {
				do_action( 'alg_mpwc_new_vendor_registration', self::$user_registered_args['user_id'], self::$user_registered_args['role'], self::$user_registered_args['automatic_approval'] );
			}
		}

		/**
		 * Validate extra register fields
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function change_user_role_to_vendor( $user_id ) {
			if ( ! isset( $_POST['alg_mpwc_apply_for_vendor'] ) ) {
				return;
			}

			if ( ! filter_var( $_POST['alg_mpwc_apply_for_vendor'], FILTER_VALIDATE_BOOLEAN ) ) {
                return;
			}

			$automatic_approval = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_REGISTRY_AUTOMATIC_APPROVAL ), FILTER_VALIDATE_BOOLEAN );

			$role = $automatic_approval ? Alg_MPWC_Vendor_Role::ROLE_VENDOR : Alg_MPWC_Vendor_Role::ROLE_VENDOR_PENDING;
			wp_update_user( array(
				'ID'   => $user_id,
				'role' => $role
			) );

			self::$user_registered=true;
			self::$user_registered_args = array(
				'user_id'            => $user_id,
				'role'               => $role,
				'automatic_approval' => $automatic_approval,
			);
		}

		/**
		 * Adds a checkbox on WooCommerce registry and edit page about becoming a vendor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_apply_for_checkbox() {
			if ( current_filter() == 'woocommerce_edit_account_form' ) {
				if ( current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) || current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR_PENDING ) || current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR_REJECTED ) ) {
					return;
				}
			}
			?>

            <div style="margin-bottom:35px;">
                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox">
                    <input class="woocommerce-form__input woocommerce-form__input-checkbox inline" name="alg_mpwc_apply_for_vendor" type="checkbox" id="alg_mpwc_apply_for_vendor"/>
                    <span><?php echo esc_html( sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_REGISTRY_CHECKBOX_TEXT ) ) ); ?></span>
                </label>
            </div>

			<?php
		}

	}
}