<?php

/** 
 * Settings page of the plugin 
 */

function cwac_ajax_cart_func() {
?>
    <div id="cw-ajax-cart-wrap">
        <h2>CW Ajax shopping cart</h2>   
        <form method="post" action="options.php">
            <?php 
            settings_fields( 'cw_option_group' );
            do_settings_sections( 'manage-ajax-cart' );
            submit_button();
            ?>
        </form>   
    </div>    
<?php    
}

/** 
 * Settings items of the plugin 
 */

function cwac_plugin_settings() {     
	// $option_group, $option_name, $sanitize_callback
	register_setting( 'cw_option_group', 'cw_cart_settings' );
    
    // $id, $title, $callback, $page
	add_settings_section( 'cw_cart_section', __( 'General settings', 'cw-ajax-cart' ), '', 'manage-ajax-cart' );
    add_settings_section( 'cw_checkout_section', __( 'Checkout settings', 'cw-ajax-cart' ), '', 'manage-ajax-cart' ); 
    add_settings_section( 'cw_mail_section', __( 'Email settings', 'cw-ajax-cart' ), '', 'manage-ajax-cart' );
    
    // $id, $title, $callback, $page, $section, $args
	add_settings_field(
        'cart_link_id', 
        __( 'Cart page' , 'cw-ajax-cart' ),
        'cwac_cart_link_field', 
        'manage-ajax-cart', 
        'cw_cart_section' 
    );
    
    add_settings_field(
        'cart_post_type', 
        __( 'Choose post type' , 'cw-ajax-cart' ),
        'cwac_cart_post_type', 
        'manage-ajax-cart', 
        'cw_cart_section' 
    );
    
	add_settings_field(
        'cart_link_text', 
        __( 'Link text in minicart' , 'cw-ajax-cart' ),
        'cwac_cart_link_text', 
        'manage-ajax-cart', 
        'cw_cart_section' 
    );
    
    add_settings_field(
        'cart_minicart_info', 
        __( 'Show info about products in minicart' , 'cw-ajax-cart' ),
        'cwac_cart_minicart_info', 
        'manage-ajax-cart', 
        'cw_cart_section' 
    );
    
    add_settings_field(
        'cart_minicart_info_type', 
        __( 'Brief or detailed information in minicart' , 'cw-ajax-cart' ),
        'cwac_cart_minicart_info_type', 
        'manage-ajax-cart', 
        'cw_cart_section' 
    );
    
    add_settings_field(
        'cart_currency', 
        __( 'Currency ($, rub)' , 'cw-ajax-cart' ),
        'cwac_cart_currency', 
        'manage-ajax-cart', 
        'cw_cart_section' 
    );
    
    add_settings_field(
        'cart_show_on_single', 
        __( 'Show add to cart button on single page (using the_content hook)' , 'cw-ajax-cart' ),
        'cwac_cart_show_on_single', 
        'manage-ajax-cart', 
        'cw_cart_section' 
    );
    
    add_settings_field(
        'cart_added_msg', 
        __( 'Message after adding to cart' , 'cw-ajax-cart' ),
        'cwac_cart_added_msg', 
        'manage-ajax-cart', 
        'cw_cart_section' 
    );
    
    add_settings_field(
        'cart_added_msg_type', 
        __( 'Kind of message after adding of the product' , 'cw-ajax-cart' ),
        'cwac_cart_added_msg_type', 
        'manage-ajax-cart', 
        'cw_cart_section' 
    );
    
    add_settings_field(
        'cart_order_fields', 
        __( 'Fields when ordering' , 'cw-ajax-cart' ),
        'cwac_cart_order_fields', 
        'manage-ajax-cart', 
        'cw_cart_section' 
    );
    
    add_settings_field(
        'cart_coupons', 
        __( 'Apply coupons' , 'cw-ajax-cart' ),
        'cwac_cart_coupons', 
        'manage-ajax-cart', 
        'cw_cart_section' 
    );
    
    // checkout settings
    add_settings_field(
        'cart_order_success_type', 
        __( 'Action after successful checkout' , 'cw-ajax-cart' ), 
        'cwac_cart_order_success_type', 
        'manage-ajax-cart', 
        'cw_checkout_section' 
    );
    
    add_settings_field(
        'cart_order_msg_success', 
        __( 'Success message ordering' , 'cw-ajax-cart' ), 
        'cwac_cart_order_msg_success', 
        'manage-ajax-cart', 
        'cw_checkout_section' 
    );
    
    add_settings_field(
        'cart_redirect_page', 
        __( 'Redirect to page' , 'cw-ajax-cart' ), 
        'cwac_cart_redirect_page', 
        'manage-ajax-cart', 
        'cw_checkout_section' 
    );
    
    // email settings
    add_settings_field(
        'cart_mail_recipient', 
        __( 'Recipient' , 'cw-ajax-cart' ),
        'cwac_cart_mail_recipient', 
        'manage-ajax-cart', 
        'cw_mail_section' 
    );
    
    add_settings_field(
        'cart_mail_subject', 
        __( 'Subject' , 'cw-ajax-cart' ),
        'cwac_cart_mail_subject', 
        'manage-ajax-cart', 
        'cw_mail_section' 
    );
    
    add_settings_field(
        'cart_mail_from_name', 
        __( '"From" Name' , 'cw-ajax-cart' ),
        'cwac_cart_mail_from_name', 
        'manage-ajax-cart', 
        'cw_mail_section' 
    );
    
    add_settings_field(
        'cart_mail_from_address', 
        __( '"From" Address' , 'cw-ajax-cart' ),
        'cwac_cart_mail_from_address', 
        'manage-ajax-cart', 
        'cw_mail_section' 
    );
    
    add_settings_field(
        'cart_mail_to_client', 
        __( 'Send email to the client' , 'cw-ajax-cart' ),
        'cwac_cart_mail_to_client', 
        'manage-ajax-cart', 
        'cw_mail_section' 
    );
}
add_action('admin_init', 'cwac_plugin_settings');


