<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\Metrics;

/**
 * Результат операции над гидом, для метрик гидов.
 */
enum GuideWriteResult: string
{
    case Success = 'success';

    case NotFound = 'not_found';

    case UnexpectedError = 'unexpected_error';
}
