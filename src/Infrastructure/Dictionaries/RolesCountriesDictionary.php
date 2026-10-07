<?php

namespace App\Infrastructure\Dictionaries;

use App\Application\Dictionaries\DTO\DictionaryDtoInterface;
use App\Application\Dictionaries\DTO\RoleCountryDto;
use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Infrastructure\DB\SchemaSqlHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @implements DictionaryProviderInterface<RoleCountryDto>
 */
final class RolesCountriesDictionary implements DictionaryProviderInterface
{
    private const TABLE = 'roles_countries';

    public function __construct(
        private readonly Connection $connection,
        private readonly SchemaSqlHelper $schemaSqlHelper,
    ) {
    }

    public function getName(): string
    {
        return 'roles-countries';
    }

    /**
     * Проверяет доступность роли в стране
     */
    public function isRoleAvailableInCountry(
        string $roleCode,
        string $countryCode
    ): bool {
        $sql = sprintf(
            '
            SELECT 1
            FROM %s rc
            JOIN %s r ON r.id = rc.role_id
            JOIN %s c ON c.id = rc.country_id
            WHERE r.code = :role
              AND c.alpha_2_code = :country
              AND rc.is_available = true
            LIMIT 1
            ',
            $this->schemaSqlHelper->table(self::TABLE),
            $this->schemaSqlHelper->table('roles'),
            $this->schemaSqlHelper->table('countries'),
        );

        return (bool) $this->connection->fetchOne($sql, [
            'role' => $roleCode,
            'country' => $countryCode,
        ]);
    }

    /**
     * @return RoleCountryDto[]
     *
     * @throws Exception
     */
    public function getItems(bool $onlyActive = true): array
    {
        $sql = sprintf(
            '
            SELECT
                r.code AS role_code,
                c.alpha_2_code AS country_code,
                rc.is_available
            FROM %s rc
            JOIN %s r ON r.id = rc.role_id
            JOIN %s c ON c.id = rc.country_id
            ',
            $this->schemaSqlHelper->table(self::TABLE),
            $this->schemaSqlHelper->table('roles'),
            $this->schemaSqlHelper->table('countries'),
        );

        if ($onlyActive) {
            $sql .= ' WHERE rc.is_available = true';
        }

        $rows = $this->connection->fetchAllAssociative($sql);

        return array_map(
            fn (array $row) => $this->hydrateDto($row),
            $rows
        );
    }

    private function hydrateDto(array $row): RoleCountryDto
    {
        return new RoleCountryDto(
            role: (string) $row['role_code'],
            country: (string) $row['country_code'],
            isAvailable: (bool) $row['is_available'],
        );
    }

    /**
     * DTO → cache array
     */
    public function toCacheArray(RoleCountryDto|DictionaryDtoInterface $dto): array
    {
        /** @var RoleCountryDto $dto */

        return [
            'role_code' => $dto->role,
            'country_code' => $dto->country,
            'is_available' => $dto->isAvailable,
        ];
    }

    /**
     * cache array → DTO
     */
    public function fromCacheArray(array $data): DictionaryDtoInterface
    {
        return new RoleCountryDto(
            role: (string) $data['role_code'],
            country: (string) $data['country_code'],
            isAvailable: (bool) $data['is_available'],
        );
    }
}
