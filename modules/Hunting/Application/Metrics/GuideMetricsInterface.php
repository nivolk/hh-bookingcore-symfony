<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\Metrics;

interface GuideMetricsInterface
{
    /**
     * Фиксирует попытку записи гидов (create/update/delete).
     */
    public function observeWrite(
        GuideWriteOperation $operation,
        GuideWriteResult $result,
        float $durationSeconds
    ): void;
}
