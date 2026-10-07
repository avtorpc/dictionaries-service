<?php

namespace App\Controller\Api\Dictionaries;

use App\Application\Dictionaries\DictionaryService;
use App\Shared\Http\Contract\DictionariesData;
use App\Shared\Http\Contract\ResponseContractInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class DictionariesController extends AbstractController
{
    public function __construct(
        private DictionaryService $service,
        private ResponseContractInterface $responseContract
    ) {}

    /**
     * Получение названий справочников
     */
    #[Route('/list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $items = $this->service->list();

        return $this->responseContract->success(
            new DictionariesData(
                total: count($items),
                items: $items,
            )
        );
    }

    /**
     * GET /dictionaries/{dictionary}?only_active=0/1
     */
    #[Route('/{dictionary}', methods: ['GET'], requirements: [
        'dictionary' => '^(?!settings$|test/error$).+'
    ])]
    public function get(string $dictionary, Request $request): JsonResponse
    {
        $onlyActive = $request->query->getBoolean('only_active', true);

        $items = $this->service->get($dictionary, $onlyActive);

        $data = array_map(
            fn($dto) => $dto->toArray(),
            $items
        );

        return $this->responseContract->success(
            new DictionariesData(
                total: count($data),
                items: $data,
            )
        );
    }
}
