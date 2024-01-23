<?php
/**
 * Install plugin
 */
namespace WovoSoft\SPromoter;

defined( 'ABSPATH' ) || exit;

/**
 * SP_Install Class.
 */
class Install
{
    public static function init()
    {
        add_action('init', array(__CLASS__, 'check_version'), 5);
    }

    /**
     * Check the plugin version and run the installer
     *
     * @return void
     */
    public static function check_version() {
        if ( version_compare( get_option( 'spromoter_social_reviews_for_woocommerce_version' ), Plugin::instance()->version, '<' ) ) {
            self::install();
        }
    }


    /**
     * Install SPromoter.
     * @return void
     * @since 1.0.0
     */
    public static function install()
    {
        if ( ! is_blog_installed() ) {
            return;
        }

        self::update_version();
    }

    /**
     * Update plugin version to current
     */
    private static function update_version()
    {
        update_option('spromoter_social_reviews_for_woocommerce_version', Plugin::instance()->version);
    }
}