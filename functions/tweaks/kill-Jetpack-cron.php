<?php
/**
* @package Kill Jetpack cron and keep it from coming back.
* @version 1.0
*/

// 1) Immediately clear any existing Jetpack cron events (late enough to avoid early-load notices).
add_action(
	'init',
	function () {
		$hooks = array(
			'jetpack_clean_nonces',
			'jetpack_v2_heartbeat',

			// Optional extra Jetpack-related hooks to be safe:
			'jetpack_sync_cron',
			'jetpack_sync_full_cron',
			'jetpack_site_icon_update',
		);

		$has_scheduled = false;
		foreach ( $hooks as $hook ) {
			if ( wp_next_scheduled( $hook ) ) {
				$has_scheduled = true;
				break;
			}
		}

		if ( ! $has_scheduled ) {
			return;
		}

		$last_cleared = (int) get_option( 'dlck_jetpack_cron_cleared', 0 );
		if ( $last_cleared && ( time() - $last_cleared ) < DAY_IN_SECONDS ) {
			return;
		}

		foreach ( $hooks as $hook ) {
			// Remove *all* scheduled instances of the hook.
			while ( wp_next_scheduled( $hook ) ) {
				wp_clear_scheduled_hook( $hook );
			}
		}

		update_option( 'dlck_jetpack_cron_cleared', time(), false );
	},
	20
);

/**
 * 2) Block future schedules of any hook that starts with "jetpack_".
 * This stops plugins/code from re-adding them later.
 * Hooked on plugins_loaded to catch Jetpack scheduling that runs before init.
 */
add_action(
	'plugins_loaded',
	function () {
		if ( ! function_exists( 'wp_next_scheduled' ) ) {
			return;
		}

		if ( ! function_exists( 'lv_block_jetpack_cron' ) ) {
			function lv_block_jetpack_cron( $pre, $event, $wp_error ) {
				// Avoid blocking during early bootstrap to prevent JIT textdomain warnings.
				if ( ! did_action( 'init' ) ) {
					return $pre;
				}

				$hook = is_array( $event ) ? ( $event['hook'] ?? '' ) : ( is_object( $event ) ? ( $event->hook ?? '' ) : '' );

				if ( is_string( $hook ) && strpos( $hook, 'jetpack_' ) === 0 ) {
					if ( $wp_error ) {
						return new WP_Error( 'jetpack_cron_blocked', sprintf( 'Blocked scheduling of %s', $hook ) );
					}
					return false; // short-circuit scheduling
				}

				return $pre;
			}
		}

		add_filter( 'pre_schedule_event', 'lv_block_jetpack_cron', 10, 3 );
		add_filter( 'pre_reschedule_event', 'lv_block_jetpack_cron', 10, 3 );
	},
	0
);

?>
