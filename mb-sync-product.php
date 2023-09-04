<?php 
/*
 * Plugin Name:       MB Synchronize all product
 * Description:       This plugin synchronizes all products from a database
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            CanSoft
 * Author URI:        https://cansoft.com/
 */
// Include your functions here
require_once( plugin_dir_path( __FILE__ ) . '/inc/all-functions/mb-product-exit.php');
require_once( plugin_dir_path( __FILE__ ) . '/inc/all-functions/mb-icitem-product-sync.php');
require_once( plugin_dir_path( __FILE__ ) . '/inc/all-functions/mb-iciloc-product-sync.php');
require_once( plugin_dir_path( __FILE__ ) . '/inc/all-functions/get-cat-by-meta-valye-and-key.php');
require_once( plugin_dir_path( __FILE__ ) . '/inc/all-functions/get-product-id-by-itemno-meta-value.php');
require_once( plugin_dir_path( __FILE__ ) . '/inc/all-functions/delete-product-by-id.php');

require_once( plugin_dir_path( __FILE__ ) . '/inc/api/fetch-all-products-data-from-icitem-table.php');
require_once( plugin_dir_path( __FILE__ ) . '/inc/api/fetch-all-products-data-from-icpricp-table.php');
require_once( plugin_dir_path( __FILE__ ) . '/inc/api/fetch-all-products-data-from-iciloc-table.php');


//WORDPRESS HOOK FOR ADD A CRON JOB EVERY 2 Min
function mb_icitem_product_cron_schedules($schedules){
    if(!isset($schedules['every_twelve_hours'])){
        $schedules['every_twelve_hours'] = array(
            'interval' => 12*60*60, // Every 12 hours
            'display' => __('Every 12 hours'));
    }
    return $schedules;
}

add_filter('cron_schedules','mb_icitem_product_cron_schedules');


function dd($data, $clean = false)
{
    if ($clean) {
        echo "<pre style='background-color:#ffffff; border:3px solid #ecf0f1; color:#df5000;'>";
        die(var_dump($data));
        echo "</pre>";
        die();
    } else {
        echo "<pre style='background-color:#ffffff; border:3px solid #ecf0f1; color:#df5000;'>";
        print_r($data);
        echo "</pre>";
        die();
    }
}

function db_raw($query, $return = 'result')
{
    $query = trim(preg_replace('/[\t\n\r\s]+/', ' ', $query));

    if ($return == 'sql') {
        return $query;
    }

    global $wpdb;

    return $wpdb->get_results($query);
}

// Enqueue all assets
function mbps_all_assets(){
    wp_enqueue_script('mbpc-main-script', plugin_dir_url( __FILE__ ) . '/assets/admin/js/script.js', null, time(), true);
}
add_action( 'admin_enqueue_scripts', 'mbps_all_assets' );


/**
 * Add menu page for this plugin
 */
function mb_product_sync_menu_pages(){
    add_submenu_page(
        'edit.php?post_type=product',
        'Product Sync',
        'Product Sync',
        'manage_options',
        'product-sync',
        'mb_products_sync'
    );
}
add_action( 'admin_menu', 'mb_product_sync_menu_pages' );



function mb_for_delete_meta_key(){
    $meta_key = 'itemno'; // Replace 'your_meta_key' with the actual meta key you want to delete

    $args = array(
        'post_type'      => 'product', // Replace 'post' with the appropriate post type
        'posts_per_page' => -1, // Retrieve all posts, you can limit it if needed
    );

    $posts = get_posts($args);

    foreach ($posts as $post) {
        $post_id = $post->ID;
        delete_post_meta($post_id, $meta_key);
    }
}
//add_action('init', 'mb_for_delete_meta_key');

/**
 * Main function for product sync
 */
