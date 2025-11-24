<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\Service\Booking;

use InvalidArgumentException;
use Modules\Hunting\Application\DTO\CreateHuntingBookingDTO;
use Modules\Hunting\Application\Metrics\BookingCreateResult;
use Modules\Hunting\Application\Metrics\BookingMetricsInterface;
use Modules\Hunting\Domain\Entity\HuntingBooking;
use Modules\Hunting\Domain\Exception\GuideAlreadyBooked;
use Modules\Hunting\Domain\Exception\GuideInactive;
use Modules\Hunting\Domain\Exception\GuideNotFound;
use Throwable;

/**
 * Декоратор над HuntingBookingService.
 * Добавляет бизнес-метрики (результат + длительность операции).
 */
final readonly class HuntingBookingServiceMetricsDecorator implements HuntingBookingServiceInterface
{
    public function __construct(
        private HuntingBookingServiceInterface $inner,
        private BookingMetricsInterface $metrics
    ) {
    }

    /**
     * @throws GuideNotFound
     * @throws GuideInactive
     * @throws GuideAlreadyBooked
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function create(CreateHuntingBookingDTO $dto): HuntingBooking
    {
        $start = microtime(true);
        $result = BookingCreateResult::Success;

        try {
            return $this->inner->create($dto);
        } catch (GuideNotFound $e) {
            $result = BookingCreateResult::GuideNotFound;
            throw $e;
        } catch (GuideInactive $e) {
            $result = BookingCreateResult::GuideInactive;
            throw $e;
        } catch (GuideAlreadyBooked $e) {
            $result = BookingCreateResult::GuideAlreadyBooked;
            throw $e;
        } catch (InvalidArgumentException $e) {
            $result = BookingCreateResult::ValidationError;
            throw $e;
        } catch (Throwable $e) {
            $result = BookingCreateResult::UnexpectedError;
            throw $e;
        } finally {
            $duration = microtime(true) - $start;
            $this->metrics->observeCreate($result, $duration);
        }
    }
}
