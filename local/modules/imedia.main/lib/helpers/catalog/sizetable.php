<?php

namespace Imedia\Main\Helpers\Catalog;

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;

use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Image\Resize;

class SizeTable
{
    /**
     * @param int $productId
     * @param int|null $elementId
     * @param array|null $sectionIds
     * @return Result
     */
    public static function get(int $productId, int $elementId = null, array $sectionIds = null): Result
    {
        $result = new Result();

        if ($elementId > 0) {
            $data = static::getByFilter([
                'ACTIVE' => 'Y',
                'ID' => $elementId
            ]);
        } else {
            $arProduct = static::getProductById($productId);

            if(!empty($sectionIds)) {
                foreach($sectionIds as $sectionId) {
                    $sectionPath[$sectionId] = true;
                }
            } else {
                $sectionPath = static::getSectionPathById($arProduct['IBLOCK_ID'], $arProduct['IBLOCK_SECTION_ID']);
            }
			if($arProduct['SIZECONVERSIONTABLEID']){
				$data = static::getByFilter([
                    'ACTIVE' => 'Y',
                    '=CODE' => $arProduct['SIZECONVERSIONTABLEID'],
                ]);
                if (empty($data)) {
                    $data = static::getByFilter([
                        'ACTIVE' => 'Y',
                        'BRAND.VALUE' => $arProduct['BRAND_ID'],
                        'SECTION.VALUE' => false
                    ]);
                }
			}elseif (intval($arProduct['BRAND_ID']) > 0) { 
                $data = static::getByFilter([
                    'ACTIVE' => 'Y',
                    'BRAND.VALUE' => $arProduct['BRAND_ID'],
                    'SECTION.VALUE' => array_keys($sectionPath)
                ]);
                if (empty($data)) {
                    $data = static::getByFilter([
                        'ACTIVE' => 'Y',
                        'BRAND.VALUE' => $arProduct['BRAND_ID'],
                        'SECTION.VALUE' => false
                    ]);
                }
            }

            if (!isset($data) || empty($data)) {
                $data = static::getByFilter([
                    'ACTIVE' => 'Y',
                    'BRAND.VALUE' => false,
                    'SECTION.VALUE' => array_keys($sectionPath)
                ]);
                if (empty($data)) {
                    $data = static::getByFilter([
                        'ACTIVE' => 'Y',
                        'BRAND.VALUE' => false,
                        'SECTION.VALUE' => false
                    ]);
                }
            }
        }

        if (!empty($data))
            $result->setData($data);

        return $result;
    }

    /**
     * @param int $productId
     * @return array
     */
    public static function getProductById(int $productId): array
    {
        $result = [];

        Loader::includeModule('iblock');

        $iblockId = IblockHelper::getId('CATALOG');
        if(!($iblockId > 0)) {
            return $result;
        }

        $iblock = Iblock::wakeUp($iblockId);
        $entity = $iblock->getEntityDataClass();

        $propertyCodeBrand = Property::getCode('BRAND');

        $query = $entity::getList([
            'select' => [
                'IBLOCK_ID',
                'IBLOCK_SECTION_ID',
                'BRAND_ID' => "{$propertyCodeBrand}.ELEMENT.ID",
				'SIZECONVERSIONTABLEID_' => 'SIZECONVERSIONTABLEID',
            ],
            'filter' => [
                'ID' => $productId
            ]
        ]);
        if ($row = $query->fetch()) {
            $result = [
                'IBLOCK_ID' => $row['IBLOCK_ID'],
                'IBLOCK_SECTION_ID' => $row['IBLOCK_SECTION_ID'],
                'BRAND_ID' => $row['BRAND_ID'],
                'SIZECONVERSIONTABLEID' => $row['SIZECONVERSIONTABLEID_VALUE'],
            ];
        }

        return $result;
    }

    /**
     * @param int $iblockId
     * @param int $sectionId
     * @return array
     */
    public static function getSectionPathById(int $iblockId, int $sectionId): array
    {
        $result = [];

        Loader::includeModule('iblock');

        $rsPath = \CIBlockSection::GetNavChain($iblockId, $sectionId, ['ID']);
        while ($arPath = $rsPath->GetNext()) {
            $result[$arPath['ID']] = true;
        }

        return $result;
    }

    /**
     * @param array $arFilter
     * @return array
     */
    public static function getByFilter(array $arFilter): array
    {
        $result = [];

        Loader::includeModule('iblock');

        $iblockId = IblockHelper::getId('SIZE_TABLE');
        if(!($iblockId > 0)) {
            return $result;
        }

        $iblock = Iblock::wakeUp($iblockId);
        $entity = $iblock->getEntityDataClass();

        $query = $entity::getList([
            'select' => [
                'NAME',
                'PREVIEW_TEXT',
                'DETAIL_TEXT',
                'BRAND_ID' => 'BRAND.ELEMENT.ID',
                'BRAND_NAME' => 'BRAND.ELEMENT.NAME',
                'BRAND_PICTURE' => 'BRAND.ELEMENT.PREVIEW_PICTURE',
            ],
            'filter' => $arFilter
        ]);
        if ($row = $query->fetch()) {
            $result = [
                'NAME' => $row['NAME'],
                'PREVIEW_TEXT' => $row['PREVIEW_TEXT'],
                'DETAIL_TEXT' => $row['DETAIL_TEXT']
            ];

            if (intval($row['BRAND_ID']) > 0) {
                $result['BRAND'] = [
                    'ID' => $row['BRAND_ID'],
                    'NAME' => $row['BRAND_NAME'],
                    'PICTURE' => $row['BRAND_PICTURE'] > 0
                        ? Resize::setSelfResizeArray($row['BRAND_PICTURE'], [120, 90, BX_RESIZE_IMAGE_PROPORTIONAL])
                        : null
                ];
            }
        }

        return $result;
    }
}

