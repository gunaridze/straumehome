<?php
namespace Imedia\Main\Helpers\Catalog;

use Bitrix\Main\Loader;
use Bitrix\Iblock\Model\Section;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Debug\Logger;

class Selected
{
    private const SESSION_CODE = 'CATALOG_SELECTED';
    public const COOKIE_CODE = 'CATALOG_SELECTED';
    public const CACHE_TAG = 'catalog-sections-top';
    private const CACHE_ID = 'sections-top';
    private const CACHE_TTL = 86400;
    private const CACHE_DIR = '/catalog';

    private static ?array $list = null;
    protected static ?string $selected = null;

    public static function set(int $sectionId): void
    {
        $session = Application::getInstance()->getSession();
        if(static::check($sectionId)){
            $session->set(static::SESSION_CODE, $sectionId);
        } else {
            $logger = new Logger\Logger();
            $logger->routes->attach(new Logger\Route\File(
                [
                    'isEnable' => true,
                    'logDir' => str_replace('Imedia\\Main', '', static::class)
                ]
            ));

            $logger->warning('Incorrect section id', [
                'sectionId' => $sectionId
            ]);
        }
    }

    public static function get(): int
    {
        $session = Application::getInstance()->getSession();

        if(!$session->has(static::SESSION_CODE)){

            $request = Context::getCurrent()->getRequest();
            $cookieSelected = $request->getCookie(static::COOKIE_CODE);

            if($cookieSelected){
                static::set($cookieSelected);
            }

        }

        if (
            !$session->has(static::SESSION_CODE)
            || !static::check((int) $session->get(static::SESSION_CODE))
        ){
            static::set(static::getDefault());
        }

        return (int) $session->get(static::SESSION_CODE);
    }

    public static function getDefault(): int
    {
        $list = static::getList();
        return (int) current($list)['ID'];
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

                    $iblockId = IblockHelper::getId('CATALOG');

                    if(!($iblockId > 0)){
                        $cache->abortDataCache();
                    }

                    $entity = Section::compileEntityByIblock($iblockId);
                    $query = $entity::getList(
                        [
                            'select' => ['ID', 'CODE', 'NAME'],
                            'filter' => [
                                '=ACTIVE' => true,
                                '=DEPTH_LEVEL' => 1
                            ],
                            'order' => [
                                'SORT' => 'ASC',
                                'ID' => 'ASC'
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
     * @param int $sectionId
     * @return bool
     */
    public static function check(int $sectionId): bool
    {
        $list = static::getList();
        foreach($list as $arSection){
            if($sectionId === (int) $arSection['ID']){
                return true;
            }
        }

        return false;
    }
}