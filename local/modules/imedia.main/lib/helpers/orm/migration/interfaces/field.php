<?php
namespace Imedia\Main\Helpers\Orm\Migration\Interfaces;


interface Field
{
    public function equals(Field $field);

    public function setTableName(string $value);

    public function getTableName();

    public function setName(string $value);

    public function getName();

    public function setType(string $value);

    public function getType();
}