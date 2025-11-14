<?php

declare(strict_types=1);

namespace Modules\Common\Domain\Exception;

use RuntimeException;

/**
 * Базовый класс для всех исключений в модулях.
 */
abstract class ModuleRuntimeException extends RuntimeException
{
}
