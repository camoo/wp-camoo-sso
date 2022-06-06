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
        add_shortcode('sso_button', [$this, 'single_sign_on_login_button_shortcode']);
    }

    public function modify_plugin_description(array $all_plugins): array
    {
        if (isset($all_plugins[self::PLUGIN_MAIN_FILE])) {
            $all_plugins[Bootstrap::PLUGIN_MAIN_FILE]['Description'] = sprintf(
                __(
                    'Camoo.Hosting SSO for WordPress. Check our <a target="_blank" href="%s">Managed WordPress packages</a> out for more.',
                    Bootstrap::DOMAIN_TEXT
                ),
                WP_CAMOO_SSO_SITE . '/wordpress-hosting'
            );
        }

        return $all_plugins;
    }

    public function addCamooSsoButton(): void
    {
        $options = get_option(Option::MAIN_SETTING_KEY);
        if (!empty($options['show_sso_button_login_page'])) {
            ?>
            <p style="text-align: center;
    text-transform: uppercase;
    position: relative;" class="sso-login-or"><span><?php echo __('OR', Bootstrap::DOMAIN_TEXT) ?></span></p>
            <p style="padding-bottom: 1px;
    margin: 20px auto;
    text-align: center;">
                <a style="color:#FFF; width:100%; text-align:center; margin-bottom:1em;" class="button button-primary button-large jwt-sso-button"
                   href="<?php esc_attr_e(site_url('?auth=sso')); ?>"><?php echo __('Login via Camoo.Hosting', Bootstrap::DOMAIN_TEXT)?></a>
            </p>
            <div style="clear:both;"></div>
            <?php
        }
    }

    public function single_sign_on_login_button_shortcode($atts): string
    {
        $a = shortcode_atts([
            'type' => 'primary',
            'title' => 'Login using Single Sign On',
            'class' => 'sso-button',
            'target' => '_blank',
            'text' => 'Login via Camoo.Hosting',
        ], $atts);

        return wp_kses(
            '<a class="' . esc_attr($a['class']) .
            '" href="' . site_url('?auth=sso') .
            '" title="' . esc_attr($a['title']) . '" target="' . esc_attr($a['target']) . '">' . $a['text'] . '</a>',
            [
                'a' => [
                    'href' => true,
                    'target' => true,
                    'rel' => true,
                    'id' => true,
                ],
            ]
        );
    }
}
