<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Lib;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

use ArrayIterator;
use IteratorAggregate;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;
use Throwable;

/**
 * Class ConstraintCollection
 * Handles a collection of JWT constraints.
 */
final class ConstraintCollection implements Constraint, IteratorAggregate
{
    /** @var Constraint[] Array of Constraint objects */
    private array $constraints = [];

    /**
     * Adds a new constraint to the collection.
     *
     * @param Constraint $constraint Constraint instance to add
     */
    public function add(Constraint $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    /**
     * Returns an iterator for the constraints.
     *
     * @return ArrayIterator An iterator for Constraint objects
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->constraints);
    }

    /**
     * Assert all constraints against a given token.
     *
     * @param Token $token JWT Token to be validated
     *
     * @throws Throwable If any constraint is not satisfied
     */
    public function assert(Token $token): void
    {
        foreach ($this->constraints as $constraint) {
            $constraint->assert($token);
        }
    }
}
