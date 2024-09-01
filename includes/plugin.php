<?php

namespace KinDigi\SPromoter;

/**
 * Main Plugin Class.
 *
 * @class Plugin
 */
final class Plugin
{
    /**
     * @var Plugin|null The single instance of the class
     * @since 1.0.0
     */
    private static ?Plugin $_instance = null;

    /**
     * Main Plugin Instance.
     *
     * Ensures only one instance of Plugin is loaded or can be loaded.
     *
     * @return Plugin
     * @since 1.0.0
     */
    public static function instance(): Plugin
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, esc_html__('Foul!', 'spromoter-social-reviews-for-woocommerce'), esc_html(constant('SP_PLUGIN_VERSION')));
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, esc_html__('Foul!', 'spromoter-social-reviews-for-woocommerce'), esc_html(constant('SP_PLUGIN_VERSION')));
    }

    /**
     * SPromoter Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->define_constants();
        $this->include_files();
        $this->init_hooks();
    }

    /**
     * Define SPromoter Constants.
     *
     * @return void
     * @since 1.0.0
     */
    private function define_constants(): void
    {
        $this->define('SP_DEBUG', $this->is_dev_mode());
        $this->define('SP_PLUGIN_DIR', $this->plugin_path());
        $this->define('SP_PLUGIN_URL', $this->plugin_url());
        $this->define('SP_PLUGIN_BASENAME', $this->plugin_basename());
        $this->define('SP_PLUGIN_VERSION', $this->plugin_version());
        $this->define('SP_PLUGIN_TEXT_DOMAIN', $this->plugin_text_domain());
        $this->define('SP_API_URL', $this->api_url());
    }

    /**
     * Check if the plugin is in development mode.
     *
     * @return bool The value of the constant if it is defined, and false otherwise.
     * @since 1.0.0
     */
    public function is_dev_mode(): bool
    {
        return defined('SP_DEBUG') && constant('SP_DEBUG');
    }

    /**
     * Plugin path getter.
     *
     * @return string Plugin path
     * @since 1.0.0
     */
    public function plugin_path(): string
    {
        return untrailingslashit(plugin_dir_path(dirname(__FILE__)));
    }

    /**
     * Plugin URL getter.
     *
     * @param string $path Path to append to the URL
     * @return string Plugin URL
     * @since 1.0.0
     */
    public function plugin_url(string $path = '/'): string
    {
        return untrailingslashit(plugins_url($path, dirname(__FILE__)));
    }

    /**
     * Plugin base name
     *
     * @return string
     * @since 1.0.0
     */
    public function plugin_basename(): string
    {
        return dirname(constant('SP_PLUGIN_FILE'), 2) . '/spromoter.php';
    }

    /**
     * Get the plugin version.
     *
     * @return string
     * @since 1.0.0
     */
    public function plugin_version(): string
    {
        $plugin_data = get_file_data(SP_PLUGIN_FILE, array('Version' => 'Version'));
        return $plugin_data['Version'];
    }

    /**
     * Get the plugin text domain.
     *
     * @return string
     * @since 1.0.0
     */
    private function plugin_text_domain(): string
    {
        $plugin_data = get_file_data(SP_PLUGIN_FILE, array('Text Domain' => 'Text Domain'));
        return $plugin_data['Text Domain'];
    }

    /**
     * Get the API URL.
     *
     * @return string
     * @since 1.0.0
     */
    private function api_url(): string
    {
        return $this->is_dev_mode() ? 'https://api.spromoter.test/v1/' : 'https://api.spromoter.com/v1/';
    }

    /**
     * Define a constant if it is not already defined.
     *
     * @param string $name
     * @param string $value
     * @return void
     * @since 1.0.0
     */
    private function define(string $name, string $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     *
     * @return void
     * @since 1.0.0
     */
    private function include_files()
    {
        include_once 'helper.php';
        include_once 'admin/api.php';

        if (is_admin()) {
            include_once 'admin/order.php';
            include_once 'admin/setting.php';
        } else {
            include_once 'frontend/widget.php';
        }
    }

    /**
     * Initialize the plugin hooks.
     *
     * @return void
     * @since 1.0.0
     */
    public function init_hooks()
    {
        add_action('init', [$this, 'load_translation']);

        add_filter('plugin_row_meta', [$this, 'plugin_meta_links'], 10, 2);

        register_deactivation_hook(SP_PLUGIN_FILE, [$this, 'deactivate']);
    }

    /**
     * Load Localisation files.
     *
     * @return void
     * @since 1.0.0
     */
    public function load_translation()
    {
        load_plugin_textdomain('spromoter-social-reviews-for-woocommerce', false, dirname(plugin_basename(__FILE__), 2) . '/languages/');
    }

    /**
     * Add settings link to plugin page.
     *
     * @param $links
     * @param $file
     * @return array|mixed
     * @since 1.0.0
     */
    public function plugin_meta_links($links, $file)
    {
        if ($file == plugin_basename(SP_PLUGIN_FILE)) {
            $links = array_merge($links, [
                '<a href="' . admin_url('admin.php?page=spromoter') . '">' . __('Settings', 'spromoter-social-reviews-for-woocommerce') . '</a>',
            ]);
        }

        return $links;
    }

    /**
     * Deactivate the plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function deactivate()
    {
        delete_option('spromoter_settings');
    }
}