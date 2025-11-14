<?php

declare(strict_types=1);

namespace Modules\Common\Infrastructure\Http\Response;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * Ответ для коллекций/списков элементов.
 *
 * @template TItem of ApiResponseInterface
 * @implements IteratorAggregate<int, TItem>
 */
abstract class AbstractListResponse extends AbstractResponse implements IteratorAggregate, Countable
{
    /** @var array<int, ApiResponseInterface> */
    private array $items;

    /**
     * @param iterable<TItem> $items
     */
    public function __construct(iterable $items)
    {
        $this->items = [];
        foreach ($items as $item) {
            $this->assertItemType($item);
            $this->items[] = $item;
        }
    }

    /**
     * Класс элемента, который допустим в этом списке.
     *
     * @return class-string
     */
    abstract protected static function itemClass(): string;

    /**
     * Фабрика списка из доменной коллекции/итерируемых сущностей.
     *
     * @template TDomain
     * @param iterable<TDomain|TItem> $items
     */
    public static function fromCollection(iterable $items): static
    {
        $itemClass = static::itemClass();
        $list = [];

        foreach ($items as $item) {
            if ($item instanceof $itemClass) {
                /** @var TItem $item */
                $list[] = $item;
                continue;
            }

            if (is_callable([$itemClass, 'fromEntity'])) {
                /** @var TItem $dto */
                $dto = $itemClass::fromEntity($item);
                $list[] = $dto;
                continue;
            }

            $given = is_object($item) ? $item::class : gettype($item);
            throw new InvalidArgumentException(
                "Cannot convert {$given} to {$itemClass}: missing fromEntity()"
            );
        }

        return new static($list);
    }

    private function assertItemType(ApiResponseInterface $item): void
    {
        $class = $this->itemClass();

        if (!$item instanceof $class) {
            $given = $item::class;
            throw new InvalidArgumentException("Invalid list item type: {$given}, expected {$class}");
        }
    }

    /**
     * @return Traversable<int, ApiResponseInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return array<int, ApiResponseInterface>
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return array{
     *     items: array<int, mixed>,
     *     total: int
     * }
     */
    public function toPayload(): array
    {
        return [
            'items' => array_map(
                static fn(ApiResponseInterface $item) => $item->toPayload(),
                $this->items
            ),
            'total' => $this->count(),
        ];
    }
}
