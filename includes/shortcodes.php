<?php

/**
 * Shortcode minicart
 */
 
function cwac_minicart() {
    global $cw_cart, $settings;
    
    $res = $cw_cart->cwac_get_minicart();
    $link_text = esc_attr( $settings['cart_link_text'] );
    $info_type = $settings['cart_minicart_info_type'];
    
    if ( isset( $settings['cart_minicart_info'] ) ) {
        $show_info = $settings['cart_minicart_info'];
    } else {
        $show_info = 0;
    }
    
    if ( $info_type == 'short' ) {
        $type_cl = 'short-info';
    }

    if ( ! $link_text ) {
        $link_text = __( 'Cart', 'cw-ajax-cart' );
    }
    ?>
    
        <div class="cw-minicart <?php echo $type_cl; ?>">
            <a href="<?php echo get_the_permalink( $settings['cart_page'] ) ?>" class="cw-minicart-link">
                <i class="cw-cart-icon"></i>
           	    <span class="cw-minicart-link-title"><?php echo $link_text ?></span>
            
                <?php if ( $show_info ) { ?>
                
                    <span class="cw-minicart-info"><?php echo $res ?></span>
                
                <?php } ?>
            </a>
        </div>
    
<?php
}

add_shortcode( 'minicart', 'cwac_minicart' );

/**
 * Shortcode product cart
 */
 
function cwac_product_buy() {
    global $post, $settings, $price_field, $old_price_field;
    
    $out = '
        <div class="single-product-cart cart-add-box">
            ' . cwac_get_price() . '
            <form>
                <div class="single-cart-quantity cw-cart-quantity">
                    <input class="minus cw-minus" type="button" value="-"/>
                    <input class="product-qty" type="number" value="1" min="1"/>
                    <input class="plus cw-plus" type="button" value="+"/>
                </div>
                <button class="add-to-cart-button" data-id="' . $post->ID . '">' . __( 'Add to cart', 'cw-ajax-cart' ) . '</button>
            </form>
        </div>';
        
    return $out;
}

add_shortcode( 'buy-button', 'cwac_product_buy' );

/**
 * Shortcode catalog cart
 */
 
function cwac_catalog_buy() {
    global $post;
    
    $get_price = cwac_get_price();
    ?>
    
        <div class="catalog-add-to-cart cart-add-box">
            
            <?php 
            if ( $get_price ) { 
                echo cwac_get_price(); 
            }
            ?>
            
            <input class="add-to-cart-button" data-id="<?php echo $post->ID; ?>" value="<?php _e( 'Add to cart', 'cw-ajax-cart' ); ?>" type="button"/>
        </div>
    
    <?php
}

add_shortcode( 'catalog-buy-button', 'cwac_catalog_buy' );

/**
 * Shortcode Page Cart
 */
 
