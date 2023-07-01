<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Check get_plugin_data function exist
 */
if (!function_exists('get_plugin_data')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

define('WP_CAMOO_SSO_URL', plugin_dir_url(dirname(__FILE__)));
define('WP_CAMOO_SSO_DIR', plugin_dir_path(dirname(__FILE__)));

$plugin_data = get_plugin_data(WP_CAMOO_SSO_DIR . 'camoo-sso.php');

define('WP_CAMOO_SSO_VERSION', $plugin_data['Version']);
define('WP_CAMOO_SSO_ADMIN_URL', get_admin_url());
const WP_CAMOO_SSO_SITE = 'https://www.camoo.hosting';
