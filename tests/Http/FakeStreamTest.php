<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Http;

use MakiseCo\Http\FakeStream;
use PHPUnit\Framework\TestCase;

class FakeStreamTest extends TestCase
{
    private const DEFAULT_CONTENT = 'This is a test!';

    public function testEmptyBody(): void
    {
        $stream = new FakeStream('');

        $this->assertTrue($stream->eof());
        $this->assertSame('', $stream->read(2));
    }

    public function testCrossCase(): void
    {
        $chunkedString = str_pad('', 8192 + 1, '0');

        $stream = new FakeStream($chunkedString);
        $read = 0;

        while (!$stream->eof()) {
            $content = $stream->read(8192);
            $read += strlen($content);
        }

        $this->assertSame(8192 + 1, $read);
    }

    public function testGetContents(): void
    {
        $stream = new FakeStream(self::DEFAULT_CONTENT);
        $content = $stream->getContents();

        $this->assertSame(self::DEFAULT_CONTENT, $content);
        $this->assertTrue($stream->eof());
    }

    public function testGetContentsReturnsOnlyFromIndexForward(): void
    {
        $stream = new FakeStream(self::DEFAULT_CONTENT);

        $index = 10;
        $stream->seek($index);

        $this->assertSame(substr(self::DEFAULT_CONTENT, $index), $stream->getContents());
    }
}
