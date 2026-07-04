<?php


namespace Imedia\Main\Helpers\Iblock;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Iblock\PropertyIndex\Facet as FacetIndex;
use Bitrix\Iblock\PropertyIndex\Storage;
use Bitrix\Iblock\IblockTable;


class Facet
{
    public static function getSectionsIdsFromPropertyValue(int $iblockId, string $propertyId, $value): array
    {
        $arSectionsIds = [];

        Loader::includeModule('iblock');

        if($propertyId !== (int) $propertyId){
            $arProperty = Property::getPropertyByCode($iblockId, $propertyId);
            $propertyId = $arProperty['ID'];
        }

        $facet = new FacetIndex($iblockId);
        $storage = $facet->getStorage();
        $facetId = Storage::propertyIdToFacetId($propertyId);

        $connection = Application::getConnection();
        $query = $connection->query("SELECT DISTINCT SECTION_ID FROM ".$storage->getTableName()." WHERE FACET_ID = " . $facetId ." AND SECTION_ID > 0 AND VALUE = " . $value);
        while($row = $query->fetch()){
            $arSectionsIds[] = $row['SECTION_ID'];
        }

        return $arSectionsIds;
    }

    public static function getValuesFromPropertyInSection(int $iblockId, string $propertyId, int $sectionId): array
    {
        $arValues = [];

        Loader::includeModule('iblock');

        if($propertyId !== (int) $propertyId){
            $arProperty = Property::getPropertyByCode($iblockId, $propertyId);
            $propertyId = $arProperty['ID'];
        }

        $facet = new FacetIndex($iblockId);
        $storage = $facet->getStorage();
        $facetId = Storage::propertyIdToFacetId($propertyId);

        $connection = Application::getConnection();

        $query = $connection->query("SELECT DISTINCT VALUE FROM ".$storage->getTableName()." WHERE FACET_ID = " . $facetId ." AND SECTION_ID = ". $sectionId);
        while($row = $query->fetch()){
            $arValues[] = $row['VALUE'];
        }

        return $arValues;
    }

    public static function getElementIdsFromPropertyValueInSection(
        int $iblockId,
        string $propertyId,
        int $sectionId,
        string $propertyValue
    ): array
    {
        $ids = [];

        Loader::includeModule('iblock');

        if($propertyId !== (int) $propertyId){
            $arProperty = Property::getPropertyByCode($iblockId, $propertyId);
            $propertyId = $arProperty['ID'];
        }

        $facet = new FacetIndex($iblockId);
        $storage = $facet->getStorage();
        $facetId = Storage::propertyIdToFacetId($propertyId);

        $connection = Application::getConnection();

        $sql = "SELECT DISTINCT ELEMENT_ID FROM ".$storage->getTableName();
        $sql .= " WHERE FACET_ID = " . $facetId;
        $sql .= " AND SECTION_ID = ". $sectionId;
        $sql .= " AND VALUE = ". $propertyValue;

        $query = $connection->query($sql);
        while($row = $query->fetch()){
            $ids[] = $row['ELEMENT_ID'];
        }

        return $ids;
    }

    public static function checkIndex(int $iblockId): bool
    {
        Loader::includeModule('iblock');

        $iblockInfo = IblockTable::getList(
            [
                'select' => ['ID', 'PROPERTY_INDEX'],
                'filter' => ['=ID' => $iblockId],
                'limit' => 1
            ]
        )->fetch();

        return ($iblockInfo['PROPERTY_INDEX'] === 'Y');
    }
}