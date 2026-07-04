<?php
namespace Imedia\Main\Helpers\Orm\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\DB\MysqliSqlHelper;
use Bitrix\Main\Entity as BitrixEntity;
use Imedia\Main\Helpers\Debug\Debug;

class Base
{
    protected string $className;
    protected string $tableName;
    protected MysqliSqlHelper $sqlHelper;
    protected $entity;
    protected $connection;
    protected $migration;

    protected function __construct(string $className)
    {
        $this->className = $className;
        $this->tableName = $this->className::getTableName();
        $this->entity = $this->className::getEntity();
        $this->connection = Application::getConnection();
        $this->sqlHelper = new MysqliSqlHelper($this->connection);
    }

    public static function process(string $className)
    {
        try{

            $process = new static($className);
            $process->diff();
            $process->migrate();

        } catch(\Exception $e){
            echo Debug::printr($e->getMessage());
        }
    }

    protected function diff()
    {
        if (!$this->connection->isTableExists($this->tableName)) {
            $this->entity->createDbTable();
            throw new \Exception('This is a new table');
        } else{

            $up = [];
            $down = [];

            $fromFields = $this->getFieldsFromSchema();
            $toFields = $this->getFieldsFromOrm();

            do{

                $from = array_shift($fromFields);

                if (isset($toFields[$from->getName()])) {
                    $to = $toFields[$from->getName()];
                    unset($toFields[$from->getName()]);
                } else {
                    $up[] = $from->getDropQuery();
                    $down[] = $from->getAddQuery();
                    continue;
                }

                if ($from->equals($to)){
                    continue;
                }

                if ($from->getName() !== $to->getName()) {
                    $up[] = $from->getChangeQuery($to);
                    $down[] = $to->getChangeQuery($from);
                }

                $up[] = $from->getModifyQuery($to);
                $down[] = $to->getModifyQuery($from);

            }
            while(!empty($fromFields));

            foreach ($toFields as $to) {
                $up[] = $to->getAddQuery();
                $down[] = $to->getDropQuery();
            }

            if(
                empty($up) &&
                empty($down)
            ){
                throw new \Exception('No changes found');
            }

            $this->migration = new Entity\Migration($this->connection, $up, $down);
        }
    }

    protected function getFieldsFromSchema(): array
    {
        $result = [];

        $sql = "
			SELECT
			  COLUMN_NAME,
			  DATA_TYPE,
			  COLUMN_TYPE,
			  COLUMN_KEY,
			  EXTRA
			FROM information_schema.COLUMNS
			WHERE TABLE_NAME = '" . $this->sqlHelper->forSql($this->tableName, 200) . "'
		";

        $query = $this->connection->query($sql);
        while($row = $query->fetch()){

            $field = new Entity\Field();

            $field->setTableName($this->tableName);
            $field->setName($row['COLUMN_NAME']);

            $type = ($row['DATA_TYPE'] === 'int') ? 'int' : $row['COLUMN_TYPE'];

            if($row['COLUMN_KEY'] === 'EXTRA'){
                $type .= ' AUTO_INCREMENT ';
            }

            $field->setType($type);

            $result[$row['COLUMN_NAME']] = $field;

        }

        return $result;
    }

    protected function getFieldsFromOrm(): array
    {
        $result = [];

        $arFields = $this->entity->getFields();

        foreach($arFields as $fieldOrm){

            if (
                ($fieldOrm instanceof BitrixEntity\ReferenceField) ||
                ($fieldOrm instanceof BitrixEntity\ExpressionField)
            ){
                continue;
            }

            $field = new Entity\Field();

            $field->setTableName($this->tableName);
            $field->setName($fieldOrm->getName());

            $type = $this->sqlHelper->getColumnTypeByField($fieldOrm);

            if ($fieldOrm->isAutocomplete()){
                $type .= ' AUTO_INCREMENT ';
            }

            $field->setType($type);

            $result[$fieldOrm->getName()] = $field;

        }

        return $result;
    }

    protected function migrate()
    {
        $this->migration->up();
    }
}