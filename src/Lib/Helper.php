<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Lib;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

final class Helper
{
    private const INTERNAL_DOMAINS = ['camoo.site', 'camoo.hosting', 'camoo.cm'];

    public static function getInstance(): self
    {
        return new self();
    }

    public function isInternalDomain(string $domain): bool
    {
        return in_array(
            $this->getDomain($domain),
            self::INTERNAL_DOMAINS,
            true
        );
    }

    public function getDomain(string $url): string
    {
        $domain = parse_url(
            (strpos($url, '://') === false ? 'http://' : '') . trim($url),
            PHP_URL_HOST
        );
        if (!preg_match('/[a-z\d][a-z\d\-]{0,63}\.[a-z]{2,6}(\.[a-z]{1,2})?$/i', $domain, $match)) {
            return trim($url);
        }

        return $match[0] ?? trim($url);
    }
}
