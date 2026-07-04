<?php
namespace Imedia\Main\Helpers\Catalog;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Iblock\Model;
use Bitrix\Main\Localization\Loc;

class Section
{
    public const CACHE_TAG = 'catalog-section';
    private const CACHE_TTL = 86400;
    private const CACHE_DIR = '/catalog';
    private const SESSION_CODE = 'CATALOG_SORT';
    public const CODE_SALE = 'sale';
    public const CODE_NEW = 'new';

    public static function getCurrent(array $arVariables, int $iblockId): array
    {
        $arCurSection = [];

        try{
            if(
                $arVariables['SECTION_ID']
                || $arVariables['SECTION_CODE']
            ){
                $arFilter = [
                    '=IBLOCK_ID' => $iblockId,
                    '=ACTIVE' => 'Y',
                    '=GLOBAL_ACTIVE' => 'Y'
                ];
                if ((int) $arVariables['SECTION_ID'] > 0){
                    $arFilter['=ID'] = $arVariables['SECTION_ID'];
                } elseif ('' !== $arVariables['SECTION_CODE']){
                    $arFilter['=CODE'] = $arVariables['SECTION_CODE'];
                }

                $cacheId = serialize($arFilter);

                $cache = Cache::createInstance();
                if ($cache->initCache(static::CACHE_TTL, $cacheId, static::CACHE_DIR)) {
                    $arCurSection = $cache->getVars();
                } elseif ($cache->startDataCache()) {

                    Loader::includeModule('iblock');

                    $entity = Model\Section::compileEntityByIblock($iblockId);
                    $query = $entity::getList(
                        [
                            'select' => [
                                'ID',
                                'CODE',
                                'IBLOCK_SECTION_ID',
                                'DEPTH_LEVEL'
                            ],
                            'filter' => $arFilter,
                            'limit' => 1
                        ]
                    );
                    $arCurSection = $query->fetch();

                    $taggedCache = Application::getInstance()->getTaggedCache();
                    $taggedCache->startTagCache(static::CACHE_DIR);
                    $taggedCache->registerTag('iblock_section_id_' . $arCurSection['ID']);
                    $taggedCache->endTagCache();
                    $cache->endDataCache($arCurSection);
                }
            }
        } catch (\Exception $e){
            //todo send to log
        }

        return ($arCurSection) ?: [];
    }

    public static function getSort(): array
    {
        $arResult = [
            'LIST' => [],
            'SELECTED' => null,
            'PARAMS' => []
        ];

        $request = Application::getInstance()->getContext()->getRequest();
        $session = Application::getInstance()->getSession();

        $arResolved = [
            'price_ask',
            'price_desk',
            'popular',
            'new',
            'discount'
        ];

        foreach($arResolved as $code){
            $arResult['LIST'][] = [
                'code' => $code,
                'label' => Loc::getMessage('T_IMEDIA_MAIN_HELPERS_CATALOG_SECTION_SORT_' . strtoupper($code))
            ];
        }

        $newValue = strtolower($request->getQuery('sort'));
        if($newValue && in_array($newValue, $arResolved, true)){
            $session->set(static::SESSION_CODE, $newValue);
            $arResult['SELECTED'] = $newValue;
        }

        if(!$arResult['SELECTED']){
            $sessionValue = $session->get(static::SESSION_CODE);
            if($sessionValue && in_array($sessionValue, $arResolved, true)){
                $arResult['SELECTED'] = $sessionValue;
            }
        }

        switch($arResult['SELECTED']){
            case 'price_ask':
                $arResult['PARAMS']['ELEMENT_SORT_FIELD2'] = 'PROPERTY_' . Property::getCode('PRICE_MIN');
                $arResult['PARAMS']['ELEMENT_SORT_ORDER2'] = 'asc';
                break;
            case 'price_desk':
                $arResult['PARAMS']['ELEMENT_SORT_FIELD2'] = 'PROPERTY_' . Property::getCode('PRICE_MAX');
                $arResult['PARAMS']['ELEMENT_SORT_ORDER2'] = 'desc';
                break;
            case 'new':
                $arResult['PARAMS']['ELEMENT_SORT_FIELD2'] = 'CREATED';
                $arResult['PARAMS']['ELEMENT_SORT_ORDER2'] = 'desc';
                break;
            case 'discount':
                $arResult['PARAMS']['ELEMENT_SORT_FIELD2'] = 'PROPERTY_' . Property::getCode('DISCOUNT_MAX');
                $arResult['PARAMS']['ELEMENT_SORT_ORDER2'] = 'desc,nulls';
                break;
            case 'popular':
            default:
                $arResult['PARAMS']['ELEMENT_SORT_FIELD2'] = 'shows';
                $arResult['PARAMS']['ELEMENT_SORT_ORDER2'] = 'desc';
                break;
        }

        return $arResult;
    }

    public static function getSections(int $iblockId): array
    {
        $arResult = [];

        $cache = Cache::createInstance();

        $cacheId = 'sections_' . $iblockId;

        if ($cache->initCache(static::CACHE_TTL, $cacheId, static::CACHE_DIR)) {
            $arResult = $cache->getVars();
        } elseif ($cache->startDataCache()) {

            Loader::includeModule('iblock');

            $arFilter = [
                '=ACTIVE' => 'Y',
                '=GLOBAL_ACTIVE' => 'Y'
            ];

            $entity = Model\Section::compileEntityByIblock($iblockId);
            $query = $entity::getList(
                [
                    'select' => [
                        'ID',
                        'CODE',
                        'IBLOCK_SECTION_ID',
                        'DEPTH_LEVEL'
                    ],
                    'filter' => $arFilter
                ]
            );
            while($row = $query->fetch()){
                $arResult[$row['ID']] = $row;
            }

            $taggedCache = Application::getInstance()->getTaggedCache();
            $taggedCache->startTagCache(static::CACHE_DIR);
            $taggedCache->registerTag('iblock_id_' . $iblockId);
            $taggedCache->endTagCache();
            $cache->endDataCache($arResult);

        }

        return $arResult;
    }

    public static function getMainParentFromCode(string $sectionCode, int $iblockId): array
    {
        $arSection = static::getCurrent(['SECTION_CODE' => $sectionCode], $iblockId);
        if(empty($arSection)){
            return [];
        }

        return static::getMainParent($arSection['ID'], $iblockId);
    }

    public static function getMainParent(int $sectionId, int $iblockId): array
    {
        $arSections = static::getSections($iblockId);

        do{

            $arMainParent = (array) $arSections[$sectionId];
            $sectionId = (int) $arMainParent['IBLOCK_SECTION_ID'];

        } while ($sectionId > 0);

        return $arMainParent;
    }
}