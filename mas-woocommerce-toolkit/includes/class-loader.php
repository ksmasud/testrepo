<?php
/**
 * Plugin bootstrap and dependency container.
 *
 * @package MAS_WCT
 */

namespace MAS_WCT;

defined( 'ABSPATH' ) || exit;

/**
 * Loads plugin classes and wires services.
 */
final class Loader {
	/** Singleton instance. */
	private static ?Loader $instance = null;

	/** Services. */
	private array $services = array();

	/** Get singleton instance.
	 *
	 * @return Loader
	 */
	public static function instance(): Loader {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Private constructor. */
	private function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/** Autoload plugin classes.
	 *
	 * @param string $class Class name.
	 * @return void
	 */
	public function autoload( string $class ): void {
		if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
			return;
		}
		$relative = strtolower( str_replace( '_', '-', str_replace( '\\', '/', substr( $class, strlen( __NAMESPACE__ . '\\' ) ) ) ) );
		$file     = MAS_WCT_PATH . 'includes/class-' . basename( $relative ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}

	/** Initialize plugin hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
		add_action( 'plugins_loaded', array( $this, 'load' ) );
	}

	/** Declare WooCommerce HPOS compatibility.
	 *
	 * @return void
	 */
	public function declare_hpos_compatibility(): void {
		if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', MAS_WCT_FILE, true );
		}
	}

	/** Load services after plugins are available.
	 *
	 * @return void
	 */
	public function load(): void {
		load_plugin_textdomain( 'mas-woocommerce-toolkit', false, dirname( MAS_WCT_BASENAME ) . '/languages' );
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return;
		}
		$logger       = new Logger();
		$backup       = new Backup( $logger );
		$rounder      = new Price_Rounder( $logger, $backup );
		$health       = new Product_Health( $logger );
		$this->services = array( new Assets(), new Admin(), new Ajax( $rounder, $health, $backup, $logger ), $logger, $backup, $rounder, $health );
		foreach ( $this->services as $service ) {
			if ( method_exists( $service, 'register' ) ) {
				$service->register();
			}
		}
	}

	/** WooCommerce dependency warning.
	 *
	 * @return void
	 */
	public function woocommerce_missing_notice(): void {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'MAS WooCommerce Toolkit requires WooCommerce 10 or newer.', 'mas-woocommerce-toolkit' ) . '</p></div>';
	}

	/** Activation tasks.
	 *
	 * @return void
	 */
	public static function activate(): void {
		ensure_directory( uploads_dir( 'logs' )['path'] );
		ensure_directory( uploads_dir( 'backups' )['path'] );
		add_option( 'mas_wct_batch_size', 200 );
	}

	/** Deactivation tasks.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		delete_transient( 'mas_wct_rounder_job' );
		delete_transient( 'mas_wct_health_job' );
	}
}
