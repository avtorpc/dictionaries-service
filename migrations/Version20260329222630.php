<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260329222630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблицы application_settings c предзаполненными данными в схеме ' . $_ENV['DB_SCHEMA'];
    }

    public function up(Schema $schema): void
    {
        $schemaName = $_ENV['DB_SCHEMA'] ?? 'public';
        $tableName = "\"{$schemaName}\".\"application_settings\"";

        // Создание таблицы
        $this->addSql("
            CREATE TABLE $tableName (
                id BIGSERIAL NOT NULL,
                setting_key VARCHAR(100) NOT NULL,
                setting_name VARCHAR(255) NOT NULL,
                value VARCHAR(255) NOT NULL,
                data_type VARCHAR(20) NOT NULL,
                min_value VARCHAR(50) DEFAULT NULL,
                max_value VARCHAR(50) DEFAULT NULL,
                description TEXT DEFAULT NULL,
                category VARCHAR(50) NOT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                PRIMARY KEY(id),
                UNIQUE (setting_key)
            )
        ");

        // Предзаполненные значения
        $this->addSql("
            INSERT INTO $tableName 
                (setting_key, setting_name, value, data_type, min_value, max_value, description, category, is_active) 
            VALUES
                ('code_expiry_seconds', 'Время жизни кода подтверждения', '10', 'integer', '5', '30', 'Время жизни кода подтверждения в секундах. Определяет, сколько минут код остаётся действительным после отправки.', 'registration', TRUE),
                ('resend_interval_seconds', 'Интервал повторной отправки кода', '30', 'integer', '10', '120', 'Интервал между повторными запросами кода в секундах. Указывает, через сколько секунд пользователь может запросить новый код.', 'registration', TRUE),
                ('max_code_regenerations', 'Макс. количество повторных генераций', '3', 'integer', '1', '5', 'Максимальное количество повторных генераций кода. Ограничивает число запросов на повторную отправку кода.', 'registration', TRUE),
                ('max_verification_attempts', 'Макс. количество попыток подтверждения', '5', 'integer', '2', '5', 'Максимальное количество попыток подтверждения кода. Определяет, сколько раз пользователь может ввести код до блокировки.', 'registration', TRUE),
                
                -- новые константы для ограничений регистрации
                ('email_cooldown_seconds', 'Интервал повторной регистрации по email', '60', 'integer', '10', '300', 'Минимальный интервал между регистрациями с одним email в секундах.', 'registration', TRUE),
                ('ip_window_seconds', 'Окно времени для IP', '60', 'integer', '10', '300', 'Временное окно для подсчёта регистраций с одного IP.', 'registration', TRUE),
                ('ip_max_attempts', 'Максимум регистраций с одного IP', '5', 'integer', '1', '20', 'Максимальное количество регистраций с одного IP за окно времени ip_window_seconds.', 'registration', TRUE),
                ('active_signup_ttl_seconds', 'TTL активной регистрации', '600', 'integer', '60', '3600', 'Время которое даётся пользователю на получение кода и его ввод на форме, в том числе повторные попытки отправки. ' ||
                                                                                                          ' Характеризует интервал в течение которого пользователь не может повторно зарегистрироваться по одному email. Время жизни активной незавершённой регистрации в секундах.', 'registration', TRUE)
        ");
    }

    public function down(Schema $schema): void
    {
        $schemaName = $_ENV['DB_SCHEMA'] ?? 'public';
        $tableName = "\"{$schemaName}\".\"application_settings\"";
        $this->addSql("DROP TABLE $tableName");
    }
}
