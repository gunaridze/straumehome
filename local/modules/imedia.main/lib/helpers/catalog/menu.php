<?php

namespace Imedia\Main\Helpers\Catalog;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\UserFieldTable;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Debug\Logger;

class Menu
{
    public const CACHE_SECTIONS_TAG = 'catalog-menu-sections';
    private const CACHE_SECTIONS_ID = 'menu-sections';
    private const CACHE_SECTIONS_TTL = 86400;
    private const CACHE_DIR = '/catalog';
    private const USER_FIELD_ENUM = [
        'UF_MENU_TYPE'
    ];

    protected static ?array $list = null;

    public static function get(): array
    {
        $arResult = static::getSections();
        return $arResult;
    }

    private static function getSections(): array
    {
        if(!static::$list){
            $arResult = [];

            try{
                $cache = Cache::createInstance();

                if ($cache->initCache(static::CACHE_SECTIONS_TTL, static::CACHE_SECTIONS_ID, static::CACHE_DIR)) {
                    $arResult = $cache->getVars();
                } elseif ($cache->startDataCache()) {

                    Loader::includeModule('iblock');

                    $arSections = [];

                    $iblockId = IblockHelper::getId('CATALOG');

                    if(!($iblockId > 0)){
                        $cache->abortDataCache();
                    }

                    $arSort = [
                        'SORT' => 'ASC',
                        'NAME' => 'ASC'
                    ];

                    $arFilter = [
                        '=ACTIVE' => 'Y',
                        '=GLOBAL_ACTIVE' => 'Y',
                        'IBLOCK_ID' => $iblockId,
                        '!CODE' => [
                            Section::CODE_NEW,
                            Section::CODE_SALE
                        ]
                    ];

                    $arSelect = [
                        'ID',
                        'NAME',
                        'PICTURE',
                        'DETAIL_PICTURE',
                        'SORT',
                        'IBLOCK_SECTION_ID',
                        'SECTION_PAGE_URL',
                        'DEPTH_LEVEL',
                        'CODE',
                        'UF_MENU_MAIN',
                        'UF_POPULAR',
                        'UF_LABEL_PLURAL',
                        'UF_MAINPAGE',
                        'UF_MENU_TYPE',
                        'UF_HIDE_LINK_NEW',
                        'UF_HIDE_LINK_SALE'
                    ];

                    $query = \CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
                    while($row = $query->GetNext(true, false)){
                        $arSections[] = $row;
                    }

                    $userFieldsMap = static::getUserFieldsMap($iblockId);

                    $arTree = static::makeSectionsTree($arSections, $userFieldsMap);
                    foreach($arTree as $arSection){
                        $arResult[$arSection['ID']] = $arSection;
                    }

                    $taggedCache = Application::getInstance()->getTaggedCache();
                    $taggedCache->startTagCache(static::CACHE_DIR);
                    $taggedCache->registerTag(static::CACHE_SECTIONS_TAG);
                    $taggedCache->endTagCache();
                    $cache->endDataCache($arResult);
                }
            } catch (\Exception $e){

                $logger = new Logger\Logger();
                $logger->routes->attach(new Logger\Route\File(
                    [
                        'isEnable' => true,
                        'logDir' => str_replace('Imedia\\Main', '', static::class)
                    ]
                ));

                $logger->critical($e->getMessage());

            }

            static::$list = $arResult;
        }

        return static::$list;
    }

    /**
     * @param array $arSections
     * @param array $arSelectedSection
     * @return array
     */
    private static function makeSectionsTree(
        array $arSections,
        array $userFieldsMap,
        array $arSelectedSection = []
    ): array
    {
        $parentId = (int) $arSelectedSection['ID'];

        $arChildren = [];
        foreach($arSections as $arSection){

            if((int) $arSection['IBLOCK_SECTION_ID'] !== $parentId){
                continue;
            }

            $arSectionFormatted = [
                'ID' => $arSection['ID'],
                'NAME' => $arSection['NAME'],
                'CODE' => $arSection['CODE'],
                'PICTURE' => $arSection['PICTURE'],
                'DETAIL_PICTURE' => $arSection['DETAIL_PICTURE'],
                'LINK' => $arSection['SECTION_PAGE_URL'],
                'LABEL_PLURAL' => $arSection['UF_LABEL_PLURAL'],
                'IS_POPULAR' => $arSection['UF_POPULAR'],
                'SHOW_IN_MENU_MAIN' => $arSection['UF_MENU_MAIN'],
                'MAINPAGE' => $arSection['UF_MAINPAGE'],
                'MENU_TYPE' => ($arSection['UF_MENU_TYPE'])
                    ? $userFieldsMap['UF_MENU_TYPE'][$arSection['UF_MENU_TYPE']]
                    : null
            ];

            if((int) $arSection['DEPTH_LEVEL'] === 1){

                $arSectionFormatted['HIDE_LINK_NEW'] = (bool) $arSection['UF_HIDE_LINK_NEW'];
                $arSectionFormatted['HIDE_LINK_SALE'] = (bool) $arSection['UF_HIDE_LINK_SALE'];

            }

            $arChildren[] = static::makeSectionsTree($arSections, $userFieldsMap, $arSectionFormatted);

        }

        if(empty($arSelectedSection)){
            $arSelectedSection = $arChildren;
        } else {
            $arSelectedSection['ITEMS'] = $arChildren;
        }

        return $arSelectedSection;
    }

    protected static function getUserFieldsMap(int $iblockId): array
    {
        $arFieldsMap = [];
        $query = UserFieldTable::getList(
            [
                'select' => ['ID', 'FIELD_NAME'],
                'filter' => [
                    'ENTITY_ID' => 'IBLOCK_'.$iblockId.'_SECTION',
                    'FIELD_NAME' => static::USER_FIELD_ENUM
                ]
            ]
        );
        while($row = $query->fetch()){
            $arFieldsMap[ $row['ID'] ] = $row['FIELD_NAME'];
        }

        $userFieldsMap = [];

        $query = \CUserFieldEnum::GetList([], ['=USER_FIELD_ID' => array_keys($arFieldsMap)]);
        while($row = $query->GetNext()){
            $userFieldsMap[ $arFieldsMap[$row['USER_FIELD_ID']] ][$row['ID']] = $row['XML_ID'];
        }

        return $userFieldsMap;
    }
}