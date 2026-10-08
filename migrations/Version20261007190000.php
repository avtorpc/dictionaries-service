<?php
declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261007190000 extends AbstractMigration
{
    public function getDescription(): string { return 'Remove seven legacy dictionaries and replace marketplace roles'; }
    public function up(Schema $schema): void
    {
        $s = $this->connection->getDatabasePlatform()->quoteSingleIdentifier($_ENV['DB_SCHEMA'] ?? 'dictionaries');
        // Children first; do not cascade into any unapproved external dependencies.
        foreach (['roles_countries', 'communication_channel_country_links', 'communication_channels', 'positions', 'verification_statuses', 'legal_forms', 'document_types'] as $table) {
            $this->addSql("DROP TABLE IF EXISTS {$s}.{$table}");
        }
        $this->addSql("DELETE FROM {$s}.roles WHERE code NOT IN ('applicant','employer')");
        $this->addSql("INSERT INTO {$s}.roles (name,name_genitive,code,is_active) VALUES ('Соискатель','соискателя','applicant',true),('Работодатель','работодателя','employer',true) ON CONFLICT (code) DO UPDATE SET name=EXCLUDED.name,name_genitive=EXCLUDED.name_genitive,is_active=true");
    }
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Legacy records must be restored from a database backup.');
    }
}
