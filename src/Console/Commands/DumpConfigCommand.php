<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

use MakiseCo\Config\ConfigRepositoryInterface;
use Symfony\Component\Console\Input\InputArgument;

class DumpConfigCommand extends AbstractCommand
{
    protected string $name = 'config:dump';
    protected string $description = 'Show app configuration';

    protected array $options = [
        ['path', InputArgument::OPTIONAL, 'Specific config path', null],
    ];

    public function handle(ConfigRepositoryInterface $config): void
    {
        $path = $this->input->getArgument('path');

        if (null === $path) {
            dump($config->toArray());
        } else {
            dump($config->get($path));
        }
    }
}
