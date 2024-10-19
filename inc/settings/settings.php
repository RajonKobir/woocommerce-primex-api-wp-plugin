<?php 

//  no direct access 
if( !defined('ABSPATH') ) : exit(); endif;


// Create Settings Menu Page Item 
add_action('admin_menu', 'woocommerce_primex_api_plugin_settings_menu');
function woocommerce_primex_api_plugin_settings_menu() {

    add_menu_page(
        __( 'Woocommerce Primex API Settings', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        __( 'Woocommerce Primex API Settings', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'manage_options',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        'woocommerce_primex_api_plugin_settings_template_callback',
        'dashicons-rest-api',
        10
    );

    add_submenu_page(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        __( 'Import Primex Products To WooCommerce', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        __( 'Import Primex Products To WooCommerce', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'manage_options',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_import_page',
        'woocommerce_primex_api_import_template_callback',
    );

    add_submenu_page(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        __( 'Woocommerce Primex API Cron Status', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        __( 'Woocommerce Primex API Cron Status', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'manage_options',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_cron_page',
        'woocommerce_primex_api_cron_template_callback',
    );

}


// Settings Template Page 
function woocommerce_primex_api_plugin_settings_template_callback() {
    
    // adding bootstrap css
    echo '<link rel="stylesheet" href="' . WOOCOMMERCE_PRIMEX_API_PLUGIN_URL . 'assets/css/bootstrap.min.css">';

    ?>

    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <div class="row">
            <form action="options.php" method="post">

                <?php 
                    // security field
                    settings_fields( WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page' );

                    // save settings button 
                    submit_button( 'Save Settings' );

                    // output settings section here
                    do_settings_sections(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page');

                    ?>
                    <h6 class="text-center">
                        Star (*) marked ones are required
                    </h6>
                    <div class="text-right" style="text-align: right;">
                    <?php 
                        // save settings button
                        submit_button( 'Save Settings', 'primary', '', false );
                        ?>
                    </div>
            </form>
        </div>


        <div class="row my-5">

            <div class="col-md-6">
                <h3 class="text-center">
                    Indications On The Cron: 
                </h3>
                <h6 class="text-primary text-center">
                    How to enable the WordPress cron?
                    <br>
                    To enable the WordPress cron job, open your wp-config.php file and locate the line:
                    <br>
                    define('DB_COLLATE', '');
                    <br>
                    Under it, add the following line:
                    <br>
                    define('DISABLE_WP_CRON', false);
                </h6>
                <h6 class="text-primary text-center">
                    Either you can turn the above WP Cron on.
                    <br>
                    Or can add this following path to your hosted server's Cron:
                    <br>
                    <?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . 'cron.php'; ?>
                </h6>
            </div>

            <div class="col-md-6">
                <h3 class="text-center">Note:</h3>
                <h6 class="text-primary text-center">
                    You can modify any external WordPress website from here.
                    <br>
                    Just need to put website URL and WC Credentials correctly for that website.
                </h6>
            </div>

        </div>

    </div>

    <?php 

}


//  Settings Template 
add_action( 'admin_init', 'woocommerce_primex_api_settings_init' );
function woocommerce_primex_api_settings_init() {

    // Setup settings section 1
    add_settings_section(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section1',
        'Woocommerce API Credentials',
        '',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        array(
            'before_section' => '<div class="row"><div class="col-md-6"><div>',
            'after_section'  => '</div>',
        )
    );

    // Setup settings section 2
    add_settings_section(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section2',
        'Other Woocommerce Settings',
        '',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        array(
            'before_section' => '<div>',
            'after_section'  => '</div></div>',
        )
    );

    // Setup settings section 3
    add_settings_section(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section3',
        'Primex API Credentials',
        '',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        array(
            'before_section' => '<div class="col-md-6"><div>',
            'after_section'  => '</div>',
        )
    );

    // Setup settings section 4
    add_settings_section(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section4',
        'OpenAI API Credentials',
        '',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        array(
            'before_section' => '<div>',
            'after_section'  => '</div></div></div>',
        )
    );


// section 1 starts here

    // Register field
    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_website_url',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add text fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_website_url',
        __( 'Website URL*', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_website_url_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section1'
    );

    // Register radio field
    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_key',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add text fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_key',
        __( 'Woocommerce API Consumer Key*', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_woocommerce_api_consumer_key_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section1'
    );


    // Register text field
    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_secret',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add text fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_secret',
        __( 'Woocommerce API Consumer Secret*', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_woocommerce_api_consumer_secret_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section1'
    );


// section 1 ends here


// section 2 starts here 

    // Register text field
    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_mul_val',
        array(
            'type' => 'number',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add text fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_mul_val',
        __( 'Woocommerce Price Multiplied By', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_woocommerce_api_mul_val_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section2'
    );


    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_wc_prod_tags',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => ''
        )
    );

    // Add text fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_wc_prod_tags',
        __( 'WooCommerce Product Tags', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_wc_prod_tags_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section2'
    );


    // Register checkbox field
    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_custom_tags_on_off',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_key',
            'default' => ''
        )
    );

    // Add checkbox fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_custom_tags_on_off',
        __( 'Force to use these tags:', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_custom_tags_on_off_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section2'
    );


    // Register checkbox field
    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_on_off',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_key',
            'default' => ''
        )
    );

    // Add checkbox fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_on_off',
        __( 'Turn OpenAI on/off:', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_open_ai_on_off_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section2'
    );


    // Register checkbox field
    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_cron_on_off',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_key',
            'default' => ''
        )
    );

    // Add checkbox fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_cron_on_off',
        __( 'Turn The Wordpress Cron On/Off:', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_cron_on_off_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section2'
    );

// section 2 ends here 


// section 3 starts here 

    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_base_url',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add text fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_base_url',
        __( 'Primex API Base URL*', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_primex_api_base_url_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section3'
    );


    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_customer_id',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add text fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_customer_id',
        __( 'Primex Customer ID*', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_primex_customer_id_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section3'
    );


    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_key',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add text fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_key',
        __( 'Primex API Key*', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_primex_api_key_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section3'
    );


    register_setting(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_language',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add text fields
    add_settings_field(
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_language',
        __( 'Primex API Language*', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
        'woocommerce_primex_api_primex_api_language_callback',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
        WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section3'
    );

// section 3 ends here


// section 4 starts here

register_setting(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_api_key',
    array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    )
);

// Add text fields
add_settings_field(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_api_key',
    __( 'OpenAI API Key*', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
    'woocommerce_primex_api_open_ai_api_key_callback',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section4'
);


register_setting(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_model',
    array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    )
);

// Add text fields
add_settings_field(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_model',
    __( 'OpenAI Model', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
    'woocommerce_primex_api_open_ai_model_callback',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section4'
);


register_setting(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_temperature',
    array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    )
);

// Add text fields
add_settings_field(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_temperature',
    __( 'OpenAI API Temperature', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
    'woocommerce_primex_api_open_ai_temperature_callback',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section4'
);

register_setting(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_max_tokens',
    array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    )
);

// Add text fields
add_settings_field(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_max_tokens',
    __( 'OpenAI API Maximum Tokens', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
    'woocommerce_primex_api_open_ai_max_tokens_callback',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section4'
);

register_setting(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_frequency_penalty',
    array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    )
);

// Add text fields
add_settings_field(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_frequency_penalty',
    __( 'OpenAI API Frequency Penalty', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
    'woocommerce_primex_api_open_ai_frequency_penalty_callback',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section4'
);

register_setting(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_presence_penalty',
    array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    )
);

// Add text fields
add_settings_field(
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_presence_penalty',
    __( 'OpenAI API Presence Penalty', WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME ),
    'woocommerce_primex_api_open_ai_presence_penalty_callback',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_page',
    WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_settings_section4'
);

// section 4 ends here




}
// Settings Template ends here 


// Settings Template input fields starts here 

// section 1 starts here
function woocommerce_primex_api_website_url_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_website_url');
    ?>
    <input type="text" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_website_url" class="regular-text" placeholder='Website URL...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : site_url() . '/'; ?>" />
    <?php 
}

function woocommerce_primex_api_woocommerce_api_consumer_key_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_key');
    ?>
    <input type="text" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_woocommerce_api_consumer_key" class="regular-text" placeholder='Woocommerce API Consumer Key...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : ''; ?>" />
    <?php 
}


