<?php

namespace App\Infrastructure\Dictionaries;

use App\Application\Dictionaries\DTO\CountryDto;
use App\Application\Dictionaries\DTO\DictionaryDtoInterface;
use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Infrastructure\DB\SchemaSqlHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final class CountriesDictionary implements DictionaryProviderInterface
{
    private const TABLE = 'countries';

    public function __construct(
        private readonly Connection $connection,
        private readonly SchemaSqlHelper $schemaSqlHelper,
    ) {
    }

    public function getName(): string
    {
        return 'countries';
    }

    /**
     * @return CountryDto[]
     *
     * @throws Exception
     */
    public function getItems(bool $onlyActive = true): array
    {
        $sql = sprintf(
            '
            SELECT
                id,
                name,
                short_name,
                name_genitive,
                full_name,
                alpha_2_code,
                alpha_3_code,
                numeric_code,
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
     * Получить страну по alpha2-коду.
     *
     * @throws Exception
     */
    public function getByAlpha2(
        string $alpha2,
        bool $onlyActive = true
    ): ?CountryDto {
        $sql = sprintf(
            '
            SELECT
                id,
                name,
                short_name,
                name_genitive,
                full_name,
                alpha_2_code,
                alpha_3_code,
                numeric_code,
                is_active
            FROM %s
            WHERE alpha_2_code = :alpha2
            ',
            $this->schemaSqlHelper->table(self::TABLE)
        );

        if ($onlyActive) {
            $sql .= ' AND is_active = true';
        }

        $row = $this->connection->fetchAssociative(
            $sql,
            ['alpha2' => $alpha2]
        );

        if ($row === false) {
            return null;
        }

        return $this->hydrateDto($row);
    }

    private function hydrateDto(array $row): CountryDto
    {
        return new CountryDto(
            id: (int) $row['id'],
            name: $row['name'],
            shortName: $row['short_name'],
            nameGenitive: $row['name_genitive'],
            fullName: $row['full_name'],
            alpha2Code: $row['alpha_2_code'],
            alpha3Code: $row['alpha_3_code'],
            numericCode: (int) $row['numeric_code'],
            isActive: (bool) $row['is_active'],
        );
    }

    /**
     * DTO → cache array
     */
    public function toCacheArray(CountryDto|DictionaryDtoInterface $dto): array
    {
        return [
            'id' => $dto->id,
            'name' => $dto->name,
            'short_name' => $dto->shortName,
            'name_genitive' => $dto->nameGenitive,
            'full_name' => $dto->fullName,
            'alpha_2_code' => $dto->alpha2Code,
            'alpha_3_code' => $dto->alpha3Code,
            'numeric_code' => $dto->numericCode,
            'is_active' => $dto->isActive,
        ];
    }

    /**
     * cache array → DTO
     */
    public function fromCacheArray(array $data): DictionaryDtoInterface
    {
        return new CountryDto(
            id: (int) $data['id'],
            name: (string) $data['name'],
            shortName: (string) $data['short_name'],
            nameGenitive: (string) $data['name_genitive'],
            fullName: (string) $data['full_name'],
            alpha2Code: (string) $data['alpha_2_code'],
            alpha3Code: (string) $data['alpha_3_code'],
            numericCode: (int) $data['numeric_code'],
            isActive: (bool) $data['is_active'],
        );
    }
}
