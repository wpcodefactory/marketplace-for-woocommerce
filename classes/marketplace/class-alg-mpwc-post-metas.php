<?php
/**
 * Marketplace for WooCommerce - Post Metas
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Post_Metas' ) ) {
	class Alg_MPWC_Post_Metas {

		// Comission Meta fields
		const COMMISSION_PRODUCT_IDS = 'alg_mpwc_product_ids';
		const COMMISSION_AUTHOR_ID   = 'alg_mpwc_author_id';
		const COMMISSION_ORDER_ID    = 'alg_mpwc_order_id';
		const COMMISSION_VALUE       = 'alg_mpwc_comission_value';
		const COMMISSION_STATUS      = 'alg_mpwc_status';

		// Order Meta fields
		const ORDER_COMISSIONS_EVALUATED = 'alg_mpwc_comissions_evaluated';
	}
}