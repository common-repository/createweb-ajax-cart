<?php
class Cwac_mail_class {
    
    /** 
     * Sending email to admin and client after order
     * @param int $id
     * @param array $order_info
     * @param array $order_items
     * @param int $subtotal
     * @param int $total
     * @param array $coupon
     */
    
    public function cwac_send_order( $id, $order_info, $order_items, $subtotal, $total, $coupon ) {
        global $settings, $options;
        
        $currency = esc_attr( $settings['cart_currency'] );
        $recipient = sanitize_email( $settings['cart_mail_recipient'] );
        $subject = esc_attr( $settings['cart_mail_subject'] );
        $from_name = esc_attr( $settings['cart_mail_from_name'] ); 
        $from_email = esc_attr( $settings['cart_mail_from_address'] ); 
        $sitename = get_option( 'blogname' );
        $subject_client = sprintf( __( 'Your order №%d accepted', 'cw-ajax-cart' ), $id );
        $date = date( 'j.m.Y' );
        
        if ( empty( $recipient ) ) {
            $recipient = get_option( 'admin_email' );
        }
        
        if ( empty( $subject ) ) {
            $subject = $sitename . ' ' . __( 'new order', 'cw-ajax-cart' );
        }
        
        $headers = "Content-type: text/html; charset=utf-8 \r\n";
        $headers .= "From: $from_name <$from_email>\r\n";
        
        $headers_client = "Content-type: text/html; charset=utf-8 \r\n";
        $headers_client .= "From: $sitename\r\n";
        
        $wrapper = "
        	background-color: #f5f5f5;
        	width:100%;
        	-webkit-text-size-adjust:none !important;
        	margin:0;
        	padding: 30px 0 30px 0;
        ";
        
        $td_style = "
            text-align:left;
            vertical-align:middle;
            border: 1px solid #eee;
            padding:10px 5px;
        ";
        
        $out = '<tr style="font-weight:bold;">';
        $out .= '<td style="' . $td_style . '">' . __( 'Title', 'cw-ajax-cart' ) . '</td>';
        $out .= '<td style="' . $td_style . '">' . __( 'Price', 'cw-ajax-cart' ) . '</td>';
        $out .= '<td style="' . $td_style . '">' . __( 'Quantity', 'cw-ajax-cart' ) . '</td>';
        $out .= '<td style="' . $td_style . '">' . __( 'Total', 'cw-ajax-cart' ) . '</td>';
        $out .= '</tr>';
             
        foreach ( $order_items as $ord ) {
            $out .= '<tr>';
            $out .= '<td style="' . $td_style . '">' . $ord['title'] . '</td>';
            
            if ( $ord['price'] ) {
                $out .= '<td style="' . $td_style . '">' . $ord['price'] . ' ' . $currency . '</td>';    
            } else {
                $out .= '<td style="' . $td_style . '">&nbsp;</td>';
            }
            
            $out .= '<td style="' . $td_style . '">' . $ord['count'] . '</td>';
            
            if ( $ord['sum'] ) {
                $out .= '<td style="' . $td_style . '">' . $ord['sum'] . ' ' . $currency . '</td>';
            } else {
                $out .= '<td style="' . $td_style . '">&nbsp;</td>';
            }
            
            $out .= '</tr>';
        }
        
        if ( $subtotal ) {
            $out .= '<tr>';
            $out .= '<td colspan="3" style="' . $td_style . ' font-weight:bold;">' . __( 'Subtotal', 'cw-ajax-cart' ) . '</td>';
            $out .= '<td style="' . $td_style . ' font-weight:bold;">' . $subtotal . ' ' . $currency . '</td>';
            $out .= '</tr>';
        }
        
        if ( $coupon ) {   
            if ( $coupon['type'] == 'percent' ) {
                $coupon_attr = $coupon['discount'] . '%';    
            } elseif ( $coupon['type'] == 'fix' ) {
                $coupon_attr = $coupon['discount'] . $currency; 
            }
            
            $coupon_info = $coupon['coupon'] . ' (' . $coupon_attr . ')';
            $out .= '<tr>';
            $out .= '<td colspan="3" style="' . $td_style . ' font-weight:bold;">' . __( 'Coupon', 'cw-ajax-cart' ) . '</td>';
            $out .= '<td style="' . $td_style . ' font-weight:bold;">' . $coupon_info . '</td>';
            $out .= '</tr>';
        }
        
        if ( $total > 0 ) {
            $out .= '<tr>';
            $out .= '<td colspan="3" style="' . $td_style . ' font-weight:bold;">' . __( 'Total', 'cw-ajax-cart' ) . '</td>';
            $out .= '<td style="' . $td_style . ' font-weight:bold;">' . $total . ' ' . $currency . '</td>';
            $out .= '</tr>';
        }
        
        $fields = $options['email_options'];
        
        foreach ( $fields as $k => $v ) {
            if ( $order_info[ $k ] ) {
                $client_info .= '<tr>';
                $client_info .= '<td style="padding:5px;">';
                $client_info .= $fields[ $k ] . ': ' . $order_info[ $k ];
                $client_info .= '</td>';
                $client_info .= '</tr>';
            }
        }
        
        $message_header = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $message_header .= '<title>' . get_bloginfo( 'name' ) . '</title></head>';
        $message_header .= '<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0"><div style="' . $wrapper . '">';
        
        $message_body = '<table border="0" cellpadding="0" cellspacing="0" style="height:100%; width:100%;">
                        <tr>
                            <td align="center" valign="middle">
                                <table width="600" style="background:#fff;">
                                    <tr style="padding:15px;background:#557da1;font-weight:bold;color:#fff;">
                                        <td colspan="4" style="padding:15px;">
                                            ' . sprintf( __( 'New order №%d ( %s )', 'cw-ajax-cart' ), $id, $date ) . '
                                        </td>
                                    </tr>
                                    ' . $out . '
                                    <tr>
                                        <td colspan="4" style="padding:15px 5px 5px; font-weight:bold;">
                                            ' . __( 'Client information', 'cw-ajax-cart' ) . '
                                        </td>
                                    </tr>
                                    ' . $client_info . '
                                </table>
                            </td>
                        </tr>    
                    </table>';
        
        $message_body_client = '<table border="0" cellpadding="0" cellspacing="0" style="height:100%; width:100%;">
                        <tr>
                            <td align="center" valign="middle">
                                <table width="600" style="background:#fff;">
                                    <tr style="padding:15px;background:#557da1;font-weight:bold;color:#fff;">
                                        <td colspan="4" style="padding:15px;">
                                            ' . sprintf( __( 'Your order №%d ( %s )', 'cw-ajax-cart' ), $id, $date ) . '
                                        </td>
                                    </tr>
                                    ' . $out . '
                                    <tr>
                                        <td colspan="4" style="padding:15px 5px 5px; font-weight:bold;">
                                            ' . __( 'Client information', 'cw-ajax-cart' ) . '
                                        </td>
                                    </tr>
                                    ' . $client_info . '
                                </table>
                            </td>
                        </tr>    
                    </table>';
        
        $message_footer = '</div></body></html>';
        
        $message_order = $message_header . $message_body . $message_footer;
        $message_client = $message_header . $message_body_client . $message_footer;
        
        $send_mail = mail( $recipient, $subject, $message_order, $headers );
        
        if ( $settings['cart_mail_to_client'] ) {
            $send_client_mail = mail( $order_info['email'], $subject_client, $message_client, $headers_client );   
        }
        
        if ( $send_mail ) {
            $res['type'] = $settings['cart_order_success_type'];
            
            if ( $settings['cart_order_success_type'] == 'text' ) {
                if ( $settings['cart_order_msg_success'] ) {
                    $res['msg'] = $settings['cart_order_msg_success'];   
                } else {
                    $res['msg'] = __( 'Order has been sent successfully', 'cw-ajax-cart' );
                }
            } else {
                $res['page'] = get_the_permalink( $settings['cart_redirect_page'] );
            }
            
            wp_send_json( $res );
        } else {
            _e( 'Failed order', 'cw-ajax-cart' );
        }
    }     
}
?>
