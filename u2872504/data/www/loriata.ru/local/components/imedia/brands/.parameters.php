<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

try{
    if (!Main\Loader::includeModule('iblock')){
        throw new \Exception('Не установлен модуль информационных блоков');
    }

    $iblockTypes = \CIBlockParameters::GetIBlockTypes(['-' => ' ']);

    $iblocks = [0 => ' '];
    $iblocksCode = ['' => ' '];
    $catalogIblocks = [0 => ' '];
    $catalogIblocksCode = ['' => ' '];

    if (
        isset($arCurrentValues['IBLOCK_TYPE']) &&
        strlen($arCurrentValues['IBLOCK_TYPE'])
    ){

        $filter = [
            'TYPE' => $arCurrentValues['IBLOCK_TYPE'],
            'ACTIVE' => 'Y'
        ];

        $query = \CIBlock::GetList(['SORT' => 'ASC'], $filter);
        while ($iblock = $query->GetNext()){
            $iblocks[$iblock['ID']] = $iblock['NAME'];
            $iblocksCode[$iblock['CODE']] = $iblock['NAME'];
        }

    }

    if (
        isset($arCurrentValues['CATALOG_IBLOCK_TYPE']) &&
        strlen($arCurrentValues['CATALOG_IBLOCK_TYPE'])
    ){

        $filter = [
            'TYPE' => $arCurrentValues['CATALOG_IBLOCK_TYPE'],
            'ACTIVE' => 'Y'
        ];

        $query = \CIBlock::GetList(['SORT' => 'ASC'], $filter);
        while ($catalogIblock = $query->GetNext()){
            $catalogIblocks[$catalogIblock['ID']] = $catalogIblock['NAME'];
            $catalogIblocksCode[$catalogIblock['CODE']] =$catalogIblock['NAME'];
        }

    }

    $arSort = \CIBlockParameters::GetElementSortFields(
        array('SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'),
        array('KEY_LOWERCASE' => 'Y')
    );

    $arAscDesc = array(
        "asc" => GetMessage("IBLOCK_SORT_ASC"),
        "desc" => GetMessage("IBLOCK_SORT_DESC"),
    );

    $arComponentParameters = [
        "GROUPS" => array(
            "FILTER_SETTINGS" => array(
                "NAME" => GetMessage("T_IBLOCK_DESC_FILTER_SETTINGS"),
            ),
            "REVIEW_SETTINGS" => array(
                "NAME" => GetMessage("T_IBLOCK_DESC_REVIEW_SETTINGS"),
            ),
            "ACTION_SETTINGS" => array(
                "NAME" => GetMessage('IBLOCK_ACTIONS')
            ),
            "COMPARE_SETTINGS" => array(
                "NAME" => GetMessage("T_IBLOCK_DESC_COMPARE_SETTINGS_EXT"),
            ),
            "PRICES" => array(
                "NAME" => GetMessage("IBLOCK_PRICES"),
            ),
            "BASKET" => array(
                "NAME" => GetMessage("IBLOCK_BASKET"),
            ),
            "SEARCH_SETTINGS" => array(
                "NAME" => GetMessage("T_IBLOCK_DESC_SEARCH_SETTINGS"),
            ),
            "TOP_SETTINGS" => array(
                "NAME" => GetMessage("T_IBLOCK_DESC_TOP_SETTINGS"),
            ),
            "SECTIONS_SETTINGS" => array(
                "NAME" => GetMessage("CP_BC_SECTIONS_SETTINGS"),
            ),
            "LIST_SETTINGS" => array(
                "NAME" => GetMessage("T_IBLOCK_DESC_LIST_SETTINGS"),
            ),
            "DETAIL_SETTINGS" => array(
                "NAME" => GetMessage("T_IBLOCK_DESC_DETAIL_SETTINGS"),
            ),
            "LINK" => array(
                "NAME" => GetMessage("IBLOCK_LINK"),
            ),
            "ALSO_BUY_SETTINGS" => array(
                "NAME" => GetMessage("T_IBLOCK_DESC_ALSO_BUY_SETTINGS"),
            ),
            "GIFTS_SETTINGS" => array(
                "NAME" => GetMessage("SALE_T_DESC_GIFTS_SETTINGS"),
            ),
            "STORE_SETTINGS" => array(
                "NAME" => GetMessage("T_IBLOCK_DESC_STORE_SETTINGS"),
            ),
            "OFFERS_SETTINGS" => array(
                "NAME" => GetMessage("CP_BC_OFFERS_SETTINGS"),
            ),
            "BIG_DATA_SETTINGS" => array(
                "NAME" => GetMessage("CP_BC_GROUP_BIG_DATA_SETTINGS")
            ),
            'ANALYTICS_SETTINGS' => array(
                'NAME' => GetMessage('ANALYTICS_SETTINGS')
            ),
            "EXTENDED_SETTINGS" => array(
                "NAME" => GetMessage("IBLOCK_EXTENDED_SETTINGS"),
                "SORT" => 10000
            )
        ),
        'PARAMETERS' => [
            "USER_CONSENT" => array(),
            'VARIABLE_ALIASES' => [
                'ELEMENT_ID' => [
                    'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_ELEMENT_ID')
                ],
                'SECTION_ID' => [
                    'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_SECTION_ID')
                ],
                'CATALOG_SECTION_ID' => [
                    'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_CATALOG_SECTION_ID')
                ],
            ],
            "AJAX_MODE" => array(),
            'SEF_MODE' => [
                'index' => [
                    'NAME' => GetMessage('SEF_MODE_INDEX'),
                    'DEFAULT' => 'index.php',
                    'VARIABLES' => []
                ],
                'section' => [
                    'NAME' => GetMessage('SEF_MODE_SECTION'),
                    'DEFAULT' => 'section/#SECTION_CODE#/',
                    'VARIABLES' => ['SECTION_ID', 'SECTION_CODE']
                ],
                'element' => [
                    'NAME' => GetMessage('SEF_MODE_ELEMENT'),
                    'DEFAULT' => '#ELEMENT_CODE#/',
                    'VARIABLES' => ['ELEMENT_ID', 'ELEMENT_CODE']
                ],
                'catalog_section' => [
                    'NAME' => GetMessage('SEF_MODE_CATALOG_SECTION'),
                    'DEFAULT' => '#ELEMENT_CODE#/#CATALOG_SECTION_CODE_PATH#/',
                    'VARIABLES' => [
                        'ELEMENT_ID',
                        'ELEMENT_CODE',
                        'CATALOG_SECTION_ID',
                        'CATALOG_SECTION_CODE',
                        'CATALOG_SECTION_CODE_PATH'
                    ]
                ],
            ],
            'IBLOCK_TYPE' => [
                'PARENT' => 'BASE',
                'NAME' => GetMessage("IBLOCK_TYPE"),
                'TYPE' => 'LIST',
                'VALUES' => $iblockTypes,
                'DEFAULT' => '',
                'REFRESH' => 'Y'
            ],
			'IBLOCK_ID' => [
                'PARENT' => 'BASE',
                'NAME' => GetMessage("IBLOCK_ID"),
                'TYPE' => 'LIST',
                'VALUES' => $iblocks
            ],
            'CATALOG_IBLOCK_TYPE' => [
                'PARENT' => 'BASE',
                'NAME' => GetMessage("CATALOG_IBLOCK_TYPE"),
                'TYPE' => 'LIST',
                'VALUES' => $iblockTypes,
                'DEFAULT' => '',
                'REFRESH' => 'Y'
            ],
            'CATALOG_IBLOCK_ID' => [
                'PARENT' => 'BASE',
                'NAME' => GetMessage("CATALOG_IBLOCK_ID"),
                'TYPE' => 'LIST',
                'VALUES' => $catalogIblocks
            ],
            "USE_FILTER" => array(
                "PARENT" => "FILTER_SETTINGS",
                "NAME" => GetMessage("T_IBLOCK_DESC_USE_FILTER"),
                "TYPE" => "CHECKBOX",
                "DEFAULT" => "N",
                "REFRESH" => "Y",
            ),
            "PAGE_ELEMENT_COUNT" => [
                "PARENT" => "LIST_SETTINGS",
                "NAME" => GetMessage("IBLOCK_PAGE_ELEMENT_COUNT"),
                "TYPE" => "STRING",
                'HIDDEN' => isset($templateProperties['LIST_PRODUCT_ROW_VARIANTS']) ? 'Y' : 'N',
                "DEFAULT" => "30"
            ],
            "ELEMENT_SORT_FIELD" => array(
                "PARENT" => "LIST_SETTINGS",
                "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD"),
                "TYPE" => "LIST",
                "VALUES" => $arSort,
                "ADDITIONAL_VALUES" => "Y",
                "DEFAULT" => "sort",
            ),
            "ELEMENT_SORT_ORDER" => array(
                "PARENT" => "LIST_SETTINGS",
                "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
                "TYPE" => "LIST",
                "VALUES" => $arAscDesc,
                "DEFAULT" => "asc",
                "ADDITIONAL_VALUES" => "Y",
            ),
            "ELEMENT_SORT_FIELD2" => array(
                "PARENT" => "LIST_SETTINGS",
                "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD2"),
                "TYPE" => "LIST",
                "VALUES" => $arSort,
                "ADDITIONAL_VALUES" => "Y",
                "DEFAULT" => "id",
            ),
            "ELEMENT_SORT_ORDER2" => array(
                "PARENT" => "LIST_SETTINGS",
                "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER2"),
                "TYPE" => "LIST",
                "VALUES" => $arAscDesc,
                "DEFAULT" => "desc",
                "ADDITIONAL_VALUES" => "Y",
            ),
            "INCLUDE_SUBSECTIONS" => array(
                "PARENT" => "LIST_SETTINGS",
                "NAME" => GetMessage("CP_BC_INCLUDE_SUBSECTIONS"),
                "TYPE" => "LIST",
                "VALUES" => array(
                    "Y" => GetMessage('CP_BC_INCLUDE_SUBSECTIONS_ALL'),
                    "A" => GetMessage('CP_BC_INCLUDE_SUBSECTIONS_ACTIVE'),
                    "N" => GetMessage('CP_BC_INCLUDE_SUBSECTIONS_NO'),
                ),
                "DEFAULT" => "Y",
            ),
            'SECTION_ID_VARIABLE' => [
                'PARENT' => 'DETAIL_SETTINGS',
                'NAME' => GetMessage('IBLOCK_SECTION_ID_VARIABLE'),
                'TYPE' => 'STRING',
                'DEFAULT' => 'SECTION_ID'
            ],
            'CATALOG_SECTION_ID_VARIABLE' => [
                'PARENT' => 'DETAIL_SETTINGS',
                'NAME' => GetMessage('IBLOCK_CATALOG_SECTION_ID_VARIABLE'),
                'TYPE' => 'STRING',
                'DEFAULT' => 'CATALOG_SECTION_ID'
            ],
            "SET_TITLE" => array(),
            "ADD_SECTIONS_CHAIN" => array(
                "PARENT" => "ADDITIONAL_SETTINGS",
                "NAME" => GetMessage("CP_BC_ADD_SECTIONS_CHAIN"),
                "TYPE" => "CHECKBOX",
                "DEFAULT" => "Y"
            ),
            'CACHE_TIME'  =>  ['DEFAULT'=>36000000],
            'CACHE_FILTER' => [
                'PARENT' => 'CACHE_SETTINGS',
                'NAME' => GetMessage("IBLOCK_CACHE_FILTER"),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ],
            'CACHE_GROUPS' => [
                'PARENT' => 'CACHE_SETTINGS',
                'NAME' => GetMessage("CP_BC_CACHE_GROUPS"),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ],
        ]
    ];

    \CIBlockParameters::AddPagerSettings(
        $arComponentParameters,
        GetMessage("T_IBLOCK_DESC_PAGER_CATALOG"), //$pager_title
        true, //$bDescNumbering
        true, //$bShowAllParam
        true, //$bBaseLink
        $arCurrentValues["PAGER_BASE_LINK_ENABLE"]==="Y" //$bBaseLinkEnabled
    );

    \CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);

    if($arCurrentValues['SEF_MODE'] === 'Y'){
        $arComponentParameters['PARAMETERS']['VARIABLE_ALIASES'] = [];
        $arComponentParameters['PARAMETERS']['VARIABLE_ALIASES']['ELEMENT_ID'] = [
            'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_ELEMENT_ID'),
            'TEMPLATE' => '#ELEMENT_ID#'
        ];
        $arComponentParameters['PARAMETERS']['VARIABLE_ALIASES']['ELEMENT_CODE'] = [
            'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_ELEMENT_CODE'),
            'TEMPLATE' => '#ELEMENT_CODE#'
        ];
        $arComponentParameters['PARAMETERS']['VARIABLE_ALIASES']['SECTION_ID'] = [
            'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_SECTION_ID'),
            'TEMPLATE' => '#SECTION_ID#'
        ];
        $arComponentParameters['PARAMETERS']['VARIABLE_ALIASES']['SECTION_CODE'] = [
            'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_SECTION_CODE'),
            'TEMPLATE' => '#SECTION_CODE#'
        ];
        $arComponentParameters['PARAMETERS']['VARIABLE_ALIASES']['SECTION_CODE_PATH'] = [
            'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_SECTION_CODE_PATH'),
            'TEMPLATE' => '#SECTION_CODE_PATH#'
        ];
        $arComponentParameters['PARAMETERS']['VARIABLE_ALIASES']['CATALOG_SECTION_ID'] = [
            'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_CATALOG_ID'),
            'TEMPLATE' => '#CATALOG_SECTION_ID#'
        ];
        $arComponentParameters['PARAMETERS']['VARIABLE_ALIASES']['CATALOG_SECTION_CODE'] = [
            'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_CATALOG_SECTION_CODE'),
            'TEMPLATE' => '#CATALOG_SECTION_CODE#'
        ];
        $arComponentParameters['PARAMETERS']['VARIABLE_ALIASES']['CATALOG_SECTION_CODE_PATH'] = [
            'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_CATALOG_SECTION_CODE_PATH'),
            'TEMPLATE' => '#CATALOG_SECTION_CODE_PATH#'
        ];
        $arComponentParameters['PARAMETERS']['VARIABLE_ALIASES']['SMART_FILTER_PATH'] = [
            'NAME' => GetMessage('CP_BC_VARIABLE_ALIASES_SMART_FILTER_PATH'),
            'TEMPLATE' => '#SMART_FILTER_PATH#'
        ];
        
        $arComponentParameters['PARAMETERS']['SEF_MODE']['smart_filter'] = [
            'NAME' => GetMessage('CP_BC_SEF_MODE_SMART_FILTER'),
            'DEFAULT' => '#ELEMENT_CODE#/#CATALOG_SECTION_PATH#/filter/#SMART_FILTER_PATH#/apply/',
            'VARIABLES' => [
                'ELEMENT_ID',
                'ELEMENT_CODE'.
                'CATALOG_SECTION_ID',
                'CATALOG_SECTION_CODE',
                'CATALOG_SECTION_CODE_PATH',
                'SMART_FILTER_PATH'
            ],
        ];
    }
}
catch(\Exception $e){
    printr($e->getMessage());
}