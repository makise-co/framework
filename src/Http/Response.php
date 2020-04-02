<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse implements ResponseInterface
{
    public function withProtocolVersion($version): self
    {
        $this->setProtocolVersion($version);

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers->allPreserveCase();
    }

    public function hasHeader($name): bool
    {
        return $this->headers->has($name);
    }

    public function getHeader($name): array
    {
        $iterator = $this->headers->getIterator();

        return (array)($iterator[$name] ?? []);
    }

    public function getHeaderLine($name): string
    {
        $value = $this->getHeader($name);
        if ([] === $value) {
            return '';
        }

        return \implode(',', $value);
    }

    public function withHeader($name, $value): self
    {
        $this->headers->replace([$name => $value]);

        return $this;
    }

    public function withAddedHeader($name, $value): self
    {
        $this->headers->add([$name => $value]);

        return $this;
    }

    public function withoutHeader($name): self
    {
        $this->headers->remove($name);

        return $this;
    }

    public function getBody()
    {
        return $this->content;
    }

    public function withBody(StreamInterface $body): self
    {
        $this->content = $body->getContents();

        return $this;
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        $this->statusCode = (int)$code;
        $this->statusText = $reasonPhrase;

        return $this;
    }

    public function getReasonPhrase(): string
    {
        return $this->statusText;
    }
}
