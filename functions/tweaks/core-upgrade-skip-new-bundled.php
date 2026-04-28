<?php
/**
 * Skip installing new bundled themes during core updates.
 */

if ( ! defined( 'CORE_UPGRADE_SKIP_NEW_BUNDLED' ) ) {
	define( 'CORE_UPGRADE_SKIP_NEW_BUNDLED', true );
}
