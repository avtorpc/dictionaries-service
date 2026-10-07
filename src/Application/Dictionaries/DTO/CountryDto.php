<?php
namespace App\Application\Dictionaries\DTO;

class CountryDto implements DictionaryDtoInterface
{
    public function __construct(
        public int $id,
        public string $name,
        public string $shortName,
        public string $nameGenitive,
        public string $fullName,
        public string $alpha2Code,
        public string $alpha3Code,
        public int $numericCode,
        public bool $isActive
    ) {}

    /**
     * @psalm-api
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_name' => $this->shortName,
            'name_genitive' => $this->nameGenitive,
            'full_name' => $this->fullName,
            'alpha_2_code' => $this->alpha2Code,
            'alpha_3_code' => $this->alpha3Code,
            'numeric_code' => $this->numericCode,
            'is_active' => $this->isActive,
        ];
    }
}
