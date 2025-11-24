<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\Service\Guide;

use Modules\Hunting\Application\DTO\CreateGuideDTO;
use Modules\Hunting\Application\DTO\UpdateGuideDTO;
use Modules\Hunting\Application\Metrics\GuideMetricsInterface;
use Modules\Hunting\Application\Metrics\GuideWriteOperation;
use Modules\Hunting\Application\Metrics\GuideWriteResult;
use Modules\Hunting\Domain\Collection\GuideCollection;
use Modules\Hunting\Domain\Entity\Guide;
use Modules\Hunting\Domain\Exception\GuideNotFound;
use Throwable;

/**
 * Декоратор над GuideService.
 *
 * Добавляет бизнес-метрики для операций записи
 */
final readonly class GuideServiceMetricsDecorator implements GuideServiceInterface
{
    public function __construct(
        private GuideServiceInterface $inner,
        private GuideMetricsInterface $metrics
    ) {
    }

    public function getById(int $id): Guide
    {
        return $this->inner->getById($id);
    }

    public function getAll(): GuideCollection
    {
        return $this->inner->getAll();
    }

    public function findActive(?int $minExperience = null): GuideCollection
    {
        return $this->inner->findActive($minExperience);
    }

    /**
     * @throws Throwable
     */
    public function create(CreateGuideDTO $dto): Guide
    {
        $start = microtime(true);
        $result = GuideWriteResult::Success;

        try {
            return $this->inner->create($dto);
        } catch (Throwable $e) {
            $result = GuideWriteResult::UnexpectedError;
            throw $e;
        } finally {
            $duration = microtime(true) - $start;
            $this->metrics->observeWrite(
                operation: GuideWriteOperation::Create,
                result: $result,
                durationSeconds: $duration
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function update(int $id, UpdateGuideDTO $dto): Guide
    {
        $start = microtime(true);
        $result = GuideWriteResult::Success;

        try {
            return $this->inner->update($id, $dto);
        } catch (GuideNotFound $e) {
            $result = GuideWriteResult::NotFound;
            throw $e;
        } catch (Throwable $e) {
            $result = GuideWriteResult::UnexpectedError;
            throw $e;
        } finally {
            $duration = microtime(true) - $start;
            $this->metrics->observeWrite(
                operation: GuideWriteOperation::Update,
                result: $result,
                durationSeconds: $duration
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function delete(int $id): void
    {
        $start = microtime(true);
        $result = GuideWriteResult::Success;

        try {
            $this->inner->delete($id);
        } catch (GuideNotFound $e) {
            $result = GuideWriteResult::NotFound;
            throw $e;
        } catch (Throwable $e) {
            $result = GuideWriteResult::UnexpectedError;
            throw $e;
        } finally {
            $duration = microtime(true) - $start;
            $this->metrics->observeWrite(
                operation: GuideWriteOperation::Delete,
                result: $result,
                durationSeconds: $duration
            );
        }
    }
}
