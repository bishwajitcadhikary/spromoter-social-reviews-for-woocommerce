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
        'review_show_in' => 'tab',
        'disable_native_review_system' => true,
        'show_bottom_line_widget' => true,
        'debug_mode' => true
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

    $api = new Api($settings['api_key']);

    $result = $api->sendRequest('check-credentials', 'POST', [
        'app_id' => $settings['app_id'],
    ]);

    return $result['status'];
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