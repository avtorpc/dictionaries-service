<?php

namespace App\Application\Dictionaries\DTO;

final class RoleCountryDto implements DictionaryDtoInterface
{
    public function __construct(
        public string $role,
        public string $country,
        public bool $isAvailable,
    ) {}

    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'country' => $this->country,
            'is_available' => $this->isAvailable,
        ];
    }
}
