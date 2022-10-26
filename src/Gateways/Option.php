<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Gateways;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Class Option
 *
 * @author CamooSarl
 */
final class Option
{
    public const MAIN_SETTING_KEY = 'wp_camoo_sso_options';

    /**
     * Get the whole Plugin Options
     *
     * @param string|null $settingName setting name
     *
     * @return mixed|string
     */
    public function get(?string $settingName = null)
    {
        if (null === $settingName) {
            $settingName = self::MAIN_SETTING_KEY;
        }

        return get_option($settingName);
    }

    public function add(string $name, $value): void
    {
        add_option($name, $value);
    }

    public function delete(string $name): void
    {
        delete_option($name);
    }

    public function update(string $name, $value): void
    {
        update_option($name, $value);
    }
}
