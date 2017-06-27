<?php
/**
 * Marketplace for WooCommerce - Pending vendor email
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Registration_Email' ) ) {

	class Alg_MPWC_Vendor_Registration_Email extends \WC_Email {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {

			// set ID, this simply needs to be a unique name
			$this->id = 'alg_mpwc_vendor_register_email';

			// this is the title in WooCommerce Email settings
			$this->title = 'Vendor registration';

			// this is the description in WooCommerce email settings
			$this->description = 'Sent when a customer registers as a vendor.';

			// these are the default heading and subject lines that can be overridden using the settings
			$this->heading = 'Vendor registration';
			$this->subject = 'Vendor registration';

			// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
			$this->template_html  = 'emails/marketplace-vendor-registration.php';
			//$this->template_plain = 'emails/marketplace-vendor-registration.php';

			// HTML email type
			$this->email_type = 'html';

			// Trigger on new user registration			
			add_action( 'alg_mpwc_new_vendor_registration', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();

			// this sets the recipient to the settings defined below in init_form_fields()
			$this->recipient = $this->get_option( 'recipient' );

			// if none was entered, just use the WP admin email as a fallback
			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}

		}

		/**
		 * Determine if the email should actually be sent and setup email merge variables
		 *
		 * @version  1.0.0
		 * @since    1.0.0
		 *
		 * @param $user_id
		 * @param $role
		 * @param $automatic_approval
		 *
		 * @internal param int $order_id
		 */
		public function trigger( $user_id, $role, $automatic_approval ) {			
			if ( !$user_id ) {
				return;
			}

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$this->object       = new WP_User( $user_id );
			$this->display_name = stripslashes( $this->object->display_name );
			$this->is_pending   = ! filter_var( $automatic_approval, FILTER_VALIDATE_BOOLEAN );
			$this->user_id      = $user_id;
			$this->email_type   = 'html';

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * get_content_html function.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return string
		 */
		public function get_content_html() {
			ob_start();
			wc_get_template( $this->template_html, array(
				'email_heading' => $this->get_heading(),
				'user_id'       => $this->user_id,
				'display_name'  => $this->display_name,
				'is_pending'    => $this->is_pending,
				'plain_text'    => false,
				'user_edit_link'=> admin_url("/user-edit.php?user_id={$this->user_id}"),
				'email'         => $this,
				//'sent_to_admin'      => true,
			));
			return ob_get_clean();
		}

		/**
		 * Initialize Settings Form Fields
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function init_form_fields() {

			$this->form_fields = array(
				'enabled'    => array(
					'title'   => 'Enable/Disable',
					'type'    => 'checkbox',
					'label'   => 'Enable this email notification',
					'default' => 'yes'
				),
				'recipient'  => array(
					'title'       => 'Recipient(s)',
					'type'        => 'text',
					'description' => sprintf( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', esc_attr( get_option( 'admin_email' ) ) ),
					'placeholder' => '',
					'default'     => ''
				),
				'subject'    => array(
					'title'       => 'Subject',
					'type'        => 'text',
					'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
					'placeholder' => '',
					'default'     => ''
				),
				'heading'    => array(
					'title'       => 'Email Heading',
					'type'        => 'text',
					'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
					'placeholder' => '',
					'default'     => ''
				),
			);
		}


	}
}