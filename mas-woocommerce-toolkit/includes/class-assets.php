<?php
/**
 * Admin assets.
 *
 * @package MAS_WCT
 */

namespace MAS_WCT;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues scripts and styles only on toolkit screens.
 */
class Assets {
	/** Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/** Enqueue admin assets.
	 *
	 * @param string $hook Current hook.
	 * @return void
	 */
	public function enqueue( string $hook ): void {
		if ( false === strpos( $hook, 'mas-wct' ) ) {
			return;
		}
		wp_enqueue_style( 'mas-wct-admin', MAS_WCT_URL . 'assets/css/admin.css', array(), MAS_WCT_VERSION );
		wp_enqueue_script( 'mas-wct-admin', MAS_WCT_URL . 'assets/js/admin.js', array( 'jquery', 'wp-util' ), MAS_WCT_VERSION, true );
		wp_localize_script(
			'mas-wct-admin',
			'masWct',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mas_wct_nonce' ),
				'i18n'    => array( 'running' => __( 'Running…', 'mas-woocommerce-toolkit' ), 'done' => __( 'Complete', 'mas-woocommerce-toolkit' ) ),
			)
		);
	}
}
