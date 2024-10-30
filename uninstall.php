<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function cw_cart_delete_plugin() {
	global $wpdb;
    
    $cookie_name = get_option( 'cw_cart' );
    $cookie_coupon_name = get_option( 'cw_cart' ) . '_coupon';
    
    setcookie( $cookie_name, "", ( time() - 1 ), "/" );
    setcookie( $cookie_coupon_name, "", ( time() - 1 ), "/" );

	delete_option( 'cw_cart' );
    delete_option( 'cw_cart_settings' );
    delete_option( 'cw_cart_options' );

	$table_name = 'cw_cart_order';

	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'cw_coupon' );" );
    $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ( '_cw_price', '_cw_old_price', '_cw_coupon_code', '_cw_coupon_type', '_cw_coupon_discount' );" );
}

cw_cart_delete_plugin();
?>