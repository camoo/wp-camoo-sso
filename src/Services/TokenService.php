<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Services;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use WP_CAMOO\SSO\Lib\ConstraintCollection;
use WP_CAMOO\SSO\Lib\Helper;
use WP_CAMOO\SSO\Lib\JwtEmptyInMemory;

final class TokenService
{
    private const HELP_DASHBOARD = 'https://hpanel.camoo.hosting';

    private string $code;

    private ?Token $token = null;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public static function getConfiguration(): Configuration
    {
        $oSigner = new Sha256();
        $key = InMemory::file(self::getPublicKeyPath());
        $configuration = Configuration::forAsymmetricSigner(
            $oSigner,
            JwtEmptyInMemory::default(),
            $key,
        );

        $configuration->setValidationConstraints(self::getConstraints($oSigner, $key));

        return $configuration;
    }

    public function validate(): bool
    {
        $config = self::getConfiguration();
        try {
            $this->token = $config->parser()->parse($this->code);
            assert($this->token instanceof UnencryptedToken);

            $constraints = $config->validationConstraints();

            $isValid = $config->validator()->validate($this->token, ...$constraints);
        } catch (RequiredConstraintsViolated $exception) {
            $isValid = false;
        }

        return $isValid;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    private static function getPermittedSiteUrl(): string
    {
        $siteUrl = site_url();

        return Helper::getInstance()->isInternalDomain($siteUrl) ? site_url('', 'https') : $siteUrl;
    }

    private static function getConstraints(Sha256 $oSigner, InMemory $key): ConstraintCollection
    {
        $constraint = new ConstraintCollection();
        $constraint->add(new SignedWith($oSigner, $key));
        $constraint->add(new LooseValidAt(new SystemClock(new DateTimeZone('UTC'))));
        $constraint->add(new IssuedBy(WP_CAMOO_SSO_SITE, self::HELP_DASHBOARD));
        $constraint->add(new PermittedFor(self::getPermittedSiteUrl()));

        return $constraint;
    }

    private static function getPublicKeyPath(): string
    {
        return dirname(plugin_dir_path(__FILE__), 2) . '/config/pub.pem';
    }
}
