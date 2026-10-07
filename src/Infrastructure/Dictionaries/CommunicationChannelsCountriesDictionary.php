<?php

declare(strict_types=1);

namespace App\Infrastructure\Dictionaries;

use App\Application\Dictionaries\DTO\ChannelCountryDto;
use App\Application\Dictionaries\DTO\DictionaryDtoInterface;
use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Infrastructure\DB\SchemaSqlHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @implements DictionaryProviderInterface<ChannelCountryDto>
 */
final class CommunicationChannelsCountriesDictionary implements DictionaryProviderInterface
{
    private const TABLE = 'communication_channel_country_links';

    public function __construct(
        private readonly Connection $connection,
        private readonly SchemaSqlHelper $schemaSqlHelper,
    ) {
    }

    public function getName(): string
    {
        return 'communication-channels-countries';
    }

    /**
     * Проверяет доступность канала связи в стране
     */
    public function isChannelAvailableInCountry(
        string $channelCode,
        string $countryCode
    ): bool {
        $sql = sprintf(
            '
            SELECT 1
            FROM %s ccl
            JOIN %s cc ON cc.id = ccl.communication_channel_id
            JOIN %s c ON c.id = ccl.country_id
            WHERE cc.code = :channel
              AND c.alpha_2_code = :country
              AND ccl.is_active = true
            LIMIT 1
            ',
            $this->schemaSqlHelper->table(self::TABLE),
            $this->schemaSqlHelper->table('communication_channels'),
            $this->schemaSqlHelper->table('countries'),
        );

        return (bool) $this->connection->fetchOne($sql, [
            'channel' => $channelCode,
            'country' => $countryCode,
        ]);
    }

    /**
     * @return ChannelCountryDto[]
     *
     * @throws Exception
     */
    public function getItems(bool $onlyActive = true): array
    {
        $sql =
            'SELECT ' .
            'cc.code AS channel_code, ' .
            'c.alpha_3_code AS country_code, ' .
            'ccl.prefix AS prefix, ' .
            'ccl.is_active AS is_active ' .
            'FROM ' . $this->schemaSqlHelper->table(self::TABLE) . ' ccl ' .
            'JOIN ' . $this->schemaSqlHelper->table('communication_channels') . ' cc ' .
            'ON cc.id = ccl.communication_channel_id ' .
            'JOIN ' . $this->schemaSqlHelper->table('countries') . ' c ' .
            'ON c.id = ccl.country_id';

        if ($onlyActive) {
            $sql .= ' WHERE ccl.is_active = true';
        }

        $rows = $this->connection->fetchAllAssociative($sql);

        return array_map(
            fn (array $row) => $this->hydrateDto($row),
            $rows
        );
    }

    private function hydrateDto(array $row): ChannelCountryDto
    {
        return new ChannelCountryDto(
            channel: (string) $row['channel_code'],
            country: (string) $row['country_code'],
            prefix: (string) $row['prefix'],
            isAvailable: (bool) $row['is_active'],
        );
    }

    /**
     * DTO → cache array
     */
    public function toCacheArray(ChannelCountryDto|DictionaryDtoInterface $dto): array
    {
        /** @var ChannelCountryDto $dto */

        return [
            'channel_code' => $dto->channel,
            'country_code' => $dto->country,
            'is_available' => $dto->isAvailable,
        ];
    }

    /**
     * cache array → DTO
     */
    public function fromCacheArray(array $data): DictionaryDtoInterface
    {
        return new ChannelCountryDto(
            channel: (string) $data['channel_code'],
            country: (string) $data['country_code'],
            isAvailable: (bool) $data['is_available'],
        );
    }
}
