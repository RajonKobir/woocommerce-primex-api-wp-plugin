<?php

// initializing options useful for cron
if ( !get_option('primex_cron_list') ) {
    add_option( 'primex_cron_list', [] );
}

if ( !get_option('primex_products_sku_list') ) {
    add_option( 'primex_products_sku_list', [] );
}

if ( !get_option('primex_sku_next_to_update') ) {
    add_option( 'primex_sku_next_to_update', '' );
}