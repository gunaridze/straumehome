<?php

namespace Imedia\Component;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Imedia\Main\Helpers\Iblock\Facet;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class Brands extends \CBitrixComponent
{
    protected $defaultUrlTemplates404 = [];
    protected $componentVariables = [];
    protected $page = null;

    public function onPrepareComponentParams($arParams)
    {
        $arParams = parent::onPrepareComponentParams($arParams);

        if($arParams['SEF_MODE'] !== 'N'){
            $arParams['SEF_MODE'] = 'Y';
        }

        if(!$arParams['SEF_FOLDER']){
            $arParams['SEF_FOLDER'] =  SITE_DIR . 'brands/';
        }

        if(!$arParams['FILTER_NAME']){
            $arParams['FILTER_NAME'] = 'arrFilter';
        }

        return $arParams;
    }

    protected function setSefDefaultParams()
    {
        $this->defaultUrlTemplates404 = [
            'index' => 'index.php',
            'section' => 'section/#SECTION_ID#/',
            'element' => 'element/#ELEMENT_ID#/',
            'catalog_section' => '#ELEMENT_ID#/#CATALOG_SECTION_ID#/',
            'smart_filter' => '#ELEMENT_ID#/#CATALOG_SECTION_ID#/filter/#SMART_FILTER_PATH#/apply/',
            'smart_filter_root' => '#ELEMENT_ID#/filter/#SMART_FILTER_PATH#/apply/',
        ];

        $this->componentVariables = [
            'ELEMENT_ID',
            'ELEMENT_CODE',
            'SECTION_ID',
            'SECTION_CODE',
            'CATALOG_SECTION_ID',
            'CATALOG_SECTION_CODE'
        ];
    }

