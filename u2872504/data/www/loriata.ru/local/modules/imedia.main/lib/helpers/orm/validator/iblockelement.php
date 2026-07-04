<?php
namespace Imedia\Main\Helpers\Orm\Validator;

use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Fields\Validators\Validator;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;

class IblockElement extends Validator
{
    protected int $iblockId;

    public function __construct($iblockId, $errorPhrase = null)
    {
        if(!((int) $iblockId > 0)){
            throw new ArgumentTypeException('iblockId', 'integer');
        }

        $this->iblockId = (int) $iblockId;

        parent::__construct($errorPhrase);
    }

    public function getIblockId(): int
    {
        return $this->iblockId;
    }

    public function validate($value, $primary, array $row, Field $field)
    {
        Loader::includeModule('iblock');

        $arItem = ElementTable::getList(
            [
                'select' => ['ID'],
                'filter' => [
                    '=IBLOCK_ID' => $this->iblockId,
                    '=ID' => $value,
                    '=ACTIVE' => true
                ],
                'limit' => 1
            ]
        )->fetch();

        if(!$arItem){
            return $this->getErrorMessage($value, $field, $this->errorPhrase);
        }

        return true;
    }
}