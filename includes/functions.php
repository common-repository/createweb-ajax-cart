<?php
/** 
 * Include files we need
 */

require_once( 'class-cw-cart.php' );
require_once( 'class-cw-mail.php' );
require_once( 'shortcodes.php' );
require_once( 'settings.php' );
require_once( 'widget.php' );

$cw_cart = new Cwac_cart_class;

/** 
 * Price metabox in the post (admin part)
 */

function cwac_metabox() {
    global $type_of_post;
    
	add_meta_box( 'cw_price_meta', 'CW Ajax Cart', 'cwac_metabox_show', $type_of_post, 'normal', 'high' );
}
 
add_action( 'admin_menu', 'cwac_metabox' );

function cwac_metabox_show( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'cw_metabox_nonce' );

    global $price_field, $old_price_field;
    
    $reg_price = get_post_meta( $post->ID, $price_field, true );
    $old_price = get_post_meta( $post->ID, $old_price_field, true );
    
	$html = '<p><label>' . __( 'Regular price', 'cw-ajax-cart' ) . ' <input type="number" min="0" name="cw_price" value="' . esc_attr( $reg_price ) . '" /></label></p>';
    $html .= '<p><label>' . __( 'Old price', 'cw-ajax-cart' ) . ' <input type="number" min="0" name="cw_old_price" value="' . esc_attr( $old_price ) . '" /></label></p>';
 
	echo $html;
}

function cwac_save_metabox( $post_id ) {
	if ( ! isset( $_POST['cw_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['cw_metabox_nonce'], basename( __FILE__ ) ) ) {
	    return $post_id;
	}
        
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
	}
        
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
	}
        
	$post = get_post( $post_id );
    
    global $type_of_post, $price_field, $old_price_field;

	if ( $post->post_type == $type_of_post ) {
        $new = intval( $_POST['cw_price'] );
        $old = intval( $_POST['cw_old_price'] );
    
		update_post_meta( $post_id, $price_field, $new );
        update_post_meta( $post_id, $old_price_field, $old );
	}
	return $post_id;
}
 
add_action( 'save_post', 'cwac_save_metabox' );

/** 
 * Price metabox in coupons (admin part)
 */

function cwac_coupon_metabox() {
	add_meta_box( 'cw_coupon_meta', __( 'Discount', 'cw-ajax-cart' ), 'cwac_coupon_metabox_show', 'cw_coupon', 'normal', 'high' );
}
 
add_action( 'admin_menu', 'cwac_coupon_metabox' );

function cwac_coupon_metabox_show( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'cw_metabox_nonce' );
    
    $val = get_post_meta( $post->ID, '_cw_coupon_type', true );
    $code = get_post_meta( $post->ID, '_cw_coupon_code', true );
    $discount = get_post_meta( $post->ID, '_cw_coupon_discount', true );

	$html = '<p><label>' . __( 'Coupon code', 'cw-ajax-cart' ) . ' ';
    $html .= '<input type="text" id="coupon_fix" name="cw_coupon_code" value="' . esc_attr( $code ) . '" /></label></p>';
    $html .= '<p class="cw-meta-wrap"><span>' . __( 'Coupon type', 'cw-ajax-cart' ) . '</span>';
    $html .= '<label><input type="radio" name="cw_coupon_type" value="fix"' . checked( $val, 'fix', false ) . ' />' . __( 'Fixed price', 'cw-ajax-cart' ) . '</label>';
    $html .= '<label><input type="radio" name="cw_coupon_type" value="percent"' . checked( $val, 'percent', false ) . ' />' . __( 'Percent', 'cw-ajax-cart' ) . '</label></p>';
    $html .= '<p><label>' . __( 'Discount', 'cw-ajax-cart' ) . ' ';
    $html .= '<input type="number" id="coupon_discount" name="cw_coupon_discount" min="0" step="1" value="' . esc_attr( $discount ) . '" /></label></p>';
    
	echo $html;
}

