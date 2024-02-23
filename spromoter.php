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
 * Version:          1.1.2
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            SPromoter
 * Author URI:        https://spromoter.com
 * Text Domain:       spromoter-social-reviews-for-woocommerce
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use WovoSoft\SPromoter\Install;
use WovoSoft\SPromoter\Plugin;

defined('ABSPATH') || exit;

if (!defined('SP_PLUGIN_FILE')) {
    define('SP_PLUGIN_FILE', __FILE__);
}

require_once plugin_dir_path(__FILE__) . 'includes/plugin.php';
require_once plugin_dir_path(__FILE__) . 'includes/install.php';


// Declare compatibility with WooCommerce features.
add_action('before_woocommerce_init', function () {
    if (class_exists(FeaturesUtil::class)) {
        FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/**
 * Load and init plugin's instance
 */
function spromoter_social_reviews_for_woocommerce()
{
    if (!defined('WC_VERSION')) {
        // Show admin notice if WooCommerce is not installed.
        missing_woocommerce_notice();
        return false;
    }

    return Plugin::instance();
}

add_action('plugins_loaded', 'spromoter_social_reviews_for_woocommerce');

function missing_woocommerce_notice()
{
    add_action('admin_notices', function () {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('SPromoter Social Reviews for WooCommerce requires WooCommerce to be installed and active.', 'spromoter-social-reviews-for-woocommerce'); ?></p>
        </div>
        <?php
    });
}