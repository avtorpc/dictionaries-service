<?php

namespace App\Infrastructure\Dictionaries;

use App\Application\Dictionaries\DTO\ApplicationSettingDto;
use App\Application\Dictionaries\DTO\DictionaryDtoInterface;
use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Infrastructure\DB\SchemaSqlHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @implements DictionaryProviderInterface<ApplicationSettingDto>
 */
final class ApplicationSettingsDictionary implements DictionaryProviderInterface
{
    private const TABLE = 'application_settings';

    public function __construct(
        private readonly Connection $connection,
        private readonly SchemaSqlHelper $schemaSqlHelper,
    ) {
    }

    public function getName(): string
    {
        return 'app-settings';
    }

    /**
     * @return ApplicationSettingDto[]
     *
     * @throws Exception
     */
    public function getItems(bool $onlyActive = true): array
    {
        $sql = sprintf(
            '
            SELECT
                id,
                setting_key,
                setting_name,
                value,
                data_type,
                min_value,
                max_value,
                description,
                category,
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
     * Получить значение настройки по ключу.
     *
     * @throws Exception
     */
    public function getValueByKey(
        string $key,
        mixed $default = null,
        bool $onlyActive = true
    ): mixed {
        $sql = sprintf(
            '
            SELECT
                value,
                data_type
            FROM %s
            WHERE setting_key = :key
            ',
            $this->schemaSqlHelper->table(self::TABLE)
        );

        if ($onlyActive) {
            $sql .= ' AND is_active = true';
        }

        $row = $this->connection->fetchAssociative(
            $sql,
            ['key' => $key]
        );

        if ($row === false) {
            return $default;
        }

        return $this->castValue(
            (string) $row['value'],
            (string) $row['data_type']
        );
    }

    private function hydrateDto(array $row): ApplicationSettingDto
    {
        return new ApplicationSettingDto(
            id: (int) $row['id'],
            settingKey: (string) $row['setting_key'],
            settingName: (string) $row['setting_name'],
            value: $this->castValue(
                (string) $row['value'],
                (string) $row['data_type']
            ),
            dataType: (string) $row['data_type'],
            minValue: $row['min_value'],
            maxValue: $row['max_value'],
            description: $row['description'],
            category: $row['category'],
            isActive: (bool) $row['is_active'],
        );
    }

    /**
     * DTO → cache array
     */
    public function toCacheArray(object $dto): array
    {
        /** @var ApplicationSettingDto $dto */

        return [
            'id' => $dto->id,
            'setting_key' => $dto->settingKey,
            'setting_name' => $dto->settingName,
            'value' => $dto->value,
            'data_type' => $dto->dataType,
            'min_value' => $dto->minValue,
            'max_value' => $dto->maxValue,
            'description' => $dto->description,
            'category' => $dto->category,
            'is_active' => $dto->isActive,
        ];
    }

    /**
     * cache array → DTO
     */
    public function fromCacheArray(array $data): DictionaryDtoInterface
    {
        return new ApplicationSettingDto(
            id: (int) $data['id'],
            settingKey: (string) $data['setting_key'],
            settingName: (string) $data['setting_name'],
            value: $data['value'],
            dataType: (string) $data['data_type'],
            minValue: $data['min_value'] ?? null,
            maxValue: $data['max_value'] ?? null,
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
            isActive: (bool) $data['is_active'],
        );
    }

    /**
     * Приведение строкового значения из БД
     * к типу, указанному в data_type.
     */
    private function castValue(
        string $value,
        string $dataType
    ): mixed {
        return match (strtolower(trim($dataType))) {
            'int', 'integer' => (int) $value,
            'float', 'double', 'decimal' => (float) $value,
            'bool', 'boolean' => in_array(
                strtolower($value),
                ['1', 'true', 'yes'],
                true
            ),
            default => $value,
        };
    }
}
