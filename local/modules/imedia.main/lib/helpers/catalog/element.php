<?php
namespace Imedia\Main\Helpers\Catalog;

use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\Model\Section;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

class Element
{
    /**
     * @param string $linkCode
     * @return array
     */
    public static function getProductsByLinkCode(string $linkCode): array
    {
        $result = [];

        Loader::includeModule('iblock');

        $iblockId = IblockHelper::getId('CATALOG');

        if (!($iblockId > 0)) {
            return $result;
        }

        $iblock = Iblock::wakeUp($iblockId);
        $entity = $iblock->getEntityDataClass();

        $query = $entity::getList([
            'select' => [
                'ID',
                'CODE',
                'IBLOCK_SECTION_ID',
                'NAME',
                'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL',
                'COLOR_' => Property::getCode('COLOR')
            ],
            'filter' => [
                Property::getCode('LINK_CODE') . '.VALUE' => $linkCode,
                '=ACTIVE' => true
            ]
        ]);
        while ($row = $query->fetch()) {
            $result[$row['ID']] = [
                'ID' => $row['ID'],
                'NAME' => $row['NAME'],
                'DETAIL_PAGE_URL' => \CIBlock::ReplaceDetailUrl($row['DETAIL_PAGE_URL'], $row, false, 'E')
            ];

            if (empty($row['COLOR_VALUE']))
                continue;

            $arColor = Color::get($row['COLOR_VALUE']);

            $result[$row['ID']]['COLOR_PICTURE'] = $arColor['PICTURE'];
        }

        return $result;
    }

    /**
     * @param array $codes
     * @return array
     */
    public static function getDeliveryMethods(array $codes): array
    {
        $result = [];

        $entityDataClass = self::getEntityDataClass('b_hlbd_delivery_reference');
        if (empty($entityDataClass))
            return $result;

        $arFilter = ['UF_DEF' => true];
        if (!empty($codes))
            $arFilter = ['UF_XML_ID' => $codes];

        $rsData = $entityDataClass::getList([
            'select' => [
                'UF_NAME',
                'UF_DESCRIPTION',
                'UF_FILE'
            ],
            'filter' => $arFilter
        ]);
        while ($arData = $rsData->fetch()) {
            $arTmp = $arData['UF_FILE'] > 0 ? \CFile::GetFileArray($arData['UF_FILE']) : false;

            $result[] = [
                'NAME' => $arData['UF_NAME'],
                'DESCRIPTION' => $arData['UF_DESCRIPTION'],
                'FILE' => $arTmp ? $arTmp['SRC'] : false
            ];
        }

        return $result;
    }

    /**
     * @param array $props
     * @return array
     */
    public static function getPropsGroups(array $props): array
    {
        $result = [];

        Loader::includeModule('iblock');

        $iblockId = IblockHelper::getId('PROPS_GROUPS');

        if (!($iblockId > 0)) {
            return $result;
        }

        $iblock = Iblock::wakeUp($iblockId);
        $entity = $iblock->getEntityDataClass();

        $query = $entity::getList([
            'select' => [
                'ID',
                'NAME',
                'PROPERTY_CODE_' => 'PROPERTY_CODE'
            ],
            'filter' => [
                'PROPERTY_CODE.VALUE' => array_keys($props)
            ],
            'order' => [
                'SORT' => 'ASC'
            ]
        ]);
        while ($row = $query->fetch()) {
            if (!isset($result[$row['ID']]['NAME']))
                $result[$row['ID']]['NAME'] = $row['NAME'];

            $result[$row['ID']]['PROPERTIES'][] = $props[$row['PROPERTY_CODE_VALUE']];
        }

        return $result;
    }

    /**
     * @param array $codes
     * @return array
     */
    public static function getCleanCareByCodes(array $codes): array
    {
        $result = [];

        $entityDataClass = self::getEntityDataClass('b_hlbd_clean_care_reference');
        if (empty($entityDataClass))
            return $result;

        $rsData = $entityDataClass::getList([
            'select' => [
                'UF_NAME',
                'UF_FILE'
            ],
            'filter' => [
                'UF_XML_ID' => $codes
            ]
        ]);
        while ($arData = $rsData->fetch()) {
            $arTmp = $arData['UF_FILE'] > 0 ? \CFile::GetFileArray($arData['UF_FILE']) : false;

            $result[] = [
                'NAME' => $arData['UF_NAME'],
                'FILE' => $arTmp ? $arTmp['SRC'] : false
            ];
        }

        return $result;
    }

    /**
     * @param int $iblockId
     * @param int $sectionId
     * @return array
     */
    public static function getRecommendProductsFilter(int $iblockId, array $sectionPath): array
    {
        $result = [];

        foreach($sectionPath as $arSectionPath) {
            $sectionIds[] = $arSectionPath['ID'];
        }

        if (!isset($sectionIds))
            return $result;

        $entity = Section::compileEntityByIblock($iblockId);
        $query = $entity::getList([
            'select' => [
                'ID',
                'UF_RECOMMEND'
            ],
            'filter' => [
                'ID' => $sectionIds
            ],
            'order' => [
                'DEPTH_LEVEL' => 'DESC'
            ]
        ]);
        while ($row = $query->fetch()) {
            if (!isset($newSectionIds) && !empty($row['UF_RECOMMEND']))
                $newSectionIds = $row['UF_RECOMMEND'];
        }

        if (!isset($newSectionIds))
            return $result;

        $maxLimit = 7;
        $leftCount = count($newSectionIds);
        foreach ($newSectionIds as $arSectionId) {
            $limit = ceil($maxLimit / $leftCount);

            $iblock = Iblock::wakeUp($iblockId);
            $entity = $iblock->getEntityDataClass();

            $query = $entity::getList([
                'select' => [
                    'ID'
                ],
                'filter' => [
                    'ACTIVE' => 'Y',
                    'IBLOCK_SECTION_ID' => $arSectionId
                ],
                'limit' => $limit
            ]);
            while ($row = $query->fetch()) {
                $result['ID'][] = $row['ID'];
                $maxLimit--;
            }

            $leftCount--;
        }

        return $result;
    }

    /**
     * @param string $hlBlockName
     * @return string
     */
    public static function getEntityDataClass(string $hlBlockName): string
    {
        $result = '';

        Loader::includeModule('highloadblock');

        $arHBlock = HighloadBlockTable::getList([
            'filter' => [
                'TABLE_NAME' => $hlBlockName
            ]
        ])->fetch();

        if (!$arHBlock)
            return $result;

        $entity = HighloadBlockTable::compileEntity($arHBlock);
        $result = $entity->getDataClass();

        return $result;
    }
}
