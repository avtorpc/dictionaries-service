<?php
declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261007190100 extends AbstractMigration
{
    public function getDescription(): string { return 'Create and populate seven qualification reference dictionaries'; }
    public function up(Schema $schema): void
    {
        $s = $this->connection->getDatabasePlatform()->quoteSingleIdentifier($_ENV['DB_SCHEMA'] ?? 'dictionaries');
        $this->addSql("CREATE SCHEMA IF NOT EXISTS {$s}");
        $this->addSql("SET LOCAL search_path TO {$s}, pg_catalog, public");
        foreach (['schema.json','seed.json'] as $file) {
            $sql = file_get_contents(dirname(__DIR__).'/resources/migrations/20261007/'.$file);
            if ($sql === false) throw new \RuntimeException('Missing immutable dictionary migration resource: '.$file);
            // Doctrine executes prepared statements; each resource entry is one command.
            foreach (json_decode($sql,true,512,JSON_THROW_ON_ERROR) as $statement) $this->addSql($statement);
        }
    }
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Versioned reference data may already be used in task snapshots.');
    }
}
