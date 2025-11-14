<?php

declare(strict_types=1);

namespace Modules\Hunting\Domain\Repository;

use DateTimeImmutable;
use Modules\Hunting\Domain\Entity\Guide;
use Modules\Hunting\Domain\Entity\HuntingBooking;

interface BookingRepositoryInterface
{
    public function existsForGuideOnDate(Guide $guide, DateTimeImmutable $date): bool;

    public function save(HuntingBooking $booking): void;
}
