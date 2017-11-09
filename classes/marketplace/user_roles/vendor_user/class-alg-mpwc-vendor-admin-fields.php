<?php
/**
 * Marketplace for WooCommerce - Vendor admin fields and metaboxes
 *
 * @version 1.0.5
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Admin_Fields' ) ) {

	class Alg_MPWC_Vendor_Admin_Fields {

		public $cmb_id = 'alg_mpwc_vendor_admin_fields';

		// User metas
		public $meta_description = '_alg_mpwc_description';
		public $meta_store_title = '_alg_mpwc_store_title';
		public $meta_address = '_alg_mpwc_address';
		public $meta_city = '_alg_mpwc_city';
		public $meta_state = '_alg_mpwc_state';
		public $meta_phone = '_alg_mpwc_phone';
		public $meta_logo = '_alg_mpwc_logo';
		public $meta_bank_account_name = '_alg_mpwc_bank_account_name';
		public $meta_bank_name = '_alg_mpwc_bank_name';
		public $meta_aba_routing_number = '_alg_mpwc_aba_routing_number';
		public $meta_bank_address = '_alg_mpwc_bank_address';
		public $meta_iban = '_alg_mpwc_iban';
		public $meta_account_holder_name = '_alg_mpwc_account_holder_name';
		public $meta_paypal_email = '_alg_mpwc_paypal_email';
		public $meta_commission_fixed_value = '_alg_mpwc_commission_fixed_value';
		public $meta_commission_percentage_value = '_alg_mpwc_commission_percentage_value';
		//public $meta_commission_base = '_alg_mpwc_commission_base';
		public $meta_block_vendor = '_alg_mpwc_blocked';

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {

		}

		/**
		 * Shows admin fields if edited user is vendor
		 *
		 * @param $cmb
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return bool
		 */
		public function show_fields_if_is_vendor( $cmb ) {
			global $user_id;
			if ( ! $user_id || empty( $user_id ) ) {
				return false;
			}

			$edited_user = new WP_User( $user_id );
			if ( in_array( Alg_MPWC_Vendor_Role::ROLE_VENDOR, $edited_user->roles ) ) {
				return true;
			}
		}

		/**
		 * Setups custom css
         *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function setup_custom_css() {
			$object = 'user'; // post | term
			$cmb_id = $this->cmb_id;
			add_action( "cmb2_after_{$object}_form_{$cmb_id}", array( $this, 'custom_css' ), 10, 2 );
		}

		/**
		 * Creates custom css
		 *
		 * @version 1.0.5
		 * @since   1.0.0
		 */
		public function custom_css( $post_id, $cmb ) {
			if(!is_admin()){
				return;
			}
			?>
            <style type="text/css" media="screen">
                #cmb2-metabox-alg_mpwc_vendor_admin_fields {
                    border-top: 2px #ccc dashed;
                    margin: 35px 0 50px;
                    padding: 20px 0 35px;
                    border-bottom: 2px #ccc dashed;
                }

                #cmb2-metabox-alg_mpwc_vendor_admin_fields .cmb-type-title {
                    margin-bottom: 15px !important;
                }

                .cmb2-id-alg-mpwc-title-payment, .cmb2-id-alg-mpwc-commissions-title {
                    margin-top: 40px !important;
                }

                #cmb2-metabox-alg_mpwc_vendor_admin_fields .cmb-type-checkbox .cmb-td {
                    padding-top: 24px !important;
                }

                #cmb2-metabox-alg_mpwc_vendor_admin_fields .cmb-row:not(.cmb-type-checkbox) .cmb-td {
                    padding-top: 20px !important;
                }
            </style>
			<?php
		}

		/**
		 * Sanitizes the option to block a vendor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $value
		 * @param $field_args
		 * @param $field
		 *
		 * @return mixed|void
		 */
		public function sanitize_vendor_block_option( $value, $field_args, $field ) {
			$value = apply_filters( 'alg_mpwc_sanitize_block_vendor_option', $value, $field_args, $field );
			return $value;
		}

		/**
		 * Adds vendor user admin fields
		 *
		 * @version 1.0.5
		 * @since   1.0.0
		 */
		public function add_fields() {
			$cmb_user = new_cmb2_box( array(
				'id'               => $this->cmb_id,
				'title'            => __( 'Vendor admin fields', 'marketplace-for-woocommerce' ),
				'object_types'     => array( 'user' ),
				'show_names'       => true,
				'new_user_section' => 'add-new-user',
				'show_on_cb'       => array( $this, 'show_fields_if_is_vendor' ),
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Marketplace', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'Fields regarding the Marketplace', 'marketplace-for-woocommerce' ),
				'id'       => 'alg_mpwc_title',
				'type'     => 'title',
				'on_front' => false,
			) );

			$cmb_user->add_field( array(
				'name'            => __( 'Block vendor', 'marketplace-for-woocommerce' ),
				'desc'            => __( 'Blocks vendor products and its public page', 'marketplace-for-woocommerce' ),
				'id'              => $this->meta_block_vendor,
				'type'            => 'checkbox',
				'on_front'        => true,
				'sanitization_cb' => array( $this, 'sanitize_vendor_block_option' ),
				'show_on_cb'      => array( $this, 'show_block_vendor_field' ),
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Logo', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_logo,
				'type'     => 'file',
				'options'  => array(
					'url' => false, // Hide the text input for the url
				),
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( "Store's name", 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_store_title,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Description', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_description,
				'type'     => 'wysiwyg',
				'options'  => array(
					'textarea_rows' => get_option( 'default_post_edit_rows', 5 ),
					'media_buttons' => false,
					'teeny'         => true,
				),
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Address', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_address,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'City', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_city,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'State', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_state,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Phone', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_phone,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Payment details', 'marketplace-for-woocommerce' ),
				'id'       => 'alg_mpwc_title_payment',
				'desc'     => 'Info about the payment details',
				'type'     => 'title',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Bank account name', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_bank_account_name,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Bank name', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_bank_name,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Bank address', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_bank_address,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'ABA routing number', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_aba_routing_number,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'IBAN', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_iban,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Account holder name', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_account_holder_name,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Paypal email', 'marketplace-for-woocommerce' ),
				'id'       => $this->meta_paypal_email,
				'type'     => 'text',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'     => __( 'Commission', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'Commission values', 'marketplace-for-woocommerce' ).'<br />'.__( 'Note: In case of empty values, the default ones will be used from Marketplace settings', 'marketplace-for-woocommerce' ),
				'id'       => 'alg_mpwc_commissions_title',
				'type'     => 'title',
				'on_front' => true,
			) );

			$cmb_user->add_field( array(
				'name'       => __( 'Fixed value', 'marketplace-for-woocommerce' ) . ' (' . get_woocommerce_currency() . ')',
				'id'         => $this->meta_commission_fixed_value,
				'desc'       => __( 'Note: Use 0 if you do not want fixed values. Empty values will be interpreted as default values from settings', 'marketplace-for-woocommerce' ) . ' (%)',
				'type'       => 'text',
				'attributes' => array(
					'type'     => 'number',
					'readonly' => current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ? 'readonly' : false,
				),
				'on_front'   => true,
			) );

			$cmb_user->add_field( array(
				'name'       => __( 'Percentage value', 'marketplace-for-woocommerce' ) . ' (%)',
				'desc'       => __( 'Note: Use 0 if you do not want percentages. Empty values will be interpreted as default values from settings', 'marketplace-for-woocommerce' ) . ' (%)',
				'id'         => $this->meta_commission_percentage_value,
				'type'       => 'text',
				'attributes' => array(
					'type'     => 'number',
					'readonly' => current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR ) ? 'readonly' : false,					
				),
				'on_front'   => true,
			) );

			do_action('alg_mpwc_vendor_admin_fields', $cmb_user);
		}

		/**
		 * Hides the block vendor option from vendors
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return bool
		 */
		public function show_block_vendor_field() {
			return ! current_user_can( Alg_MPWC_Vendor_Role::ROLE_VENDOR );
		}
	}
}