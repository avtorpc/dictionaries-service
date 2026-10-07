<?php

namespace App\Shared\Http\Contract;

use Symfony\Component\HttpFoundation\JsonResponse;

interface ResponseContractInterface
{
    public function success(
        DictionariesData $data,
        int $status = 200
    ): JsonResponse;

    public function error(
        string $code,
        string $message,
        int $status = 400,
        string $contex
    ): JsonResponse;
}
