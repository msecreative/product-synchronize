<?php 
    function mb_icitem_sync_product($page = 1){

        $all_products = fetch_all_products_data_from_icitem_table($page);
        $start = microtime(true);
        $api_ids = [];
        foreach($all_products as $product){
            $api_ids[] = $product['id'];
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
            }else{
                $post_id = get_product_id_by_itemno_meta_value($product["ITEMNO"]);

                wp_update_post($post_id, 'post_title', $product['DESC']);
                update_post_meta( $post_id, 'inactive', $product['INACTIVE']);
                $category = get_cat_by_meta_value_and_key($product['CATEGORY']);
                //set category
                wp_set_object_terms( $post_id, $category, 'product_cat');
                //set all meta value
                update_post_meta( $post_id, '_sku', $product['FMTITEMNO'] );
            }
        }

        // Send an update request to another API
        $api_url = 'https://modern.cansoft.com/db-clone/api/icitem/update'; // Replace with your API endpoint
                    
        $api_data = [
            'id' => implode(',', $api_ids),
            'status' => "Synced",
        ];
        // Use cURL to make the API request
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($api_data));
        $response = curl_exec($ch);

        // Check for errors or process the response as needed
        if ($response === false) {
            // Handle cURL error
            echo 'cURL Error: ' . curl_error($ch);
        } else {
            // Process the API response
            // $response contains the API response data
            echo 'API Response: ' . $response;
        }

        // Close the cURL session
        curl_close($ch);

       
        if (count($all_products)) {

            mb_icitem_sync_product($page);

        }

        $total = "Total Execution time: " . microtime(true) - $start;

        if (!count($all_products)) {
           wp_redirect( admin_url( "/edit.php?post_type=product&page=product-sync?msg=$total" ) );
            exit();
        }

    }