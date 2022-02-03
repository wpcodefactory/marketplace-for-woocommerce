<?php
/**
 * Marketplace for WooCommerce - General Section Settings
 *
 * @version 1.5.1
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_MPWC_Settings_General' ) ) :

class Alg_MPWC_Settings_General extends Alg_MPWC_Settings_Section {

	const OPTION_ENABLE_PLUGIN                   = 'alg_mpwc_opt_enable';

	const OPTION_COMMISSIONS_DEFAULT_STATUS      = 'alg_mpwc_opt_commissions_default_status';
	const OPTION_COMMISSIONS_FIXED_VALUE         = 'alg_mpwc_opt_commissions_fixed_value';
	const OPTION_COMMISSIONS_PERCENTAGE_VALUE    = 'alg_mpwc_opt_commissions_percentage_value';
	const OPTION_COMMISSIONS_AUTOMATIC_CREATION  = 'alg_mpwc_opt_commissions_automatic_creation';
	const OPTION_COMMISSIONS_ORDER_REFUND_STATUS = 'alg_mpwc_opt_commissions_order_refund_status';
	const OPTION_COMMISSIONS_GROUP_BY_AUTHORS    = 'alg_mpwc_opt_commissions_group_authors';
	const OPTION_COMMISSIONS_QUANTITY_SEPARATES  = 'alg_mpwc_opt_commissions_quantity_separates';
	const OPTION_COMMISSIONS_EMAIL_ENABLE        = 'alg_mpwc_opt_commissions_email_enable';
	const OPTION_COMMISSIONS_EMAIL_MESSAGE       = 'alg_mpwc_opt_commissions_email_message';
	const OPTION_COMMISSIONS_EMAIL_SUBJECT       = 'alg_mpwc_opt_commissions_email_subject';

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct( $handle_autoload = true ) {
		$this->id   = '';
		$this->desc = __( 'General', 'marketplace-for-woocommerce' );
		parent::__construct( $handle_autoload );
	}

	/**
	 * get_commission_statuses.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_commission_statuses() {
		$commission_statuses = array();
		foreach ( Alg_MPWC_Commission_Status_Tax::$terms_arr as $commission_status ) {
			$commission_statuses[ $commission_status['slug'] ] = $commission_status['label'];
		}
		return $commission_statuses;
	}

	/**
	 * get_settings.
	 *
	 * @version 1.5.1
	 * @since   1.0.0
	 * @todo    [feature] (maybe) create "include taxes" option
	 */
	function get_settings( $settings = null ) {
		$new_settings = array(

			array(
				'title'    => __( 'Marketplace Options', 'marketplace-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_mpwc_opt',
			),
			array(
				'title'    => __( 'Marketplace for WooCommerce', 'marketplace-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable plugin', 'marketplace-for-woocommerce' ) . '</strong>',
				'id'       => self::OPTION_ENABLE_PLUGIN,
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_mpwc_opt',
			),

			// Commissions
			array(
				'title'    => __( 'Commissions', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'Funds that should be transferred to vendors after a sale is made.', 'marketplace-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_mpwc_comissions_opt',
			),
			array(
				'title'    => __( 'Group by author', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'Group commissions by author', 'marketplace-for-woocommerce' ),
				'desc_tip' => __( 'If an order has products from X unique authors, X commissions will be created.', 'marketplace-for-woocommerce' ) . '<br />' .
					__( "Note: This option doesn't work with <strong>Quantity separates</strong> option.", 'marketplace-for-woocommerce' ),
				'id'       => self::OPTION_COMMISSIONS_GROUP_BY_AUTHORS,
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Quantity separates', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'Create commissions for each item separately', 'marketplace-for-woocommerce' ),
				'desc_tip' => __( 'If an order has 10 x product A and 15 x product B, 25 commissions will be created.', 'marketplace-for-woocommerce' ) . '<br />' .
					__( "Note: This option cancels <strong>Group by author</strong> option.", 'marketplace-for-woocommerce' ),
				'id'       => self::OPTION_COMMISSIONS_QUANTITY_SEPARATES,
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Default commission status', 'marketplace-for-woocommerce' ),
				'id'       => self::OPTION_COMMISSIONS_DEFAULT_STATUS,
				'default'  => 'unpaid',
				'options'  => $this->get_commission_statuses(),
				'type'     => 'select',
				'class'    => 'wc-enhanced-select'
			),
			array(
				'title'    => __( 'Fixed value', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'Fixed value that will be transfered to vendors after a sale is made.', 'marketplace-for-woocommerce' ),
				'id'       => self::OPTION_COMMISSIONS_FIXED_VALUE,
				'default'  => 0,
				'type'     => 'number',
			),
			array(
				'title'    => __( 'Percentage value', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'Percentage value that will be transfered to vendors after a sale is made.', 'marketplace-for-woocommerce' ),
				'id'       => self::OPTION_COMMISSIONS_PERCENTAGE_VALUE,
				'default'  => 80,
				'type'     => 'number',
			),
			array(
				'title'    => __( 'Creation status', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'When orders change to one of these status, correspondent commissions will be automatically created.', 'marketplace-for-woocommerce' ),
				'desc_tip' => __( 'Note 1: Leave it empty if you do not want to create commissions automatically.', 'marketplace-for-woocommerce' ) . '<br><br>' .
					__( 'Note 2: If you select 2 or more status, commissions will not be created twice, no worries.', 'marketplace-for-woocommerce' ),
				'id'       => self::OPTION_COMMISSIONS_AUTOMATIC_CREATION,
				'default'  => array( 'wc-completed' ),
				'options'  => wc_get_order_statuses(),
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select'
			),
			array(
				'title'    => __( 'Refund status', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'When orders change to one of these status, correspondent commissions will be automatically set as "Need Refund".', 'marketplace-for-woocommerce' ),
				'desc_tip' => __( 'Note: Leave it empty if you do not want to set commissions automatically to "Need Refund".', 'marketplace-for-woocommerce' ),
				'id'       => self::OPTION_COMMISSIONS_ORDER_REFUND_STATUS,
				'default'  => array( 'wc-refunded', 'wc-cancelled', 'wc-failed' ),
				'options'  => wc_get_order_statuses(),
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select'
			),
			array(
				'title'    => __( 'Create zero commissions', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'Create', 'marketplace-for-woocommerce' ),
				'desc_tip' => __( 'Sets if you want the commissions with zero total value to be created (e.g. for orders with zero total sum).', 'marketplace-for-woocommerce' ),
				'id'       => 'alg_mpwc_opt_commissions_create_zero',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_mpwc_comissions_opt',
			),

			// Email
			array(
				'title'    => __( 'Notification Email', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'Email that will be sent to vendors after a sale is made.', 'marketplace-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_mpwc_email_opt',
			),
			array(
				'title'    => __( 'Notification email', 'marketplace-for-woocommerce' ),
				'desc'     => __( 'Enable', 'marketplace-for-woocommerce' ),
				'id'       => self::OPTION_COMMISSIONS_EMAIL_ENABLE,
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Email subject', 'marketplace-for-woocommerce' ),
				'id'       => self::OPTION_COMMISSIONS_EMAIL_SUBJECT,
				'default'  => __( 'You have a new sale on {site_title} from {order_date}', 'marketplace-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Email message', 'marketplace-for-woocommerce' ),
				'id'       => self::OPTION_COMMISSIONS_EMAIL_MESSAGE,
				'default'  => __( 'You have a new sale on {site_title} from {order_date}', 'marketplace-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;height:100px;',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_mpwc_email_opt',
			),

		);

		return parent::get_settings( array_merge( $settings, $new_settings ) );
	}

}

endif;
