<?php

declare(strict_types=1);

// Check if WordPress environment is loaded
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Load WordPress plugin functions if not already available
if (!function_exists('get_plugin_data')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Define constants for plugin URLs and directories
define('WP_CAMOO_SSO_DIR', plugin_dir_path(dirname(__FILE__)));
define('WP_CAMOO_SSO_URL', plugin_dir_url(dirname(__FILE__)));

// Retrieve plugin data
$pluginData = get_plugin_data(WP_CAMOO_SSO_DIR . 'camoo-sso.php');

// Define constants for version and admin URL
define('WP_CAMOO_SSO_VERSION', $pluginData['Version']);
define('WP_CAMOO_SSO_ADMIN_URL', get_admin_url());

// Define constant for external site URL
const WP_CAMOO_SSO_SITE = 'https://www.camoo.hosting';
