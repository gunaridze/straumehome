<?php
namespace Imedia\Component;

use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\Model;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use Bitrix\Iblock\Component\Tools;
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Catalog\Section;
use Imedia\Main\Helpers\Catalog\Selected;
use Imedia\Main\Helpers\Iblock\Facet;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class Catalog extends \CBitrixComponent
{
    protected $defaultUrlTemplates404 = [];
    protected $componentVariables = [];
    protected $defaultVariableAliases404 = [];
    protected $defaultVariableAliases = [];
    protected $page = null;

    public function onPrepareComponentParams($arParams)
    {
        $arParams = parent::onPrepareComponentParams($arParams);

        if (isset($arParams["USE_FILTER"]) && $arParams["USE_FILTER"] === "Y"){
            $arParams["FILTER_NAME"] = trim($arParams["FILTER_NAME"]);
            if ($arParams["FILTER_NAME"] === '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])){
                $arParams["FILTER_NAME"] = "arrFilter";
            }

            if(!$arParams['PREFILTER_NAME']){
                $arParams['PREFILTER_NAME'] = 'arPreFilter';
            }

        } else{
            $arParams["FILTER_NAME"] = "";
            $arParams['PREFILTER_NAME'] = '';
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
        if ($arParams["ACTION_VARIABLE"] === '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ACTION_VARIABLE"])){
            $arParams["ACTION_VARIABLE"] = "action";
        }

        return $arParams;
    }

    protected function setSefDefaultParams()
    {
        $smartBase = $this->arParams["SEF_URL_TEMPLATES"]["section"] ?: "#SECTION_ID#/";

        $this->defaultUrlTemplates404 = [
            "sections" => "",
            "section" => "#SECTION_ID#/",
            "smart_filter" => $smartBase."filter/#SMART_FILTER_PATH#/apply/"
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

            if (Loader::includeModule('iblock')){
                $engine->addGreedyPart("#SECTION_CODE_PATH#");
                $engine->addGreedyPart("#SMART_FILTER_PATH#");
                $engine->setResolveCallback(
                    [
                        "CIBlockFindTools",
                        "resolveComponentEngine"
                    ]
                );
            }

            $arUrlTemplates = \CComponentEngine::makeComponentUrlTemplates(
                $this->defaultUrlTemplates404,
                $this->arParams["SEF_URL_TEMPLATES"]
            );

            $arVariableAliases = \CComponentEngine::makeComponentVariableAliases(
                $this->defaultVariableAliases404,
                $this->arParams["VARIABLE_ALIASES"]
            );

            $arAddVariables = [];

            $requestUrl = Context::getCurrent()->getRequest()->getRequestedPage();

            $patternSefFolder = str_replace('/', '\/', $this->arParams['SEF_FOLDER']);
            preg_match('/'.$patternSefFolder.'([^\/]+)/', $requestUrl, $match);
            $requestSectionCode = $match[1];

            foreach([Section::CODE_SALE, Section::CODE_NEW] as $code){

                if(!str_ends_with($requestSectionCode, '_' . $code)){
                    continue;
                }

                $sectionCode = preg_replace('/_'.$code.'$/', '', $requestSectionCode);

                $entity = Model\Section::compileEntityByIblock($this->arParams['IBLOCK_ID']);

                $arSection = $entity::getList(
                    [
                        'select' => ['ID', 'CODE'],
                        'filter' => ['=CODE' => $sectionCode],
                        'limit' => 1
                    ]
                )->fetch();

                if(!$arSection){
                    break;
                }

                $beginString = $this->arParams['SEF_FOLDER'] . $arSection['CODE'];

                $requestUrl = str_replace(
                    $beginString . $code,
                    $this->arParams['SEF_FOLDER'] . $arSection['CODE'],
                    $requestUrl
                );

                $arAddVariables['SECTION_ID'] = $arSection['ID'];
                $arAddVariables['SECTION_CODE'] = $arSection['CODE'];
                $arAddVariables['SECTION_VIRTUAL_PATH'] = $beginString . '_' . $code.'/';
                $arAddVariables['SECTION_APPEND_PATH'] = $code;

                switch($code){
                    case Section::CODE_SALE:
                        $arAddVariables['SECTION_VIRTUAL'] = 'SALE';

                        $GLOBALS[$this->arParams['PREFILTER_NAME']] = [
                            '!PROPERTY_' . Property::getCode('SALE') => false
                        ];

                        break;
                    case Section::CODE_NEW:
                        $arAddVariables['SECTION_VIRTUAL'] = 'NEW';

                        $GLOBALS[$this->arParams['PREFILTER_NAME']] = [
                            '!PROPERTY_' . Property::getCode('NEW') => false
                        ];

                        break;
                    default:
                        break;
                }

                $arAddVariables['SMART_FILTER_URL_REPLACE'] = [
                    'FROM' => $beginString . '/',
                    'TO' => $beginString . '_' . $code.'/'
                ];

                break;

            }

            $this->page = $engine->guessComponentPath(
                $this->arParams["SEF_FOLDER"],
                $arUrlTemplates,
                $arVariables,
                $requestUrl
            );

            $arVariables = array_merge($arVariables, $arAddVariables);

            if ($this->page === "smart_filter"){
                $this->page = "section";
            }

            if(!$this->page && isset($_REQUEST["q"])){
                $this->page = "search";
            }

            $b404 = false;
            if(!$this->page){
                $this->page = "sections";
                $b404 = true;
            }

            if($this->page === "section"){
                if (isset($arVariables["SECTION_ID"])){
                    $b404 |= (intval($arVariables["SECTION_ID"])."" !== $arVariables["SECTION_ID"]);
                } else{
                    $b404 |= !isset($arVariables["SECTION_CODE"]);
                }
            }

            if($b404 && Loader::includeModule('iblock')){
                $folder404 = str_replace("\\", "/", $this->arParams["SEF_FOLDER"]);
                if ($folder404 != "/"){
                    $folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
                }

                if (mb_substr($folder404, -1) === "/"){
                    $folder404 .= "index.php";
                }

                if ($folder404 !== $GLOBALS['APPLICATION']->GetCurPage(true)){
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

            if(isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0){
                $this->page = "section";
            } elseif(isset($arVariables["SECTION_CODE"]) && $arVariables["SECTION_CODE"] <> ''){
                $this->page = "section";
            } elseif(isset($_REQUEST["q"])){
                $this->page = "search";
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

        $this->getSections();
    }

    protected function getSections()
    {
        if(!$this->arResult['VARIABLES']['SECTION_VIRTUAL']){
            return;
        }

        Loader::includeModule('iblock');

        $arPropertyValue = \CIBlockPropertyEnum::GetList(
            [],
            [
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                'CODE' => Property::getCode($this->arResult['VARIABLES']['SECTION_VIRTUAL'])
            ]
        )->GetNext();
        $propertyValue = $arPropertyValue['ID'];

        $selectedCatalogId = Selected::get();

        $ids = Facet::getElementIdsFromPropertyValueInSection(
            $this->arParams['IBLOCK_ID'],
            Property::getCode($this->arResult['VARIABLES']['SECTION_VIRTUAL']),
            $selectedCatalogId,
            $propertyValue
        );

        if(empty($ids)){
            $this->arResult['VARIABLES']['SECTIONS'] = [];
            return;
        }

        $iblock = Iblock::wakeUp($this->arParams['IBLOCK_ID']);
        $entity = $iblock->getEntityDataClass();

        $query = $entity::getList(
            [
                'select' => ['DISTINCT_SECTION_ID'],
                'filter' => ['=ID' => $ids],
                'runtime' => [
                    new ExpressionField('DISTINCT_SECTION_ID', 'DISTINCT (%s)', ['IBLOCK_SECTION_ID'])
                ]
            ]
        );
        while($row = $query->fetch()){
            $this->arResult['VARIABLES']['SECTIONS'][] = $row['DISTINCT_SECTION_ID'];
        }
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
}