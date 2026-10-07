<?php

namespace App\Infrastructure\Dictionaries;

use App\Application\Dictionaries\DTO\DictionaryDtoInterface;
use App\Application\Dictionaries\DTO\PositionDto;
use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Infrastructure\DB\SchemaSqlHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @implements DictionaryProviderInterface<PositionDto>
 */
final class PositionsDictionary implements DictionaryProviderInterface
{
    private const TABLE = 'positions';

    public function __construct(
        private readonly Connection $connection,
        private readonly SchemaSqlHelper $schemaSqlHelper,
    ) {
    }

    public function getName(): string
    {
        return 'positions';
    }

    /**
     * @return PositionDto[]
     *
     * @throws Exception
     */
    public function getItems(bool $onlyActive = true): array
    {
        $sql = sprintf(
            '
            SELECT
                id,
                code,
                name,
                description,
                is_active
            FROM %s
            ',
            $this->schemaSqlHelper->table(self::TABLE)
        );

        if ($onlyActive) {
            $sql .= ' WHERE is_active = true';
        }

        $sql .= ' ORDER BY name';

        $rows = $this->connection->fetchAllAssociative($sql);

        return array_map(
            fn (array $row) => $this->hydrateDto($row),
            $rows
        );
    }

    /**
     * Получить должность по коду.
     *
     * @throws Exception
     */
    public function getByCode(
        string $code,
        bool $onlyActive = true
    ): ?PositionDto {
        $sql = sprintf(
            '
            SELECT
                id,
                code,
                name,
                description,
                is_active
            FROM %s
            WHERE code = :code
            ',
            $this->schemaSqlHelper->table(self::TABLE)
        );

        if ($onlyActive) {
            $sql .= ' AND is_active = true';
        }

        $row = $this->connection->fetchAssociative(
            $sql,
            ['code' => $code]
        );

        if ($row === false) {
            return null;
        }

        return $this->hydrateDto($row);
    }

    private function hydrateDto(array $row): PositionDto
    {
        return new PositionDto(
            id: (int) $row['id'],
            code: (string) $row['code'],
            name: (string) $row['name'],
            description: $row['description'] ?? null,
            isActive: (bool) $row['is_active'],
        );
    }

    /**
     * DTO → cache array
     */
    public function toCacheArray(PositionDto|DictionaryDtoInterface $dto): array
    {
        /** @var PositionDto $dto */

        return [
            'id' => $dto->id,
            'code' => $dto->code,
            'name' => $dto->name,
            'description' => $dto->description,
            'is_active' => $dto->isActive,
        ];
    }

    /**
     * cache array → DTO
     */
    public function fromCacheArray(array $data): DictionaryDtoInterface
    {
        return new PositionDto(
            id: (int) $data['id'],
            code: (string) $data['code'],
            name: (string) $data['name'],
            description: $data['description'] ?? null,
            isActive: (bool) $data['is_active'],
        );
    }
}
