<?php
/**
 * Email for vendor registration
 *
 * @author  Algoritmika Ltd.
 * @version 1.0.0
 * @since   1.0.0
 */
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( __( 'The user <a href="%s" target="_blank">%s</a> has just registered as a Vendor.', 'marketplace-for-woocommerce' ), get_edit_user_link( $user_id ), esc_html( $display_name ) ); ?></p>

<?php if ( $is_pending ): ?>
    <p> <?php printf( __( 'Approve user as a Vendor on <a target="_blank" href="%s">its profile page</a>', 'marketplace-for-woocommerce' ), get_edit_user_link( $user_id ) ) ?></p>
<?php endif; ?>

<?php do_action( 'woocommerce_email_footer', $email );