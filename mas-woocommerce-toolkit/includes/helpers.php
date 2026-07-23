<?php
/**
 * Shared helper functions.
 *
 * @package MAS_WCT
 */

namespace MAS_WCT;

defined( 'ABSPATH' ) || exit;

/**
 * Get plugin upload directory information.
 *
 * @param string $type Optional child folder.
 * @return array{basedir:string,baseurl:string,path:string,url:string}
 */
function uploads_dir( string $type = '' ): array {
	$upload = wp_upload_dir();
	$base   = trailingslashit( $upload['basedir'] ) . 'mas-toolkit';
	$url    = trailingslashit( $upload['baseurl'] ) . 'mas-toolkit';
	$child  = $type ? '/' . trim( sanitize_key( $type ), '/' ) : '';

	return array(
		'basedir' => $base,
		'baseurl' => $url,
		'path'    => $base . $child,
		'url'     => $url . $child,
	);
}

/**
 * Ensure a directory exists and is protected.
 *
 * @param string $path Directory path.
 * @return void
 */
function ensure_directory( string $path ): void {
	if ( ! wp_mkdir_p( $path ) ) {
		return;
	}

	$index = trailingslashit( $path ) . 'index.php';
	if ( ! file_exists( $index ) ) {
		file_put_contents( $index, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}
}

/**
 * Verify AJAX permissions.
 *
 * @param string $nonce Nonce value.
 * @return void
 */
function verify_ajax_request( string $nonce ): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to manage WooCommerce.', 'mas-woocommerce-toolkit' ) ), 403 );
	}

	if ( ! wp_verify_nonce( $nonce, 'mas_wct_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Refresh the page and try again.', 'mas-woocommerce-toolkit' ) ), 403 );
	}
}

/**
 * Format seconds for admin output.
 *
 * @param float $seconds Seconds.
 * @return string
 */
function format_duration( float $seconds ): string {
	return sprintf( '%0.2fs', max( 0, $seconds ) );
}
