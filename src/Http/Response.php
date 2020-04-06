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
    /**
     * @var StreamInterface
     */
    protected $content;

    public function setContent(?string $content): self
    {
        $this->content = new FakeStream($content ?: '');

        return $this;
    }

    public function getContent(): string
    {
        if (null === $this->content) {
            return '';
        }

        return $this->content->__toString();
    }

    public function withProtocolVersion($version): self
    {
        $response = clone $this;
        $response->setProtocolVersion($version);

        return $response;
    }

    /**
     * @inheritDoc
     */
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
        $response = clone $this;
        $response->headers->set($name, $value, true);

        return $response;
    }

    public function withAddedHeader($name, $value): self
    {
        $response = clone $this;
        $response->headers->set($name, $value, false);

        return $response;
    }

    public function withoutHeader($name): self
    {
        $response = clone $this;
        $response->headers->remove($name);

        return $response;
    }

    public function getBody(): StreamInterface
    {
        return $this->content;
    }

    public function withBody(StreamInterface $body): self
    {
        $response = clone $this;
        $response->content = $body;

        return $response;
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        $response = clone $this;
        $response->statusCode = (int)$code;
        $response->statusText = $reasonPhrase;

        return $response;
    }

    public function getReasonPhrase(): string
    {
        return $this->statusText;
    }
}
