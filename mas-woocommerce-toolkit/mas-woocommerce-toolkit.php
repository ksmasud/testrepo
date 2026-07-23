<?php
/**
 * Plugin Name: MAS WooCommerce Toolkit
 * Description: Modular WooCommerce maintenance tools for price rounding, product health checks, backups, and future store diagnostics.
 * Version: 1.0.0
 * Author: MAS
 * Requires at least: 6.8
 * Requires PHP: 8.2
 * Requires Plugins: woocommerce
 * WC requires at least: 10.0
 * Text Domain: mas-woocommerce-toolkit
 * Domain Path: /languages
 *
 * @package MAS_WCT
 */

defined( 'ABSPATH' ) || exit;

define( 'MAS_WCT_VERSION', '1.0.0' );
define( 'MAS_WCT_FILE', __FILE__ );
define( 'MAS_WCT_PATH', plugin_dir_path( __FILE__ ) );
define( 'MAS_WCT_URL', plugin_dir_url( __FILE__ ) );
define( 'MAS_WCT_BASENAME', plugin_basename( __FILE__ ) );

require_once MAS_WCT_PATH . 'includes/helpers.php';
require_once MAS_WCT_PATH . 'includes/class-loader.php';

MAS_WCT\Loader::instance()->init();

register_activation_hook( __FILE__, array( MAS_WCT\Loader::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( MAS_WCT\Loader::class, 'deactivate' ) );
