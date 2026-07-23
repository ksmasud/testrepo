<?php
/**
 * File logger.
 *
 * @package MAS_WCT
 */

namespace MAS_WCT;

defined( 'ABSPATH' ) || exit;

/**
 * Writes structured operational logs to uploads.
 */
class Logger {
	/** Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		ensure_directory( uploads_dir( 'logs' )['path'] );
	}

	/** Log an event.
	 *
	 * @param string $event Event name.
	 * @param array  $context Context data.
	 * @return void
	 */
	public function info( string $event, array $context = array() ): void {
		$this->write( 'info', $event, $context );
	}

	/** Log an error.
	 *
	 * @param string $event Event name.
	 * @param array  $context Context data.
	 * @return void
	 */
	public function error( string $event, array $context = array() ): void {
		$this->write( 'error', $event, $context );
	}

	/** Write log row.
	 *
	 * @param string $level Log level.
	 * @param string $event Event name.
	 * @param array  $context Context data.
	 * @return void
	 */
	private function write( string $level, string $event, array $context ): void {
		$dir  = uploads_dir( 'logs' )['path'];
		$file = trailingslashit( $dir ) . gmdate( 'Y-m-d' ) . '.log';
		ensure_directory( $dir );
		$row = wp_json_encode(
			array(
				'time'    => gmdate( 'c' ),
				'level'   => $level,
				'event'   => $event,
				'context' => $context,
			),
			JSON_UNESCAPED_SLASHES
		);
		file_put_contents( $file, $row . PHP_EOL, FILE_APPEND | LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}
}
