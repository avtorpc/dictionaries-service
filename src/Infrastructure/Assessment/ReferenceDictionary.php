<?php
declare(strict_types=1);
namespace App\Infrastructure\Assessment;
use App\Application\Dictionaries\DTO\{DictionaryDtoInterface, ReferenceItemDto};
use App\Domain\Dictionaries\DictionaryProviderInterface;
use App\Infrastructure\DB\SchemaSqlHelper;
use Doctrine\DBAL\Connection;

/** A configured provider for the seven reference dictionaries; never accepts a browser table name. */
final class ReferenceDictionary implements DictionaryProviderInterface
{
    private const TABLES = [
        'specializations'=>'specializations', 'grades'=>'grades', 'competencies'=>'competencies',
        'technologies'=>'technologies', 'specialization-competencies'=>'specialization_competencies',
        'criteria'=>'criteria', 'criterion-competencies'=>'criterion_competencies',
    ];
    public function __construct(private Connection $connection, private SchemaSqlHelper $schemaSqlHelper, private string $name)
    {
        if (!isset(self::TABLES[$name])) throw new \InvalidArgumentException('Unknown reference dictionary');
    }
    public function getName(): string { return $this->name; }
    public function getItems(bool $onlyActive=true): array
    {
        $table = $this->schemaSqlHelper->table(self::TABLES[$this->name]);
        $where = ''; $order = 'd.id';
        $sql = "SELECT d.* FROM {$table} d";
        if ($this->name === 'specialization-competencies') {
            $sql .= ' JOIN '.$this->schemaSqlHelper->table('specializations').' s ON s.id=d.specialization_id JOIN '.$this->schemaSqlHelper->table('competencies').' c ON c.id=d.competency_id';
            if ($onlyActive) $where=' WHERE s.active AND c.active';
            $order='d.specialization_id,d.competency_id';
        } elseif ($this->name === 'criterion-competencies') {
            $sql .= ' JOIN '.$this->schemaSqlHelper->table('criteria').' c ON c.id=d.criterion_id JOIN '.$this->schemaSqlHelper->table('competencies').' p ON p.id=d.competency_id';
            if ($onlyActive) $where=' WHERE d.active AND c.active AND p.active';
            $order='d.criterion_id,d.competency_id';
        } elseif ($this->name === 'grades') {
            $order='d.sort_order,d.id';
        } elseif ($onlyActive) {
            $where=' WHERE d.active';
        }
        return array_map(function(array $row): ReferenceItemDto {
            foreach ($row as $key=>$value) {
                if ($key==='id' || str_ends_with($key,'_id') || in_array($key,['version','sort_order'],true)) $row[$key]=(int)$value;
                if ($key==='active') $row[$key]=in_array($value,[true,1,'1','t','true'],true);
                if ($key==='check_templates' && is_string($value)) $row[$key]=json_decode($value,true,512,JSON_THROW_ON_ERROR);
            }
            return new ReferenceItemDto($row);
        }, $this->connection->fetchAllAssociative($sql.$where.' ORDER BY '.$order));
    }
    public function toCacheArray(DictionaryDtoInterface $dto): array { return $dto->toArray(); }
    public function fromCacheArray(array $data): DictionaryDtoInterface { return new ReferenceItemDto($data); }
}
