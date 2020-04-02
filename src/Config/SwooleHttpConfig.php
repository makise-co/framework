<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Config;

class SwooleHttpConfig
{
    protected int $workerNum;

    public function __construct(int $workerNum)
    {
        $this->workerNum = $workerNum;
    }

    public function getWorkerNum(): int
    {
        return $this->workerNum;
    }
}
