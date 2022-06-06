<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Services;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

final class RewriteService
{
    public function initialize()
    {
        add_filter('rewrite_rules_array', [$this, 'create_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_filter('wp_loaded', [$this, 'flush_rewrite_rules']);
        add_action('template_redirect', [$this, 'template_redirect_intercept']);
    }

    public static function getInstance(): self
    {
        return new self();
    }

    public function create_rewrite_rules(array $rules): array
    {
        global $wp_rewrite;
        $newRule = ['auth/(.+)' => 'index.php?auth=' . $wp_rewrite->preg_index(1)];

        return $newRule + $rules;
    }

    public function add_query_vars(array $vars): array
    {
        $vars[] = 'auth';

        return $vars;
    }

    public function flush_rewrite_rules(): void
    {
        flush_rewrite_rules();
    }

    public function template_redirect_intercept()
    {
        global $wp_query;
        if ($wp_query->get('auth') && $wp_query->get('auth') === 'sso') {
            $callback = new CallbackService();
            $callback();
            exit;
        }
    }
}
