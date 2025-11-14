<?php

declare(strict_types=1);

namespace Modules\Common\Domain\Collection;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * Базовая типизированная коллекция.
 *
 * @template T
 * @implements IteratorAggregate<int, T>
 * @implements Arrayable<T>
 */
abstract class AbstractTypedCollection implements IteratorAggregate, Countable, Arrayable
{
    /** @var array<int, T> */
    protected array $items;

    /**
     * @param iterable<T> $items
     */
    final public function __construct(iterable $items = [])
    {
        $arr = [];
        foreach ($items as $item) {
            $this->assertType($item);
            $arr[] = $item;
        }

        $this->items = $arr;
    }

    /**
     * Допустимый тип элементов коллекции.
     *
     * @return class-string
     */
    abstract protected function type(): string;

    private function assertType(mixed $item): void
    {
        $type = $this->type();

        if (!$item instanceof $type) {
            $given = is_object($item) ? $item::class : gettype($item);
            throw new InvalidArgumentException("Invalid item type: {$given}, expected {$type}");
        }
    }

    /**
     * @template U
     * @param iterable<U> $items
     * @return static
     */
    public static function from(iterable $items): static
    {
        return new static($items);
    }

    /**
     * @return ArrayIterator<int, T>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return array<int, T>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param callable(T):bool $predicate
     * @return static
     */
    public function filter(callable $predicate): static
    {
        $filtered = [];
        foreach ($this->items as $item) {
            if ($predicate($item)) {
                $filtered[] = $item;
            }
        }

        return new static($filtered);
    }

    /**
     * @template R
     * @param callable(T):R $mapper
     * @return array<int, R>
     */
    public function map(callable $mapper): array
    {
        $out = [];
        foreach ($this->items as $item) {
            $out[] = $mapper($item);
        }

        return $out;
    }

    /**
     * @return array<int, T>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return T|null
     */
    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }
}
