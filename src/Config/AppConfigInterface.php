<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Config;

interface AppConfigInterface
{
    public function getDirectory(): string;

    public function getName(): string;

    public function getEnv(): string;

    public function isDebug(): bool;

    public function getUrl(): string;

    public function getTimezone(): string;

    public function getLocale(): string;

    /**
     * @return string[]
     */
    public function getProviders(): array;

    /**
     * @return string[]
     */
    public function getCommands(): array;

    // TODO: Split config
    /**
     * @return string[] array of files with routes
     */
    public function getHttpRoutes(): array;

    /**
     * @return string[] an array of class names of global middleware
     */
    public function getGlobalMiddlewares(): array;
}
