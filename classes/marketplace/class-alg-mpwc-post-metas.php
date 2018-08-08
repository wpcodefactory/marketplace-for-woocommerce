<?php
/**
 * Marketplace for WooCommerce - Post Metas
 *
 * @version 1.2.1
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Post_Metas' ) ) {
	class Alg_MPWC_Post_Metas {

		// Comission Meta fields
		const COMMISSION_PRODUCT_IDS      = '_alg_mpwc_product_ids';
		const COMMISSION_AUTHOR_ID        = '_alg_mpwc_author_id';
		const COMMISSION_ORDER_ID         = '_alg_mpwc_order_id';
		//const COMMISSION_ORIGINAL_VALUE   = '_alg_mpwc_comission_original_value';

		const COMMISSION_FINAL_VALUE      = '_alg_mpwc_comission_final_value';
		const COMMISSION_FIXED_VALUE      = '_alg_mpwc_comission_fixed_value';
		const COMMISSION_PERCENTAGE_VALUE = '_alg_mpwc_comission_percentage_value';
		const COMMISSION_DEAL             = '_alg_mpwc_comission_deal';
		const COMMISSION_STATUS           = '_alg_mpwc_status';
		const COMMISSION_CURRENCY         = '_alg_mpwc_currency';
		//const COMMISSION_BASE           = '_alg_mpwc_commission_base';

		// Order Meta fields
		const ORDER_COMISSIONS_EVALUATED  = '_alg_mpwc_comissions_evaluated';
		const ORDER_RELATED_COMISSIONS    = '_alg_mpwc_related_commissions';
		const ORDER_RELATED_VENDOR        = '_alg_mpwc_related_vendor';

	}
}