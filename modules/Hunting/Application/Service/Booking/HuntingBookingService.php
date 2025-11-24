<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\Service\Booking;

use DateTimeImmutable;
use InvalidArgumentException;
use Modules\Hunting\Application\DTO\CreateHuntingBookingDTO;
use Modules\Hunting\Domain\Entity\HuntingBooking;
use Modules\Hunting\Domain\Exception\GuideAlreadyBooked;
use Modules\Hunting\Domain\Exception\GuideInactive;
use Modules\Hunting\Domain\Exception\GuideNotFound;
use Modules\Hunting\Domain\Repository\BookingRepositoryInterface;
use Modules\Hunting\Domain\Repository\GuideRepositoryInterface;

final readonly class HuntingBookingService implements HuntingBookingServiceInterface
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepo,
        private GuideRepositoryInterface $guideRepo
    ) {
    }

    /**
     * @throws GuideNotFound|GuideInactive|GuideAlreadyBooked|InvalidArgumentException
     */
    public function create(CreateHuntingBookingDTO $dto): HuntingBooking
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dto->date);
        if (!$date || $date->format('Y-m-d') !== $dto->date) {
            throw new InvalidArgumentException('Invalid date format, expected YYYY-MM-DD');
        }

        if ($dto->participantsCount < 1 || $dto->participantsCount > 10) {
            throw new InvalidArgumentException('participants_count must be between 1 and 10');
        }

        $guide = $this->guideRepo->getById($dto->guideId);
        if (!$guide->isActive()) {
            throw new GuideInactive($guide->getId() ?? 0);
        }

        if ($this->bookingRepo->existsForGuideOnDate($guide, $date)) {
            throw new GuideAlreadyBooked($guide->getId() ?? 0, $date);
        }

        $booking = new HuntingBooking(
            tourName: $dto->tourName,
            hunterName: $dto->hunterName,
            guide: $guide,
            date: $date,
            participantsCount: $dto->participantsCount
        );

        $this->bookingRepo->save($booking);

        return $booking;
    }
}
