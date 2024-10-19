<?php

// get the product object by the post id
$product = wc_get_product(get_the_ID());

// is a valid product
if( $product != null || $product != false ){

// parent product info
$parent_id = $product->get_parent_id();
$parent_product = wc_get_product($parent_id);

// creating product variations info array
$children_info_array = [];
if( $product->is_type( 'variable' ) ){
    $children = $product->get_children();
    if( count($children) > 0){
        foreach( $children as $child_key => $single_child ){
            $single_variant = wc_get_product($single_child);
            $stock_quantity = $single_variant->get_stock_quantity();
            $price_including_tax = wc_get_price_including_tax($single_variant);
            $price_excluding_tax = wc_get_price_excluding_tax($single_variant);
            $is_tax_included = ($price_including_tax - $price_excluding_tax) > 0 ? "true" : "false";
            array_push($children_info_array,[
                "@type" => "Product",
                "@id" => $single_variant->get_permalink() . "#wooCommerceProduct",
                "name" => $single_variant->get_title(),
                "description" => $single_variant->get_description(),
                "url" => $single_variant->get_permalink(),
                "sku" => $single_variant->get_sku(),
                "image" => wp_get_attachment_image_src($single_variant->get_image_id()),
                "offers" => [
                    "@type" => "Offer",
                    "url" => $single_variant->get_permalink() . "#wooCommerceOffer",
                    "price" => $single_variant->get_price(),
                    "priceCurrency" => get_woocommerce_currency(),
                    "category" => $single_variant->get_category_ids(),
                    "availability" => $stock_quantity > 0 ? "https://schema.org/InStock" : "",
                    "priceSpecification" => [
                        "@type" => "PriceSpecification",
                        "price" => $single_variant->get_price(),
                        "priceCurrency" => get_woocommerce_currency(),
                        "valueAddedTaxIncluded" => $is_tax_included,
                    ],
                ],
            ]);
        }
    }
}
// creating product variations info array ends here


// starting schema
echo '<script type="application/ld+json" class="custom-schema-section">';
echo "\n";

$schema = [
    "@context" => "https://schema.org",
    "@graph" => [
        [
            "@type" => "BreadcrumbList",
            "@id" => get_the_permalink(get_the_ID()) . "#breadcrumblist",
            "itemListElement" => [
                [
                    "@type" => "ListItem",
                    "@id" => site_url() . "/#listItem",
                    "position" => 1,
                    "name" => "Home",
                    "item" => site_url(),
                    "nextItem" => get_the_permalink(get_the_ID()) . "#listItem"
                ],
                [
                    "@type" => "ListItem",
                    "@id" => site_url() . "/#listItem",
                    "position" => 2,
                    "name" => get_the_title(get_the_ID()),
                    "previousItem" => site_url() . "/#listItem"
                ]
            ]
        ],
        [
            "@type" => "Organization",
            "@id" => site_url() . "/#organization",
            "name" => get_bloginfo( 'name' ),
            "description" => get_bloginfo( 'description' ),
            "url" => site_url(),
            "telephone" => "+31502102827"
        ],
        [
            "@type" => "ProductGroup",
            "@id" => get_the_permalink(get_the_ID()) . "#wooCommerceProduct",
            "name" => get_the_title(get_the_ID()),
            "description" => get_post_meta(get_the_ID(), 'description', true) != '' ? get_post_meta(get_the_ID(), 'description', true) : get_the_content(get_the_ID()),
            "sku" => $product->get_sku(),
            "image" => [
                "@type" => "ImageObject",
                "url" => get_post_thumbnail_id(get_the_ID()) ? wp_get_attachment_url( get_post_thumbnail_id(get_the_ID()), 'thumbnail' ) : '',
                "@id" => get_the_permalink(get_the_ID()) . "#productImage",
                "width" => get_post_thumbnail_id(get_the_ID()) ? wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full' )[1] : '',
                "height" => get_post_thumbnail_id(get_the_ID()) ? wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full' )[2] : '',
                "caption" => get_the_title(get_the_ID())
            ],
            "productGroupID" => $parent_id,
            "hasVariant" => $children_info_array,
        ],
        [
            "@type" => "WebPage",
            "@id" => get_the_permalink(get_the_ID()) . "#webpage",
            "url" => get_the_permalink(get_the_ID()),
            "name" => get_the_title(get_the_ID()) . " - " . get_bloginfo( 'name' ),
            "description" => get_post_meta(get_the_ID(), 'description', true) != '' ? get_post_meta(get_the_ID(), 'description', true) : get_the_content(get_the_ID()),
            "inLanguage" => "nl-NL",
            "isPartOf" => [
                "@id" => site_url() . "/#website"
            ],
            "breadcrumb" => [
                "@id" => get_the_permalink(get_the_ID()) . "#breadcrumblist"
            ],
            "image" => [
                "@type" => "ImageObject",
                "url" => get_post_thumbnail_id(get_the_ID()) ? wp_get_attachment_url( get_post_thumbnail_id(get_the_ID()), 'thumbnail' ) : '',
                "@id" => get_the_permalink(get_the_ID()) . "#mainImage",
                "width" => get_post_thumbnail_id(get_the_ID()) ? wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full' )[1] : '',
                "height" => get_post_thumbnail_id(get_the_ID()) ? wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full' )[2] : '',
                "caption" => get_post_thumbnail_id(get_the_ID()) ? wp_get_attachment_caption(get_post_thumbnail_id(get_the_ID())) : ''
            ],
            "primaryImageOfPage" => [
                "@id" => get_the_permalink(get_the_ID()) . "#mainImage"
            ],
            "datePublished" => get_the_date( '', get_the_ID() ),
            "dateModified" => get_the_modified_date( '', get_the_ID() )
        ],
        [
            "@type" => "WebSite",
            "@id" => site_url() . "/#website",
            "url" => site_url(),
            "name" => get_the_title(get_the_ID()),
            "alternateName" => ucwords(wp_parse_url(site_url())['host']),
            "description" => get_bloginfo( 'description' ),
            "inLanguage" => "nl-NL",
            "publisher" => [
                "@id" => site_url() . "/#organization"
            ]
        ]
    ]
];

// printing the schema in a json format
echo json_encode($schema);

echo "\n";
echo '</script>';
// starting schema ends here

}