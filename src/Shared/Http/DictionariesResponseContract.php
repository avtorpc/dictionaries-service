<?php

namespace App\Shared\Http;

use App\Shared\Http\Contract\DictionariesData;
use App\Shared\Http\Contract\DictionariesErrorData;
use App\Shared\Http\Contract\DictionariesErrorResponse;
use App\Shared\Http\Contract\DictionariesSuccessResponse;
use App\Shared\Http\Contract\ResponseContractInterface;
use App\Shared\Time\ClockInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class DictionariesResponseContract implements ResponseContractInterface
{
    public function __construct(
        private ClockInterface $clock
    ) {}

    public function success(
        DictionariesData $data,
        int $status = 200
    ): JsonResponse
    {
        $response = new DictionariesSuccessResponse(
            success: true,
            timestamp: $this->clock->nowFormatted(),
            data: $data,
        );

        return new JsonResponse($response, $status);
    }

    public function error(
        string $code,
        string $message,
        int $status = 400,
        string $contex
    ): JsonResponse
    {
        $response = new DictionariesErrorResponse(
            success: false,
            timestamp: $this->clock->nowFormatted(),
            error: new DictionariesErrorData(
                code: $code,
                message: $message,
                contex: $contex
            ),
        );

        return new JsonResponse($response, $status);
    }
}