/** 
 * Cart page 
 */

function cwac_cart_link_field() {
	global $settings;
    
    $value = $settings['cart_page'];
    
    $args = array(
        'post_type' => 'page',
        'numberposts' => -1
    );
    
    $pages = get_posts( $args );
    
    $output = '<select name="cw_cart_settings[cart_page]">';
    
    foreach ( $pages as $page ) {
        
        if ( $value == $page->ID ) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        
        $output .= '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
    }
    
    $output .= '</select>';
    
    echo $output;
}

/** 
 * Post type 
 */

function cwac_cart_post_type() {
    global $settings;
    
    $value = $settings['cart_post_type'];    
    $post_types = get_post_types();

    unset( $post_types['revision'] );
    unset( $post_types['nav_menu_item'] );
    unset( $post_types['attachment'] );
    unset( $post_types['acf'] );
    unset( $post_types['cw_coupon'] );
    unset( $post_types['vc_grid_item'] );
    unset( $post_types['wpcf7_contact_form'] );
    
    echo '<select name="cw_cart_settings[cart_post_type]" required>';
    echo '<option value="0">' . __( 'Choose post type', 'cw-ajax-cart' ) . '</option>';
    
    foreach ( $post_types as $k => $v ) {
        $selected = false;
        if ( $value == $v ) {
            $selected = 'selected';
        }
    ?>
    
        <option value="<?php echo esc_attr( $v ); ?>" <?php echo $selected; ?>><?php echo $v; ?></option>
	
    <?php 
    }    
    
    echo '</select>';
}

/** 
 * Text of the cart link 
 */

function cwac_cart_link_text() {
    global $settings;
    
    $value = $settings['cart_link_text'];
	?>
	   
       <input type="text" name="cw_cart_settings[cart_link_text]" value="<?php echo esc_attr( $value ) ?>" placeholder="<?php _e( 'Cart', 'cw-ajax-cart' ); ?>"/>
	
    <?php 
}

/** 
 * Show info in minicart 
 */

