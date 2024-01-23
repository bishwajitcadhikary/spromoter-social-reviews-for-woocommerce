<?php
/**
 * Admin Settings
 */

namespace WovoSoft\SPromoter\Admin;

class Settings
{
    protected $settings;

    public function __construct()
    {
        $this->settings = settings();
        add_action('admin_menu', [$this, 'add_admin_menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function add_admin_menus()
    {
        $icon_url = SP_PLUGIN_URL . '/assets/images/small-logo.jpg';

        add_menu_page('SPromoter', 'SPromoter', 'manage_options', 'spromoter', [$this, 'show_page'], $icon_url);
    }

    public function show_page()
    {
        // Show Login Page
        if (empty($this->settings['app_id']) && empty($this->settings['api_key']) && isset($_GET['view']) && $_GET['view'] == 'login') {
            if (!empty($_POST) && $this->login()) {
                wp_redirect(admin_url('admin.php?page=spromoter'));
                exit;
            }

            require_once SP_PLUGIN_DIR . '/includes/views/login.php';

        }

        // Show Register Page
        if (empty($this->settings['app_id']) && empty($this->settings['api_key']) && (isset($_GET['view']) && $_GET['view'] == 'register')) {
            if (!empty($_POST) && $this->register()) {
                wp_redirect(admin_url('admin.php?page=spromoter'));
                exit;
            }

            require_once SP_PLUGIN_DIR . '/includes/views/register.php';
        }

        // Show Settings Page
        if (!empty($this->settings['app_id']) && !empty($this->settings['api_key'])){
            if (!empty($_POST) && isset($_POST['export_reviews']) && $_POST['export_reviews'] ) {
                $this->export();
            }

            if (!empty($_POST) && isset($_POST['submit_past_orders']) && $_POST['submit_past_orders']) {
               $this->submit_past_orders();
            }

            if (!empty($_POST) && isset($_POST['type']) && $_POST['type'] == 'update' && $this->update_settings()){

            }


            require_once SP_PLUGIN_DIR . '/includes/views/home.php';
        }
    }

    private function login(): bool
    {
        wp_verify_nonce($_POST['_wpnonce'], 'spromoter_login_form');

        if (empty($_POST['app_id'])) {
            add_settings_error('app_id', 'app_id', 'APP ID is required', 'error');
        }

        if (empty($_POST['api_key'])) {
            add_settings_error('api_key', 'api_key', 'API Key is required', 'error');
        }

        if (empty($_POST['app_id']) || empty($_POST['api_key'])) {
            return false;
        }

        // Verify credentials with SPromoter
        $api = new Api($_POST['api_key']);
        $result = $api->sendRequest('check-credentials', 'POST', array(
            'app_id' => $_POST['app_id'],
        ));

        if (!$result['status']) {
            foreach ($result['errors'] as $key => $message) {
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

    private function register(): bool
    {
        wp_verify_nonce($_POST['_wpnonce'], 'spromoter_register_form');
        $fields = ['first_name', 'last_name', 'email', 'password', 'password_confirmation'];
        foreach ($fields as $field) {
            if (empty($_POST[$field])) {
                add_settings_error($field, $field, ucfirst(str_replace('_', ' ', $field)) . ' is required', 'error');
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

        $result = $api->sendRequest('auth/register', 'POST', $data);

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

    private function update_settings(): bool
    {

    }

    private function export()
    {
        wp_verify_nonce($_POST['_wpnonce'], 'spromoter_export_form');

        $exporter = new Export();

        list($file_name, $error) = $exporter->exportReviews();

        if (is_null($error)){
            $exporter->downloadReviews($file_name);
        }
    }

    private function submit_past_orders()
    {
        $pastOrder = new Orders();

        $orders = $pastOrder->prepareOrders();

        $api = new Api($this->settings['api_key'], $this->settings['app_id']);

        $result = $api->sendRequest('orders/bulk', 'POST', [
            'orders' => $orders,
        ]);

        if (!$result['status']) {
            add_settings_error('spromoter_messages', 'submit_past_orders', $result['message'], 'error');
        }else{
            add_settings_error('spromoter_messages', 'submit_past_orders', 'Past orders is submitted successfully.', 'updated');
        }
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('spromoter', SP_PLUGIN_URL . '/assets/css/spromoter.css', [], SP_PLUGIN_VERSION);
    }
}

new Settings();