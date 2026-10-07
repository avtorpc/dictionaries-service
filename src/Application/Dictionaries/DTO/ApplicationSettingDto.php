<?php

namespace App\Application\Dictionaries\DTO;

final class ApplicationSettingDto implements DictionaryDtoInterface
{
    public function __construct(
        public int $id,
        public string $settingKey,
        public string $settingName,
        public int|float|bool|string|null $value,
        public string $dataType,
        public ?string $minValue,
        public ?string $maxValue,
        public ?string $description,
        public string $category,
        public bool $isActive,
    ) {}


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'setting_key' => $this->settingKey,
            'setting_name' => $this->settingName,
            'value' => $this->value,
            'data_type' => $this->dataType,
            'min_value' => $this->minValue,
            'max_value' => $this->maxValue,
            'description' => $this->description,
            'category' => $this->category,
            'is_active' => $this->isActive,
        ];
    }
}