function woocommerce_primex_api_woocommerce_api_consumer_secret_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_consumer_secret');
    ?>
    <input type="password" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_woocommerce_api_consumer_secret" class="regular-text" placeholder='Woocommerce API Consumer Secret...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : ''; ?>" />
    <?php 
}

// section 1 ends here



// section 2 starts here

function woocommerce_primex_api_woocommerce_api_mul_val_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_woocommerce_api_mul_val');
    ?>
    <input type="number" min="0" step="0.01" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_woocommerce_api_mul_val" class="regular-text" placeholder='Default is 1...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : ''; ?>" />
    <?php 
}

function woocommerce_primex_api_wc_prod_tags_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_wc_prod_tags');
    ?>
    <textarea name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_wc_prod_tags" placeholder='Comma Separated Tags...' class="regular-text" rows="4">
        <?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ?     esc_textarea( $woocommerce_primex_api_input_field ) : ''; ?>
    </textarea>
    <?php 
}

function woocommerce_primex_api_custom_tags_on_off_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_custom_tags_on_off');
    ?>
    <label>
        <input type="checkbox" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_custom_tags_on_off" value="yes" <?php checked( 'yes', $woocommerce_primex_api_input_field ); ?>/> Please check to force to use these tags on the products!
    </label>
    <?php 
}

