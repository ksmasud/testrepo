<?php
/**
 * Uninstall cleanup.
 *
 * @package MAS_WCT
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'mas_wct_batch_size' );
delete_transient( 'mas_wct_rounder_job' );
delete_transient( 'mas_wct_health_job' );
