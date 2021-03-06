<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Config;

use MakiseCo\Util\PropertyAccessorHelper;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class Repository implements ConfigRepositoryInterface
{
    /**
     * @var array<string,mixed>
     */
    protected array $items = [];

    protected PropertyAccessorInterface $accessor;

    /**
     * @var array<string,string>
     */
    protected array $keysCache = [];

    public function __construct(array $items = [])
    {
        $this->accessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidIndex()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();

        $this->items = $items;
    }

    public function get(string $key, $default = null)
    {
        $key = $this->getKey($key);

        return $this->accessor->getValue($this->items, $key) ?? $default;
    }

    public function set(string $key, $value): void
    {
        $key = $this->getKey($key);

        $this->accessor->setValue($this->items, $key, $value);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        $offset = $this->getKey($offset);

        return $this->accessor->isReadable($this->items, $offset);
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        $this->set($offset, null);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    protected function getKey(string $key): string
    {
        $value = $this->keysCache[$key] ?? null;
        if (null !== $value) {
            return $value;
        }

        $newKey = PropertyAccessorHelper::fromDotNotation($key);

        $this->keysCache[$key] = $newKey;

        return $newKey;
    }
}
