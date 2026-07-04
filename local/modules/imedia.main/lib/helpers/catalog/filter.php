<?php
namespace Imedia\Main\Helpers\Catalog;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

class Filter
{
    private const CACHE_ID = 'filter-config';
    private const CACHE_TTL = 864000;
    private const CACHE_DIR = '/catalog';

    protected static ?array $config = null;

    public static function getConfig(): array
    {
        if(static::$config === null){

            $config = [];

            $cache = Cache::createInstance();

            if ($cache->initCache(static::CACHE_TTL, static::CACHE_ID, static::CACHE_DIR)) {
                $config = $cache->getVars();
            } elseif ($cache->startDataCache()) {
                Loader::includeModule('iblock');

                $iblockId = IblockHelper::getId('FILTERS');

                $arSelect = [
                    'IBLOCK_SECTION_ID',
                    'NAME',
                    'PROPERTY_PROPERTY',
                    'PROPERTY_SEARCH_USE',
                    'PROPERTY_SEARCH_PLACEHOLDER'
                ];

                $arFilter = [
                    '=ACTIVE' => 'Y',
                    '=IBLOCK_ID' => $iblockId,
                    '!IBLOCK_SECTION_ID' => false,
                    '!PROPERTY_PROPERTY' => false
                ];

                $arSort = [
                    'SORT' => 'ASC'
                ];

                $arItems = [];

                $query = \CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
                while($row = $query->GetNext(true, false)){

                    $arItem = [
                        'code' => $row['PROPERTY_PROPERTY_VALUE'],
                        'title' => $row['NAME'],
                        'useSearch' => (bool) $row['PROPERTY_SEARCH_USE_VALUE']
                    ];

                    if($row['PROPERTY_SEARCH_USE_VALUE']){
                        $arItem['searchPlaceholder'] = $row['PROPERTY_SEARCH_PLACEHOLDER_VALUE'];
                    }

                    $arItems[$row['IBLOCK_SECTION_ID']][] = $arItem;

                }

                $arFilter = [
                    '=ACTIVE' => 'Y',
                    '=IBLOCK_ID' => $iblockId,
                    '=DEPTH_LEVEL' => 1,
                    '!XML_ID' => false
                ];

                $arSelect = ['ID', 'XML_ID', 'NAME'];

                $query = \CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
                while($row = $query->GetNext(true, false)){

                    $arSection = [
                        'code' => $row['XML_ID'],
                        'title' => $row['NAME']
                    ];

                    if(isset($arItems[$row['ID']])){
                        $arSection['cols'] = $arItems[$row['ID']];
                    }

                    $config[] = $arSection;

                }

                $taggedCache = Application::getInstance()->getTaggedCache();
                $taggedCache->startTagCache(static::CACHE_DIR);
                $taggedCache->registerTag('iblock_id_' . $iblockId);
                $taggedCache->endTagCache();
                $cache->endDataCache($config);

            }

            static::$config = $config;

        }

        return static::$config;
    }
}