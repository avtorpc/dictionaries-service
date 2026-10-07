<?php

namespace App\Infrastructure\Dictionaries;

use App\Application\Dictionaries\DTO\DictionaryDtoInterface;
use App\Application\Dictionaries\DTO\VerificationStatusDto;
use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Infrastructure\DB\SchemaSqlHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @implements DictionaryProviderInterface<VerificationStatusDto>
 */
final class VerificationStatusesDictionary implements DictionaryProviderInterface
{
    private const TABLE = 'verification_statuses';

    public function __construct(
        private readonly Connection $connection,
        private readonly SchemaSqlHelper $schemaSqlHelper,
    ) {
    }

    public function getName(): string
    {
        return 'verification-statuses';
    }

    /**
     * @return VerificationStatusDto[]
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
            fn(array $row) => $this->hydrateDto($row),
            $rows
        );
    }

    /**
     * Получить запись по коду.
     *
     * @throws Exception
     */
    public function getByCode(
        string $code,
        bool $onlyActive = true
    ): ?VerificationStatusDto {
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

        return $row === false
            ? null
            : $this->hydrateDto($row);
    }

    private function hydrateDto(array $row): VerificationStatusDto
    {
        return new VerificationStatusDto(
            id: (int) $row['id'],
            code: (string) $row['code'],
            name: (string) $row['name'],
            description: $row['description'],
            isActive: (bool) $row['is_active'],
        );
    }

    /**
     * DTO → cache array
     */
    public function toCacheArray(
        VerificationStatusDto|DictionaryDtoInterface $dto
    ): array {
        /** @var VerificationStatusDto $dto */

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
        return new VerificationStatusDto(
            id: (int) $data['id'],
            code: (string) $data['code'],
            name: (string) $data['name'],
            description: $data['description'] ?? null,
            isActive: (bool) $data['is_active'],
        );
    }
}
