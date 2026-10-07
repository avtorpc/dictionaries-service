<?php

namespace App\Application\Settings;

use App\Infrastructure\Dictionaries\ApplicationSettingsDictionary;
use App\Shared\Cache\CacheKeyBuilder;
use Psr\Cache\CacheItemPoolInterface;

final class FrontendSettingsService
{
    public function __construct(
        private ApplicationSettingsDictionary $dictionary,
        private CacheItemPoolInterface $cache,
        private CacheKeyBuilder $cacheKeyBuilder
    ) {}

    public function get(): array
    {
        $cacheKey = $this->cacheKeyBuilder->settings('frontend');

        $item = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            return $item->get();
        }

        $items = [];

        foreach ($this->dictionary->getItems() as $dto) {
            $items[] = [
                'setting_key' => $dto->settingKey,
                'value' => $dto->value,
            ];
        }

        $result = [
            'total' => count($items),
            'items' => $items,
        ];

        $item->set($result);

        $this->cache->save($item);

        return $result;
    }
}
