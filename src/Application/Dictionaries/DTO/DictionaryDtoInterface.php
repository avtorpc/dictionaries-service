<?php

namespace App\Application\Dictionaries\DTO;

/**
 * Контракт DTO для HTTP API (выход наружу).
 */
interface DictionaryDtoInterface
{
    /**
     * Сериализация для API response.
     */
    public function toArray(): array;
}
