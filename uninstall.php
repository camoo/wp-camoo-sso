<?php

/**
 * Uninstalling Camoo.Hosting SSO
 *
 * @version 1.0.0
 *
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

use WP_CAMOO\SSO\Services\RewriteService;

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('wp_camoo_sso_options');
delete_option('wp_camoo_sso_db_version');

$role = get_role('administrator');
if ($role instanceof WP_Role) {
    $role->remove_cap('camoo_sso');
}
RewriteService::flushRewriteRules();
