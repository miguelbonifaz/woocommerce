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

        // Capturar el executionId del query string
        $execution_id = isset($_GET['executionId']) ? sanitize_text_field($_GET['executionId']) : '';
        error_log('executionId: ' . $execution_id);
        if ($execution_id) {
            WC()->session->set('jelou_execution_id', $execution_id);
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

// Para asegurarnos que es una petición REST API
add_action('woocommerce_new_order', 'add_custom_order_data_rest', 10, 2);

function add_custom_order_data_rest($order_id, $order) {
    $execution_id = WC()->session->get('jelou_execution_id');
    
    $order->update_meta_data('executionId', $execution_id);
    $order->save();
    error_log('order: ' . $order);
}
