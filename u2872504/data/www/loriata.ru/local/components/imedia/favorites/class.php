<?php

namespace Imedia\Component;

use Bitrix\Main\Loader;
use Bitrix\Iblock\Iblock;
use Bitrix\Main\Entity\ExpressionField;
use Imedia\Main\Helpers\Catalog\DefferedProduct;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class Favorites extends \CBitrixComponent
{
    protected $defaultUrlTemplates404 = [];
    protected $componentVariables = [];
    protected $defaultVariableAliases404 = [];
    protected $defaultVariableAliases = [];
    protected $page = null;

    public function onPrepareComponentParams($arParams)
    {
        $arParams = parent::onPrepareComponentParams($arParams);

        if (isset($arParams["USE_FILTER"]) && $arParams["USE_FILTER"]=="Y"){
            $arParams["FILTER_NAME"] = trim($arParams["FILTER_NAME"]);
            if ($arParams["FILTER_NAME"] === '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])){
                $arParams["FILTER_NAME"] = "arrFilter";
            }
        }
        else{
            $arParams["FILTER_NAME"] = "";
        }

        if(empty($arParams['USE_GIFTS_SECTION'])){
            $arParams['USE_GIFTS_SECTION'] = 'Y';
        }

        if(empty($arParams['GIFTS_SECTION_LIST_PAGE_ELEMENT_COUNT'])){
            $arParams['GIFTS_SECTION_LIST_PAGE_ELEMENT_COUNT'] = 3;
        }

        if(empty($arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'])){
            $arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'] = 4;
        }

        if(empty($arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'])){
            $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'] = 4;
        }

        $arParams['ACTION_VARIABLE'] = (isset($arParams['ACTION_VARIABLE']) ? trim($arParams['ACTION_VARIABLE']) : 'action');
        if ($arParams["ACTION_VARIABLE"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ACTION_VARIABLE"])){
            $arParams["ACTION_VARIABLE"] = "action";
        }

        return $arParams;
    }

    protected function setSefDefaultParams()
    {
        $smartBase = ($this->arParams["SEF_URL_TEMPLATES"]["section"]? $this->arParams["SEF_URL_TEMPLATES"]["section"]: "#SECTION_ID#/");

        $this->defaultUrlTemplates404 = [
            "sections" => "",
            "section" => "#SECTION_ID#/",
            "smart_filter" => $smartBase."filter/#SMART_FILTER_PATH#/apply/",
            "smart_filter_root" => "filter/#SMART_FILTER_PATH#/apply/",
        ];

        $this->componentVariables = [
            "SECTION_ID",
            "SECTION_CODE",
            "action"
        ];
    }

    protected function getResult()
    {
        if($this->arParams["SEF_MODE"] === "Y"){

            $arVariables = [];

            $engine = new \CComponentEngine($this);

            if (\Bitrix\Main\Loader::includeModule('iblock')){
                $engine->addGreedyPart("#SECTION_CODE_PATH#");
                $engine->addGreedyPart("#SMART_FILTER_PATH#");
                $engine->addGreedyPart("#SMART_FILTER_PATH_ROOT#");
                $engine->setResolveCallback([__CLASS__, 'resolveComponentEngine']);
            }

            $arUrlTemplates = \CComponentEngine::makeComponentUrlTemplates(
                $this->defaultUrlTemplates404,
                $this->arParams["SEF_URL_TEMPLATES"]
            );

            $arVariableAliases = \CComponentEngine::makeComponentVariableAliases(
                $this->defaultVariableAliases404,
                $this->arParams["VARIABLE_ALIASES"]
            );

            $this->page = $engine->guessComponentPath(
                $this->arParams["SEF_FOLDER"],
                $arUrlTemplates,
                $arVariables
            );

            if ($this->page === "smart_filter"){
                $this->page = "section";
            } else if ($this->page === "smart_filter_root"){
                $this->page = "sections";
            }

            $b404 = false;
            if(!$this->page){
                $this->page = "sections";
                $b404 = true;
            }

            if($this->page == "section"){
                if (isset($arVariables["SECTION_ID"])){
                    $b404 |= (intval($arVariables["SECTION_ID"])."" !== $arVariables["SECTION_ID"]);
                }
                else{
                    $b404 |= !isset($arVariables["SECTION_CODE"]);
                }

            }

            if($b404 && \Bitrix\Main\Loader::includeModule('iblock')){
                $folder404 = str_replace("\\", "/", $this->arParams["SEF_FOLDER"]);
                if ($folder404 != "/"){
                    $folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
                }

                if (mb_substr($folder404, -1) == "/"){
                    $folder404 .= "index.php";
                }

                if ($folder404 != $GLOBALS['APPLICATION']->GetCurPage(true)){
                    \Bitrix\Iblock\Component\Tools::process404(
                        "",
                        ($this->arParams["SET_STATUS_404"] === "Y"),
                        ($this->arParams["SET_STATUS_404"] === "Y"),
                        ($this->arParams["SHOW_404"] === "Y"),
                        $this->arParams["FILE_404"]
                    );
                }
            }

            \CComponentEngine::initComponentVariables(
                $this->page,
                $this->componentVariables,
                $arVariableAliases,
                $arVariables
            );

            $this->arResult = [
                "FOLDER" => $this->arParams["SEF_FOLDER"],
                "URL_TEMPLATES" => $arUrlTemplates,
                "VARIABLES" => $arVariables,
                "ALIASES" => $arVariableAliases
            ];
        }else{
            $arVariables = [];

            $arVariableAliases = \CComponentEngine::makeComponentVariableAliases(
                $this->defaultVariableAliases,
                $this->arParams["VARIABLE_ALIASES"]
            );

            \CComponentEngine::initComponentVariables(
                false,
                $this->componentVariables,
                $arVariableAliases,
                $arVariables
            );

            if(isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0){
                $this->page = "section";
            } elseif(isset($arVariables["SECTION_CODE"]) && $arVariables["SECTION_CODE"] <> ''){
                $this->page = "section";
            } else{
                $this->page = "sections";
            }

            $currentPage = htmlspecialcharsbx($GLOBALS['APPLICATION']->GetCurPage())."?";

            $this->arResult = [
                "FOLDER" => "",
                "URL_TEMPLATES" => [
                    "section" => $currentPage.$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"
                ],
                "VARIABLES" => $arVariables,
                "ALIASES" => $arVariableAliases
            ];
        }

        $this->getFavorites();

        if(
            ($this->page === 'section')
            && (empty($this->arResult['VARIABLES']['FAVORITES']))
        ){
            \Bitrix\Iblock\Component\Tools::process404(
                "",
                ($this->arParams["SET_STATUS_404"] === "Y"),
                ($this->arParams["SET_STATUS_404"] === "Y"),
                ($this->arParams["SHOW_404"] === "Y"),
                $this->arParams["FILE_404"]
            );
        }

        $this->getSections();

    }

    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');
            $this->getResult();
            $this->includeComponentTemplate($this->page);
        } catch (\Exception $exception) {
            printr($exception->getMessage());
        }

    }

    public static function resolveComponentEngine(\CComponentEngine $engine, $pageCandidates, &$arVariables)
    {
        /** @global CMain $APPLICATION */
        global $APPLICATION, $CACHE_MANAGER;
        static $aSearch = ["&lt;", "&gt;", "&quot;", "&#039;"];
        static $aReplace = ["<", ">", "\"", "'"];

        $component = $engine->getComponent();
        if ($component){
            $iblock_id = (int) ($component->arParams["IBLOCK_ID"]);
        } else{
            $iblock_id = 0;
        }

        if(
            isset($pageCandidates['smart_filter_root'])
            && isset($pageCandidates['section'])
        ){
            unset($pageCandidates['section']);
        }

        foreach ($pageCandidates as $pageID => $arVariablesTmp){
            foreach ($arVariablesTmp as $variableName => $variableValue){
                if ($variableName === "SMART_FILTER_PATH"){
                    $pageCandidates[$pageID][$variableName] = str_replace($aSearch, $aReplace, $variableValue);
                }
            }
        }

        $requestURL = $APPLICATION->GetCurPage(true);

        $cacheId = $requestURL.implode("|", array_keys($pageCandidates))."|".SITE_ID."|".$iblock_id.$engine->cacheSalt;
        $cache = new \CPHPCache;
        if ($cache->StartDataCache(3600, $cacheId, "iblock_find")){
            if (defined("BX_COMP_MANAGED_CACHE")){
                $CACHE_MANAGER->StartTagCache("iblock_find");
                \CIBlock::registerWithTagCache($iblock_id);
            }

            foreach ($pageCandidates as $pageID => $arVariablesTmp) {
                if ($arVariablesTmp["SECTION_CODE_PATH"] != ""){
                    if (\CIBlockFindTools::checkSection($iblock_id, $arVariablesTmp)){
                        $arVariables = $arVariablesTmp;
                        if (defined("BX_COMP_MANAGED_CACHE")){
                            $CACHE_MANAGER->EndTagCache();
                        }
                        $cache->EndDataCache(array($pageID, $arVariablesTmp));
                        return $pageID;
                    }
                }
            }

            if (defined("BX_COMP_MANAGED_CACHE")){
                $CACHE_MANAGER->AbortTagCache();
            }

            $cache->AbortDataCache();
        } else {
            $vars = $cache->GetVars();
            $pageID = $vars[0];
            $arVariables = $vars[1];
            return $pageID;
        }

        reset($pageCandidates);
        $pageID = key($pageCandidates);
        $arVariables = $pageCandidates[$pageID];

        return $pageID;
    }

    protected function getFavorites()
    {
        $this->arResult['VARIABLES']['FAVORITES'] = DefferedProduct::getAll()->getData()['favorites'];

    }

    protected function getSections()
    {
        Loader::includeModule('iblock');

        $iblock = Iblock::wakeUp(IblockHelper::getId('CATALOG'));
        $entity = $iblock->getEntityDataClass();

        $query = $entity::getList(
            [
                'select' => ['DISTINCT_SECTION_ID'],
                'filter' => ['=ID' => $this->arResult['VARIABLES']['FAVORITES']],
                'runtime' => [
                    new ExpressionField('DISTINCT_SECTION_ID', 'DISTINCT (%s)', ['IBLOCK_SECTION_ID'])
                ]
            ]
        );
        while($row = $query->fetch()){
            $this->arResult['VARIABLES']['SECTIONS'][] = $row['DISTINCT_SECTION_ID'];
        }
    }
}