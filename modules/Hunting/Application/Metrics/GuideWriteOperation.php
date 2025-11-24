<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\Metrics;

/**
 * Тип операции над гидом, для метрик записи.
 */
enum GuideWriteOperation: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
}
