<?php

namespace App\Tests\Unit\Settings;

use App\Infrastructure\Dictionaries\ApplicationSettingsDictionary;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;

class ApplicationSettingsDictionaryTest extends TestCase
{
    public function testGetItemsReturnsDtoArray(): void
    {
        $rows = [
            [
                'id' => 1,
                'setting_key' => 'site_name',
                'setting_name' => 'Site Name',
                'value' => 'MyApp',
                'data_type' => 'string',
                'min_value' => null,
                'max_value' => null,
                'description' => 'Site title',
                'category' => 'general',
                'is_active' => true,
            ]
        ];

        $connection = $this->createMock(Connection::class);

        $connection->expects($this->once())
            ->method('fetchAllAssociative')
            ->with($this->stringContains('FROM verification.application_settings'))
            ->willReturn($rows);

        $service = new ApplicationSettingsDictionary($connection);

        $result = $service->getItems(true);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);

        $dto = $result[0];

        $this->assertEquals('site_name', $dto->settingKey);
        $this->assertEquals('Site Name', $dto->settingName);
        $this->assertEquals('MyApp', $dto->value);
        $this->assertTrue($dto->isActive);
    }
}
