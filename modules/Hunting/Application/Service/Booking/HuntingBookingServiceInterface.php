<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\Service\Booking;

use Modules\Hunting\Application\DTO\CreateHuntingBookingDTO;
use Modules\Hunting\Domain\Entity\HuntingBooking;

interface HuntingBookingServiceInterface
{
    public function create(CreateHuntingBookingDTO $dto): HuntingBooking;
}
