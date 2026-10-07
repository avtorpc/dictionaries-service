<?php

namespace App\Tests\Application\Dictionaries;

use App\Application\Dictionaries\DictionaryService;
use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Shared\Exception\DictionaryNotFoundException;
use PHPUnit\Framework\TestCase;

final class DictionaryServiceTest extends TestCase
{
    public function test_list_returns_provider_names(): void
    {
        $provider1 = $this->createStub(DictionaryProviderInterface::class);
        $provider1->method('getName')->willReturn('countries');

        $provider2 = $this->createStub(DictionaryProviderInterface::class);
        $provider2->method('getName')->willReturn('roles');

        $service = new DictionaryService([$provider1, $provider2]);

        $result = $service->list();

        $this->assertSame(['countries', 'roles'], $result);
    }

    public function test_get_returns_items_with_only_active(): void
    {
        $items = [new \stdClass()];

        $provider = $this->createMock(DictionaryProviderInterface::class);
        $provider->method('getName')->willReturn('countries');

        $provider->expects($this->once())
            ->method('getItems')
            ->with(true)
            ->willReturn($items);

        $service = new DictionaryService([$provider]);

        $result = $service->get('countries', true);

        $this->assertSame($items, $result);
    }

    public function test_get_calls_without_only_active_when_null(): void
    {
        $items = [new \stdClass()];

        $provider = $this->createMock(DictionaryProviderInterface::class);
        $provider->method('getName')->willReturn('roles');

        $provider->expects($this->once())
            ->method('getItems')
            ->with(true)
            ->willReturn($items);

        $service = new DictionaryService([$provider]);

        $result = $service->get('roles', true);

        $this->assertSame($items, $result);
    }

    public function test_get_throws_exception_if_not_found(): void
    {
        $service = new DictionaryService([]);

        $this->expectException(DictionaryNotFoundException::class);

        $service->get('unknown');
    }
}