function cwac_cart_minicart_info() {
    global $settings;

    if ( isset( $settings['cart_minicart_info'] ) ) { 
        $value = $settings['cart_minicart_info']; 
    } else {
        $value = 0;
    }
	?>
    
	<input type="checkbox" name="cw_cart_settings[cart_minicart_info]" value="1" <?php checked( $value ); ?> />
	
    <?php    
}

/** 
 * Brief or full information in minicart 
 */

function cwac_cart_minicart_info_type() {
    global $settings;
    
    $value = $settings['cart_minicart_info_type'];
	?>
    
    <input type="radio" name="cw_cart_settings[cart_minicart_info_type]" id="info_type_short" value="short" <?php checked( $value, 'short' ); ?>/>
    <label for="info_type_short"><?php _e( 'Short information (only number of products)', 'cw-ajax-cart' ); ?></label><br />
    <input type="radio" name="cw_cart_settings[cart_minicart_info_type]" id="info_type_full" value="full" <?php checked( $value, 'full' ); ?>/>
    <label for="info_type_full"><?php _e( 'Complete information', 'cw-ajax-cart' ); ?></label>

	<?php    
}

/** 
 * Currency 
 */

function cwac_cart_currency() {
    global $settings;
    
    $value = $settings['cart_currency'];   
	?>
	
    <input type="text" name="cw_cart_settings[cart_currency]" value="<?php echo esc_attr( $value ) ?>" placeholder="$"/>
	
    <?php 
}

/** 
 * Show cart on single page using hook 
 */

function cwac_cart_show_on_single() {
    global $settings;
    
    if ( isset( $settings['cart_show_on_single'] ) ) {
        $value = $settings['cart_show_on_single'];    
    } else {
        $value = 0;
    }
	?>
    
	<input type="checkbox" name="cw_cart_settings[cart_show_on_single]" value="1" <?php checked( $value ); ?> />
	
    <?php    
}

/** 
 * Message after adding to cart 
 */

function cwac_cart_added_msg() {
    global $settings;
    
    $value = $settings['cart_added_msg'];   
	?>
	
    <input type="text" name="cw_cart_settings[cart_added_msg]" value="<?php echo esc_attr( $value ) ?>" placeholder="<?php _e( 'Added to cart', 'cw-ajax-cart' ); ?>"/>
	
    <?php 
}

/** 
 * Type of the message after adding to cart popup/text (near button) 
 */

function cwac_cart_added_msg_type() {
    global $settings;
    
    $value = $settings['cart_added_msg_type'];
	?>
    
    <input type="radio" name="cw_cart_settings[cart_added_msg_type]" id="added_msg_text" value="text" <?php checked( $value, 'text' ); ?>/>
    <label for="added_msg_text"><?php _e( 'Text (near adding to cart button)', 'cw-ajax-cart' ); ?></label><br />
    <input type="radio" name="cw_cart_settings[cart_added_msg_type]" id="added_msg_popup" value="popup" <?php checked( $value, 'popup' ); ?>/>
    <label for="added_msg_popup"><?php _e( 'Popup', 'cw-ajax-cart' ); ?></label>

	<?php    
}

/** 
 * Checkout form fields 
 */

function cwac_cart_order_fields() {
    global $settings, $email_options;

    if ( isset( $settings['cart_order_fields'] ) ) { 
        $value = $settings['cart_order_fields']; 
    } else {
        $value = 0;
    } 
    
    foreach( $email_options as $k => $v ) {        
        if ( $value ) {
            $key = array_key_exists( $k, $value );

            if ( $key !== false ) {
               $val_res = 1;
            } else {
                $val_res = 0;
            }
        } else {
            $val_res = 0;
        }
	?>
    
    <input type="checkbox" name="cw_cart_settings[cart_order_fields][<?php echo $k; ?>]" id="order_field_<?php echo $k; ?>" value="1" <?php checked( $val_res ); ?> />
    <label for="order_field_<?php echo $k; ?>"><?php _e( $v, 'cw-ajax-cart' ); ?></label><br />

	<?php  
    }  
}

