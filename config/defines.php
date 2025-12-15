<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Basic constants safe to load anytime:
define('WP_CAMOO_SSO_DIR', plugin_dir_path(dirname(__FILE__)));
define('WP_CAMOO_SSO_URL', plugin_dir_url(dirname(__FILE__)));
const WP_CAMOO_SSO_SITE = 'https://www.camoo.hosting';
