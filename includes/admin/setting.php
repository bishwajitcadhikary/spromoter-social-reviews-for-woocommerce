<?php

namespace KinDigi\SPromoter\Admin;

class Setting
{
    protected $settings;

    public function __construct()
    {
        $this->settings = spromoter_settings();
        add_action('admin_menu', [$this, 'add_admin_menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('woocommerce_order_status_changed', [$this, 'submit_order']);
    }

    /**
     * Add admin menus
     *
     * @return void
     * @since 1.0.0
     */
    public function add_admin_menus()
    {
        $icon_url = constant('SPROMOTER_PLUGIN_URL') . '/assets/images/small-logo.jpg';

        add_menu_page('SPromoter', 'SPromoter', 'manage_options', 'spromoter', [$this, 'show_page'], $icon_url);
    }

    /**
     * Show admin page
     *
     * @return void
     * @since 1.0.0
     */
    public function show_page()
    {
        if (empty($this->settings['app_id']) && empty($this->settings['api_key']) && isset($_GET['view']) && $_GET['view'] == 'login') {
            if (!empty($_POST) && isset($_POST['_wpnonce_spromoter_login_form'])) {
                // Verify nonce before processing form data
                if (check_admin_referer()) {
                    if ($this->login()) {
                        wp_redirect(admin_url('admin.php?page=spromoter&view=login'));
                        exit;
                    }
                }
            }

            require_once constant('SPROMOTER_PLUGIN_DIR') . '/includes/views/login.php';

        } else if (empty($this->settings['app_id']) && empty($this->settings['api_key']) && (isset($_GET['view']) && $_GET['view'] == 'register')) {
            if (!empty($_POST) && check_admin_referer()) {
                if ($this->register()) {
                    wp_redirect(admin_url('admin.php?page=spromoter'));
                    exit;
                }
            }

            require_once constant('SPROMOTER_PLUGIN_DIR') . '/includes/views/register.php';
        } else if (!empty($this->settings['app_id']) && !empty($this->settings['api_key'])) {
            if (!empty($_POST) && isset($_POST['submit_past_orders']) && check_admin_referer()) {
                $this->submit_past_orders();
            }

            if (!empty($_POST) && spromoter_post_unslash('type') == 'update' && check_admin_referer()) {
                $this->update_spromoter_settings();
            }

            require_once constant('SPROMOTER_PLUGIN_DIR') . '/includes/views/home.php';
        } else {
            wp_redirect(admin_url('admin.php?page=spromoter&view=login'));
            exit;
        }
    }

    /**
     * Authenticate user
     *
     * @return bool
     * @since 1.0.0
     */
    private function login(): bool
    {
        // Verify the nonce for security
        if (!wp_verify_nonce(spromoter_post_unslash( '_wpnonce_spromoter_login_form'), 'spromoter_login')) {
            add_settings_error('nonce', 'nonce_error', 'Nonce verification failed. Please try again.');
            return false;
        }

        // Sanitize the input fields
        $app_id = sanitize_text_field(spromoter_post_unslash( 'app_id'));
        $api_key = sanitize_text_field(spromoter_post_unslash( 'api_key'));

        // Check for empty fields and add error messages accordingly
        if (empty($app_id) || empty($api_key)) {
            add_settings_error('credentials', 'credentials_error', 'APP ID and API Key are required.');
            return false;
        }

        // Verify credentials with SPromoter
        $api = new Api($api_key, $app_id);
        $result = $api->send_request('stores/me');

        if (isset($result) && $result['status'] === 'success') {
            // Save the valid credentials in the settings
            $this->settings['app_id'] = $app_id;
            $this->settings['api_key'] = $api_key;
            update_option('spromoter_settings', $this->settings);

            return true;
        } elseif (isset($result) && $result['status'] === 'error') {
            // Handle specific error messages
            if ($result['message'] === 'Unauthenticated') {
                add_settings_error('api_key', 'api_key_error', 'Please enter a valid API key.');
            } else {
                add_settings_error('api_key', 'api_key_error', $result['message'] ?? 'An unknown error occurred. Please try again.');
            }

            // Handle any other errors returned by the API
            foreach ($result['errors'] ?? [] as $key => $message) {
                add_settings_error($key, $key, $message);
            }

            return false;
        } else {
            // General error handling if the API response is unexpected
            add_settings_error('api_key', 'api_key_error', 'Please enter a valid API key.');
            return false;
        }
    }

    /**
     * Register user
     *
     * @return bool
     * @since 1.0.0
     */
    private function register(): bool
    {
        wp_verify_nonce(spromoter_post_unslash( '_wpnonce'), '_wpnonce_spromoter_login_form');
        $fields = ['first_name', 'last_name', 'email', 'password', 'password_confirmation'];
        foreach ($fields as $field) {
            if (empty(spromoter_post_unslash($field))) {
                add_settings_error($field, $field, ucfirst(str_replace('_', ' ', $field)) . ' is required');
            }
        }

        // If any field is empty, return false
        if (empty(spromoter_post_unslash('first_name')) || empty(spromoter_post_unslash('last_name')) || empty(spromoter_post_unslash('email')) || empty(spromoter_post_unslash('password')) || empty(spromoter_post_unslash('password_confirmation'))) {
            return false;
        }

        // Verify credentials with SPromoter
        $api = new Api();
        $data = [
            'store_url' => get_site_url(),
            'store_name' => get_bloginfo('name'),
            'store_logo' => '',
        ];
        foreach ($fields as $field) {
            $data[$field] = spromoter_post_unslash($field);
        }

        $result = $api->send_request('auth/register', 'POST', $data);

        if (!$result['status']) {
            foreach ($result['errors'] as $key => $message) {
                add_settings_error($key, $key, $message);
            }

            return false;
        } else {
            $this->settings['app_id'] = $result['data']['app_id'];
            $this->settings['api_key'] = $result['data']['api_key'];
            update_option('spromoter_settings', $this->settings);

            return true;
        }
    }

    /**
     * Update settings
     *
     * @return void
     * @since 1.0.0
     */
    private function update_spromoter_settings(): void
    {
        wp_verify_nonce(spromoter_post_unslash( '_wpnonce'), 'spromoter_settings_form');
        $fields = ['app_id', 'api_key', 'order_status', 'review_show_in', 'disable_native_review_system', 'show_bottom_line_widget'];

        foreach ($fields as $field) {
            if (empty($_POST[$field])) {
                add_settings_error($field, $field, ucfirst(str_replace('_', ' ', $field)) . ' is required');
            }
        }

        if (empty(spromoter_post_unslash('app_id')) || empty(spromoter_post_unslash( 'api_key') || empty(spromoter_post_unslash('order_status')) || empty(spromoter_post_unslash('review_show_in')) || empty(spromoter_post_unslash('disable_native_review_system')) || empty(spromoter_post_unslash('show_bottom_line_widget')))) {
            return;
        }

        if (!in_array(spromoter_post_unslash('order_status'), ['completed', 'processing', 'on-hold', 'canceled', 'refunded', 'failed'])) {
            add_settings_error('order_status', 'order_status', 'Order status is invalid');
        }

        if (!in_array(spromoter_post_unslash('review_show_in'), ['tab', 'footer'])) {
            add_settings_error('review_show_in', 'review_show_in', 'Review show in is invalid');
        }

        $settings = array_merge($this->settings, [
            'app_id' => spromoter_post_unslash('app_id'),
            'api_key' => spromoter_post_unslash('api_key'),
            'order_status' => spromoter_post_unslash('order_status'),
            'review_show_in' => spromoter_post_unslash('review_show_in'),
            'disable_native_review_system' => spromoter_post_unslash('disable_native_review_system'),
            'show_bottom_line_widget' => spromoter_post_unslash('show_bottom_line_widget'),
        ]);

        update_option('spromoter_settings', $settings);

        add_settings_error('spromoter_messages', 'spromoter_messages', 'Settings updated successfully.', 'updated');

    }

    /**
     * Submit order
     *
     * @param $order_id
     * @return void
     * @since 1.0.0
     */
    public function submit_order($order_id)
    {
        $order = wc_get_order($order_id);
        if ($order->get_status() != $this->settings['order_status']) {
            return;
        }

        $orders = new Order();
        $order = $orders->submit_order_data($order);

        $api = new Api($this->settings['api_key'], $this->settings['app_id']);

        $result = $api->send_request('orders', 'POST', $order);

        if (isset($result['status']) && $result['status'] == 'success') {
            add_settings_error('spromoter_messages', 'submit_order', 'Order is submitted successfully.', 'updated');
        } else {
            add_settings_error('spromoter_messages', 'submit_order', $result['message']);
        }
    }

    /**
     * Submit past orders
     *
     * @return void
     * @since 1.0.0
     */
    private function submit_past_orders()
    {
        $orders = new Order();

        $orders = $orders->get_past_orders_data();

        $api = new Api($this->settings['api_key'], $this->settings['app_id']);

        $result = $api->send_request('orders/bulk', 'POST', [
            'orders' => $orders,
        ]);

        if (!$result['status']) {
            add_settings_error('spromoter_messages', 'submit_past_orders', $result['message']);
        } else {
            add_settings_error('spromoter_messages', 'submit_past_orders', 'Past orders is submitted successfully.', 'updated');
        }
    }

    /**
     * Enqueue scripts and styles
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('spromoter', constant('SPROMOTER_PLUGIN_URL') . '/assets/css/spromoter.css', [], constant('SPROMOTER_PLUGIN_VERSION'));
    }
}

new Setting();