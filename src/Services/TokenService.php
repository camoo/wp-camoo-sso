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
    private string $code;

    private ?Token $token;

    public function __construct(string $code, ?Token $token = null)
    {
        $this->code = $code;
        $this->token = $token;
    }

    public static function getConfiguration(): Configuration
    {
        $oSigner = new Sha256();
        $key = InMemory::file(dirname(plugin_dir_path(__FILE__), 2) . '/config/pub.pem');
        $configuration = Configuration::forAsymmetricSigner(
            $oSigner,
            JwtEmptyInMemory::default(),
            $key,
        );

        $constraint = new ConstraintCollection();
        $signWith = new SignedWith($oSigner, $key);
        $clock = new LooseValidAt(new SystemClock(new DateTimeZone('UTC')));
        $constraint->add($signWith);
        $constraint->add($clock);
        $issuedBy = new IssuedBy(WP_CAMOO_SSO_SITE, 'https://hpanel.camoo.hosting');
        $constraint->add($issuedBy);
        $siteUrl = site_url();
        $site = !Helper::getInstance()->isInternalDomain($siteUrl) ? $siteUrl : site_url('', 'https');
        $constraint->add(new PermittedFor($site));
        $configuration->setValidationConstraints($constraint);

        return $configuration;
    }

    public function validate(): bool
    {
        $config = self::getConfiguration();

        $this->token = $config->parser()->parse($this->code);
        assert($this->token instanceof UnencryptedToken);

        $constraints = $config->validationConstraints();
        try {
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
}
