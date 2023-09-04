<?php 
    function mb_icitem_sync_product($page = 0){

        $all_products = fetch_all_products_data_from_icitem_table($page);

        foreach($all_products as $product){
            if( ! mb_product_exit($product['ITEMNO'] )){
                //create product
                $post_id = wp_insert_post( array(
                    'post_title' => $product['DESC'],
                    'post_status' => 'publish',
                    'post_type' => "product",
                ) );

                //get category
                $category = get_cat_by_meta_value_and_key($product['CATEGORY']);

                //set category
                wp_set_object_terms( $post_id, $category, 'product_cat');

                //set all meta value
                update_post_meta( $post_id, '_sku', $product['FMTITEMNO'] );
                update_post_meta( $post_id, 'itemno', $product['ITEMNO'] );
                update_post_meta( $post_id, 'inactive', $product['INACTIVE']);
            }
        }
        
        if (count($all_products)) {

            mb_icitem_sync_product($page+1);

        }
    }