function woocommerce_primex_api_open_ai_on_off_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_on_off');
    ?>
    <label>
        <input type="checkbox" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_open_ai_on_off" value="yes" <?php checked( 'yes', $woocommerce_primex_api_input_field ); ?>/> Please check to turn on OpenAI!
    </label>
    <?php 
}

function woocommerce_primex_api_cron_on_off_callback() {
    // if cron file exists
    if(file_exists( WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . 'cron.php')){
        $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_cron_on_off');
        ?>
        <label>
            <input type="checkbox" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_cron_on_off" value="yes" <?php checked( 'yes', $woocommerce_primex_api_input_field ); ?>/> Please check to turn on!
        </label>
        <?php 
    }else{
        ?>
        <p class="description">
            <?php esc_html_e( 'cron.php file is missing!' ); ?>
        </p>
        <?php
    }
}

// section 2 ends here


// section 3 starts here

function woocommerce_primex_api_primex_api_base_url_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_base_url');
    ?>
    <input type="text" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_primex_api_base_url" class="regular-text" placeholder='Primex API Base URL...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : 'https://api.primex.nl/'; ?>" />
    <?php 
}

function woocommerce_primex_api_primex_customer_id_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_customer_id');
    ?>
    <input type="text" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_primex_customer_id" class="regular-text" placeholder='Primex Customer ID...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : ''; ?>" />
    <?php 
}

function woocommerce_primex_api_primex_api_key_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_key');
    ?>
    <input type="password" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_primex_api_key" class="regular-text" placeholder='Primex API Key...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : ''; ?>" />
    <?php 
}

function woocommerce_primex_api_primex_api_language_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_primex_api_language');
    ?>
    <select class="regular-text" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_primex_api_language" placeholder="Primex API Language...">
        <option value="nl-NL" <?php selected( 'nl-NL', $woocommerce_primex_api_input_field ); ?> >nl-NL</option>
        <option value="en-GB" <?php selected( 'en-GB', $woocommerce_primex_api_input_field ); ?> >en-GB</option>
        <option value="de-DE" <?php selected( 'de-DE', $woocommerce_primex_api_input_field ); ?> >de-DE</option>
    </select>
    <?php 
}

// section 3 ends here


// section 4 starts here

function woocommerce_primex_api_open_ai_api_key_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_api_key');
    ?>
    <input type="password" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_open_ai_api_key" class="regular-text" placeholder='OpenAI API Key...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : ''; ?>" />
    <?php 
}

