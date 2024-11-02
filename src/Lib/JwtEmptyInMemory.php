<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Lib;

use Lcobucci\JWT\Signer\Key;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

/**
 * Class JwtEmptyInMemory
 * Handles an empty JWT key in memory.
 */
final class JwtEmptyInMemory implements Key
{
    public static function default(): self
    {
        return new self();
    }

    public function contents(): string
    {
        return '';
    }

    public function passphrase(): string
    {
        return '';
    }
}
