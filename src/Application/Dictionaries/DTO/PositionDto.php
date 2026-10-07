<?php

namespace App\Application\Dictionaries\DTO;

final class PositionDto implements DictionaryDtoInterface
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public ?string $description,
        public bool $isActive,
    ) {
    }


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}
