<?php /** Product health template. */ defined( 'ABSPATH' ) || exit; ?>
<div class="wrap mas-wct" data-mas-wct-screen="health">
	<h1><?php esc_html_e( 'Product Health', 'mas-woocommerce-toolkit' ); ?></h1>
	<div class="mas-wct-panel"><label><?php esc_html_e( 'Batch size', 'mas-woocommerce-toolkit' ); ?> <input type="number" id="mas-wct-health-batch-size" value="200" min="1" max="1000"></label><button class="button button-primary" id="mas-wct-health-start"><?php esc_html_e( 'Start Scan', 'mas-woocommerce-toolkit' ); ?></button><button class="button" id="mas-wct-health-export"><?php esc_html_e( 'CSV Export', 'mas-woocommerce-toolkit' ); ?></button></div>
	<div class="mas-wct-progress"><span style="width:0%"></span></div>
	<table class="widefat striped" id="mas-wct-health-table"><thead><tr><th><?php esc_html_e( 'Product', 'mas-woocommerce-toolkit' ); ?></th><th><?php esc_html_e( 'SKU', 'mas-woocommerce-toolkit' ); ?></th><th><?php esc_html_e( 'Issue', 'mas-woocommerce-toolkit' ); ?></th><th><?php esc_html_e( 'Actions', 'mas-woocommerce-toolkit' ); ?></th></tr></thead><tbody></tbody></table>
</div>
