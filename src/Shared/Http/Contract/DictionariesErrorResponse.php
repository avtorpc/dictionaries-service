<?php

namespace App\Shared\Http\Contract;

final class DictionariesErrorResponse
{
    public function __construct(
        public bool $success,
        public string $timestamp,
        public DictionariesErrorData $error,
    ) {}
}
