<?php
namespace App\Application\Dictionaries\DTO;

final class RoleDto implements DictionaryDtoInterface
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public string $nameGenitive,
        public bool $isActive
    ) {}


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'name_genitive' => $this->nameGenitive,
            'is_active' => $this->isActive,
        ];
    }
}
