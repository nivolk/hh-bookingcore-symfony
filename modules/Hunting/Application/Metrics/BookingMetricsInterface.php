<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\Metrics;

interface BookingMetricsInterface
{
    /**
     * Фиксирует попытку создания бронирования.
     */
    public function observeCreate(BookingCreateResult $result, float $durationSeconds): void;
}
