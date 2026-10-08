<?php
declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20261007170000 extends AbstractMigration {
 public function getDescription(): string {return 'Registration cooldown and atomic IP window: 60s / 5 requests';}
 public function up(Schema $schema): void {
  $table='"'.str_replace('"','""',$_ENV['DB_SCHEMA']??'public').'".application_settings';
  foreach(['email_cooldown_seconds'=>60,'ip_window_seconds'=>60,'ip_max_attempts'=>5] as $key=>$value)$this->addSql("UPDATE {$table} SET value=:value WHERE setting_key=:key",['value'=>(string)$value,'key'=>$key]);
 }
 public function down(Schema $schema): void {$this->throwIrreversibleMigrationException('Previous administrator values are not known.');}
}
