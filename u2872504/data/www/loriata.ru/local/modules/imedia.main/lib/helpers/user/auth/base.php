<?php
namespace Imedia\Main\Helpers\User\Auth;

use Bitrix\Main\Loader;
use Bitrix\Sale\Fuser;
use Imedia\Main\Helpers\Sale\Cart;
use Imedia\Main\Helpers\Catalog\DefferedProduct;
use Imedia\Main\Models\DefferedProduct as Model;

abstract class Base
{
    protected static function getFUserId(): int
    {
        Loader::includeModule('sale');

        $fUserId = (int) Fuser::getId();

        return $fUserId;
    }

    protected static function transferData(int $fUserId, int $userId): void
    {
        $fAuthorizedUserId = (int) Fuser::getIdByUserId($userId);

        if($fAuthorizedUserId > 0){
            static::transferBasket($fUserId, $fAuthorizedUserId);
            static::transferDefferedProducts($fUserId, $fAuthorizedUserId);
        }
    }

    protected static function transferBasket(int $fUserId, int $fAuthorizedUserId): void
    {
        $isEmpty = true;
        $basket = Cart::getBasket($fUserId);
        foreach($basket as $item){
            $isEmpty = false;
            break;
        }

        if($isEmpty){
            return;
        }

        $authorizedBasket = Cart::getBasket($fAuthorizedUserId);

        foreach($authorizedBasket as $item){
            $item->delete();
        }

        foreach($basket as $item){
            $authorizedBasket->addItem($item);
        }

        $authorizedBasket->save();
    }

    protected static function transferDefferedProducts(int $fUserId, int $fAuthorizedUserId): void
    {
        $oldDefferedProducts = DefferedProduct::getAll($fUserId)->getData();

        $isEmpty = true;

        foreach($oldDefferedProducts as $typeElements){
            if(!empty($typeElements)){
                $isEmpty = false;
                break;
            }
        }

        if($isEmpty){
            return;
        }

        $query = Model\DefferedProductTable::getList(
            [
                'filter' => ['=OWNER' => $fUserId]
            ]
        );
        while($obj = $query->fetchObject()){
            $obj->delete();
        }

        $defferedProducts = [];

        $query = Model\DefferedProductTable::getList(
            [
                'filter' => ['=OWNER' => $fAuthorizedUserId]
            ]
        );
        while($obj = $query->fetchObject()){
            $defferedProducts[ strtolower($obj->getType()) ][$obj->getElementId()] = true;
        }

        $defferedCollection = new Model\DefferedProducts();

        $objDate = new \Bitrix\Main\Type\DateTime();

        foreach($oldDefferedProducts as $type => $typeElements){
            foreach($typeElements as $elementId){

                if(isset($defferedProducts[$type][$elementId])){
                    continue;
                }

                $defferedProduct = new Model\DefferedProduct();
                $defferedProduct->setElementId($elementId);
                $defferedProduct->setType(strtoupper($type));
                $defferedProduct->setDate($objDate);
                $defferedProduct->setOwner($fAuthorizedUserId);

                $defferedCollection[] = $defferedProduct;

            }
        }

        $defferedCollection->save();
    }
}