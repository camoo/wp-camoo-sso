<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Services;

use stdClass;
use Throwable;
use WP_CAMOO\SSO\Bootstrap;
use WP_CAMOO\SSO\Gateways\Option;
use WP_CAMOO\SSO\Lib\Helper;
use WP_User;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

class CallbackService
{
    private ?Option $options;

    /** @param Option|null $options */
    public function __construct(?Option $options = null)
    {
        $this->options = $options ?? new Option();
    }

    public function __invoke(): void
    {
        if (is_user_logged_in()) {
            wp_safe_redirect(home_url());

            return;
        }

        $sso_options = $this->options->get();

        if (!isset($_GET['code'])) {
            $this->applyRedirect();

            return;
        }

        $this->applyLogin($sso_options);
    }

    private function sanitizeObject(stdClass $userInfo): stdClass
    {
        $output = new stdClass();

        foreach ($userInfo as $param => $value) {
            if ($param === 'user_email') {
                $output->{$param} = sanitize_email($value);
            } else {
                $output->{$param} = sanitize_text_field($value);
            }
        }

        return $output;
    }

    private function applyRedirect(): void
    {
        wp_redirect(
            WP_CAMOO_SSO_SITE . '/sso/wp?aud=' . site_url(),
            302,
            'WP-' . Bootstrap::DOMAIN_TEXT . ':' . WP_CAMOO_SSO_VERSION
        );
        die;
    }

    private function validateToken(TokenService $tokenService): bool
    {
        try {
            return  $tokenService->validate();
        } catch (Throwable $exception) {
            wp_die('Single Sign On failed!! Click here to go back to the home page: <a href="' . site_url() .
                '">Home</a>');
        }
    }

    private function getUserInfo(string $userData): stdClass
    {
        return json_decode($userData);
    }

    private function applyLogin(array $sso_options): void
    {
        $code = sanitize_text_field(wp_unslash($_GET['code']));

        if (empty($code)) {
            return;
        }

        $tokenService = new TokenService($code);

        if (!$this->validateToken($tokenService)) {
            wp_die('Single Sign On failed! Click here to go back to the home page: <a href="' . site_url() .
                '">Home</a>');
        }

        $token = $tokenService->getToken();

        $roles = $token->headers()->get('roles');
        $userData = $token->claims()->get('ufo');
        $userInfo = $this->getUserInfo($userData);

        $userId = username_exists($userInfo->user_login);
        $isNew = false;

        if (!$userId && email_exists($userInfo->user_email) === false) {
            $random_password = wp_generate_password(12, false);
            $userId = wp_create_user($userInfo->user_login, $random_password, $userInfo->user_email);
            $isNew = $userId > 0;
            do_action('wpoc_user_created', $this->sanitizeObject($userInfo), 1);
        } else {
            do_action('wpoc_user_login', $this->sanitizeObject($userInfo), 1);
        }
        $this->manageLoginCookie($userInfo, $roles, !empty($sso_options['sync_roles']), $isNew);

        $user_redirect = $this->getUserRedirectUrl($sso_options);

        if (is_user_logged_in()) {
            wp_redirect($user_redirect);
            exit;
        }
        wp_die('Single Sign On Login Failed.');
    }

    private function manageLoginCookie(stdClass $userInfo, array $roles, bool $syncRoles, bool $isNew = false): void
    {
        $user = get_user_by('email', $userInfo->user_login);
        if (!$user instanceof WP_User) {
            return;
        }
        $loginUser = [
            'ID' => $user->ID,
            'display_name' => sanitize_email($userInfo->user_email),
            'nickname' => sanitize_email($userInfo->user_email),
            'first_name' => sanitize_text_field($userInfo->first_name),
            'last_name' => sanitize_text_field($userInfo->last_name),
        ];
        if (!$isNew) {
            $loginUser['user_email'] = sanitize_email($userInfo->user_email);
        }
        wp_update_user($loginUser);

        if ($syncRoles) {
            $user->set_role('');
            foreach ($roles as $role) {
                $user->add_role(sanitize_text_field($role));
            }
        }

        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
    }

    private function getUserRedirectUrl(array $sso_options)
    {
        $dashboardUrl = get_dashboard_url();
        $dashboardUrl = !Helper::getInstance()->isInternalDomain($dashboardUrl) ? $dashboardUrl :
            get_dashboard_url(0, '', 'https');

        $siteUrl = site_url();
        $site = !Helper::getInstance()->isInternalDomain($siteUrl) ? $siteUrl : site_url('', 'https');
        $user_redirect_set = !empty($sso_options['redirect_to_dashboard']) ? $dashboardUrl : $site;

        return apply_filters('wpssoc_user_redirect_url', $user_redirect_set);
    }
}