/**
 * Cart coupons
 */ 
 
function cwac_cart_coupons() {
    global $settings;
    
    if ( isset( $settings['cart_coupons'] ) ) { 
        $value = $settings['cart_coupons']; 
    } else {
        $value = 0;
    }
	?>
    
	<input type="checkbox" name="cw_cart_settings[cart_coupons]" value="1" <?php checked( $value ); ?> />
	
    <?php    
}

/** 
 * Action when the order was successfully sent (message in popup/redirect to other page) 
 */

function cwac_cart_order_success_type() {
    global $settings;
    
    $value = $settings['cart_order_success_type'];
	?>
	
    <input type="radio" name="cw_cart_settings[cart_order_success_type]" id="success_msg_text" value="text" <?php checked( $value, 'text' ); ?>/>
    <label for="success_msg_text"><?php _e( 'Success message in popup', 'cw-ajax-cart' ); ?></label><br />
    <input type="radio" name="cw_cart_settings[cart_order_success_type]" id="success_msg_redirect" value="redirect" <?php checked( $value, 'redirect' ); ?>/>
    <label for="success_msg_redirect"><?php _e( 'Redirect to page', 'cw-ajax-cart' ); ?></label>
	
    <?php 
}

/** 
 * Successful checkout notification 
 */

function cwac_cart_order_msg_success() {
    global $settings;
    
    $value = $settings['cart_order_msg_success'];
	?>
	
    <input type="text" name="cw_cart_settings[cart_order_msg_success]" value="<?php echo esc_attr( $value ) ?>" placeholder="<?php _e( 'Order has been sent successfully', 'cw-ajax-cart' ); ?>"/>
	
    <?php 
}

/** 
 * Redirect page after sending order 
 */

function cwac_cart_redirect_page() {
    global $settings;
    
    $value = $settings['cart_redirect_page'];
    
    $args = array(
        'post_type' => 'page',
        'numberposts' => -1
    );
    
    $pages = get_posts( $args );
    
    $output = '<select name="cw_cart_settings[cart_redirect_page]">';
    
    foreach ( $pages as $page ) {
        
        if ( $value == $page->ID ) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        
        $output .= '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
    }
    
    $output .= '</select>';
    
    echo $output;
}

/** 
 * Email recipient 
 */

function cwac_cart_mail_recipient() {
    global $settings;
    
    $value = $settings['cart_mail_recipient'];    
	?>
	
    <input type="text" name="cw_cart_settings[cart_mail_recipient]" value="<?php echo esc_attr( $value ) ?>" />
	
    <?php 
}

/** 
 * Email subject 
 */

function cwac_cart_mail_subject() {
    global $settings;
    
    $value = $settings['cart_mail_subject'];   
	?>
	
    <input type="text" name="cw_cart_settings[cart_mail_subject]" value="<?php echo esc_attr( $value ) ?>" />
	
    <?php 
}

/** 
 * Email From name 
 */

function cwac_cart_mail_from_name() {
    global $settings;
    
    $value = $settings['cart_mail_from_name'];    
	?>
	
    <input type="text" name="cw_cart_settings[cart_mail_from_name]" value="<?php echo esc_attr( $value ) ?>" />
	
    <?php 
}

/** 
 * Email From address 
 */

function cwac_cart_mail_from_address() {
    global $settings;
    
    $value = $settings['cart_mail_from_address'];    
	?>
	
    <input type="text" name="cw_cart_settings[cart_mail_from_address]" value="<?php echo esc_attr( $value ) ?>" />
	
    <?php 
}

/** 
 * Send copy of order to the client 
 */

function cwac_cart_mail_to_client() {
    global $settings;
    
    if ( isset( $settings['cart_mail_to_client'] ) ) { 
        $value = $settings['cart_mail_to_client']; 
    } else {
        $value = 0;
    } 
	?>
    
	<input type="checkbox" name="cw_cart_settings[cart_mail_to_client]" value="1" <?php checked( $value ); ?> />
	
    <?php    
}
?>