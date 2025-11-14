<?php

declare(strict_types=1);

namespace Modules\Hunting\Domain\Exception;

use Modules\Common\Domain\Exception\ModuleRuntimeException;

final class GuideNotFound extends ModuleRuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Guide #{$id} not found");
    }
}
