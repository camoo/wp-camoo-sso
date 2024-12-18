<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO;

if (!defined('ABSPATH')) {
    exit;
}

use WP_CAMOO\SSO\Controller\AdminController;
use WP_CAMOO\SSO\Gateways\Option;
use WP_CAMOO\SSO\Services\Integration;
use WP_CAMOO\SSO\Services\RewriteService;

/**
 * Class Bootstrap
 *
 * @author CamooSarl
 */
final class Bootstrap
{
    public const PLUGIN_MAIN_FILE = 'camoo-sso/camoo-sso.php';

    public const DOMAIN_TEXT = 'camoo-sso';

    public function initialize(): void
    {
        $this->requireDependencies();

        // Initialize services
        Integration::getInstance()->initialize();
        RewriteService::getInstance()->initialize();
        AdminController::getInstance()->initialize();

        // Register hooks
        $this->registerHooks();
    }

    /** Load plugin text domain for translations. */
    public function loadTextDomain(): void
    {
        load_plugin_textdomain(
            self::DOMAIN_TEXT,
            false,
            dirname(plugin_basename(__DIR__)) . '/includes/languages'
        );
    }

    /**
     * Modify the plugin description in the WordPress plugin list.
     *
     * @param array<string, mixed> $plugins The array of all plugins.
     *
     * @return array<string, mixed> Modified plugin array.
     */
    public function modifyPluginDescription(array $plugins): array
    {
        if (isset($plugins[self::PLUGIN_MAIN_FILE])) {
            $plugins[Bootstrap::PLUGIN_MAIN_FILE]['Description'] = wp_kses(
                sprintf(
                    esc_attr__(
                        'Camoo.Hosting Single Sign On for Managed WordPress site. This plugin allows you to log in to your website without a password. You will no longer need to remember any password or to save systematic password in your browser. Check our <a target="_blank" href="%s">Managed WordPress packages</a> out for more.',
                        'camoo-sso'
                    ),
                    esc_attr(WP_CAMOO_SSO_SITE . '/wordpress-hosting')
                ),
                [
                    'a' => [
                        'href' => true,
                        'target' => true,
                    ],
                ]
            );
        }

        return $plugins;
    }

    /** Add the Camoo SSO button to the login form. */
    public function addCamooSsoButton(): void
    {
        $options = get_option(Option::MAIN_SETTING_KEY);
        if (empty($options['show_sso_button_login_page'])) {
            return;
        }

        echo sprintf(
            '<p style="text-align: center;text-transform: uppercase;position: relative;" class="sso-login-or"><span>' .
            esc_html__('OR', 'camoo-sso') . '</span></p>
            <p style="padding-bottom: 1px;margin: 20px auto;text-align: center;">
                <a style="color:#FFF; width:%s; text-align:center; margin-bottom:1em;"
                class="button button-primary button-large jwt-sso-button"
                   href="%s">' . esc_html__('Login via Camoo.Hosting', 'camoo-sso') . '</a>
            </p>
            <div style="clear:both;"></div>',
            '100%',
            esc_url(site_url('?auth=sso'))
        );
    }

    /**
     * Generate an SSO button shortcode.
     *
     * @param array<string, mixed> $attributes Attributes for the button.
     *
     * @return string HTML for the button.
     */
    public function generateSsoButton(array $attributes): string
    {
        $btnAttr = shortcode_atts([
            'type' => 'primary',
            'title' => __('Login using Single Sign On', 'camoo-sso'),
            'class' => 'sso-button',
            'target' => '_blank',
            'text' => __('Login via Camoo.Hosting', 'camoo-sso'),
        ], $attributes);

        return wp_kses(
            sprintf(
                '<a class="%s" href="%s" title="%s" target="%s">%s</a>',
                esc_attr($btnAttr['class']),
                esc_url(site_url('?auth=sso')),
                esc_attr($btnAttr['title']),
                esc_attr($btnAttr['target']),
                esc_html($btnAttr['text'])
            ),
            [
                'a' => [
                    'href' => true,
                    'target' => true,
                    'title' => true,
                    'class' => true,
                ],
            ]
        );
    }

    private function requireDependencies(): void
    {
        $baseDir = dirname(plugin_dir_path(__FILE__));
        if (!file_exists($baseDir)) {
            return;
        }

        $dependencies = [
            $baseDir . '/vendor/autoload.php',
            $baseDir . '/config/defines.php',
        ];

        foreach ($dependencies as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

    private function registerHooks(): void
    {
        add_filter('all_plugins', [$this, 'modifyPluginDescription']);
        add_action('login_form', [$this, 'addCamooSsoButton'], 10, 1);
        add_shortcode('sso_button', [$this, 'generateSsoButton']);
        add_action('init', [$this, 'loadTextDomain']);
    }
}
