<?php
/**
 * Plugin Name: Jelou
 * Plugin URI: https://jelou.ai
 * Description: Plugin for WooCommerce that adds products to cart via URL, redirects to checkout, and integrates with Jelou's WhatsApp bot workflow
 * Version: 1.0.0
 * Author: Jelou
 * Author URI: https://jelou.ai
 * Text Domain: Jelou
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Jelou
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define plugin constants
 */
define('JELOU_VERSION', '1.0.0');
define('JELOU_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JELOU_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function jelou_load_textdomain() {
    load_plugin_textdomain('Jelou', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'jelou_load_textdomain');

/**
 * Verify that WooCommerce is active
 *
 * @since 1.0.0
 * @return void
 */
function jelou_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'jelou_woocommerce_missing_notice');
        deactivate_plugins(plugin_basename(__FILE__));
    }
}
add_action('admin_init', 'jelou_check_woocommerce');

/**
 * Display admin notice if WooCommerce is not installed
 *
 * @since 1.0.0
 * @return void
 */
function jelou_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php esc_html_e('Jelou requires WooCommerce to be installed and activated.', 'Jelou'); ?></p>
    </div>
    <?php
}

/**
 * Custom logging function that uses WooCommerce logging when available
 *
 * @param string $message The message to log
 * @return void
 */
function jelou_log($message) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    if (function_exists('wc_get_logger')) {
        $logger = wc_get_logger();
        $context = array('source' => 'jelou-cart');
        $logger->debug($message, $context);
    }
}

/**
 * Main function to handle the cart URL and product addition
 *
 * @since 1.0.0
 * @return void
 */
function jelou_url_handler() {
    // Validate and sanitize request method
    $request_method = isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : '';
    
    // Validate and sanitize request URI
    $request_uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
    if (empty($request_uri) || strpos($request_uri, '/jelou-cart/') === false) {
        return;
    }

    if (!class_exists('WooCommerce')) {
        return;
    }

    // Verify nonce if it's a form submission
    if ($request_method === 'POST') {
        $nonce = isset($_POST['jelou_nonce']) ? sanitize_text_field(wp_unslash($_POST['jelou_nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'jelou_cart_action')) {
            wp_die(esc_html__('Invalid request', 'Jelou'), esc_html__('Security Check', 'Jelou'), array('response' => 403));
        }
    }

    // Sanitize and validate executionId
    $execution_id = '';
    if (isset($_GET['executionId'])) {
        $execution_id = sanitize_text_field(wp_unslash($_GET['executionId']));
        if (!empty($execution_id)) {
            WC()->session->set('jelou_execution_id', $execution_id);
        }
    }

    // Clear cart before adding new items
    WC()->cart->empty_cart();
    
    if (!preg_match('/\/jelou-cart\/([^\/]+)/', $request_uri, $url_matches)) {
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }

    $products_string = sanitize_text_field($url_matches[1]);
    $products_array = explode(',', $products_string);
    $products_added = 0;
    
    foreach ($products_array as $product) {
        if (preg_match('/(\d+):(\d+)/', $product, $matches)) {
            $product_id = absint($matches[1]);
            $quantity = absint($matches[2]);
            
            // Validate product exists and is purchasable
            $product_obj = wc_get_product($product_id);
            if ($product_obj && $product_obj->is_purchasable() && $product_obj->is_in_stock()) {
                try {
                    $added = WC()->cart->add_to_cart($product_id, $quantity);
                    if ($added) {
                        $products_added++;
                    }
                } catch (Exception $e) {
                    $error_message = sprintf(
                        /* translators: 1: Product ID, 2: Error message */
                        esc_html__('Jelou Cart - Error with product ID: %1$s - %2$s', 'Jelou'),
                        $product_id,
                        $e->getMessage()
                    );
                    jelou_log($error_message);
                    continue;
                }
            }
        }
    }
    
    // Redirect based on products added
    if ($products_added > 0) {
        wp_safe_redirect(wc_get_checkout_url());
    } else {
        wc_add_notice(
            esc_html__('No valid products were found to add to the cart.', 'Jelou'),
            'error'
        );
        wp_safe_redirect(wc_get_cart_url());
    }
    exit;
}
add_action('wp_loaded', 'jelou_url_handler');

/**
 * Plugin activation hook
 *
 * @since 1.0.0
 * @return void
 */
function jelou_activate() {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__('This plugin requires WooCommerce to be installed and activated.', 'Jelou'),
            'Plugin dependency check',
            array('back_link' => true)
        );
    }
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'jelou_activate');

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 * @return void
 */
function jelou_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'jelou_deactivate');

/**
 * Add execution ID to order meta data
 *
 * @since 1.0.0
 * @param int    $order_id Order ID.
 * @param object $order    Order object.
 * @return void
 */
function jelou_add_order_data($order_id, $order) {
    if (!$order instanceof WC_Order) {
        return;
    }
    
    $execution_id = WC()->session->get('jelou_execution_id');
    error_log('executionId: ' . ($execution_id ? $execution_id : 'no existe'));
    
    $execution_id = WC()->session->get('jelou_execution_id');
    if (!empty($execution_id)) {
        $order->update_meta_data('executionId', sanitize_text_field($execution_id));
        $order->save();
        
        // Limpiar el executionId de la sesión después de guardarlo
        WC()->session->__unset('jelou_execution_id');
    }
}
add_action('woocommerce_new_order', 'jelou_add_order_data', 10, 2);
