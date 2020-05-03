<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest implements ServerRequestInterface
{
    /**
     * @var StreamInterface
     */
    protected $content;

    protected ?Uri $psrUri = null;

    public function getContent(bool $asResource = false): StreamInterface
    {
        return $this->content;
    }

    public function withProtocolVersion($version): self
    {
        $self = clone $this;
        $self->server->set('SERVER_PROTOCOL', $version);

        return $self;
    }

    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    public function hasHeader($name): bool
    {
        return $this->headers->has($name);
    }

    public function getHeader($name): array
    {
        return $this->headers->all($name);
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
        $self = clone $this;
        $self->headers->set($name, $value, true);

        return $self;
    }

    public function withAddedHeader($name, $value): self
    {
        $self = clone $this;
        $self->headers->set($name, $value, false);

        return $self;
    }

    public function withoutHeader($name): self
    {
        $self = clone $this;
        $self->headers->remove($name);

        return $self;
    }

    public function getBody(): StreamInterface
    {
        return $this->content;
    }

    public function withBody(StreamInterface $body): self
    {
        $self = clone $this;
        $self->content = $body;

        return $self;
    }

    public function getRequestTarget(): string
    {
        return $this->getRequestUri();
    }

    public function withRequestTarget($requestTarget): self
    {
        $self = clone $this;
        $self->requestUri = $requestTarget;

        return $self;
    }

    public function withMethod($method): self
    {
        $self = clone $this;
        $self->setMethod($method);

        return $self;
    }

    public function getUri(): UriInterface
    {
        if (null === $this->psrUri) {
            $this->psrUri = new Uri($this->getRequestUri());
        }

        return $this->psrUri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $self = clone $this;

        $query = $uri->getQuery();
        if ('' !== $query) {
            $query = '?' . $query;
        }

        $self->server->set('REQUEST_URI', $uri->getPath() . $query);
        $self->prepareRequestUri();

        return $self;
    }

    public function getServerParams(): array
    {
        return $this->server->all();
    }

    public function getCookieParams(): array
    {
        return $this->cookies->all();
    }

    public function withCookieParams(array $cookies): self
    {
        $self = clone $this;
        $self->cookies->replace($cookies);

        return $self;
    }

    public function getQueryParams(): array
    {
        return $this->query->all();
    }

    public function withQueryParams(array $query): self
    {
        $self = clone $this;
        $self->query->replace($query);

        return $self;
    }

    public function getUploadedFiles()
    {
        return $this->files;
    }

    public function withUploadedFiles(array $uploadedFiles): self
    {
        $self = clone $this;
        $self->files->replace($uploadedFiles);

        return $self;
    }

    public function getParsedBody()
    {
        return $this->request->all();
    }

    public function withParsedBody($data): self
    {
        $self = clone $this;
        $self->request->replace($data);

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes->all();
    }

    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    public function withAttribute($name, $value): self
    {
        $self = clone $this;
        $self->attributes->set($name, $value);

        return $self;
    }

    public function withoutAttribute($name): self
    {
        $self = clone $this;
        $self->attributes->remove($name);

        return $self;
    }
}
