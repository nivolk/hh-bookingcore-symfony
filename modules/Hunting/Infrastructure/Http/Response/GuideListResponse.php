<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Response;

use Modules\Common\Infrastructure\Http\Response\AbstractListResponse;

final class GuideListResponse extends AbstractListResponse
{
    static protected function itemClass(): string
    {
        return GuideResponse::class;
    }
}
