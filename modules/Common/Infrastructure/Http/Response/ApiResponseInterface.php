<?php

declare(strict_types=1);

namespace Modules\Common\Infrastructure\Http\Response;

/**
 * Базовый контракт для всех типов ответов API.
 */
interface ApiResponseInterface
{
    public function toPayload(): mixed;
}
