<?php

namespace App\Domain\Dictionaries;

use App\Application\Dictionaries\DTO\DictionaryDtoInterface;

/**
 * @template TDto of \App\Application\Dictionaries\DTO\DictionaryDtoInterface
 */
interface DictionaryProviderInterface
{
    public function getName(): string;

    /**
     * @return TDto[]
     */
    public function getItems(bool $onlyActive = true): array;

    /**
     * @param DictionaryDtoInterface $dto
     */
    public function toCacheArray(DictionaryDtoInterface $dto): array;

    /**
     * @return TDto
     */
    public function fromCacheArray(array $data): DictionaryDtoInterface;
}