function cwac_page_cart() {
    
    $cookie_name = get_option( 'cw_cart' );
    
    if ( isset( $_COOKIE[ $cookie_name ] ) ) {
        $cookie = $_COOKIE[ $cookie_name ];
    } else {
        $cookie = 0;
    }
    
    if ( $cookie == null ) {
        echo '<div class="cart-empty">' . __( 'Cart is empty', 'cw-ajax-cart' ) . '</div>';
        return;
    }
    
    global $post, $settings, $post_type, $price_field, $cw_cart;
    
    $currency = $settings['cart_currency'];
    
    $cookie = stripslashes( $cookie );    
    $cookie = json_decode( $cookie, true );  
    
    $ids = array();
    
    for ( $i = 0; $i < count( $cookie ); $i++ ) {
        $ids[] = $cookie[ $i ]['id'];
    }
    
    $cart_query = new WP_Query(
        array( 
            'post__in' => $ids, 
            'post_type' => $post_type,
            'ignore_sticky_posts' => true, 
            'posts_per_page' => -1
        )
    );
    
    if ( $cart_query->have_posts() ) : $r = $cw_cart->cwac_get_cart();
        ?>
        
        <div id="cw-cart-page-wrap">
            <div id="cw-cart-table-wrap">
            <table id="cw-cart-table">
                <thead>
                    <tr class="cw-table-top">
                        <th class="cw-product-remove"></th>
                        <th class="cw-product-thumbnail"></th>
                        <th class="cw-product-name"><?php _e( 'Title', 'cw-ajax-cart' ); ?></th>
                        <th class="cw-product-price"><?php _e( 'Price', 'cw-ajax-cart' ); ?></th>
                        <th class="cw-product-quantity"><?php _e( 'Quantity', 'cw-ajax-cart' ); ?></th>
                        <th class="cw-product-subtotal"><?php _e( 'Total', 'cw-ajax-cart' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                
        <?php
        $total_sum = 0;
        
    while ( $cart_query->have_posts() ) : $cart_query->the_post();
    
        $name = get_the_title();
        $id = $post->ID;
        $price = get_post_meta( $post->ID, $price_field, true );
        
        if ( !$price ) {
            $price = 0;    
        }
        
        $thumbnail = get_the_post_thumbnail( $post->ID, array(60,60) );
        
        //add in array product count for each item
        for ( $j = 0; $j < count( $cookie ); $j++ ) {
            if ( $cookie[ $j ]['id'] == $id ) {
                $count = $cookie[ $j ]['count'];
            }
        }
        
        $sum = $price * $count;
        ?>
        
            <tr class="cw-product-item" data-id="<?php echo $id; ?>" data-price="<?php echo $price; ?>">
                <td class="cw-product-remove">
                    <span class="cart-remove-item" data-id="<?php echo $id; ?>">×</span>
                </td>
                <td class="cw-product-thumbnail">
                    <a href="<?php the_permalink(); ?>">
                        <?php echo $thumbnail; ?></td>
                    </a>
                <td class="cw-product-name">
                    <a href="<?php the_permalink(); ?>">
                        <?php echo $name; ?>
                    </a>
                </td>
                <td class="cw-product-price">
                    
                    <?php if ( $price ) { ?>
                    
                        <span class="amount"><?php echo $price; ?></span> <?php echo $currency; ?>
                    
                    <?php } ?>
                    
                </td>
                <td class="cw-product-quantity">
                    <div class="page-cart-quantity cw-cart-quantity">
                        <input class="minus cw-minus" type="button" value="-"/>
                        <input class="product-qty" type="number" value="<?php echo $count; ?>" min="1"/>
                        <input class="plus cw-plus" type="button" value="+"/>
                    </div>
                </td>
                <td class="cw-product-subtotal">
                    
                    <?php if ( $price ) { ?>
                    
                        <span class="amount"><?php echo $sum; ?></span> <?php echo $currency; ?>
                    
                    <?php } ?>
                    
                </td>
            </tr>
            
        <?php            
        $total_sum = $total_sum + $sum;
        
    endwhile;
        ?>
                </tbody>
            </table>
            </div>
            
            <?php
            if ( isset( $_COOKIE[ $cookie_name . '_coupon' ] ) ) {
                $cookie_coupon = $_COOKIE[ $cookie_name . '_coupon' ];
            } else {
                $cookie_coupon = 0;
            }
        
            if ( $cookie_coupon ) {
                $cookie_coupon = stripslashes( $cookie_coupon );    
                $cookie_coupon = json_decode( $cookie_coupon, true );
                $coupon_type = $cookie_coupon['type'];
                $discount = $cookie_coupon['discount'];
            }
            
            if ( isset( $settings['cart_coupons'] ) ) {
                $coupon = $settings['cart_coupons'];
            } else {
                $coupon = 0;
            }
            
            if ( $coupon ) {
            ?>
            
                <div class="cart-coupon">
                    <p class="cart-coupon-title"><?php _e( 'Coupon', 'cw-ajax-cart' ); ?></p>
                    <form id="cart_coupon_form">
                        <div class="coupon-input-wrap">
                            <input type="text" id="coupon" name="coupon-code" value="<?php echo $cookie_coupon['coupon']; ?>"/>
                        </div>
                        <span id="apply_coupon"><?php _e( 'Apply', 'cw-ajax-cart' ); ?></span>
                        <span id="remove_coupon"><?php _e( 'Reset', 'cw-ajax-cart' ); ?></span>
                        <div id="coupon-msg"></div>
                    </form>
                </div>
            
            <?php } ?>
            
            <div class="cw-cart-totals">             
                
                <?php if ( $total_sum ) { ?>
                  
                    <div class="cw-total-row">
                        <span class="cw-total-subtitle"><?php _e( 'Subtotal', 'cw-ajax-cart' ); ?>:</span>
                        <span class="cw-subtotal-sum">
                            <span class="amount" data-total="<?php echo $total_sum; ?>"><?php echo $total_sum; ?></span> <?php echo $currency; ?>
                        </span>
                    </div>
                    
                    <?php 
                    if ( $cookie_coupon ) {
                        if ( $coupon_type == 'percent' ) {
                            $total_sum = $total_sum - ( $total_sum / 100 ) * $discount;
                        } elseif ( $coupon_type == 'fix' ) {
                            $total_sum = $total_sum - $discount;
                        }
                    }
                    ?>                
                    
                    <div class="cw-total-row">
                        <span class="cw-total-subtitle"><?php _e( 'Total', 'cw-ajax-cart' ); ?>:</span>                    
                        <span class="cw-total-sum">
                            <span class="amount"><?php echo $total_sum; ?></span> <?php echo $currency; ?>
                        </span>
                    </div>
                
                <?php } ?>
                
                <div class="make-order-button"><button><?php _e( 'Checkout', 'cw-ajax-cart' ); ?></button></div>
                <div style="display: none;">
                    <div id="cw_popup_wrap">
                        <div id="cw_popup">
                            <form id="cw_popup_form">
                                
                                <?php
                                global $email_options;
                                $fields = $email_options;
                                
                                $settings_fields = $settings['cart_order_fields'];
                                
                                foreach ( $settings_fields as $k => $v ) {
                                    switch ( $k ) {
                                        case 'phone':
                                            $type = 'tel';
                                            break;
                                        case 'email':
                                            $type = 'email';
                                            break;
                                        default:
                                            $type = 'text';
                                            break;
                                    }
                                    
                                    if ( $k == 'message' ) {
                                    ?>
                                        
                                        <span class="cw-input-wrap">
                                            <textarea placeholder="<?php _e( 'Message', 'cw-ajax-cart' ); ?>" name="<?php echo $k; ?>" class="cw-form-field cw-form-textarea"></textarea>
                                        </span>
                                
                                    <?php } else { 
                                        $placeholder = '';
                                        
                                        if ( $k == 'name' ) {
                                            $placeholder = __( 'Name', 'cw-ajax-cart' );
                                        } elseif ( $k == 'phone' ) {
                                            $placeholder = __( 'Phone', 'cw-ajax-cart' );
                                        } elseif ( $k == 'email' ) {
                                            $placeholder = __( 'Email', 'cw-ajax-cart' );
                                        } elseif ( $k == 'address' ) {
                                            $placeholder = __( 'Address', 'cw-ajax-cart' );
                                        }                                       
                                        ?>
                                        
                                        <span class="cw-input-wrap">
                                            <input type="<?php echo $type; ?>" placeholder="<?php echo $placeholder; ?>" name="<?php echo $k; ?>" class="cw-form-field cw-form-input"/>
                                        </span>
                                    
                                    <?php } ?>
                                    
                                <?php } ?>
                                
                                <div>
                               	    <input type="submit" name="submit" value="<?php _e( 'Send', 'cw-ajax-cart' ); ?>" class="cw-form-submit"/>
                                </div>
                            </form>
                            <span id="cw-form-preloader"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    <?php else : ?>
        
        <div class="cart-empty"><?php _e( 'Cart is empty', 'cw-ajax-cart' ); ?></div>
        
    <?php
    endif;
    wp_reset_postdata();   
}

add_shortcode( 'cart', 'cwac_page_cart' );

?>