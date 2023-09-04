<?php 
/**
 * To get product category by category meta value
 */
function get_cat_by_meta_value_and_key($meta_value){
    // Define the meta query arguments
    $meta_query_args = array(
        array(
            'key'     => 'segval', 
            'value'   => $meta_value, 
                'compare' => '=', // Adjust the comparison operator as needed (e.g., '=', '>', '<', 'LIKE')
        ),
    );

    // Get terms with the specified meta query
    $terms = get_terms( array(
        'taxonomy' => 'product_cat', // Replace 'your_taxonomy' with the actual taxonomy name
        'hide_empty' => false, // Include terms with no associated posts
        'meta_query' => $meta_query_args,
    ) );

    if(count($terms) == 1 ){
        // Loop through terms
        foreach ( $terms as $term ) {
            // echo 'Term ID: ' . $term->term_id . '<br>';
            return $term->name;
        }
    }
}