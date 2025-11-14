<?php

declare(strict_types=1);

namespace Modules\Common\Domain\Collection;

/**
 * Простой интерфейс для приведения к массиву
 *
 * @template T
 */
interface Arrayable
{
    /**
     * @return array<int, T>
     */
    public function toArray(): array;
}
