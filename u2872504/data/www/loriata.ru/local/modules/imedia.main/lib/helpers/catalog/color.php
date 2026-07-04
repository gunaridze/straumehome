<?php
namespace Imedia\Main\Helpers\Catalog;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Imedia\Main\Helpers\Debug\Logger;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

class Color
{
    private const CACHE_ID = 'colors';
    private const CACHE_TTL = 864000;
    private const CACHE_DIR = '/catalog';

    protected static ?array $list = null;

    public static function getList(): array
    {
        if(static::$list === null){

            $arResult = [];

            try{

                $cache = Cache::createInstance();

                if ($cache->initCache(static::CACHE_TTL, static::CACHE_ID, static::CACHE_DIR)) {
                    $arResult = $cache->getVars();
                } elseif ($cache->startDataCache()) {

                    Loader::includeModule('iblock');

                    $iblockId = IblockHelper::getId('COLORS');

                    if(!($iblockId > 0)){
                        $cache->abortDataCache();
                        return [];
                    }

                    $arSort = [
                        'SORT' => 'ASC',
                        'NAME' => 'ASC'
                    ];

                    $arFilter = [
                        '=ACTIVE' => 'Y',
                        '=IBLOCK_ID' => $iblockId
                    ];

                    $arSelect = [
                        'ID',
                        'NAME',
                        'SORT',
                        'PREVIEW_PICTURE'
                    ];

                    $query = \CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
                    while($row = $query->GetNext(true, false)){

                        $arResult['ITEMS'][] = [
                            'ID' => (int) $row['ID'],
                            'SORT' => (int) $row['SORT'],
                            'NAME' => $row['NAME'],
                            'PICTURE' => ($row['PREVIEW_PICTURE']) ? \CFile::GetPath($row['PREVIEW_PICTURE']) : null
                        ];

                    }

                    foreach($arResult['ITEMS'] as $key => $arItem){

                        $hash = static::getHash($arItem['NAME']);
                        $arResult['MAP'][$hash] = $key;

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

    public static function get(string $name): array
    {
        $hash = static::getHash($name);
        $list = static::getList();

        return $list['ITEMS'][$list['MAP'][$hash]] ?: [];
    }

    protected static function getHash(string $value): string
    {
        return hash('crc32', mb_strtolower(trim($value)));
    }
}