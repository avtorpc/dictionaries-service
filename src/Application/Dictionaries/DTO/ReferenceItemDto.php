<?php
declare(strict_types=1);
namespace App\Application\Dictionaries\DTO;
final readonly class ReferenceItemDto implements DictionaryDtoInterface
{
    public function __construct(private array $data) {}
    public function toArray(): array { return $this->data; }
}
