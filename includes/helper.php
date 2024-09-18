<?php

/**
 * Get the URL for assets folder
 *
 * @param string $path
 * @return string
 */
if (!function_exists('spromoter_assets_path')) {
    function spromoter_assets_path(string $path = ''): string
    {
        if (str_starts_with($path, '/')) {
            $path = substr($path, 1);
        }

        return constant('SPROMOTER_PLUGIN_URL') . '/assets' . ($path ? '/' . $path : '');
    }
}

/**
 * Get plugin settings
 *
 * @param string|null $key
 * @return mixed|array
 */
if (!function_exists('spromoter_settings')){
    function spromoter_settings(string $key = null)
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
}

/**
 * Get the URL of a product's image
 *
 * @param int $product_id
 * @return string|false
 */
if (!function_exists( 'spromoter_get_product_image_url' )) {
    function spromoter_get_product_image_url(int $product_id)
    {
        return wp_get_attachment_url(get_post_thumbnail_id($product_id));
    }
}

/**
 * Get the connection status
 *
 * @return bool
 */
if (!function_exists('spromoter_is_connected')) {
    function spromoter_is_connected(): bool
    {
        $settings = spromoter_settings();

        if (empty($settings['app_id']) || empty($settings['api_key'])) {
            return false;
        }

        $api = new KinDigi\SPromoter\Admin\Api($settings['api_key'], $settings['app_id']);

        $result = $api->send_request('stores/me');

        return isset($result['status']) && $result['status'] === 'success';
    }
}

/**
 * Get product data
 *
 * @param WC_Product $product
 * @return array
 * @since 1.0.0
 */
if (!function_exists('spromoter_product_data')){
    function spromoter_product_data(WC_Product $product): array
    {
        $settings = spromoter_settings();
        $product_data = [
            'app_id' => esc_attr($settings['app_id']),
            'shop_domain' => esc_attr(wp_parse_url(get_site_url(), PHP_URL_HOST)),
            'url' => esc_url(get_permalink($product->get_id())),
            'lang' => esc_attr('en'),
            'description' => esc_attr(wp_strip_all_tags($product->get_description())),
            'id' => esc_attr($product->get_id()),
            'title' => esc_attr($product->get_title()),
            'image-url' => esc_url(wp_get_attachment_url(get_post_thumbnail_id($product->get_id()))),
            'specs' => spromoter_product_specs($product),
        ];

        $lang = explode('-', get_bloginfo('language'));
        if (strlen($lang[0]) === 2) {
            $product_data['lang'] = esc_attr($lang[0]);
        }

        return $product_data;
    }
}

/**
 * Get product specs data
 *
 * @param WC_Product $product
 * @return array
 * @since 1.0.0
 */
if (!function_exists('spromoter_product_specs')){
    function spromoter_product_specs(WC_Product $product): array
    {
        return [
            'sku' => $product->get_sku() ?: null,
            'upc' => $product->get_attribute('upc') ?: null,
            'isbn' => $product->get_attribute('isbn') ?: null,
            'mpn' => $product->get_attribute('mpn') ?: null,
        ];
    }
}

if (!function_exists('spromoter_post_unslash')){
    function spromoter_post_unslash($key, $default = null)
    {
        if (isset($_POST) && check_admin_referer() && array_key_exists($key, $_POST)) {
            return wp_strip_all_tags(wp_unslash($_POST[$key]));
		}

		return $default;
    }
}