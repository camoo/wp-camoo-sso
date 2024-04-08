<?php

declare(strict_types=1);
/**
 * Plugin Name: CAMOO SSO
 * Plugin URI:  https://github.com/camoo/wp-camoo-sso
 * Description: Camoo.Hosting Single sign On for Managed WordPress sites
 * Version:     1.5.4
 * Author:      CAMOO SARL
 * Author URI:  https://www.camoo.hosting/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: camoo-sso
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

use WP_CAMOO\SSO\Bootstrap;

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

require_once plugin_dir_path(__FILE__) . 'src/Bootstrap.php';
(new Bootstrap())->initialize();
