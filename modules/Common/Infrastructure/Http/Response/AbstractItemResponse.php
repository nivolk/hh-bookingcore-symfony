<?php

declare(strict_types=1);

namespace Modules\Common\Infrastructure\Http\Response;

/**
 * Ответ для одной сущности/объекта.
 */
abstract class AbstractItemResponse extends AbstractResponse
{
    public function toPayload(): mixed
    {
        return $this;
    }
}
