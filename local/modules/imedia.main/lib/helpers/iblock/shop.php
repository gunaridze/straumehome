<?php
namespace Imedia\Main\Helpers\Iblock;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Imedia\Main\Helpers\Debug\Logger;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Image\Resize;

class Shop
{
    private const CACHE_ID = 'shops';
    private const CACHE_TTL = 864000;
    private const CACHE_DIR = '/iblock';

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

                    $iblockId = IblockHelper::getId('SHOPS');

                    if(!($iblockId > 0)){
                        $cache->abortDataCache();
                        return [];
                    }

                    $sectionIds = [];

                    $arSort = [
                        'SORT' => 'ASC',
                        'NAME' => 'ASC'
                    ];

                    $arFilter = [
                        '=ACTIVE' => 'Y',
                        '=IBLOCK_ID' => $iblockId
                    ];

                    $arPropertiesCodes = [
                        'ADDRESS',
                        'WORKING_HOURS',
                        'PHONE',
                        'MAP',
                        'STORE_ID'
                    ];

                    $arProperties = [];

                    \CIBlockElement::GetPropertyValuesArray(
                        $arProperties,
                        $iblockId,
                        $arFilter,
                        ['CODE' => $arPropertiesCodes]
                    );

                    $arSelect = [
                        'ID',
                        'XML_ID',
                        'NAME',
                        'SORT',
                        'PREVIEW_PICTURE',
                        'IBLOCK_SECTION_ID'
                    ];

                    $query = \CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
                    while($row = $query->GetNext(true, false)){

                        if($row['PREVIEW_PICTURE']){
                            $row['PREVIEW_PICTURE'] = [
                                'ID' => $row['PREVIEW_PICTURE'],
                                'SRC' => \CFile::GetPath($row['PREVIEW_PICTURE'])
                            ];
                        }

                        foreach($arPropertiesCodes as $code){
                            $row[$code] = $arProperties[$row['ID']][$code]['VALUE'];
                        }

                        $arResult['ITEMS'][] = $row;
                        $sectionIds[] = $row['IBLOCK_SECTION_ID'];

                    }

                    if(!empty($sectionIds)){

                        $arFilter['=ID'] = $sectionIds;

                        $arSelect = ['ID', 'NAME', 'SORT'];

                        $query = \CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
                        while($row = $query->GetNext(true, false)){
                            $arResult['SECTIONS'][] = $row;
                        }

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

    public static function getMapData(): array
    {
        $arResult = static::getList();

        $result = [
            'sections' => [],
            'items' => [],
            'features' => []
        ];

        foreach($arResult['SECTIONS'] as $arSection){
            $result['sections'][] = [
                'id' => (int) $arSection['ID'],
                'sort' => (int) $arSection['SORT'],
                'name' => $arSection['NAME']
            ];
        }

        foreach($arResult['ITEMS'] as $arItem){

            $item = [
                'id' => (int) $arItem['ID'],
                'sectionId' => (int) $arItem['IBLOCK_SECTION_ID'],
                'name' => $arItem['NAME'],
                'picture' => $arItem['PREVIEW_PICTURE']['SRC'],
                'address' => $arItem['ADDRESS'],
                'phone' => [
                    'value' => preg_replace("/[^0-9]/", '', $arItem['PHONE']),
                    'formatted' => $arItem['PHONE']
                ],
                'hours' => $arItem['WORKING_HOURS'],
                'coordinates' => explode(',', $arItem['MAP'])
            ];

            $result['items'][] = $item;

            $balloonHeader = '';
            if($arItem['PREVIEW_PICTURE']['SRC']){

                $arImage = Resize::setSelfResizeArray(
                    $arItem['PREVIEW_PICTURE'],
                    [285, 160, BX_RESIZE_IMAGE_EXACT]
                );

                $balloonHeader = '<img 
                src="'.$arImage['RESIZE'][0]['SIZES']['DEFAULT'].'"
                width="'.$arImage['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'].'"
                height="'.$arImage['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT'].'"
                >';

            }

            $balloonBody = '<div class="ballon-content">';

            $balloonBody .= '<div class="shop-title">'.$arItem['NAME'].'</div>';

            if($arItem['ADDRESS']){
                $balloonBody .= '<div class="shop-address">'.$arItem['ADDRESS'].'</div>';
            }

            if($arItem['WORKING_HOURS']){
                $balloonBody .= '<div class="shop-time">'.$arItem['WORKING_HOURS'].'</div>';
            }

            if($arItem['PHONE']){

                $phone = preg_replace('/[^\d+]/', '', $arItem['PHONE']);
                $balloonBody .= '
                <a class="shop-phone" href="'.$phone.'" target="_blank">
                    <svg viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.8963 9.39752L9.8763 8.41752C10.0083 8.28716 10.1753 8.19793 10.357 8.16065C10.5387 8.12337 10.7274 8.13965 10.9 8.20752L12.0944 8.68439C12.2689 8.75521 12.4185 8.8761 12.5244 9.03181C12.6303 9.18753 12.6877 9.3711 12.6894 9.55939V11.7469C12.6884 11.875 12.6615 12.0015 12.6103 12.119C12.559 12.2364 12.4846 12.3422 12.3914 12.4301C12.2982 12.518 12.1882 12.5861 12.068 12.6303C11.9478 12.6746 11.8198 12.694 11.6919 12.6875C3.32255 12.1669 1.6338 5.07939 1.31442 2.36689C1.29959 2.23369 1.31314 2.09886 1.35417 1.97127C1.39519 1.84367 1.46277 1.72621 1.55245 1.62661C1.64213 1.52702 1.75188 1.44753 1.87449 1.3934C1.99709 1.33926 2.12977 1.3117 2.2638 1.31252H4.37692C4.56549 1.31308 4.74957 1.37003 4.90551 1.47607C5.06144 1.5821 5.18208 1.73236 5.25192 1.90752L5.7288 3.10189C5.79891 3.27386 5.81679 3.46267 5.78022 3.64475C5.74365 3.82682 5.65425 3.99408 5.52317 4.12564L4.54317 5.10564C4.54317 5.10564 5.10755 8.92502 8.8963 9.39752Z" fill="#101112" />
                    </svg>
                    ' . $arItem['PHONE'] . '
                </a>'
                ;

            }

            $balloonBody .= '</div>';

            $result['features'][] = [
                'type' => 'Feature',
                'id' => 'point_' . $item['id'],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => explode(',', $arItem['MAP'])
                ],
                'options' => [
                    'iconLayout' => 'default#image',
                    'iconImageSize' => [68, 55],
                    'iconOffset' => [-10, -25]
                ],
                'properties' => [
                    'hintContent' => $item['name'],
                    'sectionId' => $item['sectionId'],
                    //'balloonContentHeader' => $balloonHeader,
                    //'balloonContentBody' => $balloonBody
                ]
            ];

        }

        return $result;

    }
}