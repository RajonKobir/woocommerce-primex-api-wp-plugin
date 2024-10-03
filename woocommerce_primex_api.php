<?php
/*
 * Plugin Name: Woocommerce Primex API
 * Plugin URI: 
 * Description: Woocommerce Primex API connects 3rd party Primex API To Woocommerce Store
 * Author: Rajon Kobir
 * Version: 1.0.0
 * Author URI: https://github.com/RajonKobir
 * Text Domain: WoocommercePrimexApi
 * License: GPL2+
 * Domain Path: 
*/


//  no direct access 
if( !defined('ABSPATH') ) : exit(); endif;


// Define plugin constants 
define( 'WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH', trailingslashit( plugin_dir_path(__FILE__) ) );
define( 'WOOCOMMERCE_PRIMEX_API_PLUGIN_URL', trailingslashit( plugins_url('/', __FILE__) ) );
define( 'WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME', 'woocommerce_primex_api' );

// clearing unexpected characters
function primex_secure_input($data) {
    $data = strval($data);
    $data = strtolower($data);
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $special_characters = ['&amp;', '&#38;', '&lsquo;', '&rsquo;', '&sbquo;', '&ldquo;', '&rdquo;', '&bdquo;', '&quot;', '&plus;', '&#43;', '&#x2B;', '&#8722;', '&#x2212;', '&minus;', '&ndash;', '&mdash;', '&reg;', '&#174;', '&sol;', '&#47;', '&bsol;', '&#92;', '&copy;', '&#169;' ];
    foreach($special_characters as $key => $single_character){
        $data = str_replace($single_character, '&', $data);
    }
    $data = htmlspecialchars_decode($data);
    return $data;
}


// admin or not
if( is_admin() ) {
    // admin settings page
    require_once WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . '/inc/settings/settings.php';
    //  add shortcodes 
    require_once WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . '/inc/shortcodes/shortcodes.php';
}


// register activation hook
register_activation_hook(
	__FILE__,
	'woocommerce_primex_api_activation_function'
);
function woocommerce_primex_api_activation_function(){
    require_once WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . 'install.php';
}


// register deactivation hook
register_deactivation_hook(
	__FILE__,
	'woocommerce_primex_api_deactivation_function'
);
function woocommerce_primex_api_deactivation_function(){
    require_once WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . 'uninstall.php';
}

// custom image upload
function woocommerce_primex_api_custom_image_file_upload( $api_image_url, $api_image_name ) {

	// it allows us to use download_url() and wp_handle_sideload() functions
	require_once( ABSPATH . 'wp-admin/includes/file.php' );

	// download to temp dir
	$temp_file = download_url( $api_image_url );

	if( is_wp_error( $temp_file ) ) {
		return false;
	}

    // $image_full_name = basename( $temp_file );
    $image_full_name = basename( $api_image_url );
    $image_name_array = explode( '.', $image_full_name);
    $image_name = $image_name_array[0];
    $image_extension = $image_name_array[1];

    $updated_image_full_name = $api_image_name . '.' . $image_extension;

	// move the temp file into the uploads directory
	$file = array(
		'name'     => $updated_image_full_name,
		'type'     => mime_content_type( $temp_file ),
		'tmp_name' => $temp_file,
		'size'     => filesize( $temp_file ),
	);
	$sideload = wp_handle_sideload(
		$file,
		array(
            // no needs to check 'action' parameter
			'test_form'   => false 
		)
	);

	if( ! empty( $sideload[ 'error' ] ) ) {
		// you may return error message if you want
		return false;
	}

	// it is time to add our uploaded image into WordPress media library
	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => $sideload[ 'url' ],
			'post_mime_type' => $sideload[ 'type' ],
			'post_title'     => basename( $sideload[ 'file' ] ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$sideload[ 'file' ]
	);

	if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return false;
	}

	// update medatata, regenerate image sizes
	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	wp_update_attachment_metadata(
		$attachment_id,
		wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] )
	);

    @unlink( $temp_file );

	return $attachment_id;

}
// custom image upload ends here


