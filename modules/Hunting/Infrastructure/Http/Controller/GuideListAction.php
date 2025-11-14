<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Controller;

use Modules\Common\Infrastructure\Http\Responder\ApiResponder;
use Modules\Hunting\Domain\Repository\GuideRepositoryInterface;
use Modules\Hunting\Infrastructure\Http\Request\GuidesListRequest;
use Modules\Hunting\Infrastructure\Http\Response\GuideListResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/guides', name: 'guides_list', methods: ['GET'])]
final readonly class GuideListAction
{
    public function __construct(
        private GuideRepositoryInterface $guides,
        private ApiResponder $responder
    ) {
    }

    public function __invoke(#[MapQueryString] GuidesListRequest $query): JsonResponse
    {
        $collection = $this->guides->findActive($query->minExperience);

        return $this->responder->success(
            GuideListResponse::fromCollection($collection)
        );
    }
}