    protected function getResult()
    {
        $urlTemplates = [];

        if ($this->arParams['SEF_MODE'] === 'Y'){

            $arVariables = [];

            $arUrlTemplates = \CComponentEngine::MakeComponentUrlTemplates(
                $this->defaultUrlTemplates404,
                $this->arParams['SEF_URL_TEMPLATES']
            );

            $variableAliases = \CComponentEngine::MakeComponentVariableAliases(
                $this->defaultUrlTemplates404,
                $this->arParams['VARIABLE_ALIASES']
            );

            $engine = new \CComponentEngine($this);

            if (Loader::includeModule('iblock')){
                $engine->addGreedyPart('#CATALOG_SECTION_CODE_PATH#');
                $engine->addGreedyPart('#CATALOG_SECTION_CODE#');
                $engine->addGreedyPart('#SMART_FILTER_PATH#');
                $engine->setResolveCallback([__CLASS__, 'resolveComponentEngine']);
            }

            $this->page = $engine->guessComponentPath(
                $this->arParams['SEF_FOLDER'],
                $arUrlTemplates,
                $arVariables
            );

            switch($this->page){
                case 'catalog_section':
                case 'smart_filter':
                case 'smart_filter_root':
                case 'element':
                    $this->page = 'element';
                    break;
                default:
                    $this->page = 'index';
                    break;
            }

            if(!strlen($this->page)){
                $this->page = 'index';
            }

            /*dmp(
                [
                    'page' => $this->page,
                    'v' => $arVariables
                ]
            );*/

            \CComponentEngine::InitComponentVariables(
                $this->page,
                $this->componentVariables,
                $variableAliases,
                $arVariables
            );
        }
        else{
            $this->page = 'index';
        }

        if(
            isset($arVariables['CATALOG_SECTION_CODE_PATH']) &&
            (strpos($arVariables['CATALOG_SECTION_CODE_PATH'], 'filter/') === 0)
        ){
            $arVariables['SMART_FILTER_PATH'] = str_replace(['filter/', '/apply'], null, $arVariables['CATALOG_SECTION_CODE_PATH']);
            unset($arVariables['CATALOG_SECTION_CODE_PATH']);
        }

        $this->arResult[ 'FOLDER' ] = $this->arParams['SEF_FOLDER'];
        $this->arResult[ 'URL_TEMPLATES' ] = $arUrlTemplates;
        $this->arResult[ 'VARIABLES' ] = $arVariables;
        $this->arResult[ 'ALIASES' ] = $variableAliases;
    }

    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');
            $this->getResult();
            $this->includeComponentTemplate($this->page);
        }
        catch (\Exception $exception) {
            printr($exception->getMessage());
        }

    }

    public static function resolveComponentEngine(\CComponentEngine $engine, $pageCandidates, &$arVariables)
    {
        global $APPLICATION, $CACHE_MANAGER;
        static $aSearch = ["&lt;", "&gt;", "&quot;", "&#039;"];
        static $aReplace = ["<", ">", "\"", "'"];

        $component = $engine->getComponent();
        if ($component){
            $iblockId = (int) $component->arParams['IBLOCK_ID'];
            $catalogIblockId = (int) $component->arParams['CATALOG_IBLOCK_ID'];
        } else {
            $iblockId = 0;
            $catalogIblockId = 0;
        }

        foreach ($pageCandidates as $pageID => $arVariablesTmp){
            foreach ($arVariablesTmp as $variableName => $variableValue){
                if ($variableName === 'SMART_FILTER_PATH'){
                    $pageCandidates[$pageID][$variableName] = str_replace($aSearch, $aReplace, $variableValue);
                }
            }
        }

        $requestURL = $APPLICATION->GetCurPage(true);

        reset($pageCandidates);

        if(
            isset($pageCandidates['catalog_section']) &&
            !isset($pageCandidates['catalog_section']['CATALOG_SECTION_ID']) &&
            isset($pageCandidates['smart_filter_root'])
        ){
            $keys = array_keys($pageCandidates);
            usort($keys, function ($a, $b){
                return !($a === 'smart_filter_root');
            });

            $pageCandidates = array_replace(array_flip($keys), $pageCandidates);
        }

        if(
            isset($pageCandidates['section'])
            && isset($pageCandidates['element'])
        ){

            $cacheId = $requestURL.implode("|", array_keys($pageCandidates))."|".SITE_ID."|".$iblockId.$engine->cacheSalt;
            $cache = new \CPHPCache;
            if ($cache->StartDataCache(3600, $cacheId, "iblock_find")){
                if (defined("BX_COMP_MANAGED_CACHE")){
                    $CACHE_MANAGER->StartTagCache("iblock_find");
                    \CIBlock::registerWithTagCache($iblockId);
                }

                foreach ($pageCandidates as $pageID => $arVariablesTmp){
                    if (
                        ($pageID === 'element')
                        && (isset($arVariablesTmp["ELEMENT_ID"]) || isset($arVariablesTmp["ELEMENT_CODE"]))
                    ){
                        if (\CIBlockFindTools::checkElement($iblockId, $arVariablesTmp, false)){
                            $arVariables = $arVariablesTmp;
                            if (defined("BX_COMP_MANAGED_CACHE"))
                                $CACHE_MANAGER->EndTagCache();
                            $cache->EndDataCache([$pageID, $arVariablesTmp]);
                            return $pageID;
                        }
                    }
                }

                foreach ($pageCandidates as $pageID => $arVariablesTmp){
                    if (
                        ($pageID === 'section')
                        && (isset($arVariablesTmp["SECTION_ID"]) || isset($arVariablesTmp["SECTION_CODE"]))
                    ){
                        if (\CIBlockFindTools::checkSection($iblockId, $arVariablesTmp)){
                            $arVariables = $arVariablesTmp;
                            if (defined("BX_COMP_MANAGED_CACHE"))
                                $CACHE_MANAGER->EndTagCache();
                            $cache->EndDataCache([$pageID, $arVariablesTmp]);
                            return $pageID;
                        }
                    }
                }

                if (defined("BX_COMP_MANAGED_CACHE"))
                    $CACHE_MANAGER->AbortTagCache();
                $cache->AbortDataCache();
            } else {
                $vars = $cache->GetVars();
                $pageID = $vars[0];
                $arVariables = $vars[1];
                return $pageID;
            }
        }

        if(
            isset($pageCandidates['catalog_section'])
            && isset($pageCandidates['smart_filter'])
        ){
            unset($pageCandidates['catalog_section']);
        }

        $pageID = key($pageCandidates);

        if(
            $pageCandidates[$pageID]['CATALOG_SECTION_CODE_PATH']
            && !$pageCandidates[$pageID]['CATALOG_SECTION_ID']
        ){

            if(strpos($pageCandidates[$pageID]['CATALOG_SECTION_CODE_PATH'], '/filter/') !== false){
                $arPath = explode('/filter/', $pageCandidates[$pageID]['CATALOG_SECTION_CODE_PATH']);
                $pageCandidates[$pageID]['CATALOG_SECTION_CODE_PATH'] = $arPath[0];
            }

            $pageCandidates[$pageID]['CATALOG_SECTION_ID'] = \CIBlockFindTools::GetSectionIDByCodePath(
                $catalogIblockId,
                $pageCandidates[$pageID]['CATALOG_SECTION_CODE_PATH']
            );

        }

        $arVariables = $pageCandidates[$pageID];

        return $pageID;
    }
}