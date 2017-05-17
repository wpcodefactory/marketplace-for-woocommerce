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
?>

<?php // Image ?>
<?php
$logo_id = filter_var( get_user_meta( $vendor->ID, $fields->meta_logo . '_id', true ), FILTER_VALIDATE_INT );
if ( $logo_id ) {
	$image = wp_get_attachment_image( $logo_id, 'full', false, array( 'style' => 'max-width:48%;float:left;margin:0 15px 0 0' ) );
}
?>

<div class="" style="margin-bottom:25px;">
	<?php if ( $logo_id ) { ?>
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

    <div>
        <a href="<?php echo esc_url( $vendor_products_url ); ?>">See vendor's products on shop</a>
    </div>
    <div style="clear: both"></div>
</div>