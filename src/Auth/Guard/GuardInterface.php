<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth\Guard;

use MakiseCo\Auth\AuthenticatableInterface;
use Psr\Http\Message\ServerRequestInterface;

interface GuardInterface
{
    public function authenticate(ServerRequestInterface $request): ?AuthenticatableInterface;
}
