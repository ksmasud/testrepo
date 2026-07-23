<?php
/**
 * Product health scanner.
 *
 * @package MAS_WCT
 */

namespace MAS_WCT;

defined( 'ABSPATH' ) || exit;

/**
 * Scans WooCommerce products in batches for merchandising and data issues.
 */
class Product_Health {
	private Logger $logger;

	/** Constructor.
	 * @param Logger $logger Logger.
	 */
	public function __construct( Logger $logger ) { $this->logger = $logger; }

	/** Register hooks.
	 * @return void
	 */
	public function register(): void {}

	/** Start scanner.
	 * @param int $batch_size Batch size.
	 * @return array
	 */
	public function start( int $batch_size ): array {
		$total = (int) ( new \WP_Query( array( 'post_type' => array( 'product', 'product_variation' ), 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) ) )->found_posts;
		$job   = array( 'page' => 1, 'batch_size' => max( 1, min( 1000, $batch_size ) ), 'total' => $total, 'processed' => 0, 'issues' => array(), 'started' => microtime( true ) );
		set_transient( 'mas_wct_health_job', $job, DAY_IN_SECONDS );
		$this->logger->info( 'product_health_started', array( 'total' => $total ) );
		return $job;
	}

	/** Process next scanner batch.
	 * @return array
	 */
	public function batch(): array {
		$job = get_transient( 'mas_wct_health_job' );
		if ( ! is_array( $job ) ) { return array( 'complete' => true, 'message' => __( 'No active scan.', 'mas-woocommerce-toolkit' ) ); }
		$query = new \WP_Query( array( 'post_type' => array( 'product', 'product_variation' ), 'post_status' => 'any', 'posts_per_page' => (int) $job['batch_size'], 'paged' => (int) $job['page'], 'fields' => 'ids' ) );
		foreach ( $query->posts as $id ) { $job['issues'] = array_merge( $job['issues'], $this->scan_product( (int) $id ) ); ++$job['processed']; }
		$job['page']++;
		$job['complete'] = $job['processed'] >= $job['total'] || empty( $query->posts );
		if ( $job['complete'] ) { delete_transient( 'mas_wct_health_job' ); $this->logger->info( 'product_health_completed', array( 'execution_time' => format_duration( microtime( true ) - (float) $job['started'] ), 'issues' => count( $job['issues'] ) ) ); } else { set_transient( 'mas_wct_health_job', $job, DAY_IN_SECONDS ); }
		return $job;
	}

	/** Scan a product.
	 * @param int $id Product ID.
	 * @return array
	 */
	private function scan_product( int $id ): array {
		$product = wc_get_product( $id );
		if ( ! $product ) { return array(); }
		$issues = array();
		$checks = array(
			'Missing Featured Image' => ! $product->get_image_id(),
			'Missing Gallery' => $product->is_type( 'simple' ) && empty( $product->get_gallery_image_ids() ),
			'Missing SKU' => '' === $product->get_sku(),
			'Duplicate SKU' => $product->get_sku() && wc_get_product_id_by_sku( $product->get_sku() ) !== $product->get_id(),
			'Missing Price' => '' === $product->get_price(),
			'Missing GTIN' => '' === $product->get_meta( '_global_unique_id' ),
			'Missing Brand' => taxonomy_exists( 'product_brand' ) && ! has_term( '', 'product_brand', $id ),
			'Missing Weight' => '' === $product->get_weight(),
			'Missing Dimensions' => ! $product->has_dimensions(),
			'Missing Categories' => 'product' === get_post_type( $id ) && ! has_term( '', 'product_cat', $id ),
			'Missing Short Description' => '' === trim( $product->get_short_description() ),
			'Missing Long Description' => '' === trim( $product->get_description() ),
			'Broken Featured Image' => $product->get_image_id() && ! get_post( $product->get_image_id() ),
			'No Stock Status' => '' === $product->get_stock_status(),
			'Invalid Sale Price' => '' !== $product->get_sale_price() && (float) $product->get_sale_price() < 0,
			'Sale Price Higher Than Regular Price' => '' !== $product->get_sale_price() && '' !== $product->get_regular_price() && (float) $product->get_sale_price() > (float) $product->get_regular_price(),
			'Variable Product Missing Default Variation' => $product->is_type( 'variable' ) && empty( $product->get_default_attributes() ),
			'Variable Product Missing Prices' => $product->is_type( 'variable' ) && empty( $product->get_variation_prices() ),
		);
		foreach ( $product->get_gallery_image_ids() as $image_id ) { if ( ! get_post( $image_id ) ) { $checks['Broken Gallery Images'] = true; break; } }
		foreach ( $checks as $label => $failed ) { if ( $failed ) { $issues[] = array( 'id' => $id, 'sku' => $product->get_sku(), 'name' => $product->get_name(), 'issue' => $label, 'edit' => get_edit_post_link( $id, 'raw' ), 'view' => get_permalink( $id ) ); } }
		return $issues;
	}
}
