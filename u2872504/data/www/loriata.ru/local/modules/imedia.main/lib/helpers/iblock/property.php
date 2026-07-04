<?php
namespace Imedia\Main\Helpers\Iblock;

use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;

class Property
{
    static array $bridge = [];

    public static function getPropertyValuesArray(array $elementIds, int $iblockId, array $propertyFilter = []): array
    {

        Loader::includeModule('iblock');

        $arProperties = [];

        $query = PropertyTable::getList(
            [
                'select' => ['ID', 'IBLOCK_ID', 'ACTIVE', 'SORT', 'NAME', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE'],
                'filter' => array_merge(
                    [
                        '=IBLOCK_ID' => $iblockId
                    ],
                    $propertyFilter
                )
            ]
        );
        while($row = $query->fetch()){
            $arProperties[ $row['ID'] ] = $row;
        }

        if(empty($arProperties)){
            return [];
        }

        $arPropertyValues = [];

        $enumPropertiesIds = [];
        $enumPropertiesValueIds = [];

        $query = ElementPropertyTable::getList(
            [
                'filter' => [
                    '=IBLOCK_PROPERTY_ID' => array_keys($arProperties),
                    '=IBLOCK_ELEMENT_ID' => $elementIds
                ]
            ]
        );
        while($row = $query->fetch()){
            $arProperty = $arProperties[$row['IBLOCK_PROPERTY_ID']];

            if(!isset($arPropertyValues[ $row['IBLOCK_ELEMENT_ID'] ][ $arProperty['CODE'] ])){
                $arPropertyValues[ $row['IBLOCK_ELEMENT_ID'] ][ $arProperty['CODE'] ] = $arProperty;
                if($arProperty['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST){
                    $enumPropertiesIds[] = $arProperty['ID'];
                }
            }

            if($arProperty['MULTIPLE'] === 'N'){
                $arPropertyValues[ $row['IBLOCK_ELEMENT_ID'] ][ $arProperty['CODE'] ]['VALUE'] = $row['VALUE'];
                $arPropertyValues[ $row['IBLOCK_ELEMENT_ID'] ][ $arProperty['CODE'] ]['DESCRIPTION'] = $row['DESCRIPTION'];
                $arPropertyValues[ $row['IBLOCK_ELEMENT_ID'] ][ $arProperty['CODE'] ]['PROPERTY_VALUE_ID'] = $row['ID'];
            } else {
                $arPropertyValues[ $row['IBLOCK_ELEMENT_ID'] ][ $arProperty['CODE'] ]['VALUE'][] = $row['VALUE'];
                $arPropertyValues[ $row['IBLOCK_ELEMENT_ID'] ][ $arProperty['CODE'] ]['DESCRIPTION'][] = $row['DESCRIPTION'];
                $arPropertyValues[ $row['IBLOCK_ELEMENT_ID'] ][ $arProperty['CODE'] ]['PROPERTY_VALUE_ID'][] = $row['ID'];
            }

            if(
                ($arProperty['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST) &&
                $row['VALUE']
            ){
                $enumPropertiesValueIds[] = $row['VALUE'];
            }
        }

        if(
            !empty($enumPropertiesIds)
            && !empty($enumPropertiesValueIds)
        ){

            $enumValues = static::getEnumValues($enumPropertiesIds, $enumPropertiesValueIds);

            foreach($arPropertyValues as $key => $arElementProperties){

                foreach($arElementProperties as $propertyCode => $arProperty){

                    if($arProperty['PROPERTY_TYPE'] !== PropertyTable::TYPE_LIST){
                        continue;
                    }

                    if(is_array($arProperty['VALUE'])){

                        foreach($arProperty['VALUE'] as $i => $value){
                            $arPropertyValues[$key][$propertyCode]['VALUE_ENUM_ID'][$i] = $value;
                            $arPropertyValues[$key][$propertyCode]['VALUE'][$i] = $enumValues[$value]['VALUE'];
                            $arPropertyValues[$key][$propertyCode]['VALUE_ENUM'][$i] = $enumValues[$value]['VALUE'];
                        }

                    } else{

                        $arPropertyValues[$key][$propertyCode]['VALUE_ENUM_ID'] = $arProperty['VALUE'];
                        $arPropertyValues[$key][$propertyCode]['VALUE'] = $enumValues[$arProperty['VALUE']]['VALUE'];
                        $arPropertyValues[$key][$propertyCode]['VALUE_ENUM'] = $enumValues[$arProperty['VALUE']]['VALUE'];

                    }

                }

            }

        }

        return $arPropertyValues;
    }

    public static function getEnumValues(array $enumPropertiesIds, array $enumPropertiesValueIds): array
    {
        $enumValues = [];

        $query = PropertyEnumerationTable::getList(
            [
                'select' => ['ID', 'VALUE', 'SORT', 'XML_ID'],
                'filter' => [
                    '=ID' => array_unique($enumPropertiesValueIds),
                    '=PROPERTY_ID' => array_unique($enumPropertiesIds)
                ]
            ]
        );
        while($row = $query->fetch()){
            $enumValues[ $row['ID'] ] = $row;
        }

        return $enumValues;
    }

    public static function getPropertyByCode(int $iblockId, string $code): ?array
    {
        if(!static::$bridge[$iblockId][$code]){

            $query = PropertyTable::getList([
                'select' => ['ID'],
                'filter' => [
                    '=IBLOCK_ID' => $iblockId,
                    '=CODE' => $code
                ]
            ]);

            if($row = $query->fetch()){
                static::$bridge[$iblockId][$code] = $row;
            }

        }

        return static::$bridge[$iblockId][$code];
    }
}