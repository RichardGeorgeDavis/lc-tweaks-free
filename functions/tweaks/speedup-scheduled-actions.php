<?php
/**
* @package Speedup Scheduled actions
 * @version 1.1
 */

// Increase the number of actions processed per batch.
add_filter(
	'action_scheduler_queue_runner_batch_size',
	function ( $batch_size ) {
		// You can experiment with the number – the default is usually low.
		return 50; // Processing 50 actions per batch.
	}
);

// Increase the number of concurrent batches.
add_filter(
	'action_scheduler_queue_runner_concurrent_batches',
	function ( $batches ) {
		return 3; // Process batches concurrently; adjust as needed.
	}
);

// Reduce the retention interval for completed scheduled actions to 15 days.
add_filter( 'action_scheduler_retention_interval', function( $days ) {
    return 15; // Retain only the last 15 days of completed actions.
} );

?>