function mb_products_sync(){
    ?>
    <style>
        .wrap .d-flex {
            display: flex;
            align-items: center;
            justify-content: space-evenly;
        }
    </style>
        <div class="wrap">
            <h1>This Page for Sincronize all product</h1><br>
            <div class="d-flex">
                <form method="GET">
                    <input type="hidden" name="pageno" value="1">
                    <input type="hidden" name="post_type" value="product">
                    <input type="hidden" name="page" value="product-sync">
                    <?php
                        submit_button('ICITEM Product Sync', 'primary', 'mb-product-icitem-sync');
                    ?>
                </form>

                <form method="POST">
                    <?php 
                        submit_button( 'Start ICITEM Menual', 'primary', 'mb-icitem-product-submit-menual' );
                        submit_button( 'Start ICITEM Cron Now', 'primary', 'mb-icitem-product-submit-start-cron' );
                    ?>
                </form>
            </div>
            <div class="d-flex">
                <form method="GET">
                    <input type="hidden" name="pagenoforloc" value="1">
                    <input type="hidden" name="post_type" value="product">
                    <input type="hidden" name="page" value="product-sync">
                    <?php 
                        submit_button('mb-product-iciloc-sync', 'primary', 'mb-product-iciloc-sync'); 
                    ?>
                </form>

                <form method="POST">
                    <?php 
                        submit_button( 'Start ICILOC Menual', 'primary', 'mb-iciloc-product-sync-menual' );
                        submit_button( 'Start ICILOC Cron Now', 'primary', 'mb-iciloc-product-submit-start-cron' );
                    ?>
                </form>
            </div>

            <?php 

            if (isset($_POST["mb-icitem-product-submit-menual"])) {
                mb_icitem_sync_product(1);
                wp_redirect( admin_url( "/edit.php?post_type=product&page=product-sync" ) );
                exit();
            }

            if (isset($_POST["mb-iciloc-product-sync-menual"])) {
                mb_icitem_sync_product(0);
                wp_redirect( admin_url( "/edit.php?post_type=product&page=product-sync" ) );
                exit();
            }

                /**
                 * After clicing product sync button
                 * 
                 * For Main product making
                 */
                if(isset($_GET['pageno'])){

                    $i = $_GET['pageno'] ?? 1;

                    $all_products = fetch_all_products_data_from_icitem_table($i);
                    $start = microtime(true);
                    $api_ids = [];
                    foreach($all_products as $product){
                        /**
                         * Check Product not exit 
                         * 
                         * if product already exit than it will be not created as a product
                         */
                        $api_ids[] = $product['id'];

                        if( ! mb_product_exit($product['ITEMNO'] )){
                            //create product
                            $post_id = wp_insert_post( array(
                                'post_title' => $product['DESC'],
                                'post_status' => 'publish',
                                'post_type' => "product",
                            ) );
                            $category = get_cat_by_meta_value_and_key($product['CATEGORY']);
                            //set category
                            wp_set_object_terms( $post_id, $category, 'product_cat');
                            //set all meta value
                            update_post_meta( $post_id, '_sku', $product['FMTITEMNO'] );
                            update_post_meta( $post_id, 'inactive', $product['INACTIVE']);
                            update_post_meta( $post_id, 'itemno', $product['ITEMNO'] );
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

                    echo "<pre>";
					print_r($api_data);
					echo "</pre>";

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

                    $total = microtime(true) - $start;
                    echo "Total Execution time: " . $total;

                    if(! count($all_products)){

                        wp_redirect( admin_url( "/edit.php?post_type=product&page=product-sync" ) );
                        exit();
                    }
                }

                if(isset($_GET['pagenoforloc'])){
                        
                    $pagenoforloc = $_GET['pagenoforloc'] ?? 1;
                    // if ($pagenoforloc == 2) {
                    	
                    // 	return false;
                    // }

                    $all_quantity_locations = fetch_all_products_data_from_iciloc_table($pagenoforloc);
                    $api_ids = [];
                    $start = microtime(true);
                    foreach($all_quantity_locations as $_q_location){
                    	$api_ids[] = $_q_location['id'];

                        //get product id with custom meta value (ITEMNO)
                        $product_id = get_product_id_by_itemno_meta_value($_q_location['ITEMNO']);

                        //get product with product id
                        $product = wc_get_product($product_id);

                        // //get meta key by location id
                        $quantity_location_meta_key = 'store_'.$_q_location['LOCATION'];

                        if ($product) {
                        	//update product PRICELIST as a custom meta 
                        	$product->update_meta_data($quantity_location_meta_key, $_q_location['QTYONHAND']);            
                        	$product->save(); //and finally save price list
                        }
                    }
                    $total = microtime(true) - $start;
                    echo "<span style'color:red;font-weight:bold'>Total Execution Time: </span>" . $total;

                    // API endpoint
					$apiUrl = 'modern.cansoft.com/db-clone/api/icitem/update';
                    
					// List of update IDs
					$updateIds = implode(",", $api_ids);
					echo "<pre>";
					print_r($updateIds);
					echo "</pre>";
					// Prepare the request payload
					$requestData = [
					    'id' => $updateIds,
					    'status' => 'Synced',
					];

					// Use cURL to make the API request
			        $ch = curl_init($apiUrl);
			        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
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


                    if(! count( $all_quantity_locations )){
                        wp_redirect( admin_url( "/edit.php?post_type=product&page=product-sync" ) );
                        exit();
                    }
                }

                //It work when Click Strt cron  button
                if(isset($_POST['mb-icitem-product-submit-start-cron'])){
                    if (!wp_next_scheduled('mb_icitem_add_with_cron')) {
                        wp_schedule_event(time(), 'every_twelve_hours', 'mb_icitem_add_with_cron');
                    }
                    wp_redirect( admin_url( "/edit.php?post_type=product&page=product-sync" ) );
                    exit();
                }
                //It work when Click Strt cron  button
                if(isset($_POST['mb-iciloc-product-submit-start-cron'])){
                    if (!wp_next_scheduled('mb_iciloc_add_with_cron')) {
                        wp_schedule_event(time(), 'every_twelve_hours', 'mb_iciloc_add_with_cron');
                    }
                    wp_redirect( admin_url( "/edit.php?post_type=product&page=product-sync" ) );
                    exit();
                }

            ?>
        </div>
    <?php 
}

//For clear cron schedule
function woo_product_syncronization_apis_plugin_deactivation(){
    wp_clear_scheduled_hook('mb_icitem_add_with_cron');
    wp_clear_scheduled_hook('mb_iciloc_add_with_cron');
    // It for clear all product from database
    // Query WooCommerce products

// delete meta using id
    // $meta_key = 'itemno'; // Replace 'your_meta_key' with the actual meta key you want to delete

    // $args = array(
    //     'post_type'      => 'product', // Replace 'post' with the appropriate post type
    //     'posts_per_page' => -1, // Retrieve all posts, you can limit it if needed
    // );

    // $posts = get_posts($args);

    // foreach ($posts as $post) {
    //     $post_id = $post->ID;
    //     delete_post_meta($post_id, $meta_key);
    // }
}
register_deactivation_hook(__FILE__, 'woo_product_syncronization_apis_plugin_deactivation');


//This happend when icitem caron job is runnning
function mb_run_cron_for_add_icitem_product(){
    mb_icitem_sync_product(0);
}

add_action('mb_icitem_add_with_cron', 'mb_run_cron_for_add_icitem_product');

//This happend when icitem caron job is runnning
function mb_run_cron_for_add_iciloc_product(){
    mb_sync_iciloc_product(1);
}

add_action('mb_iciloc_add_with_cron', 'mb_run_cron_for_add_iciloc_product');

