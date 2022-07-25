<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Services;

use WP_CAMOO\SSO\Gateways\Option;
use WP_Role;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Class Integration
 *
 * @author CamooSarl
 */
final class Integration
{
    public static function getInstance(): self
    {
        return new self();
    }

    public function initialize(): void
    {
        add_action('plugins_loaded', [$this, 'init_actions']);
        register_activation_hook(WP_CAMOO_SSO_DIR . 'camoo-sso.php', [new Install(), 'install']);
        register_deactivation_hook(WP_CAMOO_SSO_DIR . 'camoo-sso.php', [$this, 'deactivateCamooSso']);
    }

    public function init_actions(): void
    {
        add_filter('login_body_class', [$this, 'addClassBody']);
        add_action('wp_loaded', [$this, 'registerSsoFiles']);
        add_filter('login_headertext', [$this, 'wrapLoginFormStart']);
        add_action('login_enqueue_scripts', [$this, 'provideSsoStyle']);
        add_action('login_footer', [$this, 'wrapLoginFormEnd']);
        add_action('login_init', [$this, 'disablePasswordLogin']);
    }

    public function deactivateCamooSso(): void
    {
        $role = get_role('administrator');
        if ($role instanceof WP_Role) {
            $role->remove_cap('camoo_sso');
        }
        flush_rewrite_rules();
    }

    public function wrapLoginFormEnd(): void
    {
        echo '</div></div></div></section>';
    }

    public function provideSsoStyle(): void
    {
        wp_enqueue_style(
            'camoo-sso',
            plugins_url('/assets/css/login.css', dirname(__DIR__))
        );
    }

    public function wrapLoginFormStart(): void
    {
        echo '<section class="camoo-sso-header">
                <img class="logo" src="' . WP_CAMOO_SSO_SITE . '/img/logos/logocamoo-03.png" alt="Camoo.Hosting">
	           </section>
	           <section class="assistant-card-container">
		            <div class="assistant-card card-login">
		                <div class="card-bg"></div>
		                <div class="card-bg card-weave-medium"></div>
		                <div class="card-bg card-weave-light"></div>
		                <div id="card-login" class="card-step active">
			                <div class="card-header"></div>
			                <div class="card-content">
				                <div class="card-content-inner">';
    }

    public function addClassBody(array $classes): array
    {
        $classes[] = 'camoo-sso-assistent';

        return $classes;
    }

    public function registerSsoFiles(): void
    {
        wp_register_style('camoo-sso-admin', plugins_url('/assets/css/admin.css', dirname(__DIR__)));
        wp_register_style('camoo-sso-jquery-ui', plugins_url('/assets/css/jquery-ui.css', dirname(__DIR__)));
        wp_register_script('camoo-sso-admin', plugins_url('/assets/js/admin.js', dirname(__DIR__)));
    }

    public function disablePasswordLogin(): void
    {
        $settings = get_option(Option::MAIN_SETTING_KEY);
        $canUsernameAndPasswordLogin = $settings['disable_username_password_login'] ?? 0;
        if (empty($canUsernameAndPasswordLogin)) {
            return;
        }

        if (isset($_POST['log']) || isset($_POST['user_login'])) {
            wp_die(__('There has been a critical error on this website.'), 'Login');
        }
    }
}
