<?php

// requiring WC Rest API SDK
require_once __DIR__ . '/../../../wc-api-php-trunk/vendor/autoload.php';
use Automattic\WooCommerce\Client;


// if posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // if posted certain values
  if( isset($_POST["primex_api_product_sku"]) && isset($_POST["current_url"]) ){

    // to get the options values
    require_once '../../../../../../wp-config.php';

    // assigning
    $primex_api_product_sku = primex_secure_input($_POST["primex_api_product_sku"]);
    $current_url = primex_secure_input($_POST["current_url"]);

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
    $open_ai_api_key = '';
    $open_ai_model = '';
    $open_ai_temperature = '';
    $open_ai_max_tokens = '';
    $open_ai_frequency_penalty = '';
    $open_ai_presence_penalty = '';
    $custom_tags_on_off = '';
    $open_ai_on_off = '';

    $variant_price = 0;

    $resultHTML = '';

    // assigning values got from wp options
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_website_url')){
      $website_url = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_website_url');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_key')){
      $woocommerce_api_consumer_key = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_key');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_secret')){
      $woocommerce_api_consumer_secret = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_secret');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_mul_val')){
      $woocommerce_api_mul_val = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_mul_val');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_base_url')){
      $primex_api_base_url = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_base_url');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_customer_id')){
      $primex_customer_id = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_customer_id');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_key')){
      $primex_api_key = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_key');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_language')){
      $primex_api_language = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_language');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_wc_prod_tags')){
      $wc_prod_tags = primex_secure_input(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_wc_prod_tags'));
    }


    // open ai option values
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_api_key')){
      $open_ai_api_key = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_api_key');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_model')){
      $open_ai_model = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_model');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_temperature')){
      $open_ai_temperature = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_temperature');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_max_tokens')){
      $open_ai_max_tokens = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_max_tokens');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_frequency_penalty')){
      $open_ai_frequency_penalty = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_frequency_penalty');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_presence_penalty')){
      $open_ai_presence_penalty = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_presence_penalty');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_custom_tags_on_off')){
      $custom_tags_on_off = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_custom_tags_on_off');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_on_off')){
      $open_ai_on_off = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_on_off');
    }


    // assigning language
    $language_full_name = "";
    switch ($primex_api_language) {
      case "nl-NL":
        $language_full_name = "Dutch";
        break;
      case "en-GB":
        $language_full_name = "English";
        break;
      case "de-DE":
        $language_full_name = "German";
        break;
    }



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
    require_once('PrimexApiQueries.php');

    // instantiating
    $ApiQuery = new PrimexApiQueries;

    try {

      // sending single product API request to Primex
      $primex_api_single_product = $ApiQuery->primex_api_single_product($primex_api_product_sku, $primex_api_base_url, $primex_customer_id, $primex_api_key, $primex_api_language);

      } catch (PDOException $e) {

        $resultHTML .= "Error: " . $e->getMessage();

      }finally{

        // assigning some useful values got from Primex API response
        $primex_api_single_product = json_decode($primex_api_single_product, true);

      }
      // try catch ends here 

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

                $single_variant_name = $single_variant["Name"];
                $primex_api_variant_sku = $single_variant["Sku"];
                $single_variant_name = $single_variant["Name"] . ' #' . $primex_api_variant_sku;

                if($single_variant["Color"] != '' && !in_array($single_variant["Color"], $primex_all_colors_array)){

                  array_push($primex_all_colors_array, $single_variant["Color"]);

                  // creating product images array
                  if(isset($single_variant["Images"]["Items"])){

                    // initializing
                    $temp_array = [];
                    
                    // if product images
                    if( count( $single_variant["Images"]["Items"] ) > 0 ){
                      $temp_array = [
                        'src' => $single_variant["Images"]["Items"][0],
                        'name' => $single_variant_name,
                        'alt' => $single_variant_name,
                      ];

                    // if no product images
                    }else{
                      $temp_array = [
                        'src' => $primex_prod_img,
                        'name' => $primex_prod_name,
                        'alt' => $primex_prod_name,
                      ];
                    }

                    array_push($primex_all_image_src_array,  $temp_array);

                  }


                }

                if($single_variant["Size"] != '' && !in_array($single_variant["Size"], $primex_all_sizes_array)){
                  array_push($primex_all_sizes_array, $single_variant["Size"]);
                }

              }

            }
          }
          // end of foreach



