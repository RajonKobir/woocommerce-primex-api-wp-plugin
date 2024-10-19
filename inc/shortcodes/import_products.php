<?php

//  no direct access 
if( !defined('ABSPATH') ) : exit(); endif;

// shortcode function starts here
function importPrimexProducts($attr) {

    $args = shortcode_atts(array(

        'font_family' => 'Roboto',
        'font_color' => '#000',
        'main_div_padding_top' => '2rem',
        'main_div_padding_bottom' => '10rem',
        'form_name_input_label' => 'Product SKU',
        'form_submit_button_text' => 'Import',
        'form_submit_button_margin_top' => '1rem',
  
    ), $attr);

    // initializing 
    $api_key = '';
    $pageHTML = '';
    $signup_link = '';
    $terms_page_url = '';
    $terms_page_url = '';
    $spinner_image_file_id = '';
    $spinner_image_file_url = '';


    // adding bootstrap
    $pageHTML .= '<link rel="stylesheet" href="' . WOOCOMMERCE_PRIMEX_API_PLUGIN_URL . 'assets/css/bootstrap.min.css">';

    // adding jquery
    $pageHTML .= '<script src="' . WOOCOMMERCE_PRIMEX_API_PLUGIN_URL . 'assets/js/jquery.min.js"></script>';

    // adding bootstrap js
    $pageHTML .= '<script src="' . WOOCOMMERCE_PRIMEX_API_PLUGIN_URL . 'assets/js/bootstrap.bundle.min.js"></script>';
    
    $pageHTML .= '<style>

    .woocommerce_primex_api_main_div{
        font-family: '.$args['font_family'].', sans-serif !important;
        color: '.$args['font_color'].';
        padding-top: '.$args['main_div_padding_top'].';
        padding-bottom: '.$args['main_div_padding_bottom'].';
    }

    .form-check-input:checked {
        background-color: green;
        border-color: green;
    }

    #woocommerce_primex_api_submit_button{
        margin-top: '.$args['form_submit_button_margin_top'].';
    }

    </style>';
  
    $pageHTML .= "<div id='woocommerce_primex_api_main_div' class='woocommerce_primex_api_main_div'>";

    $pageHTML .= "<div id='woocommerce_primex_api_form_div' class='container-fluid woocommerce_primex_api_form_div'>";

    $pageHTML .= "

    <div class='row'>
    <div class='mx-auto col-10 col-md-4 col-lg-4'>

    <form id='woocommerce_primex_api_main_form' onsubmit='return false' class='g-3 needs-validation' novalidate>

    <div class='form-group'>
        <label for='validationCustom01' class='form-label'>".$args['form_name_input_label']."</label>
        <input name='woocommerce_primex_api_product_sku_field' id='woocommerce_primex_api_product_sku_field' minlength='1' maxlength='50' type='text' class='form-control' id='validationCustom01' value='' required>
        <div class='valid-feedback'>
        Perfect!
        </div>
    </div>


    <div class='form-group text-center'>
        <button id='woocommerce_primex_api_submit_button' class='btn btn-success mb-3' type='submit' name='woocommerce_primex_api_submit_button'>".$args['form_submit_button_text']."</button>
    </div>

    </form>";

    $pageHTML .= "</div>";

    $pageHTML .= "<div id='result' class='result text-center'></div>";


    $pageHTML .= '<script>

    $( document ).ready(function() {

        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function () {
        "use strict"
        
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll(".needs-validation")
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {

            form.addEventListener("submit", function (event) {

                if (!form.checkValidity()) {

                event.preventDefault();
                event.stopPropagation();

                }else{

                    event.preventDefault();
                    $("#woocommerce_primex_api_submit_button").attr("disabled", true);
                    // $("input:submit").attr("disabled", true);
                    $("#woocommerce_primex_api_submit_button").html("Importing...");
                    // $("input:submit").val("Importing...");
                    $("#result").html("<h6>Please do not refresh or close this window while importing...</h6>");
                    $("#result h6").addClass("text-center text-danger");
                    let primex_api_product_sku = $( "#woocommerce_primex_api_product_sku_field" ).val();
                    let post_url = "' . WOOCOMMERCE_PRIMEX_API_PLUGIN_URL . 'inc/shortcodes/includes/post.php";
                    let current_url = $(location).attr("href");
                    // alert(current_url);

                    $.ajax({

                        type: "POST",
                        url: post_url,
                        data: {primex_api_product_sku, current_url}, 
                        success: function(result){

                            $("#result").html(result);
                            $("#woocommerce_primex_api_submit_button").attr("disabled", false);
                            // $("input:submit").attr("disabled", false);
                            $("#woocommerce_primex_api_submit_button").html("Submit");
                            // $("input:submit").val("Save Settings");

                        }

                    });

                }
        
                form.classList.add("was-validated");

            }, false)

            })

        })();

    });
    
    </script>';

    $pageHTML .= "</div>";
  
    return $pageHTML;

}
      // end of shortcode function


