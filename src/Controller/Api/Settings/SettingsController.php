<?php

namespace App\Controller\Api\Settings;

use App\Application\Settings\FrontendSettingsService;
use App\Shared\Http\Contract\DictionariesData;
use App\Shared\Http\Contract\ResponseContractInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings')]
class SettingsController extends AbstractController
{
    public function __construct(
        private FrontendSettingsService $service,
        private ResponseContractInterface $responseContract
    ) {}

    #[Route('', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $result = $this->service->get();

        return $this->responseContract->success(
            new DictionariesData(
                total: $result['total'],
                items: $result['items'],
            )
        );
    }
}
