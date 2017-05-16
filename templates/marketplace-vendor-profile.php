<?php
/**
 * The template for displaying the vendor profile
 *
 * @author  Algoritmika Ltd.
 * @version 1.0.0
 * @since   1.0.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

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

                <?php // Title ?>
                <?php $title = sanitize_text_field( get_user_meta( $vendor->ID, $fields->meta_public_page_title, true ) ); ?>
                <h1 style="display:inline"><?php echo $title ? esc_html( $title ) : esc_html( $vendor->data->display_name ); ?></h1>

                <?php // Description ?>
                <?php $description = sanitize_text_field( get_user_meta( $vendor->ID, $fields->meta_description, true ) ); ?>
                <?php echo $title ? apply_filters( 'the_content', $description ) : '' ?>

                <?php // See all products ?>
                <?php
                    $vendor_products_url = add_query_arg(array(
                        'post_type'=>'product',
                        Alg_MPWC_Query_Vars::VENDOR=>$vendor->ID,
                    ),get_home_url().'/');
                ?>

                <div>
                    <a href="<?php echo esc_url($vendor_products_url); ?>">See all vendor's products</a>
                </div>
                <div style="clear: both"></div>
            </div>



			<?php if ( have_posts() ) : ?>

				<?php
				/**
				 * woocommerce_before_shop_loop hook.
				 *
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
				?>

				<?php woocommerce_product_loop_start(); ?>

				<?php woocommerce_product_subcategories(); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php
					/**
					 * woocommerce_shop_loop hook.
					 *
					 * @hooked WC_Structured_Data::generate_product_data() - 10
					 */
					do_action( 'woocommerce_shop_loop' );
					?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

				<?php woocommerce_product_loop_end(); ?>

				<?php
				/**
				 * woocommerce_after_shop_loop hook.
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
				?>

			<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

				<?php
				/**
				 * woocommerce_no_products_found hook.
				 *
				 * @hooked wc_no_products_found - 10
				 */
				do_action( 'woocommerce_no_products_found' );
				?>

			<?php endif; ?>

		</main><!-- #main -->
	</div><!-- #primary -->



<?php
get_sidebar('sidebar');
get_footer();