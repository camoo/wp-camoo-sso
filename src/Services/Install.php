<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Services;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

use WP_CAMOO\SSO\Gateways\Option;

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

    /** Handles plugin installation logic. */
    public function install(): void
    {
        $this->initializeOptions();
        $this->addAdminCapabilities();
        RewriteService::flushRewriteRules();
        if (is_admin()) {
            $this->upgrade();
        }
    }

    /** Handles plugin upgrade logic. */
    public function upgrade(): void
    {
        $installedVersion = $this->option->get('wp_camoo_sso_db_version') ?: WP_CAMOO_SSO_VERSION;

        if (version_compare($installedVersion, WP_CAMOO_SSO_VERSION, '<')) {
            $this->option->delete('wp_notification_new_wp_version');
            $this->option->update('wp_camoo_sso_db_version', WP_CAMOO_SSO_VERSION);

            if (version_compare($installedVersion, self::SETTING_LOGIN_USERS_FROM, '<')) {
                $this->upgradeSettings();
            }
        }
    }

    /** Initialize plugin options. */
    private function initializeOptions(): void
    {
        $currentOptions = $this->option->get() ?: [];
        $optionsToUpdate = array_merge($this->defaultSettings, $currentOptions);

        $this->option->update(Option::MAIN_SETTING_KEY, $optionsToUpdate);
    }

    /** Upgrade settings based on version comparison. */
    private function upgradeSettings(): void
    {
        $options = $this->option->get();
        $newSettings = [
            'redirect_to_dashboard' => $options['redirect_to_dashboard'] ?? 0,
            'sync_roles' => $options['sync_roles'] ?? 0,
            'show_sso_button_login_page' => $options['show_sso_button_login_page'] ?? 0,
            'allow_login_account' => 1,
        ];
        $this->option->update('wp_camoo_sso_options', $newSettings);
    }

    /** Add necessary capabilities to admin role. */
    private function addAdminCapabilities(): void
    {
        $role = get_role('administrator');
        if ($role === null) {
            return;
        }
        $role->add_cap('camoo_sso');
    }
}
