<?php

// uninstalling the cron
if ( wp_next_scheduled( 'primex_cron_event' ) ) {
    wp_clear_scheduled_hook( 'primex_cron_event' );
}