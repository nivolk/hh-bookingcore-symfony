<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Metrics;

use Modules\Hunting\Application\Metrics\BookingCreateResult;
use Modules\Hunting\Application\Metrics\BookingMetricsInterface;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Histogram;

/**
 * Бизнес-метрики бронирований.
 *
 * Экспортирует метрики:
 *
 *  - hunting_booking_create_total
 *      Счётчик всех попыток создания бронирования, разбитый по результату (@see BookingCreateResult)
 *
 *  - hunting_booking_create_duration_seconds_bucket
 *      Гистограмма длительности операции создания брони по результату (@see BookingCreateResult)
 *
 *  - hunting_booking_create_duration_seconds_count
 *       Метрики для расчёта среднего времени
 *
 *  - hunting_booking_create_duration_seconds_sum
 *       Метрики для расчёта верхних порогов
 */
final class PrometheusBookingMetrics implements BookingMetricsInterface
{
    private const string METRIC_NAMESPACE = 'app';
    private const string METRIC_SUBSYSTEM = 'booking';

    private CollectorRegistry $registry;

    /**
     * Счётчик попыток создания бронирования.
     * @var Counter|null
     */
    private ?Counter $createCounter = null;

    /**
     * Гистограмма длительности операции создания бронирования.
     * @var Histogram|null
     */
    private ?Histogram $createDuration = null;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Фиксирует попытку создания бронирования.
     * @throws MetricsRegistrationException
     */
    public function observeCreate(BookingCreateResult $result, float $durationSeconds): void
    {
        $this->getCreateCounter()->inc([$result->value]);
        $this->getCreateDuration()->observe($durationSeconds, [$result->value]);
    }

    /**
     * @return Counter
     * @throws MetricsRegistrationException
     */
    private function getCreateCounter(): Counter
    {
        if ($this->createCounter === null) {
            $this->createCounter = $this->registry->getOrRegisterCounter(
                namespace: self::METRIC_NAMESPACE,
                name: self::METRIC_SUBSYSTEM . '_create_total',
                help: 'Total number of hunting booking create attempts grouped by result.',
                labels: ['result']
            );
        }

        return $this->createCounter;
    }

    /**
     * Бакеты: 10ms, 50ms, 100ms, 250ms, 500ms, 1s, 2s, 5s
     *
     * @return Histogram
     * @throws MetricsRegistrationException
     */
    private function getCreateDuration(): Histogram
    {
        if ($this->createDuration === null) {
            $this->createDuration = $this->registry->getOrRegisterHistogram(
                namespace: self::METRIC_NAMESPACE,
                name: self::METRIC_SUBSYSTEM . '_create_duration_seconds',
                help: 'Duration of hunting booking create attempts grouped by result.',
                labels: ['result'],
                buckets: [0.01, 0.05, 0.1, 0.25, 0.5, 1.0, 2.0, 5.0]
            );
        }

        return $this->createDuration;
    }
}
