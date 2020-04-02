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
use MakiseCo\Config\AppConfigInterface;

interface ApplicationInterface
{
    public function run(array $argv): int;

    public function terminate(): void;

    public function getConfig(): AppConfigInterface;

    public function getContainer(): Container;
}
