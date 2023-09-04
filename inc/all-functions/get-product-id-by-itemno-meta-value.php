<?php 

/**
 * Get product id by ITEMNO Meta value
 */
// function get_product_id_by_itemno_meta_value($itemno_value){
//     global $wpdb;

//     $meta_key = 'itemno';
//     $meta_value = $itemno_value;
    
//     $query = $wpdb->prepare("
//         SELECT post_id
//         FROM {$wpdb->prefix}postmeta
//         WHERE meta_key = %s
//         AND meta_value = %s
//     ", $meta_key, $meta_value);
    
//     $product_ids = $wpdb->get_col($query);

//     return end($product_ids);
// }


function get_product_id_by_itemno_meta_value($itemno_value){
    if($itemno_value == ''){
        return;
    }
    $args = array(
        'post_type' => 'product',
        'meta_key' => 'itemno',
        'meta_value' => $itemno_value,
        'meta_compare' => '=',
        'posts_per_page' => 1,
    );
    
    $query = new WP_Query( $args );
    
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            // Do something with the post ID
            return $post_id;
        }
    } 
    // Restore the original post data
    wp_reset_postdata();
}