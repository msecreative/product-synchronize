<?php 

/**
 * Function for fetch all products data from ICITEM table
 */
function fetch_all_products_data_from_icitem_table($page) {

    $url = 'https://modern.cansoft.com/tables/ICITEM.php';

    $params = array(
        'page' => $page
    );

    $ch = curl_init();
    $url = add_query_arg($params, $url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);

    if ($response === false) {
        // Handle the error if the request fails
        // You can log the error or implement retry logic here
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    $data = json_decode($response, true);
    return $data;
}