<?php 

/**
 * Check product has exit or not
 * 
 * we want to check it with product no meta key (ITEMNO)
 */

function mb_product_exit($itemno_meta_value){
 
    // Set the custom meta tag key and value to search for
    $meta_key = 'itemno';
    $meta_value = $itemno_meta_value;

    // Prepare the arguments for the product query
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'any',
        'meta_query'     => array(
            array(
                'key'     => $meta_key,
                'value'   => $meta_value,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1 // Limit the query to 1 result
    );

    // Run the product query
    $products = new WP_Query( $args );

    // Check if the product(s) with the custom meta tag exist
    if ( $products->have_posts() ) {
        return true;
    }
    return false;
}