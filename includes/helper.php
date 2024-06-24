<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Get the URL for assets folder
 *
 * @param string $path
 * @return string
 */
function assets_path(string $path = ''): string
{
    if (str_starts_with($path, '/')) {
        $path = substr($path, 1);
    }

    return constant('SP_PLUGIN_URL') . '/assets' . ($path ? '/' . $path : '');
}

/**
 * Get plugin settings
 *
 * @param string|null $key
 * @return mixed|array
 */
function settings(string $key = null)
{
    $settings = get_option('spromoter_settings', []);

    $settings = array_merge(
        [
            'app_id' => '',
            'api_key' => '',
            'order_status' => 'completed',
            'review_show_in' => 'tab',
            'disable_native_review_system' => true,
            'show_bottom_line_widget' => true,
            'debug_mode' => true,
            'configured_at' => time(),
        ],
        $settings
    );

    if ($key !== null) {
        return $settings[$key] ?? null;
    }

    return $settings;
}

/**
 * Get the URL of a product's image
 *
 * @param int $product_id
 * @return string|false
 */
function get_product_image_url(int $product_id)
{
    return wp_get_attachment_url(get_post_thumbnail_id($product_id));
}

/**
 * Get the connection status
 *
 * @return bool
 */
function get_connection_status(): bool
{
    $settings = settings();

    if (empty($settings['app_id']) || empty($settings['api_key'])) {
        return false;
    }

    $api = new WovoSoft\SPromoter\Admin\Api($settings['api_key'], $settings['app_id']);

    $result = $api->send_request('stores/me');

    return isset($result['status']) && $result['status'] === 'success';
}

/**
 * Get product data
 *
 * @param WC_Product $product
 * @return array
 * @since 1.0.0
 */
function get_product_data(WC_Product $product): array
{
    $settings = settings();
    $product_data = [
        'app_id' => esc_attr($settings['app_id']),
        'shop_domain' => esc_attr(wp_parse_url(get_site_url(), PHP_URL_HOST)),
        'url' => esc_url(get_permalink($product->get_id())),
        'lang' => esc_attr('en'),
        'description' => esc_attr(wp_strip_all_tags($product->get_description())),
        'id' => esc_attr($product->get_id()),
        'title' => esc_attr($product->get_title()),
        'image-url' => esc_url(wp_get_attachment_url(get_post_thumbnail_id($product->get_id()))),
        'specs' => get_product_specs($product),
    ];

    $lang = explode('-', get_bloginfo('language'));
    if (strlen($lang[0]) === 2) {
        $product_data['lang'] = esc_attr($lang[0]);
    }

    return $product_data;
}

/**
 * Get product specs data
 *
 * @param WC_Product $product
 * @return array
 * @since 1.0.0
 */
function get_product_specs(WC_Product $product): array
{
    return [
        'sku' => $product->get_sku() ?: null,
        'upc' => $product->get_attribute('upc') ?: null,
        'isbn' => $product->get_attribute('isbn') ?: null,
        'mpn' => $product->get_attribute('mpn') ?: null,
    ];
}

