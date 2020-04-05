<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Handler;

use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Invoker\ParameterResolver\TypeHintResolver;
use MakiseCo\Http\Request as MakiseRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class ControllerInvoker extends Invoker
{
    public function __construct(ContainerInterface $container)
    {
        $resolver = new ResolverChain([
            new TypeHintResolver(),
            new AssociativeArrayResolver(),
            new NumericArrayResolver(),
            new TypeHintContainerResolver($container),
            new DefaultValueResolver(),
        ]);

        parent::__construct($resolver, $container);
    }

    public function invoke(callable $handler, array $args, ServerRequestInterface $request)
    {
        // bind request to invoke args (PSR, Symfony, Makise)
        $args[ServerRequestInterface::class] = $request;
        $args[SymfonyRequest::class] = $request;
        $args[MakiseRequest::class] = $request;

        return $this->call($handler, $args);
    }

    public function __debugInfo()
    {
        return [];
    }
}
