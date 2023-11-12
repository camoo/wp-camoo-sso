<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Lib;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

final class Helper
{
    private const INTERNAL_DOMAINS = ['camoo.site', 'camoo.hosting', 'camoo.cm'];

    private static ?self $instance = null;

    /** Returns the singleton instance of this class. */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Checks if the given domain is an internal domain.
     *
     * @param string $domain The domain to check.
     *
     * @return bool True if internal, false otherwise.
     */
    public function isInternalDomain(string $domain): bool
    {
        $extractedDomain = $this->getDomain($domain);

        return in_array($extractedDomain, self::INTERNAL_DOMAINS, true);
    }

    /**
     * Extracts the domain from a given URL.
     *
     * @param string $url The URL to extract the domain from.
     *
     * @return string The extracted domain or the original URL if extraction fails.
     */
    public function getDomain(string $url): string
    {
        $url = trim($url);
        // Prepend scheme if missing
        $urlWithScheme = (strpos($url, '://') === false) ? 'http://' . $url : $url;

        // Extract the domain
        $domain = parse_url($urlWithScheme, PHP_URL_HOST);

        // Validate the domain format
        if ($domain && preg_match('/[a-z\d][a-z\d\-]{0,63}\.[a-z]{2,6}(\.[a-z]{1,2})?$/i', $domain, $matches)) {
            return trim($matches[0] ?: '');
        }

        // Return the original URL if the domain extraction or validation fails
        return trim($url);
    }
}
