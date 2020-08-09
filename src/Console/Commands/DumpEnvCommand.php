<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

class DumpEnvCommand extends AbstractCommand
{
    protected string $name = 'env:dump';
    protected string $description = 'Show loaded environment variables';

    public function handle(): void
    {
        foreach ($_ENV as $key => $value) {
            $this->output->writeln("<comment>{$key}</comment>=<info>{$value}</info>");
        }
    }
}
