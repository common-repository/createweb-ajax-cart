<?php
class Cwac_minicart_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'cw_minicart_widget', 
			'CW Minicart widget',
			array( 'description' => __( 'Showing minicart', 'cw-ajax-cart' ))
		);
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget']; 
        
        echo do_shortcode('[minicart]');
		
        echo $args['after_widget'];
	}     
}

function cwac_cart_widgets() {
	register_widget( 'Cwac_minicart_widget' );
}

add_action( 'widgets_init', 'cwac_cart_widgets' );
?>