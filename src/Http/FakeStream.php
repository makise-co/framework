<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function strlen;
use function substr;

use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;

/**
 * FakeStream is used to provide more PSR compatibility
 */
class FakeStream implements StreamInterface
{
    /**
     * Memoized body content, as pulled via SwooleHttpRequest::rawContent().
     *
     * @var string
     */
    private string $body;

    /**
     * Length of the request body content.
     *
     * @var int
     */
    private int $bodySize;

    /**
     * Index to which we have seek'd or read within the request body.
     *
     * @var int
     */
    private int $index = 0;

    public function __construct(string $body)
    {
        $this->body = $body;
        $this->bodySize = strlen($this->body);
    }

    /**
     * {@inheritdoc}
     */
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
        $this->index = $size - 1;

        if ($index) {
            // Per PSR-7 spec, if we have seeked or read to a given position in
            // the string, we should only return the contents from that position
            // forward.
            return substr($this->body, $index);
        }

        // If we're at the start of the content, return all of it.
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->body;
    }

    public function getSize(): int
    {
        return $this->bodySize;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return $this->index >= $this->getSize() - 1;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $result = substr($this->body, $this->index, $length);

        // Reset index based on legnth; should not be > EOF position.
        $size = $this->getSize();
        $this->index = $this->index + $length >= $size
            ? $size - 1
            : $this->index + $length;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
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
                $this->index = ($size - 1) + $offset;
                break;
            default:
                throw new InvalidArgumentException(
                    'Invalid $whence argument provided; must be one of SEEK_CUR,'
                    . 'SEEK_END, or SEEK_SET'
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        throw new RuntimeException('Stream is not writable');
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null): ?array
    {
        return $key ? null : [];
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $body = $this->body;
        $this->body = '';

        $stream = \fopen('php://memory', 'wb+');
        \fwrite($stream, $body, $this->bodySize);
        \rewind($stream);

        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
    }
}
