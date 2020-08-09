<?php
/*
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class CommandOutput implements OutputInterface
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function info(string $message): void
    {
        $this->output->writeln("<info>{$message}</info>");
    }

    public function error(string $message): void
    {
        $this->output->writeln("<error>{$message}</error>");
    }

    public function warning(string $message): void
    {
        $this->output->writeln("<comment>{$message}</comment>");
    }

    public function sprintf(string $message, ...$params): void
    {
        $this->output->writeln(sprintf($message, ...$params));
    }

    public function write($messages, bool $newline = false, int $options = 0): void
    {
        $this->output->write($messages, $newline, $options);
    }

    public function writeln($messages, int $options = 0): void
    {
        $this->output->writeln($messages, $options);
    }

    public function setVerbosity(int $level): void
    {
        $this->output->setVerbosity($level);
    }

    public function getVerbosity(): int
    {
        return $this->output->getVerbosity();
    }

    public function isQuiet(): bool
    {
        return $this->output->isQuiet();
    }

    public function isVerbose(): bool
    {
        return $this->output->isVerbose();
    }

    public function isVeryVerbose(): bool
    {
        return $this->output->isVeryVerbose();
    }

    public function isDebug(): bool
    {
        return $this->output->isDebug();
    }

    public function setDecorated(bool $decorated): void
    {
        $this->output->setDecorated($decorated);
    }

    public function isDecorated(): bool
    {
        return $this->output->isDecorated();
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        $this->output->setFormatter($formatter);
    }

    public function getFormatter(): OutputFormatterInterface
    {
        return $this->output->getFormatter();
    }
}
