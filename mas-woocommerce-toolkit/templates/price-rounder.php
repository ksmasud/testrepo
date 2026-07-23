<?php /** Price rounder template. */ defined( 'ABSPATH' ) || exit; ?>
<div class="wrap mas-wct" data-mas-wct-screen="rounder">
	<h1><?php esc_html_e( 'Price Rounder', 'mas-woocommerce-toolkit' ); ?></h1>
	<div class="mas-wct-panel">
		<label><input type="checkbox" id="mas-wct-dry-run" checked> <?php esc_html_e( 'Dry Run mode', 'mas-woocommerce-toolkit' ); ?></label>
		<label><?php esc_html_e( 'Batch size', 'mas-woocommerce-toolkit' ); ?> <input type="number" id="mas-wct-batch-size" value="200" min="1" max="1000"></label>
		<label><?php esc_html_e( 'Rounding method', 'mas-woocommerce-toolkit' ); ?> <select id="mas-wct-method"><option value="nearest"><?php esc_html_e( 'Nearest whole dollar', 'mas-woocommerce-toolkit' ); ?></option><option value="up"><?php esc_html_e( 'Round Up', 'mas-woocommerce-toolkit' ); ?></option><option value="down"><?php esc_html_e( 'Round Down', 'mas-woocommerce-toolkit' ); ?></option><option value="99"><?php esc_html_e( 'Nearest .99', 'mas-woocommerce-toolkit' ); ?></option><option value="95"><?php esc_html_e( 'Nearest .95', 'mas-woocommerce-toolkit' ); ?></option><option value="49"><?php esc_html_e( 'Nearest .49', 'mas-woocommerce-toolkit' ); ?></option><option value="50"><?php esc_html_e( 'Nearest .50', 'mas-woocommerce-toolkit' ); ?></option></select></label>
		<fieldset><legend><?php esc_html_e( 'Update fields', 'mas-woocommerce-toolkit' ); ?></legend><label><input type="checkbox" name="mas-wct-fields" value="regular_price" checked> _regular_price</label><label><input type="checkbox" name="mas-wct-fields" value="sale_price"> _sale_price</label><label><input type="checkbox" name="mas-wct-fields" value="price"> _price</label></fieldset>
		<fieldset><legend><?php esc_html_e( 'Skip filters', 'mas-woocommerce-toolkit' ); ?></legend><label><input type="checkbox" id="mas-wct-skip-sale"> <?php esc_html_e( 'Sale products', 'mas-woocommerce-toolkit' ); ?></label><label><input type="checkbox" id="mas-wct-skip-hidden"> <?php esc_html_e( 'Hidden products', 'mas-woocommerce-toolkit' ); ?></label><label><input type="checkbox" id="mas-wct-skip-oos"> <?php esc_html_e( 'Out of stock', 'mas-woocommerce-toolkit' ); ?></label></fieldset>
		<button class="button button-primary" id="mas-wct-rounder-start"><?php esc_html_e( 'Start Processing', 'mas-woocommerce-toolkit' ); ?></button>
	</div>
	<div class="mas-wct-progress"><span style="width:0%"></span></div>
	<div id="mas-wct-rounder-stats" class="mas-wct-stats"></div>
</div>
