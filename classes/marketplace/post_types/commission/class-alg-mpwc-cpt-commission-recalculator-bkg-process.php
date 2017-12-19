<?php
/**
 * Marketplace for WooCommerce - Commission recalculator background process
 *
 * @version 1.1.0
 * @since   1.1.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Alg_MPWC_CPT_Commission_Recalculator_Bkg_Process' ) ) {

	class Alg_MPWC_CPT_Commission_Recalculator_Bkg_Process extends \WP_Background_Process {

		/**
		 * @var string
		 */
		protected $action = 'alg_mpwc_recalculate_comm';

		protected function task( $item ) {
			$commission_id = $item;

			$commissions_manager = new Alg_MPWC_CPT_Commission_Manager();
			$cpt                 = new Alg_MPWC_CPT_Commission();
			$cpt->get_values_from_admin();
			$commissions_manager->set_args( $cpt );
			$commissions_manager->handle_automatic_creation();
			$commissions_manager->handle_automatic_refund();
			$info = $commissions_manager->get_updated_commission_info( $commission_id );
			$commissions_manager->update_commission_values( $commission_id, $info );

			$order_id  = filter_var( get_post_meta( $commission_id, Alg_MPWC_Post_Metas::COMMISSION_ORDER_ID, true ), FILTER_SANITIZE_NUMBER_INT );
			$vendor_id = filter_var( get_post_meta( $commission_id, Alg_MPWC_Post_Metas::COMMISSION_AUTHOR_ID, true ), FILTER_SANITIZE_NUMBER_INT );
			do_action( 'alg_mpwc_insert_commission', $commission_id, $vendor_id, $order_id, $info['final_value'] );

			return false;
		}
	}
}