// //  open AI starts here 

if($open_ai_api_key && $open_ai_api_key != ''){
  if( $open_ai_on_off == 'yes' ){
    $resultHTML .= '<p class="text-center">OpenAI API has been started...</p>';
    // creating better description
    $open_ai_prompt = 'create a better product description in '.$language_full_name.' from this description &bdquo;'.$primex_prod_desc.'&bdquo;';
    try {
      // sending request to openAI
      $open_ai_request_response = $ApiQuery->open_ai_request_response( $open_ai_api_key, $open_ai_model, $open_ai_prompt, $open_ai_temperature, $open_ai_max_tokens, $open_ai_frequency_penalty, $open_ai_presence_penalty );
    } catch (PDOException $e) {
      $resultHTML .= "Error: " . $e->getMessage();
    }finally{
      $open_ai_request_response = json_decode($open_ai_request_response, true);
      if(isset($open_ai_request_response['error'])){
        $resultHTML .= '<p class="text-center">OpenAI API could not update the product description...</p>';
        $resultHTML .= '<p class="text-center">OpenAI API Error: '.$open_ai_request_response['error']["type"].' - '.$open_ai_request_response['error']["message"].'</p>';
      }else{
        if(isset($open_ai_request_response["choices"][0]["text"])){
          $resultText = $open_ai_request_response["choices"][0]["text"];
          $primex_prod_desc = str_replace('"', '', $resultText);
          $resultHTML .= '<p class="text-center">OpenAI API has updated the product description...</p>';
        }else{
          $resultHTML .= '<p class="text-center">Unknown OpenAI API Error occured on updating the product description. Please contact the developer.</p>';
        }
      }
    }

    // creating better short-description
    $open_ai_prompt = 'create a better product short description in '.$language_full_name.' from this short description &bdquo;'.$primex_prod_short_desc.'&bdquo;';
    try {
      // sending request to openAI
      $open_ai_request_response = $ApiQuery->open_ai_request_response( $open_ai_api_key, $open_ai_model, $open_ai_prompt, $open_ai_temperature, $open_ai_max_tokens, $open_ai_frequency_penalty, $open_ai_presence_penalty );
    } catch (PDOException $e) {
      $resultHTML .= "Error: " . $e->getMessage();
    }finally{
      $open_ai_request_response = json_decode($open_ai_request_response, true);
      if(isset($open_ai_request_response['error'])){
        $resultHTML .= '<p class="text-center">OpenAI API could not update the product short-description...</p>';
        $resultHTML .= '<p class="text-center">OpenAI API Error: '.$open_ai_request_response['error']["type"].' - '.$open_ai_request_response['error']["message"].'</p>';
      }else{
        if(isset($open_ai_request_response["choices"][0]["text"])){
          $resultText = $open_ai_request_response["choices"][0]["text"];
          $primex_prod_short_desc = str_replace('"', '', $resultText);
          $resultHTML .= '<p class="text-center">OpenAI API has updated the product short-description...</p>';
        }else{
          $resultHTML .= '<p class="text-center">Unknown OpenAI API Error occured on updating the product short-description. Please contact the developer.</p>';
        }
      }
    }


    if( $custom_tags_on_off != 'yes' ){
      $open_ai_prompt = 'create comma separated string of product tags from this description in '.$language_full_name.' &bdquo;'.$primex_prod_desc.'&bdquo;';
      try {
        // sending request to openAI
        $open_ai_request_response = $ApiQuery->open_ai_request_response( $open_ai_api_key, $open_ai_model, $open_ai_prompt, $open_ai_temperature, $open_ai_max_tokens, $open_ai_frequency_penalty, $open_ai_presence_penalty );
      } catch (PDOException $e) {
        $resultHTML .= "Error: " . $e->getMessage();
      }finally{
        $open_ai_request_response = json_decode($open_ai_request_response, true);
        if(isset($open_ai_request_response['error'])){
          $resultHTML .= '<p class="text-center">OpenAI API could not update the product tags...</p>';
          $resultHTML .= '<p class="text-center">OpenAI API Error: '.$open_ai_request_response['error']["type"].' - '.$open_ai_request_response['error']["message"].'</p>';
        }else{
          if(isset($open_ai_request_response["choices"][0]["text"])){
            $resultText = $open_ai_request_response["choices"][0]["text"];
            $wc_prod_tags = primex_secure_input($resultText);
            $resultHTML .= '<p class="text-center">OpenAI API has updated the product tags...</p>';
          }else{
            $resultHTML .= '<p class="text-center">Unknown OpenAI API Error occured on updating the product tags. Please contact the developer.</p>';
          }
        }
      }
    }
  }
}else{
  $resultHTML .= '<p class="text-center">OpenAI API Key is missing. Started Default Import...</p>';
}



