<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Disposable;

/**
 * Disposable is inspired from C#
 * It means that objects which are implementing DisposableInterface should be freed
 *
 * Objects which are implementing DisposableInterface should be added to DisposableContainer
 * If app needs to stop, then all disposable instances, e.g. connection pools, will be disposed
 */
interface DisposableInterface
{
    public function dispose(): void;
}
