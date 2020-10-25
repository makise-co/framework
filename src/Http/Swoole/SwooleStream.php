<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Swoole;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Swoole\Http\Request as SwooleHttpRequest;

use function strlen;
use function substr;

use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;

/**
 * @see       https://github.com/mezzio/mezzio-swoole for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-swoole/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-swoole/blob/master/LICENSE.md New BSD License
 */
final class SwooleStream implements StreamInterface
{
    /**
     * Memoized body content, as pulled via SwooleHttpRequest::rawContent().
     *
     * @var string|null
     */
    private ?string $body = null;

    /**
     * Length of the request body content.
     *
     * @var int|null
     */
    private ?int $bodySize = null;

    /**
     * Index to which we have seek'd or read within the request body.
     *
     * @var int
     */
    private int $index = 0;

    /**
     * Swoole request containing the body contents.
     */
    private SwooleHttpRequest $request;

    public function __construct(SwooleHttpRequest $request)
    {
        $this->request = $request;
    }

    // phpcs:disable WebimpressCodingStandard.Functions.Param.MissingSpecification
    // phpcs:disable WebimpressCodingStandard.Functions.ReturnType.ReturnValue

    public function getContents(): string
    {
        // If we're at the end of the string, return an empty string.
        if ($this->eof()) {
            return '';
        }

        $size = $this->getSize();
        // If we have not content, return an empty string
        if ($size === 0) {
            return '';
        }

        // Memoize index so we can use it to get a substring later,
        // if required.
        $index = $this->index;

        // Set the internal index to the end of the string
        $this->index = $size;

        if ($index) {
            // Per PSR-7 spec, if we have seeked or read to a given position in
            // the string, we should only return the contents from that position
            // forward.
            return substr($this->body, $index);
        }

        // If we're at the start of the content, return all of it.
        return $this->body;
    }

    public function __toString(): string
    {
        $this->body !== null || $this->initRawContent();
        return $this->body;
    }

    public function getSize(): int
    {
        if (null === $this->bodySize) {
            $this->body !== null || $this->initRawContent();
            $this->bodySize = strlen($this->body);
        }
        return $this->bodySize;
    }

    public function tell(): int
    {
        return $this->index;
    }

    public function eof(): bool
    {
        return $this->index >= $this->getSize();
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read($length)
    {
        $this->body !== null || $this->initRawContent();
        $result = substr($this->body, $this->index, $length);

        // Reset index based on legnth; should not be > EOF position.
        $size = $this->getSize();
        $this->index = $this->index + $length >= $size
            ? $size
            : $this->index + $length;

        return $result;
    }

    public function isSeekable(): bool
    {
        return true;
    }

    /**
     * @psalm-return void
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        $size = $this->getSize();
        switch ($whence) {
            case SEEK_SET:
                if ($offset >= $size) {
                    throw new RuntimeException(
                        'Offset cannot be longer than content size'
                    );
                }
                $this->index = $offset;
                break;
            case SEEK_CUR:
                if ($offset + $this->index >= $size) {
                    throw new RuntimeException(
                        'Offset + current position cannot be longer than content size when using SEEK_CUR'
                    );
                }
                $this->index += $offset;
                break;
            case SEEK_END:
                if ($offset + $size >= $size) {
                    throw new RuntimeException(
                        'Offset must be a negative number to be under the content size when using SEEK_END'
                    );
                }
                $this->index = $size + $offset;
                break;
            default:
                throw new InvalidArgumentException(
                    'Invalid $whence argument provided; must be one of SEEK_CUR,'
                    . 'SEEK_END, or SEEK_SET'
                );
        }
    }

    /**
     * @psalm-return void
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string): int
    {
        throw new RuntimeException('Stream is not writable');
    }

    public function getMetadata($key = null): ?array
    {
        return $key ? null : [];
    }

    public function detach(): SwooleHttpRequest
    {
        return $this->request;
    }

    public function close(): void
    {
    }

    // phpcs:enable

    /**
     * Memoize the request raw content in the $body property, if not already done.
     */
    private function initRawContent(): void
    {
        if ($this->body) {
            return;
        }
        $this->body = $this->request->rawContent() ?: '';
    }
}
