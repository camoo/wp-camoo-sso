<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Lib;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

use ArrayIterator;
use IteratorAggregate;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;

/**
 * Class ConstraintCollection
 *
 * @author CamooSarl
 */
final class ConstraintCollection implements Constraint, IteratorAggregate
{
    private array $container = [];

    public function add(Constraint $constraint): void
    {
        $this->container[] = $constraint;
    }

    /** @return ArrayIterator */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this);
    }

    public function assert(Token $token): void
    {
        foreach ($this->container as $constraint) {
            $constraint->assert($token);
        }
    }
}
