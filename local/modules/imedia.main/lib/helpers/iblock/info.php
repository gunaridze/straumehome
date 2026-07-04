<?php
namespace Imedia\Main\Helpers\Iblock;

use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;
use Bitrix\Iblock\Iblock;
use Imedia\Main\Helpers\Debug\Logger;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

class Info
{
    public const SESSION_CODE_COUPON = 'POPUP_SUBSCRIBE_FOR_COUPON';
    private const CACHE_ID = 'info';
    private const CACHE_TTL = 864000;
    private const CACHE_DIR = '/iblock';

    private static ?int $id = null;

    public static function getId(): int
    {
        if(!static::$id){

            $arResult = [];

            try{

                $cache = Cache::createInstance();

                if ($cache->initCache(static::CACHE_TTL, static::CACHE_ID, static::CACHE_DIR)) {
                    $arResult = $cache->getVars();
                } elseif ($cache->startDataCache()) {

                    Loader::includeModule('iblock');

                    $iblockId = IblockHelper::getId('INFO');

                    if(!($iblockId > 0)){
                        $cache->abortDataCache();
                    }

                    $iblock = Iblock::wakeUp($iblockId);
                    $entity = $iblock->getEntityDataClass();

                    $query = $entity::getList(
                        [
                            'select' => ['ID'],
                            'limit' => 1
                        ]
                    );

                    $arResult = $query->fetch();

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

            static::$id = $arResult['ID'];

        }

        return (int) static::$id;
    }
}