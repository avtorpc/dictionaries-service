<?php

namespace App\Application\Dictionaries;

use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Shared\Cache\CacheKeyBuilder;
use App\Shared\Cache\JsonCacheSerializer;
use App\Shared\Exception\DictionaryNotFoundException;
use App\Shared\Exception\ExceptionPayload;
use Psr\Cache\CacheItemPoolInterface;

final class DictionaryService
{
    /** @var array<string, DictionaryProviderInterface> */
    private array $providers = [];

    public function __construct(
        iterable $providers,
        private CacheItemPoolInterface $cache,
        private CacheKeyBuilder $cacheKeyBuilder,
        private JsonCacheSerializer $serializer,
    ) {
        foreach ($providers as $provider) {
            $this->providers[$provider->getName()] = $provider;
        }
    }

    public function list(): array
    {
        return array_keys($this->providers);
    }

    public function get(string $name, ?bool $onlyActive = true): array
    {
        if (!isset($this->providers[$name])) {
            throw new DictionaryNotFoundException(
                new ExceptionPayload(
                    errorCode: 'DICTIONARY_NOT_FOUND',
                    message: sprintf("Dictionary '%s' not found", $name),
                    context: ['name' => $name]
                )
            );
        }

        $cacheKey = $this->cacheKeyBuilder->dictionary($name, $onlyActive);
        $item = $this->cache->getItem($cacheKey);

        $provider = $this->providers[$name];

        /**
         * =========================
         * CACHE HIT
         * =========================
         */
        if ($item->isHit()) {
            $cached = $item->get();

            if (is_string($cached)) {
                $array = json_decode($cached, true);

                return array_map(
                    fn(array $data) => $provider->fromCacheArray($data),
                    $array
                );
            }

            return $cached;
        }

        /**
         * =========================
         * CACHE MISS
         * =========================
         */
        $data = $provider->getItems($onlyActive);

        $normalized = array_map(
            fn($dto) => $provider->toCacheArray($dto),
            $data
        );

        $item->set(json_encode($normalized, JSON_UNESCAPED_UNICODE));
        $this->cache->save($item);

        return $data;
    }
}
