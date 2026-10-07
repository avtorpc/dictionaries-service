<?php

declare(strict_types=1);

namespace App\Application\Dictionaries\DTO;

final class ChannelCountryDto implements DictionaryDtoInterface
{
    public function __construct(
        public string $channel,
        public string $country,
        public String $prefix,
        public bool $isAvailable,
    ) {}

    public function toArray(): array
    {
        return [
            'channel' => $this->channel,
            'country' => $this->country,
            'prefix' => $this->prefix,
            'is_available' => $this->isAvailable,
        ];
    }
}
