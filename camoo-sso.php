<?php

declare(strict_types=1);
/**
 * Plugin Name: CAMOO SSO
 * Plugin URI:  https://github.com/camoo/wp-camoo-sso
 * Description: Camoo.Hosting Single Sign-On for Managed WordPress sites.
 * Version:     1.5.8
 * Tested up to: 6.9
 * Author:      CAMOO SARL
 * Author URI:  https://www.camoo.hosting/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: camoo-sso
 * Domain Path: /includes/languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

use WP_CAMOO\SSO\Bootstrap;

defined('ABSPATH') || exit;

if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', static function () {
        echo '<div class="notice notice-error"><p>'
                . esc_html__('CAMOO SSO requires PHP 7.4 or higher.', 'camoo-sso')
                . '</p></div>';
    });

    return;
}

require_once plugin_dir_path(__FILE__) . 'src/Bootstrap.php';

add_action('plugins_loaded', static function () {
    (new Bootstrap())->initialize();
});
