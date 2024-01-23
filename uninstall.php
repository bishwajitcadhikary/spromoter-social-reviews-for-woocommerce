<?php
/**
 * Uninstall plugin
 */


if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('spromoter_settings');