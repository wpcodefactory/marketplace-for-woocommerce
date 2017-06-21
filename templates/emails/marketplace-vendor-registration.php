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

<p><?php printf( __( 'The user <a href="%s" target="_blank">%s</a> has just registered as a Vendor.', 'marketplace-for-woocommerce' ), $user_edit_link, esc_html( $display_name ) ); ?></p>

<?php if ( $is_pending ): ?>
    <p>Its status is still pending for approval.</p>
    <p> <?php printf( __( 'Approve <strong>%s</strong> as a Vendor on <a target="_blank" href="%s">its profile page</a>', 'marketplace-for-woocommerce' ), esc_html( $display_name ), $user_edit_link ) ?></p>
<?php endif; ?>

<?php do_action( 'woocommerce_email_footer', $email );