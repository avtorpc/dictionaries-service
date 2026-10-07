<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326230626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблицы countries с предзаполненными справочными данными в схеме ' . $_ENV['DB_SCHEMA'];
    }

    public function up(Schema $schema): void
    {
             // Получаем схему из переменной окружения или используем public
             $schemaName = $_ENV['DB_SCHEMA'] ?? 'public';

             // безопасное имя таблицы PostgreSQL
             $tableName = "\"{$schemaName}\".\"countries\"";

             $this->addSql("
                CREATE TABLE {$tableName} (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                short_name VARCHAR(100) NOT NULL,
                name_genitive VARCHAR(100) NOT NULL,
                full_name VARCHAR(255) NOT NULL,
                alpha_2_code CHAR(2) NOT NULL,
                alpha_3_code CHAR(3) NOT NULL,
                numeric_code INT NOT NULL,
                is_active BOOLEAN NOT NULL,
                CONSTRAINT uniq_country_alpha2 UNIQUE (alpha_2_code)   
            )
        ");

        // Вставляем предзаполненные данные
        $this->addSql("
            INSERT INTO {$tableName}
                (name, short_name, name_genitive, full_name, alpha_2_code, alpha_3_code, numeric_code, is_active)
            VALUES
                ('Казахстан', 'КАЗАХСТАН', 'Казахстана', 'Республика Казахстан', 'KZ', 'KAZ', 398, true),
                ('Россия', 'РОССИЯ', 'России', 'Российская Федерация', 'RU', 'RUS', 643, true),
                ('Китай', 'КИТАЙ', 'Китая', 'Китайская Народная Республика', 'CN', 'CHN', 156, true)
        ");
    }

    public function down(Schema $schema): void
    {
        // Получаем схему из переменной окружения или используем public
        $schemaName = $_ENV['DB_SCHEMA'] ?? 'public';

        // безопасное имя таблицы PostgreSQL
        $tableName = "\"{$schemaName}\".\"countries\"";

        $this->addSql("DROP TABLE {$tableName}");
    }
}
