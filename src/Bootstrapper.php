<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo;

use Closure;
use InvalidArgumentException;
use Throwable;

use function array_key_exists;

class Bootstrapper
{
    /**
     * @var Closure[]|array<string, Closure>
     */
    private array $inits;

    /**
     * @var Closure[]|array<string, Closure>
     */
    private array $stops;

    /**
     * @param string $service Service name (class-string or custom name)
     * @param Closure $init Service initialize callback
     * @param Closure $stop Service stop callback
     * @param bool $overwrite Allow callback overwriting?
     */
    public function addService(string $service, Closure $init, Closure $stop, bool $overwrite = false): void
    {
        if (!$overwrite && array_key_exists($service, $this->inits)) {
            throw new InvalidArgumentException("Service {$service} already defined in inits");
        }

        if (!$overwrite && array_key_exists($service, $this->stops)) {
            throw new InvalidArgumentException("Service {$service} already defined in stops");
        }

        $this->inits[$service] = $init;
        $this->stops[$service] = $stop;
    }

    /**
     * @param string[] $inits optional, services list/order that should be initialized
     *
     * @throws Throwable
     */
    public function init(array $inits = []): void
    {
        if ($inits === [null]) {
            return;
        }

        if ($inits === []) {
            // initialize all services
            foreach ($this->inits as $init) {
                $init();
            }

            return;
        }

        foreach ($inits as $service) {
            if (array_key_exists($service, $this->inits)) {
                throw new InvalidArgumentException("Service {$service} does not exists in inits map");
            }

            $this->inits[$service]();
        }
    }

    /**
     * @param string[] $stops optional, services list/order that should be stopped
     *
     * @throws Throwable
     */
    public function stop(array $stops = []): void
    {
        if ($stops === [null]) {
            return;
        }

        if ($stops === []) {
            // initialize all services
            foreach ($this->stops as $stop) {
                $stop();
            }

            return;
        }

        foreach ($stops as $service) {
            if (array_key_exists($service, $this->stops)) {
                throw new InvalidArgumentException("Service {$service} does not exists in stops map");
            }

            $this->stops[$service]();
        }
    }
}
