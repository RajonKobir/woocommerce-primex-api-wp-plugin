<?php

// requiring WC Rest API SDK
require_once  'wc-api-php-trunk/vendor/autoload.php';
use Automattic\WooCommerce\Client;

// to get the options values
require_once '../../../wp-config.php';

// initializing
$website_url = '';
$woocommerce_api_consumer_key = '';
$woocommerce_api_consumer_secret = '';
$woocommerce_api_mul_val = 1;
$primex_api_base_url = '';
$primex_customer_id = '';
$primex_api_key = '';
$primex_api_language = '';
$wc_prod_tags = '';
$resultHTML = '';

// get option value
$primex_cron_list = get_option('primex_cron_list');

if( $primex_cron_list ){

    if( is_array($primex_cron_list) ){

        if( count($primex_cron_list) > 0 ){

            //create or update wp-option includes most recently updated product sku for cron 
            $primex_sku_next_to_update = get_option('primex_sku_next_to_update');

            if( $primex_sku_next_to_update ){

                if( $primex_sku_next_to_update == '' ){

                    update_option('primex_sku_next_to_update', $primex_cron_list[array_keys($primex_cron_list)[0]] );

                }
            
            }else{

                update_option('primex_sku_next_to_update', $primex_cron_list[array_keys($primex_cron_list)[0]] );

            }

            // updated value
            $primex_sku_next_to_update = get_option('primex_sku_next_to_update');

            // if not empty
            if( $primex_sku_next_to_update != ''){

            // assigning values got from wp options
            $website_url = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_website_url');
            $woocommerce_api_consumer_key = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_key');
            $woocommerce_api_consumer_secret = primex_decrypt_password(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_secret'));
            $woocommerce_api_mul_val = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_mul_val');
            $primex_api_base_url = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_base_url');
            $primex_customer_id = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_customer_id');
            $primex_api_key = primex_decrypt_password(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_key'));
            $primex_api_language = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_language');
            $wc_prod_tags = primex_secure_input(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_wc_prod_tags'));

            // WC Rest API SDK instantiating
            $woocommerce = new Client(
                $website_url,
                $woocommerce_api_consumer_key,
                $woocommerce_api_consumer_secret,
                [
                    'version' => 'wc/v3',
                ]
            );

            // Primex API Queries
            require_once( WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . 'inc/shortcodes/includes/PrimexApiQueries.php');

            // instantiating
            $ApiQuery = new PrimexApiQueries;

            try {

                // sending single product API request to Primex
                $primex_api_single_product = $ApiQuery->primex_api_single_product($primex_sku_next_to_update, $primex_api_base_url, $primex_customer_id, $primex_api_key, $primex_api_language);
            
            } catch (PDOException $e) {

                $resultHTML .= "Error: " . $e->getMessage();
        
            }finally{

                // assigning some useful values got from Primex API response
                $primex_api_single_product = json_decode($primex_api_single_product, true);

                // if a valid response
                if(isset($primex_api_single_product["Master"][0]["Variants"])){

                    // initializing 
                    $primex_cat_name = '';
                    $primex_sub_cat_name = '';
                    $primex_prod_variants = [];
                    $primex_prod_name = '';
                    $primex_prod_brand = '';
                    $primex_prod_sku = '';
                    $primex_prod_img = '';
                    $primex_prod_desc = '';
                    $primex_prod_short_desc = '';
                    // used in product meta
                    $Composite = '';
                    $CountryOfOrigin = '';
                    $Genre = '';
                    $SleeveStyle = '';
                    $Version = '';
                    $Fitting = '';
                    $AvailableSizes = '';
                    $SubCollection = '';
                    $ProductKey = '';
                    $CommodityCode = '';

                    if(isset($primex_api_single_product["Master"][0]["Categories"]["Items"][0]["Name"])){
                        $primex_cat_name = $primex_api_single_product["Master"][0]["Categories"]["Items"][0]["Name"];
                      }
            
                      if(isset( $primex_api_single_product["Master"][0]["Categories"]["Items"][0]["SubCategory"]["Name"] )){
                        $primex_sub_cat_name = $primex_api_single_product["Master"][0]["Categories"]["Items"][0]["SubCategory"]["Name"];
                      }
                    
                      $primex_prod_variants = $primex_api_single_product["Master"][0]["Variants"];
            
                      if(isset( $primex_api_single_product["Master"][0]["Name"] )){
                        $primex_prod_name = $primex_api_single_product["Master"][0]["Name"];
                      }
            
                      if(isset( $primex_api_single_product["Master"][0]["Brand"] )){
                        $primex_prod_brand = $primex_api_single_product["Master"][0]["Brand"];
                      }
            
                      if(isset( $primex_api_single_product["Master"][0]["Sku"] )){
                        $primex_prod_sku = $primex_api_single_product["Master"][0]["Sku"];
                      }
            
                      if(isset( $primex_api_single_product["Master"][0]["Images"]["Items"][0] )){
                        $primex_prod_img = $primex_api_single_product["Master"][0]["Images"]["Items"][0];
                      }
            
                      if(isset( $primex_api_single_product["Master"][0]["Languages"]["Items"][0]["Description"] )){
                        $primex_prod_desc = $primex_api_single_product["Master"][0]["Languages"]["Items"][0]["Description"];
                      }
            
                      if(isset( $primex_api_single_product["Master"][0]["Languages"]["Items"][0]["Brief"] )){
                        $primex_prod_short_desc = $primex_api_single_product["Master"][0]["Languages"]["Items"][0]["Brief"];
                      }
            
                      // updated product name
                      if(str_contains($primex_prod_name, $primex_prod_brand) && !str_contains($primex_prod_name, $primex_prod_sku)){
                          $primex_prod_name = $primex_prod_name . ' #' . $primex_prod_sku;
                      }
                      elseif(!str_contains($primex_prod_name, $primex_prod_brand) && str_contains($primex_prod_name, $primex_prod_sku)){
                          $primex_prod_name = $primex_prod_brand . ' ' . $primex_prod_name;
                      }
                      elseif(!str_contains($primex_prod_name, $primex_prod_brand) && !str_contains($primex_prod_name, $primex_prod_sku)){
                          $primex_prod_name = $primex_prod_brand . ' ' . $primex_prod_name . ' #' . $primex_prod_sku;
                      }
            
                    // used in product meta
                    if(isset( $primex_api_single_product["Master"][0]["Composite"] )){
                      $Composite = $primex_api_single_product["Master"][0]["Composite"];
                    }
                    if(isset( $primex_api_single_product["Master"][0]["CountryOfOrigin"] )){
                      $CountryOfOrigin = $primex_api_single_product["Master"][0]["CountryOfOrigin"];
                    }
                    if(isset( $primex_api_single_product["Master"][0]["Genre"] )){
                      $Genre = $primex_api_single_product["Master"][0]["Genre"];
                    }
                    if(isset( $primex_api_single_product["Master"][0]["SleeveStyle"] )){
                      $SleeveStyle = $primex_api_single_product["Master"][0]["SleeveStyle"];
                    }
                    if(isset( $primex_api_single_product["Master"][0]["Version"] )){
                      $Version = $primex_api_single_product["Master"][0]["Version"];
                    }
                    if(isset( $primex_api_single_product["Master"][0]["Fitting"] )){
                      $Fitting = $primex_api_single_product["Master"][0]["Fitting"];
                    }
                    if(isset( $primex_api_single_product["Master"][0]["AvailableSizes"] )){
                      $AvailableSizes = $primex_api_single_product["Master"][0]["AvailableSizes"];
                    }
                    if(isset( $primex_api_single_product["Master"][0]["SubCollection"] )){
                      $SubCollection = $primex_api_single_product["Master"][0]["SubCollection"];
                    }
                    if(isset( $primex_api_single_product["Master"][0]["ProductKey"] )){
                      $ProductKey = $primex_api_single_product["Master"][0]["ProductKey"];
                    }
                    if(isset( $primex_api_single_product["Master"][0]["CommodityCode"] )){
                      $CommodityCode = $primex_api_single_product["Master"][0]["CommodityCode"];
                    }
            
            
                    // $primex_atrributes = ["Brand", "Color", "Size", "BodyLengthWidth"];
                    $primex_atrributes = ["Color", "Size"];
            
                    $primex_all_colors_array = [];
                    $primex_all_sizes_array = [];
            
                    //initially putting first image
                    $primex_all_image_src_array = [
                      [
                        'src' => $primex_prod_img,
                        'name' => $primex_prod_name,
                        'alt' => $primex_prod_name,
                      ],
                    ];

                // creating color, size, images array
                if(count($primex_prod_variants) > 0){
                    foreach($primex_prod_variants as $key => $single_variant){

                        if($single_variant["Available"] == true || $single_variant["OnSale"] == true){

                            $primex_api_variant_sku = $single_variant["Sku"];
                            $single_variant_name = $single_variant["Name"] . ' #' . $primex_api_variant_sku;

                        if($single_variant["Color"] != '' && !in_array($single_variant["Color"], $primex_all_colors_array)){

                            array_push($primex_all_colors_array, $single_variant["Color"]);

                            // creating product images array
                            if(isset($single_variant["Images"]["Items"][0])){

                                if($single_variant["Images"]["Items"][0] != ''){
            
                                $temp_array = [
                                    'src' => $single_variant["Images"]["Items"][0],
                                    'name' => $single_variant_name,
                                    'alt' => $single_variant_name,
                                ];
                        
                                array_push($primex_all_image_src_array,  $temp_array);
            
                                }
            
                            }

                        }

                        if($single_variant["Size"] != '' && !in_array($single_variant["Size"], $primex_all_sizes_array)){
                            array_push($primex_all_sizes_array, $single_variant["Size"]);
                        }

                        }

                    }
                }
                // end of foreach


                // getting all WC categories
                $product_category_list = [];

                // initializing
                $page = 1;

                // infinite loop
                while(1 == 1) {

                    // initializing for grabbing all categories
                    $data = [
                    'page' => $page,
                    'per_page' => 100,
                    ];

                    try{
                    // getting all WC categories
                    $product_category_list_temp = $woocommerce->get('products/categories', $data);

                    } catch (PDOException $e) {

                    $resultHTML .= "Error: " . $e->getMessage();
            
                    } 

                    $product_category_list = array_merge($product_category_list, $product_category_list_temp);

                    if( count($product_category_list_temp) < 100 ){
                    break;
                    }

                    $page++;

                }
                // infinite loop ends here


                    // creating all category names array
                    $product_category_names = [];

                    foreach($product_category_list as $key => $single_category){

                    $product_category_names[$single_category->id] = primex_secure_input($single_category->name);

                    }

                    // checking category names exist or not
                    $key1 = array_search(primex_secure_input($primex_cat_name), $product_category_names);

                    // creating WC category
                    if ($key1 !== false) {

                        $resultHTML .= '<p class="text-center">Category ('.$primex_cat_name.') already exists!</p>';

                    }else{

                    $category = [
                        'name' => $primex_cat_name
                    ];

                    try {

                        $callBack1 = $woocommerce->post('products/categories', $category);

                    } catch (PDOException $e) {

                        $resultHTML .= "Error: " . $e->getMessage();
                
                    }finally{

                        $resultHTML .= '<p class="text-center">Category ('.$primex_cat_name.') has been created!</p>';

                    }

                    }


                    // checking sub-category names exist or not
                    $key2 = array_search(primex_secure_input($primex_sub_cat_name), $product_category_names);

                    // creating WC sub-category
                    if ($key2 !== false) {

                    $resultHTML .= '<p class="text-center">Sub-Category ('.$primex_sub_cat_name.') already exists!</p>';

                    }else{

                    $sub_category = [
                        'name' => $primex_sub_cat_name,
                        'parent' => (isset($callBack1->id)) ? $callBack1->id : $key1
                    ];

                    try {

                        $callBack2 = $woocommerce->post('products/categories', $sub_category);

                    } catch (PDOException $e) {

                        $resultHTML .= "Error: " . $e->getMessage();
                
                    }finally{

                        $resultHTML .= '<p class="text-center">Sub-Category ('.$primex_sub_cat_name.') has been created!</p>';

                    }

                    }


                    // checking sub-category names exist or not
                    $key5 = array_search(primex_secure_input($primex_prod_brand), $product_category_names);

                    // creating WC sub-category
                    if ($key5 !== false) {

                    $resultHTML .= '<p class="text-center">Sub-Category ('.$primex_prod_brand.') already exists!</p>';

                    }else{

                    $sub_category = [
                        'name' => $primex_prod_brand,
                        'parent' => (isset($callBack1->id)) ? $callBack1->id : $key1
                    ];

                    try {

                        $callBack5 = $woocommerce->post('products/categories', $sub_category);

                    } catch (PDOException $e) {

                        $resultHTML .= "Error: " . $e->getMessage();
                
                    }finally{

                        $resultHTML .= '<p class="text-center">Sub-Category ('.$primex_prod_brand.') has been created!</p>';

                    }

                    }
                    // creating category and sub-category ends here
            

                    try {

                        // getting all WC attributes
                        $wc_all_attributes = $woocommerce->get('products/attributes');

                    }catch (PDOException $e) {

                        $resultHTML .= "Error: " . $e->getMessage();

                    }finally{

                    // creating all attributes array
                    $wc_all_attributes_array = [];

                    if(count($wc_all_attributes) != 0){

                        foreach($wc_all_attributes as $id => $single_attribute){

                        $wc_all_attributes_array[$single_attribute->id] = $single_attribute->name;

                        }

                    }


                    // loop through all attributes & create if not exists
                    foreach($primex_atrributes as $key => $single_attribute){

                        if(count($wc_all_attributes_array) != 0){
                        
                        if(!in_array($single_attribute, $wc_all_attributes_array)){

                            $data = [
                                'name' => $single_attribute,
                                'slug' => str_replace(' ', '_', $single_attribute),
                                'type' => 'select',
                                'order_by' => 'menu_order',
                                'has_archives' => true
                            ];

                            try {

                            $wc_create_attribute = $woocommerce->post('products/attributes', $data);

                            } catch (PDOException $e) {

                            $resultHTML .= "Error: " . $e->getMessage();
                    
                            }finally{

                            $resultHTML .= '<p class="text-center">Attribute ('.$single_attribute.') created successfully!</p>';

                            }
                            
                        }else{
                            $resultHTML .= '<p class="text-center">Attribute ('.$single_attribute.') already exists!</p>';
                          }

                        }else{

                        $data = [
                            'name' => $single_attribute,
                            'slug' => str_replace(' ', '_', $single_attribute),
                            'type' => 'select',
                            'order_by' => 'menu_order',
                            'has_archives' => true
                        ];

                        try {

                        $wc_create_attribute = $woocommerce->post('products/attributes', $data);

                        } catch (PDOException $e) {

                        $resultHTML .= "Error: " . $e->getMessage();
                
                        }finally{

                        $resultHTML .= '<p class="text-center">Attribute ('.$single_attribute.') created successfully!</p>';

                        }

                    }

                    }
                    }
                    // create attribute ends here

            
                    try {

                        // getting all attributes again
                        $wc_all_attributes = $woocommerce->get('products/attributes');
            
                    }catch (PDOException $e) {
            
                        $resultHTML .= "Error: " . $e->getMessage();
            
                    }finally{

                        // creating avilable attributes array
                        $wc_all_attributes_array = [];

                        if(count($wc_all_attributes) != 0){

                            foreach($wc_all_attributes as $id => $single_attribute){

                            $wc_all_attributes_array[$single_attribute->id] = $single_attribute->name;

                            }

                        }
                    }

              // getting all WC products
              $wc_all_products = [];
              // initializing
              $page = 1;
              // infinite loop
              while(1 == 1) {
                // initializing for grabbing all products
                $data = [
                  'page' => $page,
                  'per_page' => 100,
                ];
                try{
                  // getting all WC products
                  $all_products_list_temp = $woocommerce->get('products',  $data);
    
                } catch (PDOException $e) {
    
                  $resultHTML .= "Error: " . $e->getMessage();
          
                } 
    
                $wc_all_products = array_merge($wc_all_products, $all_products_list_temp);
    
                if( count($all_products_list_temp) < 100 ){
                  break;
                }
                $page++;
              }
              // infinite loop ends here
                
                // creating all products array
                $wc_all_prod_array = [];

                if($wc_all_products){

                if(count($wc_all_products) != 0){

                    foreach($wc_all_products as $key => $single_wc_prod){

                    $wc_all_prod_array[$single_wc_prod->id] = $single_wc_prod->sku;

                    }
                    
                }

                }


                // attempting to create or update a product
                // creating attributes array
                $attributes_array = [];

                if(count($primex_all_colors_array) != 0 ){
                array_push($attributes_array,[
                    'id'        => array_search("Color", $wc_all_attributes_array),
                    'variation' => true,
                    'visible'   => true,
                    'options'   => $primex_all_colors_array,
                ]);
                }

                if(count($primex_all_sizes_array) != 0 ){
                array_push($attributes_array,[
                    'id'        => array_search("Size", $wc_all_attributes_array),
                    'variation' => true,
                    'visible'   => true,
                    'options'   => $primex_all_sizes_array,
                ]);
                }

                // if product sku exists or not
                $key3 = array_search($primex_prod_sku, $wc_all_prod_array);

                if ($key3 !== false) {

                    // get the correct product id
                    $wc_product_id = $key3;

                    try {
                        // retrieving the product
                        $wc_retrieved_product = $woocommerce->get('products/' . strval($wc_product_id));
                    }catch (PDOException $e) {
                        $resultHTML .= "Error: " . $e->getMessage();
                    }

                    $wc_total_images = count($wc_retrieved_product->images);
                    $primex_total_images = count($primex_all_image_src_array);
                    $missed_images_array = [];
      
                    if($wc_total_images == 0){
                      $updated_images_array = [];
                    }else{
                      $updated_images_array = $wc_retrieved_product->images;
                    }
      
                    if($primex_total_images > $wc_total_images){
                        for($i = $wc_total_images; $i < $primex_total_images; $i++){
      
                          try {
                            $image_id = woocommerce_primex_api_custom_image_file_upload( $primex_all_image_src_array[$i]['src'], $primex_all_image_src_array[$i]['name'] );
                          }catch (PDOException $e) {
                            $resultHTML .= "Error: " . $e->getMessage();
                          }finally{
                            if(is_int($image_id)){
                              array_push($updated_images_array,  [
                                'id' => $image_id,
                                'name' => $primex_all_image_src_array[$i]['name'],
                                'alt' => $primex_all_image_src_array[$i]['alt'],
                              ]);
                            }else{
                              array_push($missed_images_array, $i + 1);
                            }
                          }
                        }
                    }

                    // creating product's meta data
                    $product_meta_data_array = [
                        [
                            'key' => 'name',
                            'value' => $primex_prod_name,
                        ],
                        [
                            'key' => 'category',
                            'value' => $primex_cat_name,
                        ],
                        [
                            'key' => 'sub_category',
                            'value' => $primex_sub_cat_name,
                        ],
                        [
                            'key' => 'brand',
                            'value' => $primex_prod_brand,
                        ],
                        [
                            'key' => 'colors',
                            'value' => implode(",", $primex_all_colors_array),
                        ],
                        [
                            'key' => 'sizes',
                            'value' => implode(",", $primex_all_sizes_array),
                        ],
                        [
                            'key' => 'composite',
                            'value' => $Composite,
                        ],
                        [
                            'key' => 'country_of_origin',
                            'value' => $CountryOfOrigin,
                        ],
                        [
                            'key' => 'genre',
                            'value' => $Genre,
                        ],
                        [
                            'key' => 'sleeve_style',
                            'value' => $SleeveStyle != '' ? $SleeveStyle : 'Fit Sleeve',
                        ],
                        [
                            'key' => 'version',
                            'value' => $Version,
                        ],
                        [
                            'key' => 'fitting',
                            'value' => $Fitting != '' ? $Fitting : 'Fit',
                        ],
                        [
                            'key' => 'available_sizes',
                            'value' => $AvailableSizes,
                        ],
                        [
                            'key' => 'sub_collection',
                            'value' => $SubCollection,
                        ],
                        [
                            'key' => 'product_key',
                            'value' => $ProductKey,
                        ],
                        [
                            'key' => 'commodity_code',
                            'value' => $CommodityCode,
                        ],
                    ];

                    // creating product data
                    $data = [
                        'name' => $primex_prod_name,
                        'categories' => [
                            [
                                'id' => (isset($callBack2->id)) ? $callBack2->id : $key2,
                            ],
                            [
                                'id' => (isset($callBack5->id)) ? $callBack5->id : $key5,
                            ],
                        ],
                        'images' => $updated_images_array,
                        'attributes'  => $attributes_array,
                        'meta_data' => $product_meta_data_array
                    ];

                    try {

                        // trying to update a WC product
                        $update_wc_prod = $woocommerce->put('products/' . strval($key3), $data);

                    }catch (PDOException $e) {

                        $resultHTML .= "Error: " . $e->getMessage();

                    }finally{

                        $wc_retrieved_product = $update_wc_prod;
                        $product_id = $wc_retrieved_product->id;
                        $product_sku = $wc_retrieved_product->sku;

                        $resultHTML .= '<p class="text-center">Product ('.$product_id.') => ('.$product_sku.') => ('.$primex_prod_name.') updated successfully!</p>';

                    }


                // getting all WC product variations
                $wc_all_product_variations = [];
                // initializing
                $page = 1;
                // infinite loop
                while(1 == 1) {
                    // initializing for grabbing all products
                    $data = [
                    'page' => $page,
                    'per_page' => 100,
                    ];
                    try{
                    // getting all WC products
                    $wc_all_product_variations_temp = $woocommerce->get('products/'.strval($wc_product_id).'/variations', $data);
        
                    } catch (PDOException $e) {
        
                    $resultHTML .= "Error: " . $e->getMessage();
            
                    } 
        
                    $wc_all_product_variations = array_merge($wc_all_product_variations, $wc_all_product_variations_temp);
        
                    if( count($wc_all_product_variations_temp) < 100 ){
                    break;
                    }
                    $page++;
                }
                // infinite loop ends here


                // creating WC variations sku array
                $wc_variations_sku_array = [];

                if($wc_all_product_variations){

                    if(count($wc_all_product_variations) != 0){

                    foreach($wc_all_product_variations as $key => $single_variation){

                        $single_variation_id = $single_variation->id;
                        $single_variation_sku = $single_variation->sku;

                        $wc_variations_sku_array[$single_variation_id] = $single_variation_sku;

                    }

                    }

                }



                // initializing
                $variations_all_colors_array = [];
                $variation_image_id = 0;

                foreach($primex_prod_variants as $single_key => $single_variant){
            
                    if($single_variant["Available"] == true || $single_variant["OnSale"] == true){

                        // creating variation name and sku
                        $single_variant_name = $single_variant["Name"];
                        $primex_api_variant_sku = $single_variant["Sku"];
                        // creating variant name
                        if(!str_contains($single_variant_name, $primex_api_variant_sku) ){
                            $single_variant_name = $single_variant_name . ' #' . $primex_api_variant_sku;
                        }

                        // creating attributes array
                        $variation_attributes_array = [];

                        if($single_variant["Color"] != '' ){

                            array_push($variation_attributes_array,[
                            'id' => array_search("Color", $wc_all_attributes_array),
                            'option' => $single_variant["Color"],
                            ]);

                        }

                        if($single_variant["Size"] != '' ){

                            array_push($variation_attributes_array,[
                            'id' => array_search("Size", $wc_all_attributes_array),
                            'option' => $single_variant["Size"],
                            ]);

                        }

                        try {
                            
                            // sending single product API request to Primex
                            $primex_api_variant_stock = $ApiQuery->primex_api_variant_stock($primex_api_variant_sku, $primex_api_base_url, $primex_api_key);

                        } catch (PDOException $e) {
                    
                            $resultHTML .= "Error: " . $e->getMessage();
                    
                        }finally{

                            $primex_api_variant_stock = json_decode($primex_api_variant_stock, true);

                            //initializing
                            $primex_api_stock_quantity = 0;

                            // assigning
                            $PrimexStock = $primex_api_variant_stock["stockItems"][0]["Variants"][0]["PrimexStock"];
                            $SupplierStock = $primex_api_variant_stock["stockItems"][0]["Variants"][0]["SupplierStock"];

                            if($PrimexStock > $SupplierStock){
                            $primex_api_stock_quantity = $PrimexStock;
                            }else{
                            $primex_api_stock_quantity = $SupplierStock;
                            }

                            if($single_variant["Color"] != '' && !in_array($single_variant["Color"], $variations_all_colors_array)){
                            array_push($variations_all_colors_array, $single_variant["Color"]);
                                // increament
                                if (!in_array($variation_image_id + 1, $missed_images_array)){
                                    $variation_image_id++;
                                }
                            }

                            // variant price
                            $variant_price = strval(round((floatval($woocommerce_api_mul_val) * floatval($single_variant["CustomerPrice"])), 2));

                            $variation_meta_data = [
                                [
                                    'key' => 'name',
                                    'value' => $primex_prod_name,
                                ],
                                [
                                    'key' => 'price',
                                    'value' => $variant_price,
                                ],
                                [
                                    'key' => 'description',
                                    'value' => $single_variant_name,
                                ],
                                [
                                    'key' => 'sku',
                                    'value' => strval($primex_api_variant_sku),
                                ],
                                [
                                    'key' => 'category',
                                    'value' => $primex_cat_name,
                                ],
                                [
                                    'key' => 'sub_category',
                                    'value' => $primex_sub_cat_name,
                                ],
                                [
                                    'key' => 'brand',
                                    'value' => $single_variant["Brand"],
                                ],
                                [
                                    'key' => 'color',
                                    'value' => $single_variant["Color"],
                                ],
                                [
                                    'key' => 'size',
                                    'value' => $single_variant["Size"],
                                ],
                                [
                                    'key' => 'weight',
                                    'value' => $single_variant["Weight"],
                                ],
                                [
                                    'key' => 'rgb',
                                    'value' => $single_variant["Rgb"],
                                ],
                                [
                                    'key' => 'hex',
                                    'value' => $single_variant["Hex"],
                                ],
                                [
                                    'key' => 'body_length_width',
                                    'value' => $single_variant["BodyLengthWidth"],
                                ],
                                [
                                    'key' => 'composite',
                                    'value' => $Composite,
                                ],
                                [
                                    'key' => 'country_of_origin',
                                    'value' => $CountryOfOrigin,
                                ],
                                [
                                    'key' => 'genre',
                                    'value' => $Genre,
                                ],
                                [
                                    'key' => 'sleeve_style',
                                    'value' => $SleeveStyle != '' ? $SleeveStyle : 'Fit Sleeve',
                                ],
                                [
                                    'key' => 'version',
                                    'value' => $Version,
                                ],
                                [
                                    'key' => 'fitting',
                                    'value' => $Fitting != '' ? $Fitting : 'Fit',
                                ],
                                [
                                    'key' => 'available_sizes',
                                    'value' => $AvailableSizes,
                                ],
                                [
                                    'key' => 'sub_collection',
                                    'value' => $SubCollection,
                                ],
                                [
                                    'key' => 'product_key',
                                    'value' => $single_variant["ProductKey"],
                                ],
                                [
                                    'key' => 'commodity_code',
                                    'value' => $CommodityCode,
                                ],
                            ];

                            // creating variation data
                            $data = [
                                'regular_price' => $variant_price,
                                'description' => $single_variant_name,
                                'sku' => strval($primex_api_variant_sku),
                                'image' => [
                                    'id' => $wc_retrieved_product->images[$variation_image_id]->id,
                                ],
                                'attributes' => $variation_attributes_array,
                                'manage_stock' => true,
                                'stock_quantity' => $primex_api_stock_quantity,
                                'meta_data' => $variation_meta_data
                            ];

                            // if variation sku exists or not
                        $key4 = array_search($primex_api_variant_sku, $wc_variations_sku_array);

                        if ($key4 !== false) {

                            try {

                            // updating the variant
                            $wc_update_variant = $woocommerce->put('products/'.$wc_product_id.'/variations/' . strval($key4), $data);

                            } catch (PDOException $e) {
                    
                            $resultHTML .= "Error: " . $e->getMessage();
                    
                            }finally{

                            $resultHTML .= '<p class="text-center">Variant '.($single_key + 1).' ('.$single_variant_name.') updated successfully!</p>';

                            }

                        }else{

                            try {
        
                              // creating a variant
                              $wc_update_variant = $woocommerce->post('products/'.$wc_product_id.'/variations', $data);
        
                            } catch (PDOException $e) {
                      
                              $resultHTML .= "Error: " . $e->getMessage();
                      
                            }finally{
        
                              $resultHTML .= '<p class="text-center">Variant '.($single_key + 1).' ('.$single_variant_name.') created successfully!</p>';
        
                            }
        
                        }

                    }

                }

            } //foreach ends here


            // update the wp option for next to update sku

            $key6 = array_search($primex_sku_next_to_update, $primex_cron_list);

            $keys = array_keys($primex_cron_list);

            $key7 =  array_search($key6, $keys);

            $resultHTML .= '<p class="text-center">Product SKU: '.($key7 + 1).' =>  '.$key6.' =>  '.$primex_sku_next_to_update.' updated successfully!</p>';

            if( $key7 == (count($keys) - 1) ){

                $next_item = 0;

                $next_key = $keys[$next_item];

            }else{

                $next_item = $key7 + 1;

                $next_key = $keys[$next_item];

            }

            // if empty
            if($primex_cron_list[$next_key] == ''){
                // remove the empty item 
                unset($primex_cron_list[$next_key]);
                // update option
                update_option('primex_sku_next_to_update', '' );
                $resultHTML .= '<p class="text-center">Next to update product has been emptied!</p>';
            }else{
                // update option
                update_option('primex_sku_next_to_update', $primex_cron_list[$next_key] );
                $resultHTML .= '<p class="text-center">Next to update product SKU: '.($next_item + 1).' => '.$next_key.' =>  '.$primex_cron_list[$next_key].'</p>';
            }



            // if product found ends here
            // if product not found starts here
        }else{

            $key6 = array_search($primex_sku_next_to_update, $primex_cron_list);

            $keys = array_keys($primex_cron_list);

            $key7 =  array_search($key6, $keys);

            $resultHTML .= '<p class="text-center">Product SKU: '.($key7 + 1).' =>  '.$key6.' =>  '.$primex_sku_next_to_update.' could not be found!</p>';

            $resultHTML .= '<p class="text-center">Please Manually import: Product Name  =>  '.$primex_prod_name.' , Product SKU =>  '.$primex_prod_sku.'</p>';

            if( $key7 >= (count($keys) - 1) ){
                $next_item = 0;
                $next_key = $keys[$next_item];
            }else{
                $next_item = $key7 + 1;
                if(array_key_exists($next_item, $keys)){
                    $next_key = $keys[$next_item];
                }else{
                    $next_item = 0;
                    $next_key = $keys[$next_item];
                }
            }

            // update option
            update_option('primex_sku_next_to_update', $primex_cron_list[$next_key] );

            // remove the unfound item 
            unset($primex_cron_list[$key6]);

            // update the option
            update_option('primex_cron_list', $primex_cron_list);

            $resultHTML .= '<p class="text-center">Product SKU: '.($key7 + 1).' =>  '.$key6.' =>  '.$primex_sku_next_to_update.' has been removed from the cron list!</p>';

            // get option value
            $primex_cron_list = get_option('primex_cron_list');

            if(count($primex_cron_list) > 0){
                $resultHTML .= '<p class="text-center">Next to update product SKU: '.($next_item + 1).' => '.$next_key.' =>  '.$primex_cron_list[$next_key].'</p>';
            }else{
                $resultHTML .= '<p class="text-center">Cron List Has Been Emptied!</p>';
            }
        }

}else{

    $resultHTML .= '<p class="text-center">Got no results from Primex!</p>';

}

}
// end of finally

            }else{

                $resultHTML .= '<p class="text-center">No Primex Products To Update!</p>';
    
            }

        }else{

            // clean the option
            update_option('primex_sku_next_to_update', '' );

            $resultHTML .= '<p class="text-center">No Primex Products Found!</p>';

        }

    }else{

        // clean the option
        update_option('primex_sku_next_to_update', '' );

        $resultHTML .= '<p class="text-center">No Primex Products Found!</p>';

    }

}else{

    // clean the option
    update_option('primex_sku_next_to_update', '' );

    $resultHTML .= '<p class="text-center">No Primex Products Found!</p>';

}


// return results
echo $resultHTML;