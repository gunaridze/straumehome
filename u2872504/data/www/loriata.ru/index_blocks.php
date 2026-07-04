<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)	die();

global $APPLICATION;

$APPLICATION->SetPageProperty('classes--page', 'catalog-home');
$APPLICATION->SetPageProperty('title-type', 'seo');

$APPLICATION->IncludeComponent(
    'imedia:catalog.sections.main',
    '',
    [
        'CATALOG_LINK' => 'catalog/'
    ],
    false,
    ['HIDE_ICONS' => true]
);

$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    'index',
    [
        'AREA_FILE_SHOW' => 'page',
        'AREA_FILE_SUFFIX' => 'seo_text'
    ]
);

$APPLICATION->ShowViewContent('title');