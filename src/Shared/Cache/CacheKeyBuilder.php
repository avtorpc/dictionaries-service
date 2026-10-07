<?php

namespace App\Shared\Cache;

final class CacheKeyBuilder
{
    public function __construct(
        private string $version = 'v1'
    ) {}

    public function dictionary(string $name, ?bool $onlyActive = null): string
    {
        return $this->build([
            'dict',
            $name,
            match ($onlyActive) {
                true => 'active',
                false => 'inactive',
                null => 'all',
            },
            'items',
        ]);
    }

    public function settings(string $scope = 'frontend'): string
    {
        return $this->build([
            'settings',
            $scope,
            'items',
        ]);
    }

    public function custom(array $parts): string
    {
        return $this->build($parts);
    }

    private function build(array $parts): string
    {
        return implode('_', array_merge(
            [$this->version],
            $parts
        ));
    }
}
