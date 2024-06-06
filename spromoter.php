<?php
/**
 * SPromoter Social Reviews for WooCommerce
 *
 * @package           SPromoter
 * @author            SPromoter Social Reviews for WooCommerce
 * @copyright         2024 WovoSoft
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       SPromoter Social Reviews for WooCommerce
 * Plugin URI:        https://reviews.spromoter.com/integrations/woocommerce
 * Description:       SPromoter Social Reviews for WooCommerce helps you to collect and display reviews from your customers on your WooCommerce store.
 * Version:           1.1.6
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            SPromoter
 * Author URI:        https://spromoter.com
 * Text Domain:       spromoter-social-reviews-for-woocommerce
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined('ABSPATH') || exit;

if (!defined('SP_PLUGIN_FILE')) {
    define('SP_PLUGIN_FILE', __FILE__);
}

require_once plugin_dir_path(__FILE__) . 'includes/plugin.php';

/**
 * Load and init plugin's instance
 */
function spromoter_social_reviews_for_woocommerce() {
    if (!defined('WC_VERSION')) {
        // Show admin notice if WooCommerce is not installed.
        add_action('admin_notices', 'spromoter_missing_woocommerce_notice');
        return false;
    }

    return WovoSoft\SPromoter\Plugin::instance();
}

add_action('plugins_loaded', 'spromoter_social_reviews_for_woocommerce');

/**
 * Display an admin notice if WooCommerce is not installed
 */
function spromoter_missing_woocommerce_notice() {
    if (current_user_can('activate_plugins')) {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('SPromoter Social Reviews for WooCommerce requires WooCommerce to be installed and active.', 'spromoter-social-reviews-for-woocommerce'); ?></p>
        </div>
        <?php
    }
}
