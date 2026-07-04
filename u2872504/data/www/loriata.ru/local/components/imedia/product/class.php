<?php
namespace Imedia\Component;

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use Bitrix\Iblock\Component\Tools;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class Product extends \CBitrixComponent
{
    protected $defaultUrlTemplates404 = [];
    protected $componentVariables = [];
    protected $defaultVariableAliases404 = [];
    protected $defaultVariableAliases = [];
    protected $page = null;

    public function onPrepareComponentParams($arParams)
    {
        $arParams = parent::onPrepareComponentParams($arParams);

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
        $this->defaultUrlTemplates404 = [
            "product" => "#ELEMENT_ID#/"
        ];

        $this->componentVariables = [
            "ELEMENT_ID",
            "ELEMENT_CODE",
            "action"
        ];
    }

    protected function getResult()
    {
        if($this->arParams["SEF_MODE"] === "Y"){

            $arVariables = [];

            $engine = new \CComponentEngine($this);

            if (Loader::includeModule('iblock')){
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

            $requestUrl = Context::getCurrent()->getRequest()->getRequestedPage();

            $this->page = $engine->guessComponentPath(
                $this->arParams["SEF_FOLDER"],
                $arUrlTemplates,
                $arVariables,
                $requestUrl
            );

            $b404 = false;
            if(!$this->page){
                $this->page = 'product';
                $b404 = true;
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

            if($arVariables['ELEMENT_ID']){

                $arFilter = [
                    '=IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                    '=ID' => $arVariables['ELEMENT_ID']
                ];

                $arSelect = ['IBLOCK_SECTION_ID'];

                $arElement = \CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect)
                    ->GetNext(true, false);

                $arVariables['SECTION_ID'] = $arElement['IBLOCK_SECTION_ID'];

            }

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

            $this->page = 'product';

            $currentPage = htmlspecialcharsbx($GLOBALS['APPLICATION']->GetCurPage())."?";

            $this->arResult = [
                "FOLDER" => "",
                "URL_TEMPLATES" => [
                    "product" => $currentPage.$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#",
                ],
                "VARIABLES" => $arVariables,
                "ALIASES" => $arVariableAliases
            ];

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