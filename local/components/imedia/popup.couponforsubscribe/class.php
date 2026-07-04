<?php

namespace Imedia\Component;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Imedia\Main\App;
use Imedia\Main\Helpers\Common;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Iblock\Info;
use Imedia\Main\Helpers\Image\Resize;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class PopupCouponForSubscribe extends \CBitrixComponent
{
    private const CACHE_ID = 'coupon-for-subscribe';
    private const CACHE_TTL = 864000;
    private const CACHE_DIR = '/popup';

    protected function allowShow(): bool
    {
        $session = Application::getInstance()->getSession();
        return !$session->has(Info::SESSION_CODE_COUPON);
    }

    protected function getResult()
    {
        $this->arResult = [];

        try{

            $cache = Cache::createInstance();

            if ($cache->initCache(static::CACHE_TTL, static::CACHE_ID, static::CACHE_DIR)) {
                $this->arResult = $cache->getVars();
            } elseif ($cache->startDataCache()) {

                Loader::includeModule('iblock');

                $iblockId = IblockHelper::getId('INFO');

                if(!($iblockId > 0)){
                    $cache->abortDataCache();
                    return;
                }

                $elementId = Info::getId();
                if(!($elementId > 0)){
                    $cache->abortDataCache();
                    return;
                }

                $arProperties = [];

                \CIBlockElement::GetPropertyValuesArray(
                    $arProperties,
                    $iblockId,
                    ['ID' => $elementId],
                    ['CODE' => [
                        'POPUP_ACTIVE',
                        'POPUP_PAGES_DISABLED',
                        'POPUP_PICTURE',
                        'POPUP_TITLE',
                        'POPUP_SUBTITLE',
                        'POPUP_TIMEOUT'
                    ]],
                    [
                        'PROPERTY_FIELDS' => ['ID', 'VALUE'],
                        'GET_RAW_DATA' => 'Y'
                    ]
                );

                $arProperties = $arProperties[$elementId];

                $picture = null;
                if($arProperties['POPUP_PICTURE']['VALUE']){

                    $dimensions = [330, 348];
                    $sizes = [
                        'DEFAULT' => $dimensions,
                        'DEFAULT_2X' => array_map(function($item){
                            return $item * 2;
                        }, $dimensions)
                    ];

                    foreach($sizes as $i => $size){
                        $sizes[$i][] = BX_RESIZE_IMAGE_EXACT;
                    }

                    $img = new Resize($sizes);
                    $img->add($arProperties['POPUP_PICTURE']['VALUE']);
                    $arImage = $img->getResizeArray();

                    $picture = [
                        'src' => $arImage['RESIZE'][0]['SIZES']['DEFAULT'],
                        'src2x' => $arImage['RESIZE'][0]['SIZES']['DEFAULT_2X'],
                        'width' => $arImage['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'],
                        'height' => $arImage['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']
                    ];

                }

                $this->arResult = [
                    'DATA' => [
                        'title' => $arProperties['POPUP_TITLE']['VALUE'],
                        'subtitle' => $arProperties['POPUP_SUBTITLE']['VALUE'],
                        'picture' => $picture,
                        'timeout' => (int) $arProperties['POPUP_TIMEOUT']['VALUE'] * 1000
                    ],
                    'PAGES_DISABLED' => $arProperties['POPUP_PAGES_DISABLED']['VALUE'],
                    'ACTIVE' => (bool) $arProperties['POPUP_ACTIVE']['VALUE']
                ];

                $taggedCache = Application::getInstance()->getTaggedCache();
                $taggedCache->startTagCache(static::CACHE_DIR);
                $taggedCache->registerTag('iblock_id_' . $iblockId);
                $taggedCache->endTagCache();
                $cache->endDataCache($this->arResult);

            }

        } catch (\Exception $e){
            ShowError($e->getMessage());
        }
    }

    protected function allowShowFromPage(): bool
    {
        if(
            empty($this->arResult)
            || !$this->arResult['ACTIVE']
        ){
            $session = Application::getInstance()->getSession();
            $session->set(Info::SESSION_CODE_COUPON, 1);
            return false;
        }

        if(empty($this->arResult['PAGES_DISABLED'])){
            return true;
        }

        $app = App::getInstance();
        $path = $app->getRelativePath();

        return !in_array($path, $this->arResult['PAGES_DISABLED'], true);
    }

    public function executeComponent()
    {
        try {

            if(!$this->allowShow()){
                return;
            }

            $this->getResult();

            if(!$this->allowShowFromPage()){
                return;
            }

            $this->includeComponentLang('class.php');
            $this->includeComponentTemplate();
        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }
    }
}