function woocommerce_primex_api_open_ai_model_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_model');
    ?>
    <input type="text" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_open_ai_model" class="regular-text" list="open_ai_models" placeholder="Double click for dropdown. Default: text-davinci-003" value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : ''; ?>">
    <datalist id="open_ai_models">
        <option value="gpt-4">
        <option value="gpt-3.5-turbo-instruct">
        <option value="gpt-3.5-turbo">
        <option value="text-davinci-003">
        <option value="gpt-3.5-turbo-16k">
    </datalist>
    <?php 
}

function woocommerce_primex_api_open_ai_temperature_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_temperature');
    ?>
    <input type="number" step="0.01" min="0" max="1" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_open_ai_temperature" class="regular-text" placeholder='Default is 0.9...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : ''; ?>" />
    <?php 
}

function woocommerce_primex_api_open_ai_max_tokens_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_max_tokens');
    ?>
    <input type="number" step="1" min="0" max="8000" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_open_ai_max_tokens" class="regular-text" placeholder='Default is 500...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : ''; ?>" />
    <?php 
}

function woocommerce_primex_api_open_ai_frequency_penalty_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_frequency_penalty');
    ?>
    <input type="number" step="0.01" min="0" max="1" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_open_ai_frequency_penalty" class="regular-text" placeholder='Default is 0...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : ''; ?>" />
    <?php 
}

function woocommerce_primex_api_open_ai_presence_penalty_callback() {
    $woocommerce_primex_api_input_field = get_option(WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME . '_open_ai_presence_penalty');
    ?>
    <input type="number" step="0.01" min="0" max="1" name="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_NAME; ?>_open_ai_presence_penalty" class="regular-text" placeholder='Default is 0.6...' value="<?php echo isset($woocommerce_primex_api_input_field) && $woocommerce_primex_api_input_field != '' ? $woocommerce_primex_api_input_field : ''; ?>" />
    <?php 
}

// section 4 ends here

// Settings Template input fields ends here 



// Submenu page 2
function woocommerce_primex_api_import_template_callback() {

    // adding bootstrap css
    echo '<link rel="stylesheet" href="' . WOOCOMMERCE_PRIMEX_API_PLUGIN_URL . 'assets/css/bootstrap.min.css">';

    ?>

    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <div class="row mt-3">

            <div class="col-md-2">
                <div class="">
                    <label for="primex_api_page_number" class="form-label">Page Number*</label>
                    <select class="form-control" id="primex_api_page_number" name="primex_api_page_number" required>
                    <?php 
                        for($i=1; $i<=20; $i++){
                            echo '<option value="'.$i.'" >'.$i.'</option>';
                        }
                    ?>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="">
                    <label for="primex_api_items_per_page" class="form-label">Items Per Page*</label>
                    <input type="number" step="1" min="1" max="200" class="form-control" id="primex_api_items_per_page" name="primex_api_items_per_page" placeholder="Min 1 - Max 200 (Default is 200)" required>
                </div>
            </div>

            <?php
                // grabbing brands
                $brands = file_get_contents( WOOCOMMERCE_PRIMEX_API_PLUGIN_PATH . 'inc/settings/brands.json' );
                $brands = json_decode($brands, true);
                $brands = $brands["brands"];
            ?>

            <div class="col-md-4">
                <label for="primex_filter_brands" class="form-label">Filter Brands (Separate Multiple Values By Commas)</label>
                <input type="text" id="primex_filter_brands" name="primex_filter_brands" class="form-control" list="primex_brands_list" placeholder="Default is Null - Double click to view example list" value="">
                <datalist id="primex_brands_list">
                    <?php
                        foreach($brands as $brand_key => $single_brand){
                            echo '<option value="'.$single_brand.'">';
                        }
                    ?>
                </datalist>
            </div>

            <div class="col-md-3">
                <div class="">
                    <button type="submit" id="primex_api_sku_list" class="btn btn-primary mt-4">Show SKU List</button>
                </div>
            </div>

        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="mt-5">
                    <?php 
                        echo do_shortcode('[importPrimexProductsShortcode]');
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>

    $( document ).ready(function() {
        $("#primex_api_sku_list").click(function(){
            $("#primex_api_sku_list").attr("disabled", true);
            $("#woocommerce_primex_api_submit_button").attr("disabled", true);
            let woocommerce_primex_api_submit_button = $("#woocommerce_primex_api_submit_button").html();
            $("#primex_api_sku_list").html("Please wait...");
            $("#woocommerce_primex_api_submit_button").html("Please wait...");
            let primex_api_page_number = $("#primex_api_page_number").val();
            let primex_api_items_per_page = $("#primex_api_items_per_page").val();
            let primex_filter_brands = $("#primex_filter_brands").val();
            let post_url = "<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_URL . 'inc/shortcodes/includes/post.php'; ?>";
            $.ajax({
                type: "POST",
                url: post_url,
                data: {primex_api_page_number, primex_api_items_per_page, primex_filter_brands}, 
                success: function(result){
                    $("#primex_api_sku_list").attr("disabled", false);
                    $("#woocommerce_primex_api_submit_button").attr("disabled", false);
                    $("#primex_api_sku_list").html("Show SKU List");
                    $("#woocommerce_primex_api_submit_button").html(woocommerce_primex_api_submit_button);
                    $("#result").html(result);
                }
            });
        });
    });

    </script>


    <?php

}
// Submenu page 2 ends here



