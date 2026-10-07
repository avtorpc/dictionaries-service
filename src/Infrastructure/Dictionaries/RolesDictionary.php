<?php

namespace App\Infrastructure\Dictionaries;

use App\Application\Dictionaries\DTO\RoleDto;
use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Infrastructure\DB\SchemaSqlHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use App\Application\Dictionaries\DTO\DictionaryDtoInterface;

/**
 * @implements DictionaryProviderInterface<RoleDto>
 */
final class RolesDictionary implements DictionaryProviderInterface
{
    private const TABLE = 'roles';

    public function __construct(
        private readonly Connection $connection,
        private readonly SchemaSqlHelper $schemaSqlHelper,
    ) {}


    public function getName(): string
    {
        return 'roles';
    }

    /**
     * @return RoleDto[]
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
                name_genitive,
                is_active
            FROM %s
            ',
            $this->schemaSqlHelper->table(self::TABLE)
        );

        if ($onlyActive) {
            $sql .= ' WHERE is_active = true';
        }

        $sql .= ' ORDER BY id';

        $rows = $this->connection->fetchAllAssociative($sql);

        return array_map(
            fn (array $row) => $this->hydrateDto($row),
            $rows
        );
    }


    /**
     * Получить роль по коду.
     *
     * @throws Exception
     */
    public function getByCode(string $code, bool $onlyActive = true): ?RoleDto
    {
        $sql = sprintf(
            '
            SELECT
                id,
                code,
                name,
                name_genitive,
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

        return $row === false
            ? null
            : $this->hydrateDto($row);
    }


    private function hydrateDto(array $row): RoleDto
    {
        return new RoleDto(
            id: (int) $row['id'],
            code: (string) $row['code'],
            name: (string) $row['name'],
            nameGenitive: $row['name_genitive'],
            isActive: (bool) $row['is_active'],
        );
    }


    /**
     * DTO → cache array
     */
    public function toCacheArray(RoleDto|DictionaryDtoInterface $dto): array
    {
        /** @var RoleDto $dto */

        return [
            'id' => $dto->id,
            'code' => $dto->code,
            'name' => $dto->name,
            'name_genitive' => $dto->nameGenitive,
            'is_active' => $dto->isActive,
        ];
    }


    /**
     * @return DictionaryDtoInterface
     */
    public function fromCacheArray(array $data): DictionaryDtoInterface
    {
        return new RoleDto(
            id: (int) $data['id'],
            code: (string) $data['code'],
            name: (string) $data['name'],
            nameGenitive: $data['name_genitive'] ?? null,
            isActive: (bool) $data['is_active'],
        );
    }
}
