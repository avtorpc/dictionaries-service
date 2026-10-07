<?php

declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261007120000 extends AbstractMigration
{
    public function getDescription(): string { return 'Email registration: 300s code, 600s request, 60s resend, 3 regenerations'; }
    public function up(Schema $schema): void
    {
        $name = $_ENV['DB_SCHEMA'] ?? 'public';
        $table = '"'.str_replace('"', '""', $name).'"."application_settings"';
        $this->addSql("UPDATE {$table} SET value = '300', min_value = '1', max_value = '600', description = 'Срок кода в секундах, ограниченный сроком заявки' WHERE setting_key = 'code_expiry_seconds'");
        foreach (['resend_interval_seconds' => 60, 'max_code_regenerations' => 3, 'max_verification_attempts' => 5, 'active_signup_ttl_seconds' => 600] as $key => $value) {
            $this->addSql("UPDATE {$table} SET value = :value WHERE setting_key = :key", ['value' => (string) $value, 'key' => $key]);
        }
    }
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Previous administrator settings cannot be safely inferred.');
    }
}
