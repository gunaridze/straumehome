<?php
namespace Imedia\Main\Helpers\Iblock;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

class Iblock
{
    public const CACHE_TAG = 'iblock-code-list';
    private const CACHE_ID = 'code-list';
    private const CACHE_TTL = 86400;
    private const CACHE_DIR = '/iblock';

    protected static ?array $list = null;

    /**
     * @param string $code
     * @return int
     */
    public static function getId(string $code): int
    {
        $list = static::getList();
        foreach($list as $arIblock){
            if($code === $arIblock['CODE']){
                return (int) $arIblock['ID'];
            }
        }

        return 0;
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getCode(int $id): string
    {
        $list = static::getList();
        foreach($list as $arIblock){
            if($id === (int) $arIblock['ID']){
                return $arIblock['CODE'];
            }
        }

        return '';
    }

    public static function getList(): array
    {
        if(static::$list === null){

            $arResult = [];

            $cache = Cache::createInstance();

            if ($cache->initCache(static::CACHE_TTL, static::CACHE_ID, static::CACHE_DIR)) {
                $arResult = $cache->getVars();
            } elseif ($cache->startDataCache()) {
                Loader::includeModule('iblock');

                $query = IblockTable::getList(
                    [
                        'select' => ['ID', 'NAME', 'CODE', 'API_CODE', 'IBLOCK_TYPE_ID'],
                        'filter' => [
                            '=ACTIVE' => true
                        ]
                    ]
                );
                while($row = $query->fetch()){
                    $arResult[] = $row;
                }

                $taggedCache = Application::getInstance()->getTaggedCache();
                $taggedCache->startTagCache(static::CACHE_DIR);
                $taggedCache->registerTag(static::CACHE_TAG);
                $taggedCache->endTagCache();
                $cache->endDataCache($arResult);
            }

            static::$list = $arResult;

        }

        return static::$list;
    }
}