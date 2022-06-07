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
        require_once dirname(plugin_dir_path(__FILE__)) . '/config/defines.php';
        require_once dirname(plugin_dir_path(__FILE__)) . '/vendor/autoload.php';
        Integration::getInstance()->initialize();
        RewriteService::getInstance()->initialize();
        AdminController::getInstance()->initialize();
        add_filter('all_plugins', [$this, 'modify_plugin_description']);
        add_action('login_form', [$this, 'addCamooSsoButton'], 10, 1);
        add_shortcode('sso_button', [$this, 'generateSsoButton']);
        add_action('init', [$this, 'loadTextDomain']);
    }

    public function loadTextDomain(): void
    {
        load_plugin_textdomain(
            self::DOMAIN_TEXT,
            false,
            dirname(plugin_basename(__DIR__)) . '/includes/languages'
        );
    }

    public function modify_plugin_description(array $all_plugins): array
    {
        if (isset($all_plugins[self::PLUGIN_MAIN_FILE])) {
            $all_plugins[Bootstrap::PLUGIN_MAIN_FILE]['Description'] = wp_kses(
                sprintf(
                    __(
                        'Camoo.Hosting Single Sign On for Managed WordPress site. This plugin allows you to log in to your website without password. You will no longer need to remember any password or to save systematically password on your browser. Check our <a target="_blank" href="%s">Managed WordPress packages</a> out for more.',
                        Bootstrap::DOMAIN_TEXT
                    ),
                    WP_CAMOO_SSO_SITE . '/wordpress-hosting'
                ),
                [
                    'a' => [
                        'href' => true,
                        'target' => true,
                    ],
                ]
            );
        }

        return $all_plugins;
    }

    public function addCamooSsoButton(): void
    {
        $options = get_option(Option::MAIN_SETTING_KEY);
        if (empty($options['show_sso_button_login_page'])) {
            return;
        }

        echo sprintf(
            '<p style="text-align: center;text-transform: uppercase;position: relative;" class="sso-login-or"><span>' .
                __('OR', Bootstrap::DOMAIN_TEXT) . '</span></p>
            <p style="padding-bottom: 1px;margin: 20px auto;text-align: center;">
                <a style="color:#FFF; width:%s; text-align:center; margin-bottom:1em;" class="button button-primary button-large jwt-sso-button"
                   href="%s">' . __('Login via Camoo.Hosting', Bootstrap::DOMAIN_TEXT) . '</a>
            </p>
            <div style="clear:both;"></div>',
            '100%',
            esc_attr(site_url('?auth=sso'))
        );
    }

    public function generateSsoButton(array $attributes): string
    {
        $btnAttr = shortcode_atts([
            'type' => 'primary',
            'title' => __('Login using Single Sign On', Bootstrap::DOMAIN_TEXT),
            'class' => 'sso-button',
            'target' => '_blank',
            'text' => __('Login via Camoo.Hosting', Bootstrap::DOMAIN_TEXT),
        ], $attributes);

        return wp_kses(
            '<a class="' . esc_attr($btnAttr['class']) .
            '" href="' . site_url('?auth=sso') .
            '" title="' . esc_attr($btnAttr['title']) . '" target="' . esc_attr($btnAttr['target']) . '">' .
            esc_attr($btnAttr['text']) . '</a>',
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
}
