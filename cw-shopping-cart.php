<?php
/*
Plugin Name: CreateWeb AJAX cart
Plugin URI: https://wordpress.org/plugins/createweb-ajax-cart/
Description: Simple AJAX Cart for Wordpress 
Author: Igor Tkachenko
Author URI: http://createweb.in.ua/
Text Domain: createweb-ajax-cart
Domain Path: /languages
Version: 2.1
*/ 

/*  Copyright 2017  algiz  (email : algiz@bigmir.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

/*
    For Installation instructions, usage, revision history and other info: see readme.txt included in this package
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CWAC_VERSION', '2.1' );
define( 'CWAC_PATH', plugin_dir_path(__FILE__) );

/** 
 * Creates custom post type
 */
    
function cwac_init() {
    $labels = array(
        'name' => __( 'Coupons', 'cw-ajax-cart' ),
        'singular_name' => __( 'Coupon', 'cw-ajax-cart' ),
        'add_new' => __( 'Add New' , 'cw-ajax-cart' ),
        'add_new_item' => __( 'Add New' , 'cw-ajax-cart' ),
        'edit_item' =>  __( 'Edit coupon' , 'cw-ajax-cart' ),
        'new_item' => __( 'New coupon' , 'cw-ajax-cart' ),
        'view_item' => __( 'View coupon', 'cw-ajax-cart' ),
        'search_items' => __( 'Search coupons', 'cw-ajax-cart' ),
        'not_found' =>  __( 'No coupons found', 'cw-ajax-cart' ),
        'not_found_in_trash' => __( 'No coupons found in Trash', 'cw-ajax-cart' ),
    );
        
    register_post_type( 'cw_coupon', array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        '_builtin' =>  false,
        'capability_type' => 'post',
        'hierarchical' => true,
        'rewrite' => false,
        'query_var' => 'cw_coupon',
        'supports' => array(
            'title'
        ),
        'show_in_menu' => false
    ));
}

add_action( 'init', 'cwac_init' );

/** 
 * Plugin admin pages
 */

function cwac_add_menu() {
    add_menu_page( 
        'CW Ajax Cart', 
        'CW Ajax Cart', 
        'manage_options', 
        'manage-ajax-cart',
        'cwac_ajax_cart_func',
        'dashicons-cart'
    );    
    
    add_submenu_page( 
        'manage-ajax-cart', 
        __( 'Orders', 'cw-ajax-cart' ), 
        __( 'Orders', 'cw-ajax-cart' ), 
        'manage_options', 
        'manage-cw-cart-order', 
        'cwac_cart_order'
    ); 
    
    add_submenu_page( 
        'manage-ajax-cart', 
        __( 'Coupons', 'cw-ajax-cart' ), 
        __( 'Coupons', 'cw-ajax-cart' ), 
        'manage_options', 
        'edit.php?post_type=cw_coupon'
    ); 

    function add_action_links( $links ) {
        $cw_links = '<a href="' . admin_url( 'admin.php?page=manage-ajax-cart' ) . '">' . translate( 'Settings' ) . '</a>';
        
        array_unshift( $links, $cw_links );
        return $links;
    }
    
    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'add_action_links' );
}

add_action( 'admin_menu', 'cwac_add_menu' );

/** 
 * Connecting admin styles and js
 */
 
function cwac_admin_script_init() {
    wp_enqueue_script( 'cw_admin_cart_script', plugins_url( '/admin/js/admin-script.js', __FILE__), array('jquery') );  
    wp_enqueue_style( 'cw_admin_cart_style', plugins_url( '/admin/css/admin.css', __FILE__) );
}

add_action( 'admin_init', 'cwac_admin_script_init' );

/** 
 * Connecting user styles and js
 */
 
function cwac_user_script_init() {
    wp_enqueue_script( 'cw_cart_cookie', plugins_url( '/js/jquery.cookie.js', __FILE__), array('jquery'), false, true );
    wp_enqueue_script( 'cw_cart_script', plugins_url( '/js/script.js', __FILE__), array('jquery'), false, true );
    wp_enqueue_script( 'cw_cart_fancybox', plugins_url( '/libs/fancybox/jquery.fancybox.pack.js', __FILE__), array('jquery'), false, true ); 
    wp_enqueue_style( 'cw_cart_style', plugins_url( '/css/style.css', __FILE__) );
    wp_enqueue_style( 'cw_cart_fancybox_css', plugins_url( '/libs/fancybox/jquery.fancybox.css', __FILE__) ); 
}

add_action( 'wp_enqueue_scripts', 'cwac_user_script_init' );

