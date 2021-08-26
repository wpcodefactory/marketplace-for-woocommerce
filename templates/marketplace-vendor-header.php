<?php
/**
 * Header of a vendor public page
 *
 * @version 1.4.2
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */
?>

<?php // User fields ?>
<?php $fields = new Alg_MPWC_Vendor_Admin_Fields(); ?>

<?php
// Get vendor
$vendor_query_string = get_query_var( Alg_MPWC_Query_Vars::VENDOR );
if ( is_numeric( $vendor_query_string ) ) {
	$vendor = get_user_by( 'id', $vendor_query_string );
} else {
	$vendor = get_user_by( 'slug', $vendor_query_string );
}

// Shop link label
$shop_link_label = sanitize_text_field( get_option( Alg_MPWC_Settings_Vendor::OPTION_PUBLIC_PAGE_SHOP_LINK_LABEL, __( "See all vendor's products", 'marketplace-for-woocommerce' ) ) );
$logo_enabled    = filter_var( get_option( Alg_MPWC_Settings_Vendor::OPTION_PUBLIC_PAGE_LOGO, true ), FILTER_VALIDATE_BOOLEAN );

?>

<?php // Image ?>
<?php
$logo_id = filter_var( get_user_meta( $vendor->ID, $fields->meta_logo . '_id', true ), FILTER_VALIDATE_INT );
if ( $logo_id ) {
	$image = wp_get_attachment_image( $logo_id, 'full', false, array( 'style' => 'max-width:48%;float:left;margin:0 15px 0 0' ) );
}
?>

<div class="" style="margin-bottom:25px;">
	<?php if ( $logo_id && $logo_enabled ) { ?>
		<?php echo $image; ?>
	<?php } ?>

	<?php // Description ?>
	<?php $description = sanitize_text_field( get_user_meta( $vendor->ID, $fields->meta_description, true ) ); ?>
	<?php echo $description ? apply_filters( 'the_content', $description ) : '' ?>

	<?php // See all products ?>
	<?php
	$vendor_products_url = add_query_arg( array(
		'post_type'                 => 'product',
		Alg_MPWC_Query_Vars::VENDOR => $vendor->ID,
	), get_home_url() . '/' );
	?>

	<?php if ( ! empty( $vendor_products_url ) ): ?>
		<div>
			<a href="<?php echo esc_url( $vendor_products_url ); ?>"><?php echo esc_html( $shop_link_label ); ?></a>
		</div>
	<?php endif; ?>

	<?php if ( 'yes' === get_option( 'alg_mpwc_opt_public_page_rating', 'no' ) ) {
		echo alg_marketplace_for_wc()->get_vendor_rating( $vendor->ID, get_option( 'alg_mpwc_opt_public_page_rating_template',
			'<div class="alg-mpwc-vendor-rating">%rating_html%</div>' ) );
	} ?>

	<div style="clear: both"></div>
</div>