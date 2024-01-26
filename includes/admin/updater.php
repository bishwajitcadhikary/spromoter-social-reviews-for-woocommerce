<?php

namespace WovoSoft\SPromoter\Admin;

use stdClass;
use WovoSoft\SPromoter\Admin;

class Updater
{

    public $plugin_slug;
    public $version;
    public $cache_key;
    public $cache_allowed;
    public $base_url = 'https://api.spromoter.com';

    /**
     * @var Updater
     */
    private static $_instance;


    /**
     * Main Updater Instance.
     *
     * Ensures only one instance of Updater is loaded or can be loaded.
     *
     * @return Updater
     */
    public static function instance(): Updater
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {

        if (defined('WP_SPROMOTER_DEV_MODE')) {
            add_filter('https_ssl_verify', '__return_false');
            add_filter('https_local_ssl_verify', '__return_false');
            add_filter('http_request_host_is_external', '__return_true');
            $this->base_url = 'http://api.spromoter.test';
        }

        $this->plugin_slug = SP_PLUGIN_TEXT_DOMAIN;
        $this->version = SP_PLUGIN_VERSION;
        $this->cache_key = 'spromoter_updater';
        $this->cache_allowed = !defined('WP_SPROMOTER_DEV_MODE');

        add_filter('plugins_api', [$this, 'info'], 20, 3);
        add_filter('site_transient_update_plugins', [$this, 'update']);
        add_action('upgrader_process_complete', [$this, 'purge'], 10, 2);
        add_action('admin_notices', [$this, 'available_notice']);
    }

    public function request()
    {
        $remote = get_transient($this->cache_key);

        if (!$remote || !$this->cache_allowed) {

            $remote = wp_remote_get($this->base_url . '/v1/update/wordpress/latest-version-info', [
                    'timeout' => 10,
                    'headers' => [
                        'Accept' => 'application/json'
                    ]
                ]
            );

            if (is_wp_error($remote) || 200 !== wp_remote_retrieve_response_code($remote) || empty(wp_remote_retrieve_body($remote))) {
                return false;
            }

            set_transient($this->cache_key, $remote, DAY_IN_SECONDS);

        }

        return json_decode(wp_remote_retrieve_body($remote));
    }

    function info($response, $action, $args)
    {

        // do nothing if you're not getting plugin information right now
        if ('plugin_information' !== $action) {
            return $response;
        }

        // do nothing if it is not our plugin
        if (empty($args->slug) || $this->plugin_slug !== $args->slug) {
            return $response;
        }

        // get updates
        $remote = $this->request();

        if (!$remote) {
            return $response;
        }

        $response = new stdClass();

        $response->name = $remote->name;
        $response->slug = $remote->slug;
        $response->version = $remote->version;
        $response->tested = $remote->tested;
        $response->requires = $remote->requires;
        $response->author = $remote->author;
        $response->author_profile = $remote->author_profile;
        $response->donate_link = $remote->donate_link;
        $response->homepage = $remote->homepage;
        $response->download_link = $remote->download_url;
        $response->trunk = $remote->download_url;
        $response->requires_php = $remote->requires_php;
        $response->last_updated = $remote->last_updated;

        $response->sections = [
            'description' => $remote->sections->description,
            'installation' => $remote->sections->installation,
            'changelog' => $remote->sections->changelog
        ];

        if (!empty($remote->banners)) {
            $response->banners = [
                'low' => $remote->banners->low,
                'high' => $remote->banners->high
            ];
        }

        return $response;

    }

    public function update($transient)
    {

        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = $this->request();

        if ($remote && version_compare($this->version, $remote->version, '<') && version_compare($remote->requires, get_bloginfo('version'), '<=') && version_compare($remote->requires_php, PHP_VERSION, '<')) {
            $this->setTransient($remote, $transient);
        }

        return $transient;

    }

    public function upgrade()
    {
        $remote = $this->request();

        if (!$remote) {
            return false;
        }

        if (version_compare($this->version, $remote->version, '<') && version_compare($remote->requires, get_bloginfo('version'), '<=') && version_compare($remote->requires_php, PHP_VERSION, '<')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            $upgrader = new \Plugin_Upgrader();

            $transient = get_site_transient('update_plugins');
            $transient = $this->setTransient($remote, $transient);
            set_site_transient('update_plugins', $transient);

            $result = $upgrader->upgrade(SP_PLUGIN_BASENAME);

            if (!$result instanceof \WP_Error) {
                activate_plugin(SP_PLUGIN_BASENAME);
            }

            echo sprintf('<a href="%s" class="button">%s</a>', admin_url('admin.php?page=spromoter'), __('Go to spromoter settings', SP_PLUGIN_TEXT_DOMAIN));
            exit();
        }else{
            wp_redirect(admin_url('admin.php?page=spromoter'));
            exit;
        }
    }

    /**
     * @param $remote
     * @param $transient
     * @return mixed
     */
    public function setTransient($remote, $transient)
    {
        $response = new stdClass();
        $response->slug = $this->plugin_slug;
        $response->plugin = SP_PLUGIN_BASENAME;
        $response->new_version = $remote->version;
        $response->tested = $remote->tested;
        $response->package = $remote->download_url;

        $transient->response[$response->plugin] = $response;

        return $transient;
    }

    public function purge($updater, $options)
    {

        if ($this->cache_allowed && 'update' === $options['action'] && 'plugin' === $options['type']) {
            // just clean the cache when new plugin version is installed
            delete_transient($this->cache_key);
        }

    }

    public function available_notice()
    {
        $update_info = $this->request();

        if ($update_info && version_compare($this->version, $update_info->version, '<') && !isset($_GET['action'])){
            $notice = sprintf(
                '<div class="notice notice-info is-dismissible">
                        <p>%s %s</p>
                        <p><a class="button-primary" href="%s">%s</a></p>
                    </div>',
                __('A new version of the SPromoter Social Reviews for WooCommerce is available:', SP_PLUGIN_TEXT_DOMAIN),
                esc_html($update_info->version),
                wp_nonce_url(admin_url('admin.php?page=spromoter&action=update_plugin'), 'update_plugin_nonce'),
                __('Update Now', SP_PLUGIN_TEXT_DOMAIN)
            );

            echo $notice;
        }
    }
}

Updater::instance();