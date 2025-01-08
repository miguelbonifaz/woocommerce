# Jelou

A WooCommerce plugin that enables adding products to cart via URL, redirects to checkout, and integrates with Jelou's WhatsApp bot workflow.

## Description

This plugin provides two main functionalities:
1. Allows adding products to the WooCommerce cart using URL parameters and automatically redirects users to the checkout page
2. Stores the executionId in the session and sends it back during the order creation event, enabling WhatsApp bot workflow continuation

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- WooCommerce plugin installed and activated

## Installation

1. Download the plugin
2. Upload to your WordPress plugins directory
3. Activate the plugin through the WordPress admin interface

## Usage

### Adding Products to Cart
Add products to cart using URLs in the following format:

```
https://your-site.com/jelou-cart/productId:quantity,productId:quantity
```

Example:
```
https://your-site.com/jelou-cart/123:2,456:1
```

This will add:
- 2 units of product ID 123
- 1 unit of product ID 456

### WhatsApp Bot Integration
The plugin automatically:
1. Captures the executionId from the URL parameter
2. Stores it in the WooCommerce session
3. Sends it back during the order creation event

## Support

For support, please visit [Jelou.ai](https://jelou.ai)

## License

This project is licensed under the GPL v2 or later

## Author

[Jelou](https://jelou.ai) 