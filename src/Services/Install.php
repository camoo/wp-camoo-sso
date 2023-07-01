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
    private const SETTING_LOGIN_USERS_FROM = '1.4';

    /** Default Settings */
    protected array $defaultSettings = [
        'redirect_to_dashboard' => 1,
        'sync_roles' => 1,
        'show_sso_button_login_page' => 1,
        'allow_login_account' => 1,
        'disable_username_password_login' => 0,
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

        $options = $this->option->get();
        if (empty($options)) {
            $options = [];
        }
        foreach ($this->defaultSettings as $key => $value) {
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
        $installedCamooSsoVersion = $this->option->get('wp_camoo_sso_db_version');

        if (version_compare($installedCamooSsoVersion, WP_CAMOO_SSO_VERSION, '<')) {
            $this->option->update('wp_camoo_sso_db_version', WP_CAMOO_SSO_VERSION);
            if (version_compare($installedCamooSsoVersion, self::SETTING_LOGIN_USERS_FROM, '<')) {
                $options = $this->option->get();
                $newSettings = [
                    'redirect_to_dashboard' => $options['redirect_to_dashboard'],
                    'sync_roles' => $options['sync_roles'],
                    'show_sso_button_login_page' => $options['show_sso_button_login_page'],
                    'allow_login_account' => 1,
                ];
                $this->option->update('wp_camoo_sso_options', $newSettings);
            }
        }
    }
}
