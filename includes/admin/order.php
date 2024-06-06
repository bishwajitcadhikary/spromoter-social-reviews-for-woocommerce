<?php

namespace WovoSoft\SPromoter\Admin;

use WC_Order;

class Order
{
    protected $settings;

    public function __construct()
    {
        $this->settings = settings();
    }

    /**
     * Submit order
     *
     * @param $order
     * @return array
     * @since 1.0.0
     */
    public function submit_order_data($order): array
    {
        do_action('woocommerce_init');

        $order_id = $order->get_id();
        $orderStatus = $order->get_status();

        $orderData = [
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'customer_email' => $order->get_billing_email(),
            'order_id' => "$order_id",
            'order_date' => $order->get_date_created()->format('Y-m-d H:i:s'),
            'currency' => $order->get_currency(),
            'status' => $orderStatus,
            'total' => $order->get_total(),
            'data' => $order->get_data(),
            'platform' => 'woocommerce',
        ];


        $items = array();
        foreach ($order->get_items() as $item) {
            $product = wc_get_product($item['product_id']);
            $productId = $product->get_id();
            $items[] = array(
                'id' => "$productId",
                'name' => $product->get_name(),
                'image' => get_product_image_url($product->get_id()),
                'url' => $product->get_permalink(),
                'description' => wp_strip_all_tags($product->get_description()),
                'lang' => get_locale(),
                'price' => $product->get_price(),
                'quantity' => $item['quantity'],
                'specs' => array(
                    'sku' => $product->get_sku(),
                    'upc' => $product->get_attribute('upc'),
                    'ean' => $product->get_attribute('ean'),
                    'isbn' => $product->get_attribute('isbn'),
                    'asin' => $product->get_attribute('asin'),
                    'gtin' => $product->get_attribute('gtin'),
                    'mpn' => $product->get_attribute('mpn'),
                    'brand' => $product->get_attribute('brand'),
                )
            );
        }

        $orderData['items'] = $items;

        return $orderData;
    }

    /**
     * Get past orders data
     *
     * @return array
     * @since 1.0.0
     */
    public function get_past_orders_data(): array
    {
        if (defined('WC_VERSION') && (version_compare(WC_VERSION, '3.0') >= 0)) {
            return $this->prepare_orders();
        } else {
            return $this->prepare_orders_legacy();
        }
    }

    /**
     * Prepare orders data
     *
     * @return array
     * @since 1.0.0
     */
    public function prepare_orders(): array
    {
        $configuredAt = $this->settings['configured_at'];
        $orders = wc_get_orders([
            'limit' => -1,
            'status' => ['completed', 'processing'],
            'type' => 'shop_order',
            'date_created' => '<=' . date('Y-m-d', $configuredAt)
        ]);

        $data = [];
        foreach ($orders as $order) {
            $order_id = $order->get_id();
            $data[] = [
                'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'customer_email' => $order->get_billing_email(),
                'order_id' => "$order_id",
                'order_date' => $order->get_date_created()->format('Y-m-d H:i:s'),
                'currency' => $order->get_currency(),
                'status' => $order->get_status(),
                'total' => $order->get_total(),
                'data' => $order->get_data(),
                'platform' => 'woocommerce',
                'items' => $this->prepare_order_items($order->get_items())
            ];
        }

        return $data;
    }

    /**
     * Prepare orders data for legacy versions
     *
     * @return array
     * @since 1.0.0
     */
    private function prepare_orders_legacy(): array
    {
        global $wpdb;

        $orders = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}posts
            WHERE post_type = 'shop_order'
            AND post_status IN ('wc-completed', 'wc-processing')
            AND post_date >= '" . date('Y-m-d', strtotime('-1 month')) . "'
        ");

        $data = [];
        foreach ($orders as $order) {
            $order = new WC_Order($order->ID);
            $data[] = [
                $order_id = $order->get_id(),
                'customer_name' => $order->billing_first_name . ' ' . $order->billing_last_name,
                'customer_email' => $order->billing_email,
                'order_id' => "$order_id",
                'order_date' => $order->order_date,
                'currency' => $order->order_currency,
                'status' => $order->status,
                'total' => $order->order_total,
                'data' => $order->get_data(),
                'platform' => 'woocommerce',
                'items' => $this->prepare_order_items($order->get_items())
            ];
        }

        return $data;
    }

    /**
     * Prepare order items
     *
     * @param array $get_items
     * @return array
     * @since 1.0.0
     */
    private function prepare_order_items(array $get_items = []): array
    {
        $items = [];
        foreach ($get_items as $item) {
            $product = wc_get_product($item['product_id']);
            $productId = $product->get_id();
            $items[] = [
                'id' => "$productId",
                'name' => $product->get_name(),
                'image' => get_product_image_url($product->get_id()),
                'url' => $product->get_permalink(),
                'description' => wp_strip_all_tags($product->get_description()),
                'lang' => get_locale(),
                'price' => $product->get_price(),
                'quantity' => $item['quantity'],
                'specs' => [
                    'sku' => $product->get_sku(),
                    'upc' => $product->get_attribute('upc'),
                    'ean' => $product->get_attribute('ean'),
                    'isbn' => $product->get_attribute('isbn'),
                    'asin' => $product->get_attribute('asin'),
                    'gtin' => $product->get_attribute('gtin'),
                    'mpn' => $product->get_attribute('mpn'),
                    'brand' => $product->get_attribute('brand'),
                ]
            ];
        }

        return $items;
    }
}