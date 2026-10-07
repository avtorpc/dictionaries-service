<?php

namespace App\Infrastructure\Dictionaries;

use App\Application\Dictionaries\DTO\CommunicationChannelDto;
use App\Application\Dictionaries\DTO\DictionaryDtoInterface;
use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Infrastructure\DB\SchemaSqlHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final class CommunicationChannelsDictionary implements DictionaryProviderInterface
{
    private const TABLE = 'communication_channels';

    public function __construct(
        private readonly Connection $connection,
        private readonly SchemaSqlHelper $schemaSqlHelper,
    ) {
    }

    public function getName(): string
    {
        return 'communication-channels';
    }

    /**
     * @return CommunicationChannelDto[]
     *
     * @throws Exception
     */
    public function getItems(bool $onlyActive = true): array
    {
        $table = $this->schemaSqlHelper->table(self::TABLE);

        $sql = "
            SELECT
                id,
                code,
                name,
                is_active
            FROM {$table}
        ";

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
     * Получить канал связи по коду.
     *
     * @throws Exception
     */
    public function getByCode(
        string $code,
        bool $onlyActive = true
    ): ?CommunicationChannelDto {
        $table = $this->schemaSqlHelper->table(self::TABLE);

        $sql = "
            SELECT
                id,
                code,
                name,
                is_active
            FROM {$table}
            WHERE code = :code
        ";

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

    private function hydrateDto(array $row): CommunicationChannelDto
    {
        return new CommunicationChannelDto(
            id: (int) $row['id'],
            code: (string) $row['code'],
            name: (string) $row['name'],
            isActive: (bool) $row['is_active'],
        );
    }

    /**
     * DTO → cache array
     */
    public function toCacheArray(
        CommunicationChannelDto|DictionaryDtoInterface $dto
    ): array {
        return [
            'id' => $dto->id,
            'code' => $dto->code,
            'name' => $dto->name,
            'is_active' => $dto->isActive,
        ];
    }

    /**
     * cache array → DTO
     */
    public function fromCacheArray(array $data): DictionaryDtoInterface
    {
        return new CommunicationChannelDto(
            id: (int) $data['id'],
            code: (string) $data['code'],
            name: (string) $data['name'],
            isActive: (bool) $data['is_active'],
        );
    }
}
