<?php 
    function mb_sync_iciloc_product($page = 1){
        
        $all_quantity_locations = fetch_all_products_data_from_iciloc_table($page);
        $api_ids = [];
        $start = microtime(true);
        foreach($all_quantity_locations as $_q_location){
            $api_ids[] = $_q_location['id'];
            //get product id with custom meta value (ITEMNO)
            $product_id = get_product_id_by_itemno_meta_value($_q_location["ITEMNO"]);

            //get product with product id
            $product = wc_get_product($product_id);

            // //get meta key by location id
            $quantity_location_meta_key = 'store_'.$_q_location["LOCATION"];

            if ($product) {
                //update product PRICELIST as a custom meta 
                $product->update_meta_data($quantity_location_meta_key, $_q_location->QTYONHAND);
                $product->save(); //and finally save price list
            }
        }
        $total = microtime(true) - $start;
        echo "Total execution time is ". $total;

         // Send an update request to another API
         $api_url = 'https://modern.cansoft.com/db-clone/api/iciloc/update'; // Replace with your API endpoint
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
            
        if( count( $all_quantity_locations )){
            mb_sync_iciloc_product($page);
        }
    }