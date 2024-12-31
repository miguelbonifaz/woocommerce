<?php
/**
 * Plugin Name: Jelou
 * Plugin URI: 
 * Description: Plugin to add products to cart via URL and redirect to checkout
 * Version: 1.0.0
 * Author: Jelou
 * Author URI: https://jelou.ai
 * Text Domain: jelou-cart
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Verify that WooCommerce is active
function jelou_cart_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'jelou_cart_woocommerce_missing_notice');
        deactivate_plugins(plugin_basename(__FILE__));
    }
}
add_action('admin_init', 'jelou_cart_check_woocommerce');

// Show notification if WooCommerce is not installed
function jelou_cart_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php _e('Jelou requires WooCommerce to be installed and activated.', 'jelou-cart'); ?></p>
    </div>
    <?php
}

// Main function to handle the cart URL
function jelou_cart_url_handler() {
    if (strpos($_SERVER['REQUEST_URI'], '/jelou-cart/') !== false) {
        if (!class_exists('WooCommerce')) {
            return;
        }

        WC()->cart->empty_cart();
        
        if (preg_match('/\/jelou-cart\/([^\/]+)/', $_SERVER['REQUEST_URI'], $url_matches)) {
            $products_string = $url_matches[1];
            $products_array = explode(',', $products_string);
            $products_added = 0;
            
            foreach ($products_array as $product) {
                if (preg_match('/(\d+):(\d+)/', $product, $matches)) {
                    $product_id = $matches[1];
                    $quantity = $matches[2];
                    
                    $product = wc_get_product($product_id);
                    if ($product && $product->is_purchasable()) {
                        try {
                            $added = WC()->cart->add_to_cart($product_id, $quantity);
                            if ($added) {
                                $products_added++;
                            }
                        } catch (Exception $e) {
                            error_log("Error con producto ID: {$product_id} - " . $e->getMessage());
                        }
                    }
                }
            }
            
            // If at least one product was added, redirect to checkout
            if ($products_added > 0) {
                wp_redirect(wc_get_checkout_url());
                exit;
            } else {
                wp_redirect(wc_get_cart_url());
                exit;
            }
        }
    }
}
add_action('wp_loaded', 'jelou_cart_url_handler');

// Plugin activation
register_activation_hook(__FILE__, 'jelou_cart_activate');
function jelou_cart_activate() {
    // Flush rewrite rules for the custom URL
    flush_rewrite_rules();
}

// Plugin deactivation
register_deactivation_hook(__FILE__, 'jelou_cart_deactivate');
function jelou_cart_deactivate() {
    // Clear rewrite rules when deactivating
    flush_rewrite_rules();
}

add_action( 'woocommerce_checkout_create_order', 'wp_kama_woocommerce_checkout_create_order_action', 10, 2 );

/**
 * Function for `woocommerce_checkout_create_order` action-hook.
 * 
 * @param  $order 
 * @param  $data  
 *
 * @return void
 */
function wp_kama_woocommerce_checkout_create_order_action( $order, $data ){
	error_log("Order: " . print_r($order, true));
	error_log("Data: " . print_r($data, true));
}
