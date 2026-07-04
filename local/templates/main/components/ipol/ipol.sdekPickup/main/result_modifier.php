<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Type;

$arDateMap = [
    'PVZ' => 'p_date',
    'POSTAMAT' => 'i_date'
];

foreach($arDateMap as $type => $code){

    [$from, $to] = explode('-', $arResult['DELIVERY'][$code]);

    $arResult['DATE_FORMATTED'][$type] = null;
    if($from){
        $objFrom = (new Type\Date())->add($from . ' day');

        if($to && ($to !== $from)){
            $objTo = (new Type\Date())->add($to . ' day');

            if($objFrom->format('m') === $objTo->format('m')){

                $arResult['DATE_FORMATTED'][$type] = $objFrom->format('j')
                    . ' - '
                    . $objTo->format('j')
                    . ' '
                    . \FormatDate('F', $objFrom);

            } else {
                $arResult['DATE_FORMATTED'][$type] = \FormatDate('j F', $objFrom)
                    . ' - '
                    . \FormatDate('j F', $objTo);
            }

        } else {
            $arResult['DATE_FORMATTED'][$type] = \FormatDate('j F', $objFrom);
        }
    }

}