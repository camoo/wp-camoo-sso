<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Services;

use WP_CAMOO\SSO\Gateways\Option;
use WP_Role;

defined('ABSPATH') || die('You are not allowed to call this script directly!');

/**
 * Handles the integration of the SSO functionality into WordPress.
 *
 * @author CamooSarl
 */
final class Integration
{
    private const AT_LEAST_VERSION = '6.1';

    private static ?self $instance = null;

    private string $pluginPath;

    /** Class should only use static getInstance */
    private function __construct()
    {
        $this->pluginPath = WP_CAMOO_SSO_DIR . 'camoo-sso.php';
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function initialize(): void
    {
        add_action('plugins_loaded', [$this, 'initActions']);
        register_activation_hook($this->pluginPath, [new Install(), 'install']);
        register_deactivation_hook($this->pluginPath, [$this, 'deactivateCamooSso']);
        add_filter(
            'plugin_action_links_' . plugin_basename($this->pluginPath),
            [$this, 'onPluginActionLinks'],
            1,
            1
        );
    }

    public function onPluginActionLinks($links)
    {
        $link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('options-general.php?page=wp_camoo_sso_options'),
            __( 'Settings' )
        );
        array_unshift($links, $link);

        return $links;
    }

    public function initActions(): void
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
        $this->removeAdminCapabilities();
        RewriteService::flushRewriteRules();
    }

    public function wrapLoginFormEnd(): void
    {
        if (!$this->isLoginPage()) {
            return;
        }
        echo '</div></div></div></section>';
    }

    public function provideSsoStyle(): void
    {
        if (!$this->isLoginPage()) {
            return;
        }
        wp_enqueue_style(
            'camoo-sso',
            plugins_url('/assets/css/login.css', dirname(__DIR__))
        );
    }

    public function wrapLoginFormStart(): void
    {
        if (!$this->isLoginPage()) {
            return;
        }
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
        if (empty($canUsernameAndPasswordLogin) || !$this->isLoginPage()) {
            return;
        }

        add_action('login_form_login', function () {
            if (isset($_POST['log']) || isset($_POST['user_login'])) {
                wp_die(__('There has been a critical error on this website.'), 'Login');
            }
        });
    }

    private function removeAdminCapabilities(): void
    {
        $role = get_role('administrator');
        if ($role instanceof WP_Role) {
            $role->remove_cap('camoo_sso');
        }
    }

    private function isLoginPage(): bool
    {
        return !is_wp_version_compatible(self::AT_LEAST_VERSION) || is_login();
    }
}
