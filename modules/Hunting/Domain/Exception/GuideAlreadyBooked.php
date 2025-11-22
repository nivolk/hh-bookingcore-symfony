<?php

declare(strict_types=1);

namespace Modules\Hunting\Domain\Exception;

use DateTimeImmutable;
use Modules\Common\Domain\Exception\BaseDomainException;

final class GuideAlreadyBooked extends BaseDomainException
{
    public function __construct(int $id, DateTimeImmutable $date)
    {
        parent::__construct(sprintf('Guide #%d already booked on %s', $id, $date->format('Y-m-d')));
    }
}
