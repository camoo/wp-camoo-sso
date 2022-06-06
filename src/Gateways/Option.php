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
     * @param string|null $setting_name setting name
     *
     * @return mixed|string
     */
    public function get(?string $setting_name = null)
    {
        if (null === $setting_name) {
            $setting_name = self::MAIN_SETTING_KEY;
        }

        return get_option($setting_name);
    }

    public function add(string $option_name, $option_value): void
    {
        add_option($option_name, $option_value);
    }

    public function delete(string $name): void
    {
        delete_option($name);
    }

    public function update(string $option_name, $option_value): void
    {
        update_option($option_name, $option_value);
    }
}
