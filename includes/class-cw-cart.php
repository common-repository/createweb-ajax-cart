<?php
class Cwac_cart_class {
    
    public function cwac_get_cookie() {
        $cookie_name = get_option( 'cw_cart' );
        
        if ( isset( $_COOKIE[ $cookie_name ] ) ) {
            $cookie = $_COOKIE[ $cookie_name ];
        } else {
            $cookie = 0;
        }
        
        return $cookie;
    }
    
    public function cwac_get_coupon() {
        global $post;
        
        $cookie_coupon_name = get_option( 'cw_cart' ) . '_coupon';
        
        if ( isset( $_COOKIE[ $cookie_coupon_name ] ) ) {
            $cookie = $_COOKIE[ $cookie_coupon_name ];
        } else {
            $cookie = 0;
        }
        
        if ( $cookie ) {
            $cookie_coupon = stripslashes( $cookie );    
            $cookie_coupon = json_decode( $cookie_coupon, true );
            $name = $cookie_coupon['coupon'];
            $type = $cookie_coupon['type'];
            $discount = $cookie_coupon['discount'];
            
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
                    if ( $k['coupon'] == $name ) {
                        $res['coupon'] = $k['coupon'];
                        $res['type'] = $k['type'];
                        $res['discount'] = $k['discount']; 
                    }
                }
                
                return $res;
            endif;
            wp_reset_postdata(); 
        }
    }
    
    public function cwac_get_cart() {
        $cookie = $this->cwac_get_cookie();      
        
        if ( $cookie ) {
            global $post, $post_type, $price_field;
            
            //преобразовуем cookie в массив
            $cookie = stripslashes( $cookie );    
            $cookie = json_decode( $cookie, true ); 

            //создаем массив с id товара   
            $ids = array();
            
            for ( $i = 0; $i < count($cookie); $i++ ) {
                $ids[] = $cookie[$i]['id'];
            }
            
            $count_ids = count( $ids ); 
            $cart_price = array();
            $count_arr = array();
            
            if ( !$price_field ) {
                return;
            }
            
            //для каждого товара в корзине, получаем сумму и количество
            $new_query = new WP_Query( 
                array( 
                    'post__in' => $ids, 
                    'post_type' => $post_type,
                    'ignore_sticky_posts' => true, 
                    'posts_per_page' => -1 
                ) 
            );
            
            if($new_query->have_posts()) : while ($new_query->have_posts()) : $new_query->the_post();
                
                $id = $post->ID;
                $price = get_post_meta( $id, $price_field, true );
                
                if ( !$price ) {
                    $price = 0;
                }
                
                for ( $j = 0; $j < count( $cookie ); $j++ ) {
                    if ( $cookie[$j]['id'] == $id ) {
                        $count = $cookie[$j]['count'];
                                           
                        //заносим в массив количество товара для каждой позиции  
                        $count_arr[] = $count;
                    }
                }           
                
                $sum = $price * $count; 

                //заносим в массив стоимость для каждой позиции                         
                $cart_price[] = $sum;
                $order[$id]['title'] = get_the_title();
                $order[$id]['price'] = $price;
                $order[$id]['count'] = $count;
                $order[$id]['sum'] = $sum;
                            
            endwhile;
            endif;
            wp_reset_postdata();
            
            $coupon = $this->cwac_get_coupon();

            $arr = array(
                'count_ids' => $count_ids,
                'cart_price' => $cart_price,
                'cart_count' => $count_arr,
                'order' => $order,
                'coupon' => $coupon
            );
            
            return $arr;
        }
    }
    
    public function cwac_get_cart_sum() {
        $cart = $this->cwac_get_cart();
        if ( $cart ) {
            $cart_sum = 0;
            
            for ( $i = 0; $i < count( $cart['cart_price'] ); $i++ ) {
                $cart_sum += $cart['cart_price'][$i];
            }
            return $cart_sum;
        }
    }
    
    public function cwac_get_cart_count() {
        $cart = $this->cwac_get_cart();
        if ( $cart ) {
            $count_total = 0;
            
            for ( $k = 0; $k < count( $cart['cart_count'] ); $k++ ) {
                $count_total += $cart['cart_count'][$k];
            }           
            return $count_total;
        }
    }
    
    public function cwac_get_minicart() {
        global $settings;
        
        $cookie = $this->cwac_get_cookie();    
        $cart = $this->cwac_get_cart();    
        $minicart_info = $settings['cart_minicart_info_type'];
        
        if ( $cart ) {
            if ( $cookie ) {              
                $count_ids = $cart['count_ids']; 
                $cart_sum = $this->cwac_get_cart_sum();
                $count_total = $this->cwac_get_cart_count();
                
                if ( ! empty( $count_ids ) ) {
                    $plural = sprintf(
                        _n(
                            '%s item',
                            '%s items',
                            $count_total,
                            'cw-ajax-cart'
                        ),
                        $count_total
                    );
                    
                    if ( $minicart_info == 'short' ) {
                        $result = $count_total;
                    } else {
                        $result = $plural .' '. $cart_sum . ' ' . esc_attr( $settings['cart_currency'] );   
                    }
                }
            } else {
                if ( $minicart_info == 'short' ) {
                    $result = '0';
                } else {
                    $result = '0 '. esc_attr( $settings['cart_currency'] ); 
                }      
            }
            
            //возвращаем результат - количество товаров и сумма
            return $result; 
        } else {
            return 0;
        }
    }
    
    public function cwac_get_order() {
        $cart = $this->cwac_get_cart();
    }
}
?>
