<?php
/**
 * AJAX controller.
 *
 * @package MAS_WCT
 */

namespace MAS_WCT;

defined( 'ABSPATH' ) || exit;

/**
 * Handles secure admin AJAX endpoints.
 */
class Ajax {
	private Price_Rounder $rounder;
	private Product_Health $health;
	private Backup $backup;
	private Logger $logger;

	/** Constructor.
	 * @param Price_Rounder $rounder Price rounder.
	 * @param Product_Health $health Health scanner.
	 * @param Backup $backup Backup service.
	 * @param Logger $logger Logger.
	 */
	public function __construct( Price_Rounder $rounder, Product_Health $health, Backup $backup, Logger $logger ) { $this->rounder = $rounder; $this->health = $health; $this->backup = $backup; $this->logger = $logger; }

	/** Register endpoints.
	 * @return void
	 */
	public function register(): void {
		foreach ( array( 'rounder_start', 'rounder_batch', 'health_start', 'health_batch', 'restore_backup' ) as $action ) { add_action( 'wp_ajax_mas_wct_' . $action, array( $this, $action ) ); }
	}

	/** Start rounder.
	 * @return void
	 */
	public function rounder_start(): void {
		verify_ajax_request( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ) );
		$args = array(
			'dry_run' => ! empty( $_POST['dry_run'] ), 'method' => sanitize_key( wp_unslash( $_POST['method'] ?? 'nearest' ) ), 'batch_size' => absint( $_POST['batch_size'] ?? 200 ), 'category' => absint( $_POST['category'] ?? 0 ), 'brand' => absint( $_POST['brand'] ?? 0 ), 'skip_sale' => ! empty( $_POST['skip_sale'] ), 'skip_hidden' => ! empty( $_POST['skip_hidden'] ), 'skip_oos' => ! empty( $_POST['skip_oos'] ), 'fields' => array_map( 'sanitize_key', (array) ( $_POST['fields'] ?? array( 'regular_price' ) ) ),
		);
		wp_send_json_success( $this->rounder->start( $args ) );
	}

	/** Process rounder batch.
	 * @return void
	 */
	public function rounder_batch(): void { verify_ajax_request( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ) ); wp_send_json_success( $this->rounder->batch() ); }

	/** Start health scan.
	 * @return void
	 */
	public function health_start(): void { verify_ajax_request( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ) ); wp_send_json_success( $this->health->start( absint( $_POST['batch_size'] ?? 200 ) ) ); }

	/** Process health batch.
	 * @return void
	 */
	public function health_batch(): void { verify_ajax_request( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ) ); wp_send_json_success( $this->health->batch() ); }

	/** Restore backup.
	 * @return void
	 */
	public function restore_backup(): void { verify_ajax_request( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ) ); wp_send_json_success( $this->backup->restore( sanitize_text_field( wp_unslash( $_POST['file'] ?? '' ) ) ) ); }
}
