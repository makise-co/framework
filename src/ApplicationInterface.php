<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo;

use DI\Container;

interface ApplicationInterface
{
    /**
     * @param array<string> $argv
     * @return int
     */
    public function run(array $argv): int;

    public function terminate(): void;

    public function getContainer(): Container;

    public function getAppDir(): string;

    public function getVersion(): string;
}
