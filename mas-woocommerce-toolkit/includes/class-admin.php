<?php
/**
 * Admin menu and screens.
 *
 * @package MAS_WCT
 */

namespace MAS_WCT;

defined( 'ABSPATH' ) || exit;

/**
 * Creates WooCommerce > MAS Toolkit pages.
 */
class Admin {
	/** Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'menu' ), 60 );
	}

	/** Add submenu pages.
	 *
	 * @return void
	 */
	public function menu(): void {
		add_submenu_page( 'woocommerce', __( 'MAS Toolkit', 'mas-woocommerce-toolkit' ), __( 'MAS Toolkit', 'mas-woocommerce-toolkit' ), 'manage_woocommerce', 'mas-wct', array( $this, 'dashboard' ) );
		add_submenu_page( 'mas-wct', __( 'Dashboard', 'mas-woocommerce-toolkit' ), __( 'Dashboard', 'mas-woocommerce-toolkit' ), 'manage_woocommerce', 'mas-wct', array( $this, 'dashboard' ) );
		add_submenu_page( 'mas-wct', __( 'Price Rounder', 'mas-woocommerce-toolkit' ), __( 'Price Rounder', 'mas-woocommerce-toolkit' ), 'manage_woocommerce', 'mas-wct-price-rounder', array( $this, 'price_rounder' ) );
		add_submenu_page( 'mas-wct', __( 'Product Health', 'mas-woocommerce-toolkit' ), __( 'Product Health', 'mas-woocommerce-toolkit' ), 'manage_woocommerce', 'mas-wct-product-health', array( $this, 'product_health' ) );
		add_submenu_page( 'mas-wct', __( 'Settings', 'mas-woocommerce-toolkit' ), __( 'Settings', 'mas-woocommerce-toolkit' ), 'manage_woocommerce', 'mas-wct-settings', array( $this, 'settings' ) );
		add_submenu_page( 'mas-wct', __( 'Future Tools', 'mas-woocommerce-toolkit' ), __( 'Future Tools', 'mas-woocommerce-toolkit' ), 'manage_woocommerce', 'mas-wct-future-tools', array( $this, 'future_tools' ) );
	}

	/** Render dashboard.
	 *
	 * @return void
	 */
	public function dashboard(): void { require MAS_WCT_PATH . 'templates/dashboard.php'; }

	/** Render price rounder.
	 *
	 * @return void
	 */
	public function price_rounder(): void { require MAS_WCT_PATH . 'templates/price-rounder.php'; }

	/** Render product health.
	 *
	 * @return void
	 */
	public function product_health(): void { require MAS_WCT_PATH . 'templates/product-health.php'; }

	/** Render settings.
	 *
	 * @return void
	 */
	public function settings(): void { echo '<div class="wrap mas-wct"><h1>' . esc_html__( 'MAS Toolkit Settings', 'mas-woocommerce-toolkit' ) . '</h1><p>' . esc_html__( 'Settings framework reserved for future modules.', 'mas-woocommerce-toolkit' ) . '</p></div>'; }

	/** Render future tools.
	 *
	 * @return void
	 */
	public function future_tools(): void { echo '<div class="wrap mas-wct"><h1>' . esc_html__( 'Future Tools', 'mas-woocommerce-toolkit' ) . '</h1><p>' . esc_html__( 'Order Analyzer, PayPal Analyzer, Google Merchant Checker, and Performance Scanner are scaffolded for extension.', 'mas-woocommerce-toolkit' ) . '</p></div>'; }
}
