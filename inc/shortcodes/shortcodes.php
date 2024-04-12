<?php

//  no direct access 
if( !defined('ABSPATH') ) : exit(); endif;

// WordPress Shortcode 
require_once 'import_products.php';
add_shortcode('importPrimexProductsShortcode', 'importPrimexProducts');