// On Successful WC Checkout
add_action('woocommerce_order_status_completed', 'primex_order' );
function primex_order( $order_id ) {
    // Getting an instance of the order object
    $order = wc_get_order( $order_id );
    if($order->is_paid()){
        // initializing
        $ProductLines = [];
        foreach ( $order->get_items() as $item_id => $item ) {
            // if variable product
            if( $item['variation_id'] > 0 ){
                $variation_id = $item['variation_id']; 
                $product_id = $item['product_id'];
                $primex_cron_list = get_option('primex_cron_list');
                // if primex product
                if( count($primex_cron_list) > 0 ){
                    $keys = array_keys($primex_cron_list);
                    // checking if primex product or not
                    if (in_array($product_id, $keys)) {
                        // Get the product object
                        $product = wc_get_product( $variation_id );
                        $variant_sku = $product->sku;
                        $variant_quantity = $item["quantity"];
                        array_push($ProductLines, [
                            "VariantSku" => $variant_sku,
                            "QuantityOrdered" => $variant_quantity,
                        ]);
                    }
                }
            } 
        }


        if(count($ProductLines) > 0){
            // initializing
            $resultHTML = '';
            
            // assigning values got from wp options
            $primex_api_base_url = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_base_url');
            $primex_customer_id = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_customer_id');
            $primex_api_key = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_key');

            // Customer billing information details
            $billing_first_name = $order->get_billing_first_name();
            $billing_last_name  = $order->get_billing_last_name();
            $billing_company    = $order->get_billing_company();
            $billing_address_1  = $order->get_billing_address_1();
            $billing_address_2  = $order->get_billing_address_2();
            $billing_city       = $order->get_billing_city();
            $billing_state      = $order->get_billing_state();
            $billing_postcode   = $order->get_billing_postcode();
            $billing_country    = $order->get_billing_country();

            $billing_full_name = $billing_first_name . ' ' . $billing_last_name;
            $billing_address = $billing_address_1 . ', ' . $billing_address_2;

            // for putting on primex order api
            $Reference = parse_url(site_url(), PHP_URL_HOST) . ' - ' . $order_id . ' - ' . $billing_first_name;

            // Primex API Queries
            require_once( WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . 'inc/shortcodes/includes/PrimexApiQueries.php');
            // instantiating
            $ApiQuery = new PrimexApiQueries;
            try {
                // sending create order API request to Primex
                $primex_api_create_order = $ApiQuery->primex_api_create_order( $primex_api_base_url, $primex_customer_id, $primex_api_key, json_encode($ProductLines), $billing_company, $billing_full_name, $billing_address, $billing_postcode, $billing_city, $Reference );
            }catch (PDOException $e) {
                $resultHTML .= "Error: " . $e->getMessage() . PHP_EOL;
            }finally{
                // // for testing purpose
                // $myfile = fopen( WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . "test.txt", "w");
                // // $resultHTML .= $primex_api_create_order . PHP_EOL;
                // $resultHTML .= $Reference . PHP_EOL;
                // // $resultHTML .= json_encode($ProductLines);
                // fwrite($myfile, $resultHTML);
                // fclose($myfile);
                return;
            }
        }
    }
    // if is paid ends here
}
// wc successful checkout ends here




// triggers on manually trashing a Primex Product
add_action( 'wp_trash_post', 'delete_primex_product', 10, 1 );
function delete_primex_product( $post_id ){
    // if WC product
    $product = wc_get_product( $post_id );
    if ( !$product ) {
        return;
    }
    $primex_cron_list = get_option('primex_cron_list');
    // if primex product
    if( count($primex_cron_list) > 0 ){
        $keys = array_keys($primex_cron_list);
        // checking if primex product or not
        if (in_array($post_id, $keys)) {
            // remove the item from cron list
            unset($primex_cron_list[$post_id]);
            // update the option
            update_option('primex_cron_list', $primex_cron_list);
            // clean next to update option
			$primex_cron_list = get_option('primex_cron_list');
			if( count($primex_cron_list) == 0 ){
				update_option( 'primex_sku_next_to_update', '' );
			}
        }
    }else{
		update_option( 'primex_sku_next_to_update', '' );
	}
}
// triggers on manually trashing a Primex Product ends here


