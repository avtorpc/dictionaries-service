<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use Doctrine\DBAL\{DriverManager, Tools\DsnParser};
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

$url=getenv('DATABASE_URL');
if (!is_string($url)||$url==='') throw new RuntimeException('DATABASE_URL is required');
$params=(new DsnParser(['postgresql'=>'pdo_pgsql','postgres'=>'pdo_pgsql']))->parse($url);
$admin=DriverManager::getConnection($params);
$name='dictionary_install_contract_'.bin2hex(random_bytes(5));
$kernel=null;$target=null;
try {
 $admin->executeStatement('CREATE DATABASE '.$name);
 $params['dbname']=$name;
 $target=DriverManager::getConnection($params);
 $target->executeStatement('CREATE SCHEMA dictionaries');
 $newUrl=preg_replace('~/[^/?]+(?=\?|$)~','/'.$name,$url,1);
 $_SERVER['DATABASE_URL']=$_ENV['DATABASE_URL']=$newUrl;
 $_SERVER['DB_SCHEMA']=$_ENV['DB_SCHEMA']='dictionaries';
 putenv('DATABASE_URL='.$newUrl);putenv('DB_SCHEMA=dictionaries');
 $kernel=new App\Kernel('prod',false);
 $app=new Application($kernel);$app->setAutoExit(false);$app->setCatchExceptions(false);
 foreach([1,2] as $run){
  $input=new ArrayInput(['command'=>'doctrine:migrations:migrate','--no-interaction'=>true]);
  $input->setInteractive(false);
  $output=new BufferedOutput();
  if($app->run($input,$output)!==0)throw new RuntimeException('Doctrine migration failed');
 }
 if((int)$target->fetchOne("SELECT count(*) FROM pg_tables WHERE schemaname='dictionaries'")!==10)throw new RuntimeException('Unexpected table set');
 if((int)$target->fetchOne('SELECT count(*) FROM public.doctrine_migration_versions_dictionaries')!==7)throw new RuntimeException('Migration metadata not recorded correctly');
 if((int)$target->fetchOne('SELECT count(*) FROM dictionaries.specialization_competencies')!==1543)throw new RuntimeException('Seed missing');
 echo "Full Doctrine fresh install and repeated no-op migration passed; metadata recorded in public\n";
}finally{
 if($kernel!==null)$kernel->shutdown();
 if($target!==null)$target->close();
 $admin->executeStatement('DROP DATABASE IF EXISTS '.$name.' WITH (FORCE)');
 $admin->close();
}
