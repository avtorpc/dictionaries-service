<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use Doctrine\DBAL\{DriverManager, Tools\DsnParser, Schema\Schema};
use App\Infrastructure\Assessment\ReferenceDictionary;
use App\Infrastructure\DB\SchemaSqlHelper;

function check(bool $condition,string $message): void { if (!$condition) throw new RuntimeException($message); }
$url=getenv('DATABASE_URL');
check(is_string($url) && $url!=='', 'DATABASE_URL is required');
$db=DriverManager::getConnection((new DsnParser(['postgresql'=>'pdo_pgsql','postgres'=>'pdo_pgsql']))->parse($url));
$root=dirname(__DIR__);
$prefix='dictionary_contract_'.bin2hex(random_bytes(5));
$schemas=[$prefix.'_fresh',$prefix.'_upgrade'];
$oldSchema=$_ENV['DB_SCHEMA']??null;
function applyMigration($db,string $root,string $schema,string $name): void {
 require_once $root.'/migrations/'.$name.'.php';
 $_ENV['DB_SCHEMA']=$schema;
 $class='DoctrineMigrations\\'.$name;
 $db->transactional(function()use($db,$class){
  $migration=new $class($db,new Psr\Log\NullLogger());
  $migration->up(new Schema());
  foreach($migration->getSql() as $sql)$db->executeQuery($sql->getStatement(),$sql->getParameters(),$sql->getTypes())->free();
 });
}
try {
 foreach($schemas as $index=>$schema){
  $db->executeStatement('CREATE SCHEMA '.$schema);
  foreach(['Version20260326222938','Version20260326230626','Version20260329222630','Version20261007120000','Version20261007170000'] as $name)applyMigration($db,$root,$schema,$name);
  if($index===1){
   // Simulate a populated previous installation, including its real dependency shapes.
   $db->executeStatement("DELETE FROM {$schema}.roles");
   $db->executeStatement("INSERT INTO {$schema}.roles(name,name_genitive,code,is_active) VALUES('Прежняя роль','прежней роли','SELLER',true)");
   $db->executeStatement("CREATE TABLE {$schema}.roles_countries(id INT PRIMARY KEY,role_id BIGINT REFERENCES {$schema}.roles(id),country_id BIGINT REFERENCES {$schema}.countries(id))");
   $db->executeStatement("INSERT INTO {$schema}.roles_countries SELECT 1,r.id,c.id FROM {$schema}.roles r CROSS JOIN {$schema}.countries c LIMIT 1");
   $db->executeStatement("CREATE TABLE {$schema}.communication_channels(id INT PRIMARY KEY)");
   $db->executeStatement("INSERT INTO {$schema}.communication_channels VALUES(1)");
   $db->executeStatement("CREATE TABLE {$schema}.communication_channel_country_links(id INT PRIMARY KEY,communication_channel_id INT REFERENCES {$schema}.communication_channels(id),country_id BIGINT REFERENCES {$schema}.countries(id))");
   $db->executeStatement("INSERT INTO {$schema}.communication_channel_country_links SELECT 1,1,id FROM {$schema}.countries LIMIT 1");
   foreach(['positions','verification_statuses','legal_forms','document_types'] as $table){
    $db->executeStatement("CREATE TABLE {$schema}.{$table}(id INT PRIMARY KEY)");
    $db->executeStatement("INSERT INTO {$schema}.{$table} VALUES(1)");
   }
   $db->executeStatement("UPDATE {$schema}.application_settings SET value='75' WHERE setting_key='resend_interval_seconds'");
  }
  $settings=$db->fetchAllAssociative("SELECT * FROM {$schema}.application_settings ORDER BY id");
  $countries=$db->fetchAllAssociative("SELECT * FROM {$schema}.countries ORDER BY id");
  applyMigration($db,$root,$schema,'Version20261007190000');
  applyMigration($db,$root,$schema,'Version20261007190100');
  check($settings===$db->fetchAllAssociative("SELECT * FROM {$schema}.application_settings ORDER BY id"),'Settings changed');
  check($countries===$db->fetchAllAssociative("SELECT * FROM {$schema}.countries ORDER BY id"),'Countries changed');
  check($db->fetchFirstColumn("SELECT code FROM {$schema}.roles ORDER BY code")===['applicant','employer'],'Incorrect roles');
  $counts=['specializations'=>120,'grades'=>3,'competencies'=>65,'technologies'=>62,'specialization_competencies'=>1543,'criteria'=>72,'criterion_competencies'=>78];
  foreach($counts as $table=>$count)check((int)$db->fetchOne("SELECT count(*) FROM {$schema}.{$table}")===$count,'Incorrect count for '.$table);
  check((int)$db->fetchOne("SELECT count(*) FROM pg_tables WHERE schemaname=?",[$schema])===10,'Unexpected table set');
  check((int)$db->fetchOne("SELECT count(*) FROM {$schema}.competencies p WHERE NOT EXISTS(SELECT 1 FROM {$schema}.criterion_competencies cc WHERE cc.competency_id=p.id)")===0,'Uncovered competencies');
  foreach($counts as $table=>$count){
   $provider=new ReferenceDictionary($db,new SchemaSqlHelper($schema),str_replace('_','-',$table));
   $items=$provider->getItems();check(count($items)===$count,'Provider count mismatch');
   $cached=$provider->toCacheArray($items[0]);
   check($provider->fromCacheArray($cached)->toArray()===$items[0]->toArray(),'Cache roundtrip mismatch');
   if($table==='criteria')check(is_array($cached['check_templates']) && is_int($cached['version']) && is_bool($cached['active']),'Incorrect JSON/types');
  }
  $db->executeStatement("UPDATE {$schema}.competencies SET active=false WHERE code='api-design'");
  $provider=new ReferenceDictionary($db,new SchemaSqlHelper($schema),'criterion-competencies');
  check(count($provider->getItems())<count($provider->getItems(false)),'Inactive parent not filtered');
  try{$db->executeStatement("INSERT INTO {$schema}.criterion_competencies(criterion_id,competency_id,scope_note) SELECT id,99999999,'bad' FROM {$schema}.criteria LIMIT 1");throw new RuntimeException('Foreign key was not enforced');}catch(Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException){}
  try{$db->executeStatement("INSERT INTO {$schema}.criteria(code,version,name,description,check_templates,active) SELECT code,version+1,name,description,check_templates,true FROM {$schema}.criteria LIMIT 1");throw new RuntimeException('Active criterion version was not unique');}catch(Doctrine\DBAL\Exception\UniqueConstraintViolationException){}
  applyMigration($db,$root,$schema,'Version20261007190000');
  check((int)$db->fetchOne("SELECT count(*) FROM {$schema}.roles")===2,'Cleanup not repeat safe');
  echo ($index===0?'Fresh installation':'Populated legacy upgrade').": 10 tables, roles, preserved settings/countries, 1943 seed rows, providers, filtering and constraints passed\n";
 }
}finally{
 foreach($schemas as $schema)$db->executeStatement('DROP SCHEMA IF EXISTS '.$schema.' CASCADE');
 if($oldSchema===null)unset($_ENV['DB_SCHEMA']);else $_ENV['DB_SCHEMA']=$oldSchema;
}