// triggers on manually un-trashing a Primex Product
add_action( 'untrash_post', 'un_delete_primex_product', 10, 1 );
function un_delete_primex_product( $post_id ){
    // if WC product
    $product = wc_get_product( $post_id );
    if ( !$product ) {
        return;
    }
    $product_id = $product->id;
    $product_sku = $product->sku;

    // if primex product
    $primex_products_sku_list = get_option('primex_products_sku_list');
    if (in_array($product_sku, $primex_products_sku_list)) {
        $primex_cron_list = get_option('primex_cron_list');
        if (!in_array($product_sku, $primex_cron_list)){
            $primex_cron_list[$product_id] = $product_sku;
            // update the option
            update_option('primex_cron_list', $primex_cron_list);
        }
    }
}
// triggers on manually un-trashing a Primex Product ends here


// permanently delete hook
add_action( 'before_delete_post', 'permanently_delete_primex_product', 10, 1 );
function permanently_delete_primex_product( $post_id ){
    // if WC product
    $product = wc_get_product( $post_id );
    if ( !$product ) {
        return;
    }
    $primex_products_sku_list = get_option('primex_products_sku_list');
    // if primex product
    if( count($primex_products_sku_list) > 0 ){
        $keys = array_keys($primex_products_sku_list);
        // checking if primex product or not
        if (in_array($post_id, $keys)) {
            // remove the item from cron list
            unset($primex_products_sku_list[$post_id]);
            // update the option
            update_option('primex_products_sku_list', $primex_products_sku_list);
			// clean next to update option
			$primex_products_sku_list = get_option('primex_products_sku_list');
			if( count($primex_products_sku_list) == 0 ){
				update_option( 'primex_sku_next_to_update', '' );
			}
        }
    }else{
		update_option( 'primex_sku_next_to_update', '' );
	}
}
// permanently delete hook ends here


// adding new cron task to the system
if(file_exists( WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . 'cron.php')){
    // if cron is turned on
    $cron_on_off = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_cron_on_off');
    if($cron_on_off == 'yes'){
        add_filter( 'cron_schedules', function ( $schedules ) {
            $schedules['primex_per_ten_minutes'] = array(
                'interval' => 600, // ten minutes
                'display' => __( 'Ten Minutes' )
            );
            return $schedules;
        } );
        // cron function starts here
        add_action('primex_cron_event', 'primex_cron_function');
        function primex_cron_function() {
            $resultHTML = '';
            try{
                // run the cron
                $primex_curl = curl_init();
                curl_setopt($primex_curl, CURLOPT_URL, WOOCOMMERCE_PRIMEX_API_PLUGIN_URL . 'cron.php');
                curl_exec($primex_curl);
                if (curl_errno ( $primex_curl )) {
                    $resultHTML .= date("Y-m-d h:i:sa") . ' - Curl error: ' . curl_error ( $primex_curl ) . PHP_EOL;
                    // for outputting the error
                    $myfile = fopen( WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . "cron-curl-error.txt", "a");
                    fwrite($myfile, $resultHTML);
                    fclose($myfile);
                }
                curl_close($primex_curl); 
            }catch (PDOException $e) {
                $resultHTML .= date("Y-m-d h:i:sa") . " - Error: " . $e->getMessage() . PHP_EOL;
                // for outputting the error
                $myfile = fopen( WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . "cron-curl-error.txt", "a");
                fwrite($myfile, $resultHTML);
                fclose($myfile);
            }finally{
                // // for outputting the error
                // $myfile = fopen( WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . "cron-curl-error.txt", "a");
                // fwrite($myfile, $resultHTML);
                // fclose($myfile);

                // Clear all W3 Total Cache
                if ( function_exists( 'w3tc_flush_all' ) ) {
                    w3tc_flush_all();
                }
            }
        }

        // add cron to the schedule
        if ( ! wp_next_scheduled( 'primex_cron_event' ) ) {
            wp_schedule_event( time(), 'primex_per_ten_minutes', 'primex_cron_event' );
        }

    }else{
        // turn off the cron
        if ( wp_next_scheduled( 'primex_cron_event' ) ) {
            wp_clear_scheduled_hook( 'primex_cron_event' );
        }
    }
    // cron function ends here

}else{
    // turn off the cron
    if ( wp_next_scheduled( 'primex_cron_event' ) ) {
        wp_clear_scheduled_hook( 'primex_cron_event' );
    }
}
// adding new cron task to the system ends here