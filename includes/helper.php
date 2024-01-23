<?php


use WovoSoft\SPromoter\Admin\Api;

function assets_path($path = '')
{
    if (str_starts_with($path, '/')){
        $path = substr($path, 1);
    }

    return SP_PLUGIN_URL . '/assets' . ($path ? '/' . $path : '');
}

function settings($key = null)
{
    $settings = get_option('spromoter_settings', []);

    $settings = array_merge(array(
        'app_id' => '',
        'api_key' => '',
        'order_status' => 'completed',
        'review_show_in' => 'footer',
        'disable_native_review_system' => true,
        'show_bottom_line_widget' => true,
        'debug_mode' => true,
        'configured_at' => time(),
    ), $settings);

    if ($key){
        return $settings[$key];
    }

    return $settings;
}

function get_product_image_url($product_id) {
    return wp_get_attachment_url(get_post_thumbnail_id($product_id));
}

function get_connection_status()
{
    $settings = settings();

    if (empty($settings['app_id']) || empty($settings['api_key'])){
        return false;
    }

    $api = new Api($settings['api_key'], $settings['app_id']);

    $result = $api->sendRequest('check-credentials', 'POST');

    return $result['status'];
}

/**
 * Get Product data
 *
 * @param $product
 *
 * @return array
 */
function get_product_data($product) {
    $settings = settings();
    $product_data = array(
        'app_id' => esc_attr($settings['app_id']),
        'shop_domain' => esc_attr(parse_url(get_bloginfo('url'),PHP_URL_HOST)),
        'url' => esc_attr(get_permalink($product->get_id())),
        'lang' => esc_attr('en'),
        'description' => esc_attr(wp_strip_all_tags($product->get_description())),
        'id' => esc_attr($product->get_id()),
        'title' => esc_attr($product->get_title()),
        'image-url' => esc_attr(wp_get_attachment_url(get_post_thumbnail_id($product->get_id()))),
        'specs' => get_product_specs($product),
    );

    $lang = explode('-', get_bloginfo('language'));
    if(strlen($lang[0]) == 2) {
        $product_data['lang'] = $lang[0];
    }

    return $product_data;
}

/**
 * Get product specs data
 *
 * @param $product
 *
 * @return array
 */
function get_product_specs($product) {
    return array(
        'sku' => $product->get_sku() ?? $product->data['sku'] ?? '',
        'upc' => $product->get_attribute('upc') ?? $product->data['upc'] ?? '',
        'isbn' => $product->get_attribute('isbn') ?? $product->data['isbn'] ?? '',
        'brand' => $product->get_attribute('brand') ?? $product->data['brand'] ?? '',
        'mpn' => $product->get_attribute('mpn') ?? $product->data['mpn'] ?? '',
    );
}

if (!function_exists('dd')){
    /**
     * Dump and die
     * @param $data
     * @return void
     */
    function dd($data){
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die();
    }
}