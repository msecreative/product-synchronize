<?php 

/**
 * Delete woocommerce product by id
 */

function mb_delete_product($product_id) {
    wp_delete_post($product_id, true);
}