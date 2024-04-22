# MWC Headless Checkout

A _blazingly fast_ 🚀 headless checkout for WooCommerce.

## Requirements

- WooCommerce

## Customize Appearance

Visit Appearance => Customizer => WooCommerce => Checkout to change colors and other settings

## Customize Fields

Fields and validation requirements can be change via custom code, or a plugin such as Checkout Field Editor. Fields cannot be moved, but validation requirements and attributes can be customized, and additional fields can be added. Only simple field types such as input (with any type like text, password, etc), textarea, select, multi-select, radio, checkbox, and heading are supported. Other field types are not supported.

## Payment Gateways

Stripe, Apple/Google Pay with Stripe, and PayPal are all supported after being configured.

### Technical explanation

This is a React app that communicates with WordPress via GraphQL. It is loaded in an iframe in order to isolate the styles.

## Debugging

Visit the customizer settings `Customizer -> WooCommerce -> Checkout` and enable logging. This will load the scripts from /staging which have logs, and add PHP logs in the WooCommerce logs admin tab. You can also `define("GD_CHECKOUT_DEBUG", true)` to wp-config.php to load the latest edge (unstable) scripts.

You can deploy the frontend app to aws staging folder with logs to help debug.

### Local Development

You can upload your dist scripts straight to the WP plugin and bypass AWS for local development or debugging.

Add `define("GD_CHECKOUT_LOCAL_SCRIPTS", true)` to wp-config.php to prevent the iframe src index.html from being overwritten. This is helpful if you want to load local scripts for the checkout, but requires updating the /dist folder to add and load the scripts. This is normally used with a vite debug build and a deploy script, for example our script is `yarn deploy:checkout:local`.

## Tests

To run acceptance tests:

`php vendor/bin/codecept run --steps`

## Distribution

Generate a clean plugin zip file for distribution, run these inside the plugin directory:

`composer install --no-dev`
`wp dist-archive .`

Add name to the zip: enhanced-checkout-woocommerce.1.0.2.zip
Upload binary as part of release