/** 
 * Connecting language files
 */

function cwac_load_plugin_textdomain() {
    load_plugin_textdomain( 'cw-ajax-cart', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'plugins_loaded', 'cwac_load_plugin_textdomain' );

/** 
 * Plugin update
 */
 
function cwac_cart_update() {
    $installed_ver = $options['version'];
    
    if ( CWAC_VERSION == $installed_ver ) {
        return;
    } else {
        $options = array(
            'version' => CWAC_VERSION
        );
        
        update_option( 'cw_cart_options', $options );
    }
}

/** 
 * Creating DB and options during activation of the plugin
 */

function cwac_cart_activate() {
    global $wpdb;
    $table_name = 'cw_cart_order';
    
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        $sql = 'CREATE TABLE ' . $table_name . '(
            id INT NOT NULL AUTO_INCREMENT,
            order_id VARCHAR(10) NOT NULL COLLATE utf8_general_ci,
            order_name VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
            order_phone VARCHAR(255) NOT NULL COLLATE utf8_general_ci, 
            order_email VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
            order_address VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
            order_message TEXT NOT NULL COLLATE utf8_general_ci,
            order_product TEXT NOT NULL COLLATE utf8_general_ci,
            order_sum VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
            order_total VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
            order_coupon VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
            order_status VARCHAR(55) NOT NULL COLLATE utf8_general_ci,   
            order_date DATE NOT NULL,
            PRIMARY KEY (id)
        );';
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    
    $admin_email = get_option( 'admin_email' );
    $blogname = get_option( 'blogname' );
    
    $default_settings = array(
        'cart_minicart_info' => 1,
        'cart_minicart_info_type' => 'short',
        'cart_currency' => '$',
        'cart_added_msg_type' => 'text',
        'cart_order_fields' => array( 'name' => '1', 'phone' => '1', 'email' => '1' ),
        'cart_order_success_type' => 'text',
        'cart_mail_recipient' => $admin_email,
        'cart_mail_subject' => $blogname . ' new order',
        'cart_mail_from_name' => $blogname,
        'cart_mail_from_address' => $admin_email
    );
    
    $options = array(
        'email_options' => array(
            'name' => 'Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'address' => 'Address',
            'message' => 'Message'
        ),
        'price' => '_cw_price',
        'old_price' => '_cw_old_price',
        'version' => CWAC_VERSION
    );
    
    $uid = uniqid( rand() );
    $cookie_name = 'cw_cart_' . $uid;
    
    add_option( 'cw_cart', $cookie_name );
    add_option( 'cw_cart_settings', $default_settings );
    add_option( 'cw_cart_options', $options );
}

register_activation_hook( __FILE__, 'cwac_cart_activate' );

/** 
 * Global variables
 */

$settings = get_option( 'cw_cart_settings' );
$options = get_option( 'cw_cart_options' );
$price_field = $options['price'];
$old_price_field = $options['old_price'];
$email_options = $options['email_options'];
$type_of_post = $settings['cart_post_type'];

/** 
 * Adding WP global js variables
 */
 
function cwac_cart_scripts() {
    global $settings;
    
    $added_msg = $settings['cart_added_msg'];
    
    if ( !$added_msg ) {
        $added_msg = __( 'Added to cart', 'cw-ajax-cart' );
    }
    
    $coupon_cookie = get_option( 'cw_cart' ) . '_coupon';
    
    wp_localize_script( 'cw_cart_script', 'cart_ajax', 
        array(
           'ajax_url' => admin_url( 'admin-ajax.php' ),
           'cart_cookie' => get_option( 'cw_cart' ),
           'nonce' => wp_create_nonce( 'cart_ajax-nonce' ),
           'coupon_cookie' => $coupon_cookie
        )
    );
    
    wp_localize_script( 'cw_cart_script', 'cw_cart_params', 
        array(
           'added_msg' => $added_msg,
           'added_msg_type' => $settings['cart_added_msg_type'],
           'cart_empty' => __( 'Cart is empty', 'cw-ajax-cart' ),
           'coupon_apply_msg' => __( 'Coupon successfully applied', 'cw-ajax-cart' ),
           'coupon_not_valid' => __( 'Coupon is not valid', 'cw-ajax-cart' )
        )
    );
}

add_action( 'wp_enqueue_scripts', 'cwac_cart_scripts' );

/** Shortcodes in widget and excerpt **/

add_filter('widget_text', 'do_shortcode');
add_filter('the_excerpt', 'do_shortcode');

require_once CWAC_PATH . '/includes/functions.php';
?>