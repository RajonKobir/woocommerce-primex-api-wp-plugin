<?php

require_once __DIR__ . '/../../../open-ai/vendor/autoload.php';

use Orhanerday\OpenAi\OpenAi;

//  no direct access 
if( !defined('ABSPATH') ) : exit(); endif;

class PrimexApiQueries
{

    // grabs single product info from primex
    public function primex_api_single_product($primex_api_product_sku, $primex_api_base_url, $primex_customer_id, $primex_api_key, $primex_api_language)
    {

      // initializing
      $result = '';

      try {

        // connecting to the API
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $primex_api_base_url . 'product',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "CustomerId": "'.$primex_customer_id.'",
            "Sku": "'.$primex_api_product_sku.'",
            "GetCustomerPrice": 1,
            "Languages": "'.$primex_api_language.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'ApiKey: ' . $primex_api_key,
            'Type: JSON',
            'Content-Type: application/json'
          ),
        ));
        
        $result = curl_exec($curl);

        if (curl_errno ( $curl )) {
          $result = 'Curl error: ' . curl_error ( $curl );
        }
        
        curl_close($curl);

      } catch (PDOException $e) {

          $result = "Error: " . $e->getMessage();

      }

      return $result;

    }


    // grabs single variation stock info
    public function primex_api_variant_stock($primex_api_variant_sku, $primex_api_base_url, $primex_api_key)
    {

      // initializing
      $result = '';

      try {

        // connecting to the API
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $primex_api_base_url . 'stock',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "VariantSkus":"'.$primex_api_variant_sku.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'ApiKey: ' . $primex_api_key,
            'Type: JSON',
            'Content-Type: application/json'
          ),
        ));
        
        $result = curl_exec($curl);

        if (curl_errno ( $curl )) {
          $result = 'Curl error: ' . curl_error ( $curl );
        }
        
        curl_close($curl);

      } catch (PDOException $e) {

          $result = "Error: " . $e->getMessage();

      }

      return $result;

    }


    // creating a order on primex
    public function primex_api_create_order( $primex_api_base_url, $primex_customer_id, $primex_api_key, $ProductLines, $Company, $Name, $Address, $PostalCode, $Locality, $Reference )
    {

      // initializing
      $result = '';

      try {

        // connecting to the API
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $primex_api_base_url . 'order',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "CustomerId": "'.$primex_customer_id.'",
            "ProductLines": '.$ProductLines.',
            "DeliveryAddress": {
            "Company": "'.$Company.'",
            "Name": "'.$Name.'",
            "Address": "'.$Address.'",
            "PostalCode": "'.$PostalCode.'",
            "Locality": "'.$Locality.'"
            },
            "Reference": "'.$Reference.'"
        }',
        CURLOPT_HTTPHEADER => array(
          'ApiKey: ' . $primex_api_key,
          'Type: JSON',
          'Content-Type: application/json'
        ),

        ));
        
        $result = curl_exec($curl);

        if (curl_errno ( $curl )) {
          $result = 'Curl error: ' . curl_error ( $curl );
        }
        
        curl_close($curl);

      } catch (PDOException $e) {

          $result = "Error: " . $e->getMessage();

      }

      return $result;

    }


    // creating a request to OpenAI
    public function open_ai_request_response( $open_ai_api_key, $open_ai_model, $open_ai_prompt, $open_ai_temperature, $open_ai_max_tokens, $open_ai_frequency_penalty, $open_ai_presence_penalty ){

      $result = '';

      if(!$open_ai_model || $open_ai_model == ''){
        $open_ai_model = 'text-davinci-003';
      }
      if(!$open_ai_temperature || $open_ai_temperature == ''){
        $open_ai_temperature = 0.9;
      }
      if(!$open_ai_max_tokens || $open_ai_max_tokens == ''){
        $open_ai_max_tokens = 500;
      }
      if(!$open_ai_frequency_penalty || $open_ai_frequency_penalty == ''){
        $open_ai_frequency_penalty = 0;
      }
      if(!$open_ai_presence_penalty || $open_ai_presence_penalty == ''){
        $open_ai_presence_penalty = 0.6;
      }

      try {

        $open_ai = new OpenAi($open_ai_api_key);

        $response = $open_ai->completion([
            'model' => $open_ai_model,
            'prompt' => $open_ai_prompt,
            'temperature' => $open_ai_temperature,
            'max_tokens' => $open_ai_max_tokens,
            'frequency_penalty' => $open_ai_frequency_penalty,
            'presence_penalty' => $open_ai_presence_penalty,
        ]);

    } catch (PDOException $e) {

        $response = "Error: " . $e->getMessage();

    }finally{

      $response = json_decode($response, true);

      // if no error
      if(!isset($response['error'])){
        if(isset($response["choices"][0]["text"])){
          $result = $response["choices"][0]["text"];
        }
      }

    }

      return $result;

    }
    // end of public function open_ai_request_response 


}