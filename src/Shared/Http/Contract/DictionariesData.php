<?php

namespace App\Shared\Http\Contract;

final class DictionariesData
{
    public function __construct(
        public int $total,
        public array $items,
    ) {}
}
