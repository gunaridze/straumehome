<?php
namespace Imedia\Main\Helpers\Catalog\Service;

use Bitrix\Main\Loader;
use Bitrix\Catalog\GroupTable;
use Bitrix\Catalog\ProductTable;
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

class Deactivate
{
    protected int $elementId;
    protected int $basePriceId;
    protected array $arItem;

    protected function __construct(int $elementId)
    {
        $this->elementId = $elementId;
    }

    public static function process(int $elementId)
    {
        $process = new static($elementId);

        $process->basePriceId = $process->getBasePriceId();
        if(!($process->basePriceId > 0)){
            return;
        }

        $process->arItem = $process->getItem();
        if(empty($process->arItem)){
            return;
        }

        $productType = (int) $process->arItem['TYPE'];
        $iblockId = (int) $process->arItem['IBLOCK_ID'];

        if($productType === ProductTable::TYPE_SKU){

            if(
                $process->haveActiveOffers()
                && $process->arItem['DETAIL_PICTURE']
            ){
                return;
            }

        } else if (
            ($productType === ProductTable::TYPE_PRODUCT)
            || ($iblockId === IblockHelper::getId('CATALOG'))
        ){

            if(
                $process->arItem['DETAIL_PICTURE']
                && ((float) $process->arItem['PRICE_' . $process->getBasePriceId()] > 0)
            ){
                return;
            }

        } else if (
            ($productType === ProductTable::TYPE_OFFER)
            || ($iblockId === IblockHelper::getId('OFFERS'))
        ) {

            if((float) $process->arItem['PRICE_' . $process->getBasePriceId()] > 0){
                return;
            }

        } else {
            return;
        }

        $process->deactivate();

        $process->checkParent();

    }

    protected function getBasePriceId(): int
    {
        Loader::includeModule('catalog');

        $arPriceGroup = GroupTable::getList(
            [
                'select' => ['ID'],
                'filter' => ['=BASE' => true],
                'limit' => 1
            ]
        )->fetch();

        return (int) $arPriceGroup['ID'];
    }

    protected function getItem(): ?array
    {
        Loader::includeModule('iblock');

        $arFilter = [
            '=IBLOCK_ID' => [
                IblockHelper::getId('CATALOG'),
                IblockHelper::getId('OFFERS')
            ],
            '=ID' => $this->elementId
        ];

        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'TYPE',
            'DETAIL_PICTURE',
            'PRICE_' . $this->getBasePriceId(),
            'PROPERTY_' . Property::getCode('CML2_LINK')
        ];

        $arElement = \CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect)
            ->GetNext(true, false);

        if ($arElement === false) {
            return null;
             } else {
            return $arElement;
         }
    }

    protected function haveActiveOffers(): bool
    {
        $arFilter = [
            '=ACTIVE' => 'Y',
            '=IBLOCK_ID' => [
                IblockHelper::getId('OFFERS')
            ],
            '=PROPERTY_' . Property::getCode('CML2_LINK') => $this->elementId
        ];

        return (bool) \CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], ['ID'])->Fetch();
    }

    protected function deactivate(): void
    {
        if($this->arItem['ACTIVE'] === 'N'){
            return;
        }

        $el = new \CIBlockElement;
        $el->Update($this->elementId, [
            'ACTIVE' => 'N',
            'BREAK' => 'Y'
        ]);
    }

    protected function checkParent(): void
    {
        if((int) $this->arItem['TYPE'] === ProductTable::TYPE_OFFER){
            static::process($this->arItem['PROPERTY_' . Property::getCode('CML2_LINK') . '_VALUE']);
        }
    }
}