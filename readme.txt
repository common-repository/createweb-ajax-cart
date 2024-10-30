=== CW Ajax Cart ===
Contributors: igor2704
Tags: cart, ajax cart, shop cart, ecommerce, e-commerce, store, sales, shop
Requires at least: 3.3
Tested up to: 4.9
Stable tag: 4.9

CreateWeb AJAX cart is simple and easy plugin to create a simple shop.

== Description ==

CW Ajax Cart is a free eCommerce plugin that allows you to create your own shop. There is no payment gateways in this plugin. 
At first you need to select Cart page and Post type for your product on settings page.
For minicart you can use widget or shortcode

== Installation ==

1. Upload `cw-shopping-cart` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Create Cart page and use shortcode [cart] on this page (screenshot-1)
4. Go to plugin settings page, select cart page and set post type of your product (screenshot-2)
5. Create a new product, and fill Price metabox (screenshot-3)
6. You can switch on Coupons in the cart on settings page
7. Create coupons if you need, there are 2 types of the coupons - fixed price and percent (screenshot-4)

= Shortcodes =

If you want to use shortcodes editing the files you need to use this WP function - do_shortcode('');
Example - echo do_shortcode( '[minicart]' );

[minicart] - minicart, you can put it in sidebar or header (also there is widget for minicart)
[buy-button] - buy button for single product
[catalog-buy-button] - buy button for category (you can put it in excerpt field of the product, or you need to edit category file)
[cart] - cart page

== Frequently Asked Questions ==

= Can i use online payment in this plugin? =

No, there is no payment in this plugin.

== Screenshots ==

1. Cart page shortcode (screenshot-1)
2. General settings (screenshot-2)
3. Adding price to product (screenshot-3)
4. Creating coupons (screenshot-4)
5. Example of using [catalog-buy-button] shortcode editing theme file (screenshot-5)

== Changelog ==

= 2.1 =
Removed price from cart and email notification, if product is without price

Updated some translations

= 2.0 =
Russian translation

== Upgrade Notice ==

= 2.0 =
Russian translation