<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\Metrics;

/**
 * Результат попытки создания бронирования. Используется для метрик.
 */
enum BookingCreateResult: string
{
    case Success = 'success';
    case GuideNotFound = 'guide_not_found';
    case GuideInactive = 'guide_inactive';
    case GuideAlreadyBooked = 'guide_already_booked';
    case ValidationError = 'validation_error';
    case UnexpectedError = 'unexpected_error';
}
