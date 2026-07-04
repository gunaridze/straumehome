<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!empty($arResult['ITEMS'])){

    $arLettersGroup = [];

    foreach($arResult['ITEMS'] as $arItem){

        $currentLetter = strtoupper(mb_substr($arItem['NAME'], 0, 1));

        if(!preg_match("/[A-Z]/", $currentLetter)){

            if(preg_match("/[А-Я]/", $currentLetter)){
                $currentLetter = 'CYRILLIC';
            } else {
                $currentLetter = 'OTHER';
            }

        }

        $arLettersGroup[$currentLetter][] = $arItem;

    }

    $arItems = [];
    foreach($arLettersGroup as $letter => $items){
        $arItems[] = [
            'LETTER' => $letter,
            'COLS' => array_chunk($items, 3)
        ];
    }

    usort($arItems, function($a, $b){

        if($a['LETTER'] === 'OTHER'){
            return 1;
        }

        if($b['LETTER'] === 'OTHER'){
            return -1;
        }

        if($a['LETTER'] === 'CYRILLIC'){
            return 1;
        }

        if($b['LETTER'] === 'CYRILLIC'){
            return -1;
        }

        return $a['LETTER'] <=> $b['LETTER'];

    });

    $arResult['ITEMS'] = $arItems;

    unset($arLettersGroup);
    unset($arItems);

}