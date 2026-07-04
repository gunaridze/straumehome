<?php
namespace Imedia\Main\Helpers\Catalog;

use Bitrix\Main\Loader;
use Bitrix\Sale\Fuser;
use Bitrix\Main\Type;
use Bitrix\Main\Result;
use Imedia\Main\Models\DefferedProduct\DefferedProductTable;
use Imedia\Main\Models\DefferedProduct\DefferedProduct as DefferedProductObject;

class DefferedProduct
{
    public static function getAll(int $ownerId = null): Result
    {
        $result = new Result();

        $data = [
            'favorites' => [],
            'comparison' => []
        ];

        if(!$ownerId){
            $ownerId = static::getOwnerId();
        }

        $query = DefferedProductTable::getList([
            'filter' => [
                '=OWNER' => $ownerId
            ]
        ]);

        while($obj = $query->fetchObject()){
            $data[ strtolower($obj->getType()) ][] = $obj->getElementId();
        }

        $result->setData($data);

        return $result;
    }

    public static function getOwnerId(): int
    {
        Loader::includeModule('sale');
        $fUserId = Fuser::getId();

        return $fUserId;
    }

    public static function add(int $productId, string $type): Result
    {
        $result = new Result();

        $type = strtoupper($type);
        $ownerId = static::getOwnerId();

        $defferedProduct = DefferedProductTable::getList([
            'filter' => [
                '=ELEMENT_ID' => $productId,
                '=TYPE' => $type,
                '=OWNER' => $ownerId
            ]
        ])->fetchObject();
        if(!$defferedProduct){

            $defferedProduct = new DefferedProductObject();
            $defferedProduct->setElementId($productId);
            $defferedProduct->setType($type);
            $defferedProduct->setDate(new Type\DateTime());
            $defferedProduct->setOwner($ownerId);
            $saveResult = $defferedProduct->save();
            if(!($saveResult->isSuccess())){
                return $saveResult;
            }

        }

        $result->setData(
            [
                'id' => $defferedProduct->getId()
            ]
        );

        return $result;
    }

    public static function remove(int $productId, string $type): Result
    {
        $result = new Result();

        $type = strtoupper($type);
        $ownerId = static::getOwnerId();

        $defferedProduct = DefferedProductTable::getList([
            'filter' => [
                '=ELEMENT_ID' => $productId,
                '=TYPE' => $type,
                '=OWNER' => $ownerId
            ]
        ])->fetchObject();

        if($defferedProduct){
            $defferedProduct->delete();
        }

        return $result;
    }
}