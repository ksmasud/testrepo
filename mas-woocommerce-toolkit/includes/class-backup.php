<?php
/**
 * Price backup and restore service.
 *
 * @package MAS_WCT
 */

namespace MAS_WCT;

defined( 'ABSPATH' ) || exit;

/**
 * Creates and restores CSV price backups.
 */
class Backup {
	/** Logger. */
	private Logger $logger;

	/** Constructor.
	 *
	 * @param Logger $logger Logger dependency.
	 */
	public function __construct( Logger $logger ) { $this->logger = $logger; }

	/** Register hooks.
	 *
	 * @return void
	 */
	public function register(): void { ensure_directory( uploads_dir( 'backups' )['path'] ); }

	/** Create a backup CSV for products.
	 *
	 * @param array $product_ids Product IDs.
	 * @return string Backup file path.
	 */
	public function create( array $product_ids ): string {
		$dir  = uploads_dir( 'backups' )['path'];
		$file = trailingslashit( $dir ) . 'price-backup-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 6, false ) . '.csv';
		ensure_directory( $dir );
		$handle = fopen( $file, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		fputcsv( $handle, array( 'Product ID', 'SKU', 'Product Name', 'Regular Price', 'Sale Price', 'Current Price', 'Date' ) );
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( (int) $product_id );
			if ( ! $product ) { continue; }
			fputcsv( $handle, array( $product->get_id(), $product->get_sku(), $product->get_name(), $product->get_regular_price(), $product->get_sale_price(), $product->get_price(), gmdate( 'c' ) ) );
		}
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		$this->logger->info( 'backup_created', array( 'file' => basename( $file ), 'count' => count( $product_ids ) ) );
		return $file;
	}

	/** Restore prices from a CSV backup.
	 *
	 * @param string $file Backup file path.
	 * @return array Result counts.
	 */
	public function restore( string $file ): array {
		$result = array( 'updated' => 0, 'errors' => array() );
		$real   = realpath( $file );
		$base   = realpath( uploads_dir( 'backups' )['path'] );
		if ( ! $real || ! $base || 0 !== strpos( $real, $base ) || ! is_readable( $real ) ) {
			return array( 'updated' => 0, 'errors' => array( __( 'Invalid backup file.', 'mas-woocommerce-toolkit' ) ) );
		}
		$handle = fopen( $real, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$header = fgetcsv( $handle );
		if ( false === $header || ! in_array( 'Product ID', $header, true ) ) {
			fclose( $handle );
			return array( 'updated' => 0, 'errors' => array( __( 'Backup header is invalid.', 'mas-woocommerce-toolkit' ) ) );
		}
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$product = wc_get_product( absint( $row[0] ?? 0 ) );
			if ( ! $product ) { $result['errors'][] = 'Missing product ' . absint( $row[0] ?? 0 ); continue; }
			$product->set_regular_price( wc_format_decimal( $row[3] ?? '' ) );
			$product->set_sale_price( wc_format_decimal( $row[4] ?? '' ) );
			$product->set_price( wc_format_decimal( $row[5] ?? '' ) );
			$product->save();
			++$result['updated'];
		}
		fclose( $handle );
		$this->logger->info( 'backup_restored', $result );
		return $result;
	}
}
