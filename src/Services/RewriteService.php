<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Services;

use WP_Rewrite;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

final class RewriteService
{
    private const QUERY_VAR = 'auth';

    private const QUERY_VALUE = 'sso';

    private static ?self $instance = null;

    /** This class needs only static getInstance */
    private function __construct()
    {
    }

    public function initialize(): void
    {
        add_filter('rewrite_rules_array', [$this, 'createRewriteRules']);
        add_filter('query_vars', [$this, 'addQueryVariables']);
        add_action('template_redirect', [$this, 'interceptRedirect']);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function createRewriteRules(array $rules): array
    {
        /** @var WP_Rewrite $wp_rewrite */
        global $wp_rewrite;
        $newRule = ['auth/(.+)' => 'index.php?auth=' . $wp_rewrite->preg_index(1)];

        return $newRule + $rules;
    }

    /**
     * @param string[] $vars
     *
     * @return string[]
     */
    public function addQueryVariables(array $vars): array
    {
        $vars[] = self::QUERY_VAR;

        return $vars;
    }

    public function interceptRedirect(): void
    {
        global $wp_query;
        if ($wp_query->get(self::QUERY_VAR) && $wp_query->get(self::QUERY_VAR) === self::QUERY_VALUE) {
            $this->handleSSOCallback();
            exit;
        }
    }

    // Call this method only when necessary, such as on plugin activation or deactivation.
    public static function flushRewriteRules(): void
    {
        flush_rewrite_rules();
    }

    private function handleSSOCallback(): void
    {
        $callback = new CallbackService();
        $callback();
    }
}
