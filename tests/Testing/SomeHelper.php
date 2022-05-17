<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Testing;

trait SomeHelper
{
    private bool $someHelperBooted = false;
    private bool $someHelperStopped = false;

    public function bootSomeHelper(): void
    {
        $this->someHelperBooted = true;
    }

    public function cleanupSomeHelper(): void
    {
        $this->someHelperStopped = true;
    }
}
