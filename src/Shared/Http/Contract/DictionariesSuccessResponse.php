<?php

namespace App\Shared\Http\Contract;

final class DictionariesSuccessResponse
{
    public function __construct(
        public bool $success,
        public string $timestamp,
        public DictionariesData $data,
    ) {}
}
