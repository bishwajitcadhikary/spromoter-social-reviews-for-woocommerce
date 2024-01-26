<?php
namespace WovoSoft\SPromoter;

use function Sodium\add;

defined('ABSPATH') || exit;

/**
 * Main Plugin Class.
 *
 * @class Plugin
 */
final class Plugin
{
    /**
     * @var Plugin
     */
    private static $_instance;

    /**
     * SPromoter version.
     *
     * @var string
     */
    public $version;

    /**
     * Main Plugin Instance.
     *
     * Ensures only one instance of Plugin is loaded or can be loaded.
     *
     * @return Plugin
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
        _doing_it_wrong(__FUNCTION__, __('Foul!', 'spromoter-social-reviews-for-woocommerce'), $this->version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Foul!', 'spromoter-social-reviews-for-woocommerce'), $this->version);
    }

    /**
     * SPromoter Constructor.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->setPluginVersion();
        $this->define_constants();
        $this->includes();
        $this->initHooks();
    }

    /**
     * Plugin URL getter.
     *
     * @param string $path
     * @return string
     */
    public function plugin_url(string $path = '/' ): string
    {
        return untrailingslashit( plugins_url( $path, dirname( __FILE__ ) ) );

    }

    /**
     * Plugin path getter.
     *
     * @return string
     */
    public function plugin_path(): string
    {
        return untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) );
    }

    public function setPluginVersion()
    {
        $plugin_data = get_file_data( SP_PLUGIN_FILE, array( 'Version' => 'Version' ) );
        $this->version = $plugin_data['Version'];
    }

    /**
     * Plugin base name
     *
     * @return string
     */
    public function plugin_basename(): string
    {
        return dirname( plugin_basename( __FILE__ ), 2 ) . '/spromoter.php';
    }

    /**
     * Define SPromoter Constants.
     * @return void
     */
    private function define_constants()
    {
        $this->define('SP_PLUGIN_FILE', __FILE__);
        $this->define('SP_PLUGIN_DIR', $this->plugin_path());
        $this->define('SP_PLUGIN_URL', $this->plugin_url());
        $this->define('SP_PLUGIN_BASENAME', $this->plugin_basename());
        $this->define('SP_PLUGIN_VERSION', $this->version);
        $this->define('SP_PLUGIN_TEXT_DOMAIN', 'spromoter-social-reviews-for-woocommerce');
    }

    private function define(string $name, string $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     * @return void
     */
    private function includes()
    {
        include_once 'helper.php';
        include_once 'admin/api.php';

        if (is_admin()) {
            include_once 'admin/export.php';
            include_once 'admin/orders.php';
            include_once 'admin/updater.php';
            include_once 'admin/settings.php';
        } else {
            include_once 'frontend/widgets.php';
        }
    }

    /**
     * Initialize the plugin hooks.
     *
     * @return void
     */
    public function initHooks()
    {
        add_action('init', [$this, 'load_translation']);

        add_filter( 'plugin_row_meta', [$this, 'plugin_meta_links'], 10, 2 );

        register_deactivation_hook( SP_PLUGIN_FILE, [$this, 'deactivate'] );
    }

    /**
     * Load Localisation files.
     * @return void
     */
    public function load_translation()
    {
        load_plugin_textdomain( 'spromoter-social-reviews-for-woocommerce', false, dirname( plugin_basename( __FILE__ ), 2 ) . '/languages/' );
    }

    public function plugin_meta_links($links, $file)
    {
        if ($file == plugin_basename(SP_PLUGIN_FILE)) {
            $links = array_merge($links, [
                '<a href="' . admin_url('admin.php?page=spromoter') . '">' . __('Settings', 'spromoter-social-reviews-for-woocommerce') . '</a>',
                '<a href="https://reviews.spromoter.com/contact" target="_blank">' . __('Contact', 'spromoter-social-reviews-for-woocommerce') . '</a>',
                '<a href="https://github.com/bishwajitcadhikary" target="_blank">' . __('Developer', 'spromoter-social-reviews-for-woocommerce') . '</a>',
            ]);
        }

        return $links;
    }

    public function deactivate()
    {
        if (WP_DEBUG) {
            delete_option('spromoter_settings');
        }
    }
}