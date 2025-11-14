<?php

declare(strict_types=1);

namespace Modules\Hunting\Domain\Collection;

use Modules\Common\Domain\Collection\AbstractTypedCollection;
use Modules\Hunting\Domain\Entity\Guide;

/**
 * @extends AbstractTypedCollection<Guide>
 */
final class GuideCollection extends AbstractTypedCollection
{
    protected function type(): string
    {
        return Guide::class;
    }
}
