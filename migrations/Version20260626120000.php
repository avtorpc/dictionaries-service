<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблицы document_types с предзаполненными справочными данными';
    }

    public function up(Schema $schema): void
    {
        $schemaName = $_ENV['DB_SCHEMA'] ?? 'public';

        $tableName = "\"{$schemaName}\".\"document_types\"";

        $this->addSql("
            CREATE TABLE {$tableName} (
                id BIGSERIAL PRIMARY KEY,
                code VARCHAR(100) NOT NULL,
                name VARCHAR(100) NOT NULL,
                is_active BOOLEAN NOT NULL,
                CONSTRAINT uniq_document_type_code UNIQUE (code)
            )
        ");

        $this->addSql("
            INSERT INTO {$tableName}
                (code, name, is_active)
            VALUES
                ('passport', 'Паспорт', true),
                ('regcert', 'Свидетельство о регистрации', true)
        ");
    }

    public function down(Schema $schema): void
    {
        $schemaName = $_ENV['DB_SCHEMA'] ?? 'public';

        $tableName = "\"{$schemaName}\".\"document_types\"";

        $this->addSql("DROP TABLE {$tableName}");
    }
}
