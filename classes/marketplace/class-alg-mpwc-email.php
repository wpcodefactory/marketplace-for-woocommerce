<?php
/**
 * Marketplace for WooCommerce - Email
 *
 * @version 1.2.3
 * @since   1.2.3
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Email' ) ) {
	class Alg_MPWC_Email {

		/**
		 * Get email template
		 * @version 1.2.3
		 * @since   1.2.3
		 *
		 * @param $content
		 * @param string $email_heading
		 *
		 * @return string
		 */
		public static function wrap_in_wc_email_template( $content, $email_heading = '' ) {
			return self::get_wc_email_part( 'header', $email_heading ) .
			       $content .
			       str_replace( '{site_title}', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), self::get_wc_email_part( 'footer' ) );
		}

		/**
		 * Gets email part
		 *
		 * @version 1.2.3
		 * @since   1.2.3
		 *
		 * @param $part
		 * @param string $email_heading
		 *
		 * @return false|string
		 */
		public static function get_wc_email_part( $part, $email_heading = '' ) {
			ob_start();
			switch ( $part ) {
				case 'header':
					wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
				break;
				case 'footer':
					wc_get_template( 'emails/email-footer.php' );
				break;
			}
			return ob_get_clean();
		}

	}
}