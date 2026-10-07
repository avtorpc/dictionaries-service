<?php

namespace App\Shared\Http\Contract;

final class DictionariesErrorData
{
    public function __construct(
        public string $code,
        public string $message,
        public string $contex
    ) {}
}
