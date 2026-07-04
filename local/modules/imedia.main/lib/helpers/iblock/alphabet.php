<?php
namespace Imedia\Main\Helpers\Iblock;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Iblock\ElementTable;
use Imedia\Main\Helpers\Debug\Logger;

class Alphabet
{
    const CACHE_DIR = '/alphabet';
    const CACHE_TTL = 864000;

    public static function get(int $iblockId, array $arFilter = []): array
    {
        $arResult = [];

        try{

            $cache = Cache::createInstance();

            $cacheId = $iblockId;
            if(!empty($arFilter)){
                $cacheId .= '_' . md5(implode('=', $arFilter));
            }

            if ($cache->initCache(static::CACHE_TTL, $cacheId, static::CACHE_DIR)) {
                $arResult = $cache->getVars();
            } elseif ($cache->startDataCache()) {

                Loader::includeModule('iblock');

                $query = ElementTable::getList(
                    [
                        'select' => ['DISTINCT_FIRST_LETTER'],
                        'filter' => array_merge(
                            [
                                '=ACTIVE' => 'Y',
                                '=IBLOCK_ID' => $iblockId
                            ],
                            $arFilter
                        ),
                        'order' => ['NAME' => 'ASC'],
                        'runtime' => [
                            new ExpressionField(
                                'DISTINCT_FIRST_LETTER',
                                'DISTINCT LEFT(%s, 1)',
                                ['NAME']
                            )
                        ]
                    ]
                );
                while($row = $query->fetch()){
                    $arResult[] = $row['DISTINCT_FIRST_LETTER'];
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

        return $arResult;
    }
}