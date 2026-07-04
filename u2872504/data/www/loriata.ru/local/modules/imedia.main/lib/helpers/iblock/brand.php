<?php
namespace Imedia\Main\Helpers\Iblock;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Imedia\Main\Helpers\Debug\Logger;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

class Brand
{
    private const CACHE_ID = 'brands';
    private const CACHE_TTL = 86400;
    private const CACHE_DIR = '/iblock';

    public static ?array $list = null;

    public static function get(int $brandId): ?array
    {
        if(!($brandId > 0)){
            return null;
        }

        $list = static::getList();
        return $list[$brandId];
    }

    public static function getBrandId(string $brandName): int
    {
        Loader::includeModule('iblock');

        $params = [
            'max_len' => '100',
            'change_case' => 'L',
            'replace_space' => '_',
            'replace_other' => '_',
            'delete_repeat_replace' => 'true',
            'use_google' => 'false'
        ];
        $code = \CUtil::translit($brandName, 'ru', $params);

        $list = static::getList();

        foreach($list as $arItem){

            if(
                ($arItem['NAME'] === $brandName)
                || ($arItem['CODE'] === $code)
            ){
                return (int) $arItem['ID'];
            }

        }

        $el = new \CIBlockElement;

        $iblockId = IblockHelper::getId('BRANDS');

        $arFields = [
            'NAME' => $brandName,
            'CODE' => $code,
            'SORT' => 500,
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $iblockId,
            'BREAK' => 'Y'
        ];

        $brandId = (int) $el->Add($arFields);
        static::$list = null;

        return $brandId;

    }

    public static function getList(): array
    {
        if(!static::$list){

            $arResult = [];

            try{

                $cache = Cache::createInstance();

                if ($cache->initCache(static::CACHE_TTL, static::CACHE_ID, static::CACHE_DIR)) {
                    $arResult = $cache->getVars();
                } elseif ($cache->startDataCache()) {

                    Loader::includeModule('iblock');

                    $iblockId = IblockHelper::getId('BRANDS');

                    if(!($iblockId > 0)){
                        $cache->abortDataCache();
                        return [];
                    }

                    $arSort = [];

                    $arFilter = [
                        '=ACTIVE' => 'Y',
                        '=IBLOCK_ID' => $iblockId
                    ];

                    $arSelect = [
                        'ID',
                        'NAME',
                        'CODE',
                        'DETAIL_PAGE_URL',
                        'PREVIEW_PICTURE'
                    ];

                    $query = \CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
                    while($row = $query->GetNext(true, false)){
                        $arResult[ $row['ID'] ] = $row;
                    }

                    $taggedCache = Application::getInstance()->getTaggedCache();
                    $taggedCache->startTagCache(static::CACHE_DIR);
                    $taggedCache->registerTag('iblock_id_' . $iblockId);
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
}