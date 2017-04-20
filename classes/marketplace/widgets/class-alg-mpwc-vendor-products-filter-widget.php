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
				'alg_mpwc_vendor_products_filter_widget', // Base ID
				esc_html__( 'Vendor products filter', 'marketplace-for-woocommerce' ), // Name
				array( 'description' => esc_html__( 'Filters Marketplace vendor products', 'marketplace-for-woocommerce' ), ) // Args
			);

			$filter = new Alg_MPWC_Vendor_Products_Filter();
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
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
			}

			/*
			$users_with_role = implode( ",", get_users( array(
				'fields' => 'id',
				'role'   => Alg_MPWC_Vendor_Role_Manager_Adm::ROLE_VENDOR,
			) ) );

			wp_dropdown_users( array(
				'show_option_none' => __( 'Select a vendor', 'marketplace-for-woocommerce' ),
				//'selected' => ($_POST['user'] ? :    get_current_user_id()),
				'include_selected' => true,
				'include'          => $users_with_role,
			) );
			echo '<input type="hidden" name="alg_mpwc_vendor">';
			*/

			$filter = new Alg_MPWC_Vendor_Products_Filter();
			echo $filter->get_html();



			//echo esc_html__( 'Hello, World!', 'marketplace-for-woocommerce' );
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