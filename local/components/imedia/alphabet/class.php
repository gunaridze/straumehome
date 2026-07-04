<?php

namespace Imedia\Component;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Imedia\Main\Helpers\Iblock\Alphabet as AlphabetHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class Alphabet extends \CBitrixComponent
{

    protected function prepareParams()
    {
        $this->arParams['IBLOCK_ID'] = (int) $this->arParams['IBLOCK_ID'];
        if(!($this->arParams['IBLOCK_ID'] > 0)){
            throw new \Exception('Iblock id not set');
        }
    }

    protected function getResult()
    {
        $arFilter = [];

        if($this->arParams['SECTION_ID']){
            $arFilter['=IBLOCK_SECTION_ID'] = $this->arParams['SECTION_ID'];
        } elseif ($this->arParams['SECTION_CODE']) {
            $arFilter['=IBLOCK_SECTION.CODE'] = $this->arParams['SECTION_CODE'];
        }

        $alphabet = AlphabetHelper::get((int) $this->arParams['IBLOCK_ID'], $arFilter);
        $alphabetQuery = strtoupper($this->request->get('letter'));

        $this->arResult['HAVE_SELECTED'] = false;
        $this->arResult['ITEMS'] = [];

        $haveOther = false;
        $haveCyrillic = false;

        foreach($alphabet as $symbol){

            $isSelected = $alphabetQuery === $symbol;
            if($isSelected){
                $this->arResult['HAVE_SELECTED'] = true;
            }

            if(!preg_match("/[A-Z]/", $symbol)){

                if(preg_match("/[А-Я]/", $symbol)){
                    $haveCyrillic = true;
                } else {
                    $haveOther = true;
                }

                continue;

            }

            if($isSelected){
                $GLOBALS[$this->arParams['FILTER_NAME']]['NAME'] = $symbol. '%';
            }

            $this->arResult['ITEMS'][] = [
                'VALUE' => $symbol,
                'LINK' => '?letter=' . $symbol,
                'SELECTED' => $isSelected
            ];

        }

        if($haveCyrillic){

            $cyrillicSelected = strtolower($alphabetQuery) === 'cyrillic';

            $this->arResult['ITEMS'][] = [
                'VALUE' => 'А-Я',
                'LINK' => '?letter=cyrillic',
                'SELECTED' => $cyrillicSelected
            ];

            if($cyrillicSelected){
                $cyrillicAlphabet = range(chr(0xC0), chr(0xDF));

                $filter = ['LOGIC' => 'OR'];

                foreach($cyrillicAlphabet as $letter){
                    $filter[] = ['NAME' => iconv('CP1251','UTF-8', $letter) . '%'];
                }

                $GLOBALS[$this->arParams['FILTER_NAME']][] = $filter;
            }

        }

        if($haveOther){

            $otherSelected = strtolower($alphabetQuery) === 'other';

            $this->arResult['ITEMS'][] = [
                'VALUE' => '0-9',
                'LINK' => '?letter=other',
                'SELECTED' => $otherSelected
            ];

            if($otherSelected){

                $filter = ['LOGIC' => 'OR'];

                for($i = 0; $i < 10; $i++){
                    $filter[] = ['NAME' => $i . '%'];
                }

                $GLOBALS[$this->arParams['FILTER_NAME']][] = $filter;

            }
        }
    }

    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');
            $this->prepareParams();
            $this->getResult();
            $this->includeComponentTemplate();
        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }

    }
}