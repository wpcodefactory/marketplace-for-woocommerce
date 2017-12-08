<?php
/**
 * Marketplace for WooCommerce - Filter vendor products widget
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! class_exists( 'Alg_MPWC_Vendor_Products_Filter_Widget' ) ) {
	class Alg_MPWC_Vendor_Products_Filter_Widget extends WP_Widget {

		/**
		 * Constructor
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			parent::__construct(
				'alg_mpwc_vendor_filter_widget', // Base ID
				esc_html__( 'Vendors filter', 'marketplace-for-woocommerce' ), // Name
				array( 'description' => esc_html__( 'Filters Marketplace vendors products', 'marketplace-for-woocommerce' ), ) // Args
			);

			$filter = new Alg_MPWC_Vendor_Filter();
			$filter->setup();
		}

		/**
		 * Front-end display of widget.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @see     WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {
			echo $args['before_widget'];

			$post_type = get_query_var( 'post_type' );
			$is_public_page               = filter_var( get_query_var( Alg_MPWC_Query_Vars::VENDOR_PUBLIC_PAGE ), FILTER_VALIDATE_BOOLEAN );

			if ( ! $post_type || ! is_shop() || $is_public_page) {
				return;
			}

			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
			}

			$filter = new Alg_MPWC_Vendor_Filter();
			echo $filter->get_html();

			echo $args['after_widget'];
		}

		/**
		 * Back-end widget form.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @see     WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {
			$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Filter vendor products', 'marketplace-for-woocommerce' );
			?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'marketplace-for-woocommerce' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
                       value="<?php echo esc_attr( $title ); ?>">
            </p>
			<?php
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @see     WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance          = array();
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

			return $instance;
		}
	}
}