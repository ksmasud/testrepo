<?php
/**
 * Price rounder batch processor.
 *
 * @package MAS_WCT
 */

namespace MAS_WCT;

defined( 'ABSPATH' ) || exit;

/**
 * Processes product prices in resumable batches.
 */
class Price_Rounder {
	private Logger $logger;
	private Backup $backup;

	/** Constructor.
	 * @param Logger $logger Logger.
	 * @param Backup $backup Backup service.
	 */
	public function __construct( Logger $logger, Backup $backup ) { $this->logger = $logger; $this->backup = $backup; }

	/** Register hooks.
	 * @return void
	 */
	public function register(): void {}

	/** Start a rounder job.
	 * @param array $args Sanitized arguments.
	 * @return array Job state.
	 */
	public function start( array $args ): array {
		$query_args = $this->query_args( $args, 1 );
		$total      = (int) ( new \WP_Query( $query_args ) )->found_posts;
		$job        = array( 'id' => wp_generate_uuid4(), 'args' => $args, 'page' => 1, 'total' => $total, 'processed' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0, 'started' => microtime( true ), 'backup' => '', 'changed' => array() );
		set_transient( 'mas_wct_rounder_job', $job, DAY_IN_SECONDS );
		$this->logger->info( 'price_rounder_started', $job );
		return $job;
	}

	/** Process next batch.
	 * @return array Job state.
	 */
	public function batch(): array {
		$job = get_transient( 'mas_wct_rounder_job' );
		if ( ! is_array( $job ) ) { return array( 'complete' => true, 'message' => __( 'No active job.', 'mas-woocommerce-toolkit' ) ); }
		$args      = $job['args'];
		$query     = new \WP_Query( $this->query_args( $args, (int) $job['page'] ) );
		$ids       = array_map( 'absint', wp_list_pluck( $query->posts, 'ID' ) );
		$dry_run   = ! empty( $args['dry_run'] );
		if ( ! $dry_run && empty( $job['backup'] ) && $ids ) { $job['backup'] = $this->backup->create( $ids ); }
		foreach ( $ids as $id ) { $this->process_product( $id, $args, $job ); }
		$job['page']++;
		$job['complete']  = $job['processed'] >= $job['total'] || empty( $ids );
		$job['estimated'] = $job['processed'] > 0 ? format_duration( ( microtime( true ) - (float) $job['started'] ) * ( max( 0, $job['total'] - $job['processed'] ) / max( 1, $job['processed'] ) ) ) : '—';
		if ( $job['complete'] ) { $this->finish( $job ); } else { set_transient( 'mas_wct_rounder_job', $job, DAY_IN_SECONDS ); }
		return $job;
	}

	/** Build WP_Query args.
	 * @param array $args Input args.
	 * @param int   $page Page.
	 * @return array
	 */
	private function query_args( array $args, int $page ): array {
		$batch = max( 1, min( 1000, absint( $args['batch_size'] ?? get_option( 'mas_wct_batch_size', 200 ) ) ) );
		$tax   = array();
		if ( ! empty( $args['category'] ) ) { $tax[] = array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => array( absint( $args['category'] ) ) ); }
		if ( ! empty( $args['brand'] ) && taxonomy_exists( 'product_brand' ) ) { $tax[] = array( 'taxonomy' => 'product_brand', 'field' => 'term_id', 'terms' => array( absint( $args['brand'] ) ) ); }
		return array( 'post_type' => array( 'product', 'product_variation' ), 'post_status' => ! empty( $args['skip_hidden'] ) ? array( 'publish' ) : array( 'publish', 'private' ), 'posts_per_page' => $batch, 'paged' => $page, 'fields' => 'ids', 'tax_query' => $tax );
	}

	/** Process one product.
	 * @param int   $id Product ID.
	 * @param array $args Arguments.
	 * @param array $job Mutable job.
	 * @return void
	 */
	private function process_product( int $id, array $args, array &$job ): void {
		$product = wc_get_product( $id );
		++$job['processed'];
		if ( ! $product || ( ! empty( $args['skip_sale'] ) && $product->is_on_sale() ) || ( ! empty( $args['skip_oos'] ) && ! $product->is_in_stock() ) ) { ++$job['skipped']; return; }
		$fields  = array_filter( (array) ( $args['fields'] ?? array( 'regular_price', 'sale_price', 'price' ) ) );
		$changed = false;
		foreach ( $fields as $field ) {
			$getter = 'get_' . $field; $setter = 'set_' . $field;
			if ( ! is_callable( array( $product, $getter ) ) || ! is_callable( array( $product, $setter ) ) ) { continue; }
			$old = $product->{$getter}();
			if ( '' === $old ) { continue; }
			$new = $this->round( (float) $old, sanitize_key( $args['method'] ?? 'nearest' ) );
			if ( (string) $old !== (string) $new ) { $changed = true; $product->{$setter}( $new ); }
		}
		if ( $changed ) { ++$job['updated']; $job['changed'][] = $id; if ( empty( $args['dry_run'] ) ) { $product->save(); } } else { ++$job['skipped']; }
	}

	/** Round a price.
	 * @param float  $price Price.
	 * @param string $method Method.
	 * @return string
	 */
	private function round( float $price, string $method ): string {
		$value = match ( $method ) { 'up' => ceil( $price ), 'down' => floor( $price ), '99' => floor( $price ) + 0.99, '95' => floor( $price ) + 0.95, '49' => floor( $price ) + 0.49, '50' => floor( $price ) + 0.50, default => round( $price ) };
		return wc_format_decimal( max( 0, $value ), wc_get_price_decimals() );
	}

	/** Finish job maintenance.
	 * @param array $job Job state.
	 * @return void
	 */
	private function finish( array $job ): void {
		wc_delete_product_transients();
		if ( function_exists( 'wc_update_product_lookup_tables' ) ) { wc_update_product_lookup_tables(); }
		wp_cache_flush();
		delete_transient( 'mas_wct_rounder_job' );
		$this->logger->info( 'price_rounder_completed', array( 'execution_time' => format_duration( microtime( true ) - (float) $job['started'] ), 'products_updated' => $job['updated'], 'skipped' => $job['skipped'], 'errors' => $job['errors'] ) );
	}
}
