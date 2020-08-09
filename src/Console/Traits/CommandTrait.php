<?php
/*
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Traits;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait CommandTrait
{
    protected OutputInterface $output;
    protected InputInterface $input;

    /**
     * Check if verbosity level of output is higher or equal to VERBOSITY_VERBOSE.
     *
     * @return bool
     */
    protected function isVerbose(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * Returns the option value for a given option name.
     *
     * @param string $name
     * @return string|string[]|bool|null The option value
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException When option given doesn't exist
     */
    protected function getOption(string $name)
    {
        return $this->input->getOption($name);
    }

    /**
     * Returns the argument value for a given argument name.
     *
     * @param string $name
     * @return string|string[]|null The argument value
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException When argument given doesn't exist
     */
    protected function getArgument(string $name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool $newline Whether to add a newline
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function write($messages, bool $newline = false)
    {
        return $this->output->write($messages, $newline);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function writeln($messages)
    {
        return $this->output->writeln($messages);
    }

    /**
     * Identical to write function but provides ability to format message. Does not add new line.
     *
     * @param string $format
     * @param array ...$args
     */
    protected function sprintf(string $format, ...$args)
    {
        return $this->output->write(sprintf($format, ...$args), false);
    }

    /**
     * Writes a green labeled message to the output and adds a newline at the end.
     *
     * @param string $message
     */
    public function info(string $message): void
    {
        $this->output->writeln("<info>{$message}</info>");
    }

    /**
     * Writes a yellow message to the output and adds a newline at the end.
     *
     * @param string $message
     */
    public function warning(string $message): void
    {
        $this->output->writeln("<comment>{$message}</comment>");
    }

    /**
     * Writes a red labeled message to the output and adds a newline at the end.
     *
     * @param string $message
     */
    public function error(string $message): void
    {
        $this->output->writeln("<error>{$message}</error>");
    }

    /**
     * Table helper instance with configured header and pre-defined set of rows.
     *
     * @param array $headers
     * @param array $rows
     * @param string $style
     * @return Table
     */
    protected function makeTable(array $headers, array $rows = [], string $style = 'default'): Table
    {
        $table = new Table($this->output);

        return $table->setHeaders($headers)->setRows($rows)->setStyle($style);
    }
}
