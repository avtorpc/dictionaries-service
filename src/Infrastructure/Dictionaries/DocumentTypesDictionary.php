<?php

namespace App\Infrastructure\Dictionaries;

use App\Application\Dictionaries\DTO\DictionaryDtoInterface;
use App\Application\Dictionaries\DTO\DocumentTypeDto;
use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Infrastructure\DB\SchemaSqlHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final class DocumentTypesDictionary implements DictionaryProviderInterface
{
    private const TABLE = 'document_types';

    public function __construct(
        private readonly Connection $connection,
        private readonly SchemaSqlHelper $schemaSqlHelper,
    ) {
    }

    public function getName(): string
    {
        return 'document-types';
    }

    /**
     * @return DocumentTypeDto[]
     * @throws Exception
     */
    public function getItems(bool $onlyActive = true): array
    {
        $sql = 'SELECT id, code, name, is_active FROM ' . $this->schemaSqlHelper->table(self::TABLE);

        if ($onlyActive) {
            $sql .= ' WHERE is_active = true';
        }

        $sql .= ' ORDER BY id';

        $rows = $this->connection->fetchAllAssociative($sql);

        return array_map(fn(array $row) => $this->hydrateDto($row), $rows);
    }

    public function getByCode(string $code): ?DocumentTypeDto
    {
        $sql = 'SELECT id, code, name, is_active FROM ' . $this->schemaSqlHelper->table(self::TABLE) . ' WHERE code = :code LIMIT 1';

        $row = $this->connection->fetchAssociative($sql, ['code' => $code]);

        if ($row === false) {
            return null;
        }

        return $this->hydrateDto($row);
    }

    public function toCacheArray(DocumentTypeDto|DictionaryDtoInterface $dto): array
    {
        return [
            'id' => $dto->id,
            'code' => $dto->code,
            'name' => $dto->name,
            'is_active' => $dto->isActive,
        ];
    }

    public function fromCacheArray(array $data): DictionaryDtoInterface
    {
        return new DocumentTypeDto(
            id: (int) $data['id'],
            code: (string) $data['code'],
            name: (string) $data['name'],
            isActive: (bool) $data['is_active'],
        );
    }

    private function hydrateDto(array $row): DocumentTypeDto
    {
        return new DocumentTypeDto(
            id: (int) $row['id'],
            code: $row['code'],
            name: $row['name'],
            isActive: (bool) $row['is_active'],
        );
    }
}
