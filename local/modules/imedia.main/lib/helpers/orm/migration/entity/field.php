<?php
namespace Imedia\Main\Helpers\Orm\Migration\Entity;

use Imedia\Main\Helpers\Orm\Migration\Interfaces\Field as FieldInterface;

class Field implements FieldInterface
{
    protected string $tableName;
    protected string $name;
    protected string $type;

    public function setTableName(string $value): void
    {
        $this->tableName = $value;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setName(string $value): void
    {
        $this->name = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setType(string $value): void
    {
        $this->type = $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function equals(FieldInterface $field): bool
    {
        if ($this->name !== $field->getName()){
            return false;
        }

        if (
            ($this->type === 'blob')
            && (strpos($field->getType(), 'varchar') !== false)
        ){
            return true;
        }

        return true;
    }


    public function getDropQuery(): string
    {
        $query = 'ALTER TABLE `' . $this->tableName . '` DROP COLUMN ';
        $query .= '`' . $this->name . '`;';

        return $query;
    }

    public function getAddQuery(): string
    {
        $query = 'ALTER TABLE `' . $this->tableName . '` ADD ';
        $query .= '`' . $this->name . '` ';
        $query .= " " . $this->type . " ";

        return $query;
    }

    public function getChangeQuery(FieldInterface $field): string
    {
        $query = 'ALTER TABLE `' . $this->tableName . '` CHANGE ';
        $query .= ' `' . $this->name . '` ';
        $query .= ' `' . $field->getName() . '` ';
        $query .= " " . $field->getType() . " ";

        return $query;
    }

    public function getModifyQuery(FieldInterface $field): string
    {
        $query = 'ALTER TABLE `' . $this->tableName . '` MODIFY ';
        $query .= ' `' . $this->name . '` ';

        if ($this->type === 'blob'){
            $query .= " blob ";
        } else{
            $query .= " " . $field->getType() . " ";
        }

        return $query;
    }

}