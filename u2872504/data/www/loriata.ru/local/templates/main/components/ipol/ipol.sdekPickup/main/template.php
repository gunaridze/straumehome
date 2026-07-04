<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Web\Json;

$deliveries = [];

$arModes = [
    'PVZ' => [
        'forced' => \Ipolh\SDEK\option::get('pvzID'),
        'profs'  => CDeliverySDEK::getDeliveryId('pickup')
    ],
    'POSTAMAT' => [
        'forced' => COption::GetOptionString(CDeliverySDEK::$MODULE_ID,'pickupID',false),
        'profs'  => CDeliverySDEK::getDeliveryId('postamat')
    ]
];

foreach($arModes as $mode => $content){
    foreach($content['profs'] as $id){
        $deliveries[$id] = $mode;
    }
}

$items = [];
foreach(['PVZ', 'POSTAMAT'] as $type){
    foreach($arResult[$type] as $city => $arItems){
        foreach($arItems as $id => $arItem){

            $arItem['type'] = $type;
            $arItem['city'] = $city;
            $arItem['id'] = $id;
            $arItem['date'] = [
                'formatted' => $arResult['DATE_FORMATTED'][$type]
            ];

            $items[] = $arItem;

        }
    }
}


$data = [
    'deliveries' => $deliveries,
    'items' => $items,
    'token' => sdekHelper::getWidgetToken()
];

echo Json::encode($data);