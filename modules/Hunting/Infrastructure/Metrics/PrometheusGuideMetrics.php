<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Metrics;

use Modules\Hunting\Application\Metrics\GuideMetricsInterface;
use Modules\Hunting\Application\Metrics\GuideWriteOperation;
use Modules\Hunting\Application\Metrics\GuideWriteResult;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Histogram;

/**
 * Метрики операций над гидами (create/update/delete).
 *
 * Экспортирует метрики:
 *
 *  - hunting_guide_write_total{operation="...",result="..."}
 *      Счётчик всех попыток записи гида, разбитый по типу операции и результату
 *      (@see GuideWriteOperation)  (@see GuideWriteResult).
 *
 *  - hunting_guide_write_duration_seconds_bucket{le="...",operation="...",result="..."}
 *      Гистограмма времени выполнения операций записи гида
 *
 *  - hunting_guide_write_duration_seconds_count / _sum
 *      Метрики для расчёта среднего времени
 *
 *  - hunting_guide_write_duration_seconds_sum
 *      Метрики для расчёта верхних порогов
 */
final class PrometheusGuideMetrics implements GuideMetricsInterface
{
    private const string METRIC_NAMESPACE = 'hunting';

    private const string METRIC_SUBSYSTEM = 'guide';

    private CollectorRegistry $registry;

    /**
     * Счётчик попыток записи гида.
     */
    private ?Counter $writeCounter = null;

    /**
     * Гистограмма длительности операций записи гида.
     */
    private ?Histogram $writeDuration = null;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Фиксирует попытку операции записи над гидом.
     *
     * @throws MetricsRegistrationException
     */
    public function observeWrite(
        GuideWriteOperation $operation,
        GuideWriteResult $result,
        float $durationSeconds
    ): void {
        $this->getWriteCounter()->inc([$operation->value, $result->value]);
        $this->getWriteDuration()->observe($durationSeconds, [$operation->value, $result->value]);
    }

    /**
     * @return Counter
     * @throws MetricsRegistrationException
     */
    private function getWriteCounter(): Counter
    {
        if ($this->writeCounter === null) {
            $this->writeCounter = $this->registry->getOrRegisterCounter(
                namespace: self::METRIC_NAMESPACE,
                name: self::METRIC_SUBSYSTEM . '_write_total',
                help: 'Total number of guide write attempts grouped by operation and result.',
                labels: ['operation', 'result']
            );
        }

        return $this->writeCounter;
    }

    /**
     * Бакеты: 10ms, 50ms, 100ms, 250ms, 500ms, 1s, 2s, 5s
     *
     * @return Histogram
     * @throws MetricsRegistrationException
     */
    private function getWriteDuration(): Histogram
    {
        if ($this->writeDuration === null) {
            $this->writeDuration = $this->registry->getOrRegisterHistogram(
                namespace: self::METRIC_NAMESPACE,
                name: self::METRIC_SUBSYSTEM . '_write_duration_seconds',
                help: 'Duration of guide write operations grouped by operation and result.',
                labels: ['operation', 'result'],
                buckets: [0.01, 0.05, 0.1, 0.25, 0.5, 1.0, 2.0, 5.0]
            );
        }

        return $this->writeDuration;
    }
}
