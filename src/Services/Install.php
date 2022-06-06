<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Services;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

use  WP_CAMOO\SSO\Gateways\Option;

/**
 * Class Install
 *
 * @author CamooSarl
 */
final class Install
{
    /** Default Settings */
    protected array $default_settings = [
        'redirect_to_dashboard' => 1,
        'client_id' => '',
        'append_sso_url' => 0,
        'append_client_id' => 0,
        'sync_roles' => 1,
        'show_sso_button_login_page' => 1,
    ];

    private ?Option $option;

    public function __construct(?Option $option = null)
    {
        $this->option = $option ?? new Option();
    }

    /** Creating plugin tables */
    public function install(): void
    {
        $this->option->add('wp_camoo_sso_db_version', WP_CAMOO_SSO_VERSION);
        $this->option->delete('wp_notification_new_wp_version');

        // Sync default options with existing options so new options are automatically added when the plugin is updated
        $options = $this->option->get();
        foreach ($this->default_settings as $key => $value) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
        }

        $this->option->update(Option::MAIN_SETTING_KEY, $options);

        $role = get_role('administrator');
        $role->add_cap('camoo_sso');

        if (is_admin()) {
            self::upgrade();
        }
    }

    /** Upgrade plugin requirements if needed */
    public function upgrade(): void
    {
        $installer_wp_camoo_sso_ver = $this->option->get('wp_camoo_sso_db_version');

        if ($installer_wp_camoo_sso_ver < WP_CAMOO_SSO_VERSION) {
            $this->option->update('wp_camoo_sso_db_version', WP_CAMOO_SSO_VERSION);
        }
    }
}
