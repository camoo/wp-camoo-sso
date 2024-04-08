<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Services;

use Lcobucci\JWT\Token;
use stdClass;
use Throwable;
use WP_CAMOO\SSO\Bootstrap;
use WP_CAMOO\SSO\Gateways\Option;
use WP_CAMOO\SSO\Lib\Helper;
use WP_User;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

class CallbackService
{
    private const LOGIN_USER_TYPE = 'l';

    private const SITE_URL_LINK = '<a href="%s">Home</a>';

    private ?Option $options;

    public function __construct(?Option $options = null)
    {
        $this->options = $options ?? new Option();
    }

    public function __invoke(): void
    {
        if (is_user_logged_in()) {
            $this->redirect(home_url());

            return;
        }

        $options = $this->options->get();

        if (!isset($_GET['code'])) {
            $this->applyRedirect();

            return;
        }

        $this->applyLogin($options);
    }

    private function redirect(string $url): void
    {
        wp_safe_redirect($url);
        exit;
    }

    private function sanitizeCode(string $code): string
    {
        return sanitize_text_field(wp_unslash($code));
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
        exit;
    }

    private function validateToken(TokenService $tokenService): bool
    {
        try {
            return $tokenService->validate();
        } catch (Throwable $exception) {
            $this->handleLoginFailure();
        }

        return false;
    }

    private function getUserInfo(string $userData): stdClass
    {
        return json_decode($userData);
    }

    private function processToken(Token $token, array $options): void
    {
        $userType = $token->claims()->get('for');
        if ($userType === self::LOGIN_USER_TYPE && empty($options['allow_login_account'])) {
            wp_die('You are not allowed to log in to this site via Single Sign On! Click here to go back to ' .
                'the home page: ' . sprintf(self::SITE_URL_LINK, site_url()));
        }

        $roles = $token->headers()->get('roles');
        $userData = $token->claims()->get('ufo');
        $userInfo = $this->getUserInfo($userData);

        $userId = username_exists($userInfo->user_login);
        $isNew = false;

        if (!$userId && email_exists($userInfo->user_email) === false) {
            $randomPassword = wp_generate_password(12, false);
            $userId = wp_create_user($userInfo->user_login, $randomPassword, $userInfo->user_email);
            $isNew = $userId > 0;
            do_action('wpoc_user_created', $this->sanitizeObject($userInfo), 1);
        } else {
            do_action('wpoc_user_login', $this->sanitizeObject($userInfo), 1);
        }
        $this->manageLoginCookie($userInfo, $roles, !empty($options['sync_roles']), $isNew);

        $userRedirectUrl = $this->getUserRedirectUrl($options);

        if (is_user_logged_in()) {
            wp_redirect($userRedirectUrl);
            exit;
        }
        wp_die('Single Sign On Login Failed.');
    }

    private function applyLogin(array $options): void
    {
        $code = $this->sanitizeCode($_GET['code'] ?? '');

        if (empty($code)) {
            return;
        }

        $tokenService = new TokenService($code);

        if (!$this->validateToken($tokenService)) {
            $this->handleLoginFailure();
        }

        $token = $tokenService->getToken();
        $this->processToken($token, $options);
    }

    private function handleLoginFailure(): void
    {
        wp_die(
            'Single Sign On failed! Click here to go back to the home page: ' .
            sprintf(self::SITE_URL_LINK, site_url())
        );
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

    /** @return mixed|null */
    private function getUserRedirectUrl(array $options)
    {
        $dashboardUrl = get_dashboard_url();
        $dashboardUrl = !Helper::getInstance()->isInternalDomain($dashboardUrl) ? $dashboardUrl :
            get_dashboard_url(0, '', 'https');

        $siteUrl = site_url();
        $site = !Helper::getInstance()->isInternalDomain($siteUrl) ? $siteUrl : site_url('', 'https');
        $userRedirectUrl = !empty($options['redirect_to_dashboard']) ? $dashboardUrl : $site;

        return apply_filters('wpssoc_user_redirect_url', $userRedirectUrl);
    }
}
