<?php

namespace WovoSoft\SPromoter\Admin;

class Setting
{
    protected $settings;

    public function __construct()
    {
        $this->settings = settings();
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
        $icon_url = constant('SP_PLUGIN_URL'). '/assets/images/small-logo.jpg';

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
            if (!empty($_POST) && isset($_POST['_wpnonce_spromoter_login_form']) && wp_verify_nonce($_POST['_wpnonce_spromoter_login_form'], 'spromoter_login_form') && $this->login()) {
                wp_redirect(admin_url('admin.php?page=spromoter'));
                exit;
            }

            require_once constant('SP_PLUGIN_DIR') . '/includes/views/login.php';

        } else if (empty($this->settings['app_id']) && empty($this->settings['api_key']) && (isset($_GET['view']) && $_GET['view'] == 'register')) {
            if (!empty($_POST) && $this->register()) {
                wp_redirect(admin_url('admin.php?page=spromoter'));
                exit;
            }

            require_once constant('SP_PLUGIN_DIR') . '/includes/views/register.php';
        }else if (!empty($this->settings['app_id']) && !empty($this->settings['api_key'])){
            if (!empty($_POST) && isset($_POST['export_reviews']) && $_POST['export_reviews'] ) {
                $this->export();
            }

            if (!empty($_POST) && isset($_POST['submit_past_orders']) && $_POST['submit_past_orders']) {
               $this->submit_past_orders();
            }

            if (!empty($_POST) && isset($_POST['type']) && $_POST['type'] == 'update'){
                $this->update_settings();
            }

            require_once constant('SP_PLUGIN_DIR') . '/includes/views/home.php';
        }else{
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
        if (empty($_POST['_wpnonce_spromoter_login_form']) || !wp_verify_nonce($_POST['_wpnonce_spromoter_login_form'], 'spromoter_login_form')) {
            return false;
        }

        if (empty($_POST['app_id'])) {
            add_settings_error('app_id', 'app_id', 'APP ID is required');
        }

        if (empty($_POST['api_key'])) {
            add_settings_error('api_key', 'api_key', 'API Key is required');
        }

        if (empty($_POST['app_id']) || empty($_POST['api_key'])) {
            return false;
        }

        // Verify credentials with SPromoter
        $api = new Api($_POST['api_key']);
        $result = $api->send_request('check-credentials', 'POST', array(
            'app_id' => $_POST['app_id'],
        ));

        if (isset($result['status']) && $result['status'] == 'error' || !$result) {
            if ($result['message'] == 'Unauthenticated'){
                add_settings_error('api_key', 'api_key', 'Please enter valid api key');
            }

            foreach ($result['errors'] ?? [] as $key => $message) {
                add_settings_error($key, $key, $message);
            }

            return false;
        }else{
            $this->settings['app_id'] = $_POST['app_id'];
            $this->settings['api_key'] = $_POST['api_key'];
            update_option('spromoter_settings', $this->settings);

            return true;
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
        wp_verify_nonce($_POST['_wpnonce'], 'spromoter_register_form');
        $fields = ['first_name', 'last_name', 'email', 'password', 'password_confirmation'];
        foreach ($fields as $field) {
            if (empty($_POST[$field])) {
                add_settings_error($field, $field, ucfirst(str_replace('_', ' ', $field)) . ' is required');
            }
        }

        // If any field is empty, return false
        if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['password_confirmation'])) {
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
            $data[$field] = $_POST[$field];
        }

        $result = $api->send_request('auth/register', 'POST', $data);

        if (!$result['status']) {
            foreach ($result['errors'] as $key => $message) {
                add_settings_error($key, $key, $message);
            }

            return false;
        }else{
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
    private function update_settings(): void
    {
        wp_verify_nonce($_POST['_wpnonce'], 'spromoter_settings_form');
        $fields = ['app_id', 'api_key', 'order_status', 'review_show_in', 'disable_native_review_system', 'show_bottom_line_widget'];

        foreach ($fields as $field) {
            if (empty($_POST[$field])) {
                add_settings_error($field, $field, ucfirst(str_replace('_', ' ', $field)) . ' is required');
            }
        }

        if (empty($_POST['app_id']) || empty($_POST['api_key'] || empty($_POST['order_status']) || empty($_POST['review_show_in']) || empty($_POST['disable_native_review_system']) || empty($_POST['show_bottom_line_widget']))) {
            return;
        }

        if (!in_array($_POST['order_status'], ['completed', 'processing', 'on-hold', 'canceled', 'refunded', 'failed'])) {
            add_settings_error('order_status', 'order_status', 'Order status is invalid');
        }

        if (!in_array($_POST['review_show_in'], ['tab', 'footer'])) {
            add_settings_error('review_show_in', 'review_show_in', 'Review show in is invalid');
        }

        $settings = array_merge($this->settings, [
            'app_id' => $_POST['app_id'],
            'api_key' => $_POST['api_key'],
            'order_status' => $_POST['order_status'],
            'review_show_in' => $_POST['review_show_in'],
            'disable_native_review_system' => $_POST['disable_native_review_system'],
            'show_bottom_line_widget' => $_POST['show_bottom_line_widget'],
        ]);

        update_option('spromoter_settings', $settings);

        add_settings_error('spromoter_messages', 'spromoter_messages', 'Settings updated successfully.', 'updated');

    }

    /**
     * Export reviews
     * 
     * @return void
     * @since 1.0.0
     */
    private function export()
    {
        wp_verify_nonce($_POST['_wpnonce_spromoter_export_form'], 'spromoter_export_form');

        $exporter = new Export();

        list($file_name, $error) = $exporter->export_reviews();

        if (is_null($error)){
            $exporter->download_reviews($file_name);
            exit();
        } else {
            add_settings_error('spromoter_messages', 'export_reviews', $error);
        }
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
        }else{
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
        }else{
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
        wp_enqueue_style('spromoter', constant('SP_PLUGIN_URL') . '/assets/css/spromoter.css', [], constant('SP_PLUGIN_VERSION'));
    }
}

new Setting();