// Submenu page 3
function woocommerce_primex_api_cron_template_callback() {
    
    // adding bootstrap css
    echo '<link rel="stylesheet" href="' . WOOCOMMERCE_PRIMEX_API_PLUGIN_URL . 'assets/css/bootstrap.min.css">';

    // initializing
    $outputText = 'Please Add Primex Products';

    // updated value
    $primex_cron_list = get_option('primex_cron_list');
    $primex_sku_next_to_update = get_option('primex_sku_next_to_update');

    $total_cron_items = count($primex_cron_list);

    // if not empty
    if( $primex_sku_next_to_update != '' && $total_cron_items  > 0 ){

        $key6 = array_search($primex_sku_next_to_update, $primex_cron_list);

        $keys = array_keys($primex_cron_list);

        $key7 =  array_search($key6, $keys);

        $outputText = ($key7 + 1) . ' => ' . $key6 . ' => ' . $primex_sku_next_to_update;

    }


?>


    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <div class="row">

            <div class="col-md-12">
                <h3 class="mt-5 text-center">
                    Next to update : 
                </h3>
                <h6 class="text-danger text-center">
                    Item Position => Product ID => Product SKU
                </h6>
                <h6 class="text-danger text-center">
                    <?php echo $outputText; ?>
                </h6>
                <div class="d-flex justify-content-center align-items-center">
                    <a target="_blank" href="<?php echo WOOCOMMERCE_PRIMEX_API_PLUGIN_URL . 'cron.php'; ?>">
                        <button id='woocommerce_primex_api_submit_button' class='btn btn-success mt-3' type='submit' name='woocommerce_primex_api_submit_button'>Run The Cron Manually</button>
                    </a>
                </div>
            </div>

        </div>
    </div>


<?php

// if not empty
if( $primex_cron_list != '' && count($primex_cron_list) > 0 ){
    $iteration = 1;
    $resultHTML = '<div class="mt-5 w-100">';
    $resultHTML .= '<span><b>Items in the Cron:</b> </span>';
    $resultHTML .= '<span>(Position) => WC Product ID => WC Product SKU</span>';
    $resultHTML .= '<br>';
    $resultHTML .= '<span>Total <b>'.$total_cron_items.'</b> items</span>';
    $resultHTML .= '<br>';
    foreach($primex_cron_list as $key => $value){
        $resultHTML .= '<span class=""> ('.$iteration.') => '.$key.' => '.$value.', </span>';
        $iteration++;
    }
    $resultHTML .= '</div>';
    echo $resultHTML;
}

}
// Submenu page 3 ends here