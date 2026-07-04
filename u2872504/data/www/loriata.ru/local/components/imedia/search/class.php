<?php
namespace Imedia\Component;

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\Component\Tools;
use Imedia\Main\Helpers\Catalog\Selected;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class Search extends \CBitrixComponent
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

        if(empty($arParams['PARAM_QUERY'])){
            $arParams['PARAM_QUERY'] = 'q';
        }

        if(
            empty($arParams['MIN_GOOD_RESULT_COUNT'])
            || (1 > (int) $arParams['MIN_GOOD_RESULT_COUNT'])
        ){
            $arParams['MIN_GOOD_RESULT_COUNT'] = 10;
        }

        if(!is_array($arParams['SECTIONS_INDEX'])){
            $arParams['SECTIONS_INDEX'] = [];
        }

        return $arParams;
    }

    protected function setSefDefaultParams()
    {
        $this->defaultUrlTemplates404 = [
            'index' => '',
            'smart_filter_root' => 'filter/#SMART_FILTER_PATH#/apply/',
            "smart_filter" => "#SECTION_CODE_PATH#/filter/#SMART_FILTER_PATH#/apply/",
            "section" => "#SECTION_CODE_PATH#/"
        ];

        $this->componentVariables = [
            "SECTION_ID",
            "SECTION_CODE",
            'action'
        ];
    }

    protected function getResult()
    {
        if($this->arParams["SEF_MODE"] === "Y"){

            $arVariables = [];

            $engine = new \CComponentEngine($this);

            if (Loader::includeModule('iblock')){
                $engine->addGreedyPart("#SECTION_CODE_PATH#");
                $engine->addGreedyPart("#SMART_FILTER_PATH#");
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

            if(!$arVariables['SECTION_ID']){

                if($arVariables['SECTION_CODE_PATH']){

                    $arVariables['SECTION_ID'] = \CIBlockFindTools::GetSectionIDByCodePath(
                        $this->arParams['IBLOCK_ID'],
                        $arVariables['SECTION_CODE_PATH']
                    );

                } elseif ($arVariables['SECTION_CODE']){

                    $arVariables['SECTION_ID'] = \CIBlockFindTools::GetSectionID(
                        null,
                        $arVariables['SECTION_CODE'],
                        ['=IBLOCK_ID' => $this->arParams['IBLOCK_ID']]
                    );

                }

            }

            $this->page = "index";

            $b404 = false;
            if(!$this->page){
                $this->page = "index";
                $b404 = true;
            }

            if($b404 && Loader::includeModule('iblock')){
                $folder404 = str_replace("\\", "/", $this->arParams["SEF_FOLDER"]);
                if ($folder404 != "/"){
                    $folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
                }

                if (mb_substr($folder404, -1) == "/"){
                    $folder404 .= "index.php";
                }

                if ($folder404 != $GLOBALS['APPLICATION']->GetCurPage(true)){
                    Tools::process404(
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
        } else {
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

            $this->arResult = [
                "FOLDER" => "",
                "URL_TEMPLATES" => [],
                "VARIABLES" => $arVariables,
                "ALIASES" => $arVariableAliases
            ];
        }

        if(!$this->arResult['VARIABLES']['SECTION_ID']){
            $this->arResult['VARIABLES']['SECTION_ID'] = Selected::get();
        }

        Loader::includeModule('search');

        $this->prepareQuery();

        if(!$this->arResult['VARIABLES']['QUERY']){
            Tools::process404(
                "",
                ($this->arParams["SET_STATUS_404"] === "Y"),
                ($this->arParams["SET_STATUS_404"] === "Y"),
                ($this->arParams["SHOW_404"] === "Y"),
                $this->arParams["FILE_404"]
            );
        }

        $this->getSearchResult();
        $this->setTitle();
        $this->addChain();
        $this->getSections();

    }

    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');
            $this->getResult();
            $this->includeComponentTemplate($this->page);
        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }

    }

    public static function resolveComponentEngine(\CComponentEngine $engine, $pageCandidates, &$arVariables)
    {
        static $aSearch = ["&lt;", "&gt;", "&quot;", "&#039;"];
        static $aReplace = ["<", ">", "\"", "'"];

        foreach ($pageCandidates as $pageID => $arVariablesTmp){
            foreach ($arVariablesTmp as $variableName => $variableValue){
                if ($variableName === "SMART_FILTER_PATH"){
                    $pageCandidates[$pageID][$variableName] = str_replace($aSearch, $aReplace, $variableValue);
                }
            }
        }

        reset($pageCandidates);
        $pageID = key($pageCandidates);
        $arVariables = $pageCandidates[$pageID];

        return $pageID;
    }

    protected function prepareQuery()
    {
        $this->arResult['VARIABLES']['QUERY']
            = $this->arResult['VARIABLES']['ORIGINAL_QUERY']
            = trim(filter_var(
            $this->request->getQuery($this->arParams['PARAM_QUERY']),
            FILTER_SANITIZE_STRING
        ));

        if(!$this->arResult['VARIABLES']['QUERY']){
            return;
        }

        $arParams = [
            'QUERY' => $this->arResult['VARIABLES']['QUERY'],
            'SITE_ID' => LANG,
            'MODULE_ID' => 'iblock'
        ];

        $arSort = [];

        $arParamsEx = [
            'LIMIT' => 0
        ];

        $obSearch = new \CSearch;
        $obSearch->Search($arParams, $arSort, $arParamsEx);
        $result = $obSearch->Fetch();

        $originalQueryCount = $result['COUNT'];

        if(
            ($originalQueryCount < $this->arParams['MIN_GOOD_RESULT_COUNT'])
            && ($this->arParams['SEARCH_USE_LANGUAGE_GUESS'] === 'Y')
        ){

            $altQuery = null;

            $arLang = \CSearchLanguage::GuessLanguage($this->arResult['VARIABLES']['QUERY']);
            if(
                is_array($arLang)
                && ($arLang['from'] !== $arLang['to'])
            ){
                $altQuery = \CSearchLanguage::ConvertKeyboardLayout(
                    $this->arResult['VARIABLES']['QUERY'],
                    $arLang['from'],
                    $arLang['to']
                );
            }

            if($altQuery){

                $arParams['QUERY'] = $altQuery;
                $obSearch->Search($arParams, $arSort, $arParamsEx);
                $result = $obSearch->Fetch();

                $altQueryCount = $result['COUNT'];

                if($altQueryCount > $originalQueryCount){
                    $this->arResult['VARIABLES']['QUERY'] = $altQuery;
                }

            }

        }
    }

    protected function getSearchResult()
    {
        if(!$this->arResult['VARIABLES']['QUERY']){
            return;
        }

        $arParams = [
            'QUERY' => $this->arResult['VARIABLES']['QUERY'],
            'SITE_ID' => LANG,
            'MODULE_ID' => 'iblock',
            'PARAM2' => $this->arParams['IBLOCK_ID'],
            '!ITEM_ID' => 'S%'
        ];

        $arSort = [
            'RANK' => 'DESC'
        ];

        $arParamsEx = [
            'LIMIT' => $this->arParams['SEARCH_PAGE_RESULT_COUNT'],
            'SIMPLE_RESULT' => 'Y'
        ];

        $this->arResult['VARIABLES']['ID'] = [];

        $obSearch = new \CSearch;
        $obSearch->Search($arParams, $arSort, $arParamsEx);
        while($id = $obSearch->Fetch()){
            $this->arResult['VARIABLES']['ID'][] = (is_array($id)) ? $id['ITEM_ID'] : $id;
        }
    }

    protected function setTitle()
    {
        if($this->arParams['SET_TITLE'] !== 'Y'){
            return;
        }

        global $APPLICATION;
        $APPLICATION->setTitle(Loc::getMessage('IM_SEARCH_TITLE', [
            '#QUERY#' => $this->arResult['VARIABLES']['QUERY']
        ]));

        $APPLICATION->setPageProperty('title', Loc::getMessage('IM_SEARCH_TITLE', [
            '#QUERY#' => $this->arResult['VARIABLES']['QUERY']
        ]));

    }

    protected function addChain()
    {
        if($this->arParams['ADD_QUERY_CHAIN'] === 'N'){
            return;
        }

        global $APPLICATION;
        $APPLICATION->AddChainItem(Loc::getMessage('IM_SEARCH_CHAIN', [
            '#QUERY#' => $this->arResult['VARIABLES']['QUERY']
        ]));
    }

    protected function getSections()
    {
        if(empty($this->arResult['VARIABLES']['ID'])){
            $this->arResult['VARIABLES']['SECTIONS'] = [];
            return;
        }

        Loader::includeModule('iblock');

        $iblock = Iblock::wakeUp($this->arParams['IBLOCK_ID']);
        $entity = $iblock->getEntityDataClass();

        $query = $entity::getList(
            [
                'select' => ['DISTINCT_SECTION_ID'],
                'filter' => ['=ID' => $this->arResult['VARIABLES']['ID']],
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