function cwac_coupon_save_metabox( $post_id ) {
	if ( ! isset( $_POST['cw_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['cw_metabox_nonce'], basename( __FILE__ ) ) ) {
	    return $post_id;
	}
        
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
	}
        
	if ( !current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
	}
        
	$post = get_post( $post_id );
    
	if ( $post->post_type == 'cw_coupon' ) {
        $coupon_code = sanitize_text_field( $_POST['cw_coupon_code'] );
        $coupon_type = sanitize_text_field( $_POST['cw_coupon_type'] );
        $coupon_discount = intval( $_POST['cw_coupon_discount'] );
    
		update_post_meta( $post_id, '_cw_coupon_code', $coupon_code );
        update_post_meta( $post_id, '_cw_coupon_type', $coupon_type );
        update_post_meta( $post_id, '_cw_coupon_discount', $coupon_discount );
	}
	return $post_id;
}
 
add_action( 'save_post', 'cwac_coupon_save_metabox' );

/** 
 * Return quantity and total sum of the cart
 */

function cwac_cart_callback() {    
    $nonce = $_POST['nonce'];
    
    if ( wp_verify_nonce( $nonce, 'cart_ajax-nonce' ) ) {       
        global $cw_cart;        
        
        $res = $cw_cart->cwac_get_minicart();       
        wp_send_json( $res );
    }
    
    exit;    
}

add_action( 'wp_ajax_cwac_cart', 'cwac_cart_callback' );
add_action( 'wp_ajax_nopriv_cwac_cart', 'cwac_cart_callback' );

/** 
 * Show product price in shortcode [product-cart]
 */

function cwac_get_price() {    
    global $post, $settings, $price_field, $old_price_field;  
    
    $current_price = get_post_meta( $post->ID, $price_field, true );
    $old_price = get_post_meta( $post->ID, $old_price_field, true ); 
    
    if ( $current_price ) {
        $out = '<div class="cw-price">';
                
        if ( $old_price ) {                  
            $out .= '<span class="cw-price-old">' . $old_price . ' <span>' . esc_attr( $settings['cart_currency'] ) . '</span></span>';      
        }      
        
        $out .= '<span class="cw-price-new">'. $current_price .' <span>'. esc_attr( $settings['cart_currency'] ) . '</span></span>';      
        $out .= '</div>'; 
        
        return $out;           
    }
}

/** 
 * Sending order by email and adding it to the DB
 */

function cwac_add_new_order() {
    global $wpdb, $settings, $cw_cart; 
           
    $subtotal = $cw_cart->cwac_get_cart_sum();
    $order_items = $cw_cart->cwac_get_cart();
    
    $nonce = $_POST['nonce'];
    $id = mt_rand( 1, 999999);
    
    parse_str( sanitize_text_field( $_POST['fields'] ), $fields );
    
    foreach ( $fields as $field_name => $field_val ) {
        $arr[ $field_name ] = sanitize_text_field( trim( $field_val ) );
    }
    
    $order = serialize( $order_items['order'] );
    $date = date( 'Y-m-d' );
    
    if ( $order_items['coupon'] ) {
        $coupon = serialize( $order_items['coupon'] );
        
        $type = $order_items['coupon']['type'];
        $discount = $order_items['coupon']['discount'];
        
        if ( $type == 'percent' ) {
            $total = $subtotal - ( $subtotal / 100 ) * $discount;
        } elseif ( $type == 'fix' ) {
            $total = $subtotal - $discount;
        }
    } else {
        $coupon = '';
        $total = $subtotal;
    }
    
    if ( wp_verify_nonce( $nonce, 'cart_ajax-nonce' ) ) {
        $name = $arr['name'];
        $phone = $arr['phone'];
        $email = $arr['email'];
        $address = $arr['address'];
        $message = $arr['message'];
        $status = 'processing';
        
        $sql = $wpdb->query(
            $wpdb->prepare("
                INSERT INTO cw_cart_order (order_id, order_name, order_phone, order_email, order_address, 
                order_message, order_product, order_sum, order_total, order_coupon, order_status, order_date) 
                VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                array(
                    $id,
                    $name,
                    $phone,
                    $email,
                    $address,
                    $message,
                    $order,
                    $subtotal,
                    $total,
                    $coupon,
                    $status,
                    $date
                )
            )
        );
        
        $order_info = array(
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'message' => $message
        );

        if ( $sql == 1 ) {
            $cw_mail = new Cwac_mail_class;
            $cw_mail->cwac_send_order( $id, $order_info, $order_items['order'], $subtotal, $total, $order_items['coupon'] );    
        }
    }
       
    exit;    
}

add_action( 'wp_ajax_cwac_add_new_order', 'cwac_add_new_order' );
add_action( 'wp_ajax_nopriv_cwac_add_new_order', 'cwac_add_new_order' );

/** 
 * Add success message popup to page
 */

function cwac_add_popup( $message ) {
    global $settings; 
    
    $message = esc_attr( $settings['cart_added_msg'] );
    
    if ( ! $message ) {
        $message = __( 'Added to cart', 'cw-ajax-cart' );
    }
    
    $message = apply_filters( 'cw_added_popup_filter', $message );
    ?>
    
    <div style="display:none" class="fancybox-hidden"> 
        <div id="popup_cart_added">
            <div class="popup-cart-msg"><?php echo $message; ?></div> 
        </div>
    </div>
    
    <?php
    exit;
} 

add_action( 'wp_ajax_cwac_add_popup', 'cwac_add_popup' );
add_action( 'wp_ajax_nopriv_cwac_add_popup', 'cwac_add_popup' );  

/** 
 * Show cart on single page using the_content hook
 */

global $settings;

if ( isset( $settings['cart_show_on_single'] ) ) {
    add_filter( 'the_content', 'cwac_show_cart_on_single' ); 
    
    function cwac_show_cart_on_single( $content ) {
        if ( is_single() ) {
            $code = cwac_product_buy();
            return $code . $content;
        }
        
        return $content;
    }
}

/** 
 * Orders page of the plugin
 */
 
function cwac_cart_order() {
    ?>
    
    <div id="cw-ajax-cart-wrap">
        <h2><?php _e( 'Orders', 'cw-ajax-cart' ); ?></h2>        
        <div id="cart_table_wrap">
            <?php cwac_order_table(); ?>
        </div>
    </div>
    
<?php    
}

/** 
 * Orders table on the admin page
 */

function cwac_order_table() {
    global $wpdb, $settings, $options;
    
    $currency = esc_attr( $settings['cart_currency'] );
    ?>
    
    <div>
        <div id="cw_orders_table">         
            <table class="cw-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="cw-td-first"><span><?php _e( 'Order ID', 'cw-ajax-cart' ); ?></span></th>
                        <th><?php _e( 'Date', 'cw-ajax-cart' ); ?></th>
                        <th><?php _e( 'Order info', 'cw-ajax-cart' ); ?></th>
                        <th><?php _e( 'Products', 'cw-ajax-cart' ); ?></th>
                        <th><?php _e( 'Status', 'cw-ajax-cart' ); ?></th>
                    </tr>
                </thead>                
                <tbody>
                
                <?php
                $orders = $wpdb->get_results("SELECT * FROM cw_cart_order ORDER BY id DESC", 'ARRAY_A');
                $fields = $options['email_options'];
                
                foreach ( $orders as $order ) {
                    $date_db = $order['order_date'];
                    $date_format = date( 'd.m.Y', strtotime( $date_db ) );
                    $status = $order['order_status'];
                    ?>
                
                    <tr>
                        <td class="td-order-item" data-colname="<?php _e( 'Order ID', 'cw-ajax-cart' ); ?>"><?php echo $order['order_id']; ?></td>
                        <td class="td-order-item" data-colname="<?php _e( 'Date', 'cw-ajax-cart' ); ?>"><?php echo $date_format; ?></td>
                        <td class="td-order-item" data-colname="<?php _e( 'Order info', 'cw-ajax-cart' ); ?>">
                            
                            <?php 
                            foreach ( $fields as $k => $v ) {
                                if ( $order['order_'.$k] ) {
                                    echo '<span class="info-item"><b>' . $v . '</b> - ' . $order['order_'.$k] . '</span>';
                                }
                            } ?>
                            
                        </td>
                        <td class="cw-order-item-wrap">
                            <table class="cw-table-order-item">
                                <thead>
                                    <tr>
                                        <td><?php _e( 'Title', 'cw-ajax-cart' ); ?></td>
                                        <td><?php _e( 'Price', 'cw-ajax-cart' ); ?></td>
                                        <td><?php _e( 'Quantity', 'cw-ajax-cart' ); ?></td>
                                        <td><?php _e( 'Total', 'cw-ajax-cart' ); ?></td>
                                    </tr>
                                </thead>
                                <tbody>
                    
                                <?php
                                $products = unserialize( $order['order_product'] );
                                
                                if ( $products ) {
                                    foreach ( $products as $product ) {
                                    ?>
                                    
                                        <tr>
                                            <td><?php echo $product['title']; ?></td>
                                            <td><?php echo $product['price']; ?></td>
                                            <td><?php echo $product['count']; ?></td>
                                            <td><?php echo $product['sum']; ?></td>
                                        </tr>
                                    
                                    <?php } ?>
                                <?php } ?>
                                
                                <tr>
                                    <td class="order-total-sum" colspan="4">
                                        <?php echo __( 'Subtotal', 'cw-ajax-cart' ) . ' ' . $order['order_sum'] . ' ' . $currency; ?> 
                                    </td>
                                </tr>
                                
                                <?php if ( $order['order_coupon'] ) { ?>
                                
                                    <tr>
                                        <td class="order-total-sum" colspan="4">
                                        
                                            <?php
                                            $coupon = unserialize( $order['order_coupon'] );
                                            
                                            if ( $coupon['type'] == 'percent' ) {
                                                $coupon_attr = $coupon['discount'] . '%';    
                                            } elseif ( $coupon['type'] == 'fix' ) {
                                                $coupon_attr = $coupon['discount'] . $currency; 
                                            }
                                            
                                            $coupon_info = $coupon['coupon'] . ' (' . $coupon_attr . ')';
                                            ?>
                                        
                                            <?php echo __( 'Coupon', 'cw-ajax-cart' ) . ' - ' . $coupon_info; ?> 
                                        </td>
                                    </tr>
                                
                                <?php } ?>
                                
                                <tr>
                                    <td class="order-total-sum" colspan="4">
                                        <?php echo __( 'Total', 'cw-ajax-cart' ) . ' ' . $order['order_total'] . ' ' . $currency; ?> 
                                    </td>
                                </tr>
                                
                                </tbody>
                            </table>
                        </td>
                        <td class="td-order-status">
                            <form class="cw-order-status">
                            
                                <?php if ( $status == 'processing' ) {
                                    $sel_cl = 'active-processing';
                                } else {
                                    $sel_cl = 'active-completed';
                                } 
                                ?>
                                
                                <select name="order-status" class="<?php echo $sel_cl; ?>">
                                    <option value="processing" class="processing" <?php if ( $status == 'processing' ) echo 'selected'; ?>><?php _e( 'Processing', 'cw-ajax-cart' ); ?></option>
                                    <option value="completed" class="completed" <?php if ( $status == 'completed' ) echo 'selected'; ?>><?php _e( 'Completed', 'cw-ajax-cart' ); ?></option>
                                </select>
                                <input type="submit" id="change_status" class="button-primary" value="<?php _e( 'Update', 'cw-ajax-cart' ); ?>" data-id="<?php echo $order['id']; ?>"/>
                            </form>
                        </td>
                    </tr>
                
                <?php } ?>
                
                </tbody>                               
            </table>
        </div>
    </div>
    
<?php    
}

/** 
 * Changing order status processing/completed on the orders page
 */

function cwac_change_status() {
    global $wpdb;
    
    $id = intval( $_POST['id'] );
    $status = sanitize_text_field( $_POST['status'] );
    
    if ( $id ) {          
        $sql = $wpdb->update( 'cw_cart_order',
            array( 'order_status' => $status ),
            array( 'id' => $id ),
            array( '%s' ),
            array( '%d' )
        );
        
        if ( $sql == 1 ) {
            $res = cwac_order_table();
        }
    }
    
    echo $res;
    exit;
}

add_action('wp_ajax_cwac_change_status', 'cwac_change_status');

/** 
 * Apply coupon on cart page
 */
 
function cwac_cart_coupon() {
    
    $nonce = $_POST['nonce'];
    $coupon = sanitize_text_field( $_POST['coupon'] );
    
    if ( wp_verify_nonce( $nonce, 'cart_ajax-nonce' ) ) {
        
        global $post;        
        
        $args = array(
        	'post_type' => 'cw_coupon', 
        	'posts_per_page' => -1
        );
        
        $new_query = new WP_Query( $args );
        
        if($new_query->have_posts()) :
            $i = 0;
            while ($new_query->have_posts()) : $new_query->the_post();
                
                $arr[ $i ]['coupon'] = get_post_meta( $post->ID, '_cw_coupon_code', true );
                $arr[ $i ]['type'] = get_post_meta( $post->ID, '_cw_coupon_type', true );
                $arr[ $i ]['discount'] = get_post_meta( $post->ID, '_cw_coupon_discount', true );
            
                $i++;    
            endwhile;
            
            foreach ( $arr as $k ) {
                if ( $k['coupon'] == $coupon ) {
                    $res['coupon'] = $k['coupon'];
                    $res['type'] = $k['type'];
                    $res['discount'] = $k['discount']; 
                }
            }
            
            wp_send_json( $res );
        else : 
            exit;
        endif;
        wp_reset_postdata();        
    }
    
    exit;    
}

add_action('wp_ajax_cwac_cart_coupon', 'cwac_cart_coupon');
add_action('wp_ajax_nopriv_cwac_cart_coupon', 'cwac_cart_coupon');
?>