<?php

namespace App\Application\Dictionaries\DTO;

class CommunicationChannelDto implements DictionaryDtoInterface
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public bool $isActive,
//        public \DateTimeImmutable $createdAt,
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
            'code' => $this->code,
            'name' => $this->name,
            'is_active' => $this->isActive,
//            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