// //  open AI ends here 




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

                      $resultHTML .= '<p class="text-center">Attribute '.($key + 1).' ('.$single_attribute.') created successfully!</p>';

                    }
                    
                  }else{
                    $resultHTML .= '<p class="text-center">Attribute '.($key + 1).' ('.$single_attribute.') already exists!</p>';
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

                  $resultHTML .= '<p class="text-center">Attribute '.($key + 1).' ('.$single_attribute.') created successfully!</p>';

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



              // getting all WC product tags
              $retrieved_all_tags = [];
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
                  // getting all WC products
                  $retrieved_all_tags_temp = $woocommerce->get('products/tags', $data);
    
                } catch (PDOException $e) {
    
                  $resultHTML .= "Error: " . $e->getMessage();
          
                } 
    
                $retrieved_all_tags = array_merge($retrieved_all_tags, $retrieved_all_tags_temp);
    
                if( count($retrieved_all_tags_temp) < 100 ){
                  break;
                }
                $page++;
              }
              // infinite loop ends here


              // creating all tags array
              $tag_names_array = [];

              if($retrieved_all_tags){

                if(count($retrieved_all_tags) != 0){

                  foreach($retrieved_all_tags as $key => $single_tag){

                    $tag_names_array[$single_tag->id] = primex_secure_input($single_tag->name);

                  }

                }

              }

              // creating tags array
              $tags_array = [];
              $final_tag_names_array = [];
              
              // creating the tag if not exists
              if($wc_prod_tags != ''){

                $wc_prod_tags = explode(',' , $wc_prod_tags);

                if(count($wc_prod_tags) > 0){

                  foreach($wc_prod_tags as $key => $single_tag){

                    $single_tag = primex_secure_input($single_tag);

                    $tag_key = array_search($single_tag, $tag_names_array);

                    if ($tag_key !== false) {

                      array_push($tags_array,[
                        'id' => $tag_key,
                      ]);
                      array_push($final_tag_names_array, $single_tag);

                      $resultHTML .= '<p class="text-center">Tag '.($key + 1).' ('.$single_tag.') already exists!</p>';

                    }else{

                      $data = [
                          'name' => $single_tag
                      ];

                      try {

                        $wc_create_tag = $woocommerce->post('products/tags', $data);

                      }catch (PDOException $e) {

                        $resultHTML .= "Error: " . $e->getMessage();

                      }finally{

                        array_push($tags_array,[
                          'id' => $wc_create_tag->id,
                        ]);
                        array_push($final_tag_names_array, $wc_create_tag->name);

                        $resultHTML .= '<p class="text-center">Tag '.($key + 1).' ('.$single_tag.') created successfully!</p>';

                      }

                    }

                  }

                }

              }
          // creating the tags ends here


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
            // creating attributes array ends here


            // creating product's meta data
            $product_meta_data_array = [
              [
                  'key' => 'name',
                  'value' => $primex_prod_name,
              ],
              [
                  'key' => 'description',
                  'value' => $primex_prod_desc,
              ],
              [
                  'key' => 'short_description',
                  'value' => $primex_prod_short_desc,
              ],
              [
                  'key' => 'sku',
                  'value' => strval($primex_prod_sku),
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
                  'key' => 'tags',
                  'value' => implode(",", $final_tag_names_array),
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
                      $image_id = woocommerce_primex_api_custom_image_file_upload( $primex_all_image_src_array[$i]['src'], $primex_all_image_src_array[$i]['name'], $wc_product_id );
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

              // creating product data
              $data = [
                'name' => $primex_prod_name,
                'type' => 'variable',
                'description' => $primex_prod_desc,
                'short_description' => $primex_prod_short_desc,
                'sku' => strval($primex_prod_sku),
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
                'tags'  => $tags_array,
                'meta_data' =>  $product_meta_data_array
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

            }else{

              // creating product data
              $data = [
                'name' => $primex_prod_name,
                'type' => 'variable',
                'description' => $primex_prod_desc,
                'short_description' => $primex_prod_short_desc,
                'sku' => strval($primex_prod_sku),
                'categories' => [
                    [
                        'id' => (isset($callBack2->id)) ? $callBack2->id : $key2,
                    ],
                    [
                        'id' => (isset($callBack5->id)) ? $callBack5->id : $key5,
                    ],
                ],
                'attributes'  => $attributes_array,
                'tags'  => $tags_array,
                'meta_data' =>  $product_meta_data_array
              ];

              try {

                // trying to create a WC product
                $create_wc_prod = $woocommerce->post('products', $data);

              }catch (PDOException $e) {

                $resultHTML .= "Error: " . $e->getMessage();

              }finally{

                // get the correct product id
                $wc_retrieved_product = $create_wc_prod;
                $wc_product_id = $wc_retrieved_product->id;

                //create or update wp-option includes list of product sku for cron 
                $product_id = $wc_retrieved_product->id;
                $product_sku = $wc_retrieved_product->sku;


                if( $product_id != '' && $product_sku != ''){

                  $resultHTML .= '<p class="text-center">Product ('.$product_id.') => ('.$product_sku.') => ('.$primex_prod_name.') created successfully!</p>';

                  $primex_cron_list = get_option('primex_cron_list');

                  if ( !in_array($product_sku, $primex_cron_list) ){

                    $primex_cron_list[$product_id] = $product_sku;

                    update_option('primex_cron_list', $primex_cron_list);
    
                    $resultHTML .= '<p class="text-center">Product '.$product_id.' => '.$product_sku.' => ('.$primex_prod_name.') has been inserted to the cron list successfully!</p>';

                  }

                  $primex_products_sku_list = get_option('primex_products_sku_list');

                  if ( !in_array($product_sku, $primex_products_sku_list) ){

                    $primex_products_sku_list[$product_id] = $product_sku;

                    update_option('primex_products_sku_list', $primex_products_sku_list);
    
                    $resultHTML .= '<p class="text-center">Product '.$product_id.' => '.$product_sku.' => ('.$primex_prod_name.') has been inserted to the all primex products list successfully!</p>';

                  }

                  $primex_sku_next_to_update = get_option('primex_sku_next_to_update');

                  if($primex_sku_next_to_update == ''){

                    update_option('primex_sku_next_to_update', $product_sku );

                    $resultHTML .= '<p class="text-center">Next to update option was empty</p>';
                    $resultHTML .= '<p class="text-center">Product '.$product_id.' => '.$product_sku.' => ('.$primex_prod_name.') has been inserted to the next to update cron successfully!</p>';

                  }

                }else{
                  $resultHTML .= '<p class="text-center">Product ('.$primex_prod_name.') could not be imported!</p>';
                }
              // product not created ends here




              // get the correct product id
              try {
                // retrieving the product
                $wc_retrieved_product = $woocommerce->get('products/' . strval($wc_product_id));
              }catch (PDOException $e) {
                $resultHTML .= "Error: " . $e->getMessage();
              }finally{
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
                        $image_id = woocommerce_primex_api_custom_image_file_upload( $primex_all_image_src_array[$i]['src'], $primex_all_image_src_array[$i]['name'], $wc_product_id );
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
  
  
                // creating product data
                $data = [
                  'images' => $updated_images_array
                ];
              }


              try {

                // trying to update a WC product
                $update_wc_prod = $woocommerce->put('products/' . strval($product_id), $data);

              }catch (PDOException $e) {

                $resultHTML .= "Error: " . $e->getMessage();

              }finally{

                // get the correct product id
                $wc_retrieved_product = $update_wc_prod;
                $wc_product_id = $wc_retrieved_product->id;

                //create or update wp-option includes list of product sku for cron 
                $product_id = $wc_retrieved_product->id;
                $product_sku = $wc_retrieved_product->sku;

                $resultHTML .= '<p class="text-center">Product ('.$product_id.') => ('.$product_sku.') => ('.$primex_prod_name.') images inserted successfully!</p>';

              }


            }

          }
          // end of if-else
          // creating product ends here


          // if product exists
          if ($wc_product_id != ''){
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
                    if(isset($single_variant["CustomerPrice"])){
                      $variant_price = round((floatval($woocommerce_api_mul_val) * floatval($single_variant["CustomerPrice"])), 2);
                    }

                    // creating variation data
                    $variation_meta_data = [
                      [
                          'key' => 'name',
                          'value' => $primex_prod_name,
                      ],
                      [
                          'key' => 'price',
                          'value' => strval($variant_price),
                      ],
                      [
                          'key' => 'description',
                          'value' => $single_variant_name,
                      ],
                      [
                          'key' => 'short_description',
                          'value' => $primex_prod_short_desc,
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
                          'key' => 'tags',
                          'value' => implode(",", $final_tag_names_array),
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
                      'regular_price' => strval($variant_price),
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

                      // updating a variant
                      $wc_create_or_update_variant = $woocommerce->put('products/'.$wc_product_id.'/variations/' . strval($key4), $data);

                    } catch (PDOException $e) {
              
                      $resultHTML .= "Error: " . $e->getMessage();
              
                    }finally{

                      $resultHTML .= '<p class="text-center">Variant '.($single_key + 1).' ('.$single_variant_name.') updated successfully!</p>';

                    }


                  }else{

                    try {

                      // creating a variant
                      $wc_create_or_update_variant = $woocommerce->post('products/'.$wc_product_id.'/variations', $data);

                    } catch (PDOException $e) {
              
                      $resultHTML .= "Error: " . $e->getMessage();
              
                    }finally{

                      $resultHTML .= '<p class="text-center">Variant '.($single_key + 1).' ('.$single_variant_name.') created successfully!</p>';

                    }

                  }

                }

              }

            }
            // end of foreach

    }else{
      $resultHTML .= '<p class="text-center">Got no product for importing the variations!</p>';
    }

    }else{
      $resultHTML .= '<p class="text-center">Got no results from Primex!</p>';
    }


  echo $resultHTML;


  }  // if posted certain values ends



  // if posted certain values
  if( isset($_POST["primex_api_page_number"]) && isset($_POST["primex_api_items_per_page"]) && isset($_POST["primex_filter_brands"]) ){

    // to get the options values
    require_once '../../../../../../wp-config.php';

    // assigning
    $primex_api_page_number = primex_secure_input($_POST["primex_api_page_number"]);
    $primex_api_items_per_page = primex_secure_input($_POST["primex_api_items_per_page"]);
    $primex_filter_brands = $_POST["primex_filter_brands"];

    // php text to array 
    $primex_filter_brands_array = [];
    if( trim($primex_filter_brands) != '' ){
      $primex_filter_brands_array = array_map('trim', explode(',', $primex_filter_brands));
    }


    if( $primex_api_items_per_page == '' || !is_numeric($primex_api_items_per_page)){
      $primex_api_items_per_page = 200;
    }
    if( $primex_api_items_per_page != '' ){
      if( $primex_api_items_per_page < 1 || $primex_api_items_per_page > 200 ){
        $primex_api_items_per_page = 200;
      }
    }

    // initializing
    $website_url = '';
    $woocommerce_api_consumer_key = '';
    $woocommerce_api_consumer_secret = '';
    $woocommerce_api_mul_val = 1;
    $primex_api_base_url = '';
    $primex_customer_id = '';
    $primex_api_key = '';
    $primex_api_language = '';

    $resultHTML = '';

    // assigning values got from wp options
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_website_url')){
      $website_url = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_website_url');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_key')){
      $woocommerce_api_consumer_key = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_key');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_secret')){
      $woocommerce_api_consumer_secret = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_secret');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_mul_val')){
      $woocommerce_api_mul_val = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_mul_val');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_base_url')){
      $primex_api_base_url = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_base_url');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_customer_id')){
      $primex_customer_id = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_customer_id');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_key')){
      $primex_api_key = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_key');
    }
    if(get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_language')){
      $primex_api_language = get_option( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_language');
    }

    // assigning language
    $language_full_name = "";
    switch ($primex_api_language) {
      case "nl-NL":
        $language_full_name = "Dutch";
        break;
      case "en-GB":
        $language_full_name = "English";
        break;
      case "de-DE":
        $language_full_name = "German";
        break;
    }

    // Primex API Queries
    require_once('PrimexApiQueries.php');

    // instantiating
    $ApiQuery = new PrimexApiQueries;

    try {

      // sending single product API request to Primex
      $primex_api_sku_list = $ApiQuery->primex_api_sku_list($primex_api_page_number, $primex_api_items_per_page, $primex_api_base_url, $primex_customer_id, $primex_api_key, $primex_api_language);

      } catch (PDOException $e) {

        $resultHTML .= "Error: " . $e->getMessage();

      }finally{

        // assigning some useful values got from Primex API response
        $primex_api_sku_list = json_decode($primex_api_sku_list, true);

      }
      // try catch ends here 
      $primex_all_sku_array = [];
      $primex_all_brands_array = [];
      foreach( $primex_api_sku_list["Master"] as $single_sku_key => $single_sku_value ){
        if(isset($single_sku_value["Sku"])){
          array_push($primex_all_sku_array, $single_sku_value["Sku"]);
          array_push($primex_all_brands_array, $single_sku_value["Brand"]);
        }
      }

    // get all available products in WC
    $primex_products_sku_list = get_option('primex_products_sku_list');
    if(count($primex_all_sku_array) > 0){
      $resultHTML .= '<p class="text-center">Click any items below to import: (Green ones are already imported!)</p>';
      $resultHTML .= '<style>
          .primex_clickable_catalog{
              cursor: pointer;
          }
          .primex_catalog{
              margin-right: 10px;
              display: inline-block;
          }
          .primex_catalog:last-child{
              margin-right: 0;
          }
      </style>';
      $total_iteration = 0;
      $filtered_sku_array = [];
      foreach( $primex_all_sku_array as $primex_single_sku_key => $primex_single_sku_value ){
        if( count($primex_filter_brands_array) > 0 ){
          if (!in_array($primex_all_brands_array[$primex_single_sku_key], $primex_filter_brands_array)){
            continue;
          }
        }
        if (in_array($primex_single_sku_value, $primex_products_sku_list)){
          $resultHTML .= '<span class="text-success primex_catalog">'.$primex_single_sku_value.' ('.$primex_all_brands_array[$primex_single_sku_key].')</span>';
          array_push($filtered_sku_array, $primex_single_sku_value);
          $total_iteration++;
        }else{
          $resultHTML .= '<span class="text-danger primex_catalog primex_clickable_catalog"  data-value="'.$primex_single_sku_value.'">'.$primex_single_sku_value.' ('.$primex_all_brands_array[$primex_single_sku_key].')</span>';
          array_push($filtered_sku_array, $primex_single_sku_value);
          $total_iteration++;
        }
        
      }

      // import all button 
      $resultHTML .= '<br><br>';
      $resultHTML .= '<p class="text-center">Showing total '.$total_iteration.' Products.</p>';
      $resultHTML .= '<br>';
      $resultHTML .= '<button type="submit" id="woocommerce_primex_api_import_all_button" class="woocommerce_primex_api_import_all_button btn btn-warning mb-3" name="woocommerce_primex_api_import_all_button">Import All</button>';

      $resultHTML .= '
      <script>

        $(".primex_clickable_catalog").click(function(){
          $("#woocommerce_primex_api_product_sku_field").val($(this).data("value"));
        });

        $(".woocommerce_primex_api_import_all_button").click(async function(event){
          event.preventDefault();
          let filtered_sku_array = '.json_encode($filtered_sku_array).';
          let primex_api_product_sku;
          let post_url = "' . WOOCOMMERCE_PRIMEX_API_PLUGIN_URL . 'inc/shortcodes/includes/post.php";
          let current_url = $(location).attr("href");
          let primex_api_sku_list_button_text = $("#primex_api_sku_list").html();
          let woocommerce_primex_api_submit_button_text = $("#woocommerce_primex_api_submit_button").html();
          $("#primex_api_sku_list").attr("disabled", true);
          $("#primex_api_sku_list").html("Importing...");
          $("#woocommerce_primex_api_submit_button").attr("disabled", true);
          $("#woocommerce_primex_api_submit_button").html("Importing...");
          $("#result").html("<h6>Please do not refresh or close this window while importing...</h6>");
          $("#result h6").addClass("text-center text-danger");
          
          for (let index = 0; index < filtered_sku_array.length; index++) {
            primex_api_product_sku = filtered_sku_array[index];
            await $.ajax({
                type: "POST",
                url: post_url,
                data: {primex_api_product_sku, current_url}, 
                success: function(result){
                    $("#result").html(result);
                    if(index < filtered_sku_array.length){
                      $("#primex_api_sku_list").attr("disabled", true);
                      $("#primex_api_sku_list").html("Importing...");
                      $("#woocommerce_primex_api_submit_button").attr("disabled", true);
                      $("#woocommerce_primex_api_submit_button").html("Importing...");
                      $("#result").prepend("<h6>"+(index+1)+" Products Have been Imported or Updated So Far...</h6>");
                      $("#result").prepend("<h6>Please do not refresh or close this window while importing...</h6>");
                      $("#result h6").addClass("text-center text-danger");
                    }
                }
            });
          }

          $("#result").prepend("<h6>All The Selected "+filtered_sku_array.length+" Products Have Been Successfully Imported or Updated!</h6>");
          $("#result h6").addClass("text-center text-success");
          $("#primex_api_sku_list").attr("disabled", false);
          $("#primex_api_sku_list").html(primex_api_sku_list_button_text);
          $("#woocommerce_primex_api_submit_button").attr("disabled", false);
          $("#woocommerce_primex_api_submit_button").html(woocommerce_primex_api_submit_button_text);

        });
      </script>
      ';
    }

    echo $resultHTML;

  }
  // if posted certain values ends





}   // if posted ends

?>