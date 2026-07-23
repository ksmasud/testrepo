<?php
/** Dashboard template. */
defined( 'ABSPATH' ) || exit;
$cards = array( 'Price Rounder' => 'Batch price rounding with backups and restore.', 'Product Health' => 'Find catalog quality issues.', 'Future Modules' => 'Extension-ready module registry.', 'Order Analyzer' => 'Coming soon.', 'PayPal Analyzer' => 'Coming soon.', 'Google Merchant Checker' => 'Coming soon.', 'Performance Scanner' => 'Coming soon.' );
?>
<div class="wrap mas-wct">
	<h1><?php esc_html_e( 'MAS WooCommerce Toolkit', 'mas-woocommerce-toolkit' ); ?></h1>
	<div class="mas-wct-grid">
		<?php foreach ( $cards as $title => $description ) : ?>
			<section class="mas-wct-card">
				<h2><?php echo esc_html( $title ); ?></h2>
				<p><?php echo esc_html( $description ); ?></p>
			</section>
		<?php endforeach; ?>
	</div>
</div>
