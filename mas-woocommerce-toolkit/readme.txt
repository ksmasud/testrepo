=== MAS WooCommerce Toolkit ===
Contributors: mas
Tags: woocommerce, prices, product health, hpos, admin
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later

Premium-quality modular WooCommerce toolkit for batch price rounding, CSV backups, restore workflows, and product health scanning.

== Description ==
MAS WooCommerce Toolkit adds WooCommerce > MAS Toolkit with Dashboard, Price Rounder, Product Health, Settings, and Future Tools pages. It is object-oriented, PSR-4 ready, HPOS compatible, multisite safe, and built for stores with 100,000+ products by using resumable AJAX batches.

== Installation Instructions ==
1. Upload the `mas-woocommerce-toolkit` folder to `/wp-content/plugins/`.
2. Ensure WordPress 6.8+, WooCommerce 10+, and PHP 8.2+ are active.
3. Activate MAS WooCommerce Toolkit from Plugins.
4. Open WooCommerce > MAS Toolkit.

== Developer Documentation ==
Services are wired in `MAS_WCT\Loader`. New modules should add a focused service class in `includes/`, an admin template in `templates/`, and AJAX methods in `MAS_WCT\Ajax` only when background processing is needed. Use `manage_woocommerce`, `mas_wct_nonce`, sanitized input, escaped output, and batched queries.

== Future Extension Guide ==
Create a service with a `register()` method, inject dependencies through `Loader::load()`, add a submenu in `Admin::menu()`, enqueue screen-specific assets through `Assets`, and store large runtime data in uploads or custom tables rather than autoloaded options.

== Changelog ==
= 1.0.0 =
* Initial release with Dashboard, Price Rounder, Product Health, CSV backups, restore service, logging, AJAX progress, and future module scaffolding.
