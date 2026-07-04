<?php
namespace Imedia\Main\Helpers\Component;

use Imedia\Main\Helpers\Catalog\Selected;

trait CatalogMenuAside
{
    public function getChildItem(array $arItem, array $selectedSections = []): string
    {
        if(empty($arItem['ITEMS'])){

            $result = '<li class="aside-filters__sublist-item">';

            $result .= '<a class="aside-filters__link';
            if($this->isSelected($arItem)){
                $result .= ' active';
            }
            $result .= '" href="'.$arItem['LINK'].'" title="'.$arItem['NAME'].'">'.$arItem['NAME'].'</a>';

            $result .= '</li>';

        } else {
            $result = '<li class="aside-filters__sublist-title">';

            $result .= '<a class="aside-filters__link';
            if($this->isSelected($arItem)){
                $result .= ' active';
            }
            $result .= '" href="'.$arItem['LINK'].'" title="'.$arItem['NAME'].'">'.$arItem['NAME'].'</a>';

            $isOpen = $this->isSelected($arItem) || in_array($arItem['ID'], $selectedSections);

            $result .= '<button class="aside-filters__item-drop';

            if($isOpen){
                $result .= ' aside-filters__item-drop--active';
            }

            $result .= '">
                        <svg viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.25 4L7 7.6L10.75 4L12 5.2L7 10L2 5.2L3.25 4Z" fill="#101112"></path>
                        </svg>
                    </button>
                </li>    
            ';

            $result .= '<ul class="aside-filters__sublist"';

            if($isOpen){
                $result .= ' style="display: block;"';
            }

            $result .= '>';

            foreach($arItem['ITEMS'] as $arChild){
                $result .= $this->getChildItem($arChild, $selectedSections);
            }

            $result .= '</ul>';
        }

        return $result;
    }

    public function isSelected(array $arItem): bool
    {
        return (int) $arItem['ID'] === (int) $this->arParams['SELECTED_SECTION_ID'];
    }

    protected function getSelectedSections(): array
    {
        if(
            !((int) $this->arParams['SELECTED_SECTION_ID'] > 0)
            || empty($this->arResult)
        ){
            return [];
        }

        $ids = [];

        $this->getSelected($this->arResult, $ids);

        return $ids;
    }

    protected function getSelected(array $arItem, array &$ids = []): int
    {
        if((int) $this->arParams['SELECTED_SECTION_ID'] === (int) $arItem['ID']){
            return (int) $arItem['ID'];
        }

        foreach($arItem['ITEMS'] as $arChild){

            $id = $this->getSelected($arChild, $ids);
            if($id > 0){
                $ids[] = $id;
                return (int) $arItem['ID'];
            }

        }

        return 0;
    }

    public function getSelectedItem(array $arItem): ?array
    {
        if((int) $this->arParams['SELECTED_SECTION_ID'] === (int) $arItem['ID']){
            return $arItem;
        }

        foreach($arItem['ITEMS'] as $arChild){

            $arSelected = $this->getSelectedItem($arChild);
            if($arSelected){

                if(!$arSelected['PARENT']){
                    $arSelected['PARENT'] = [
                        'ID' => $arItem['ID'],
                        'NAME' => $arItem['NAME'],
                        'LINK' => $arItem['LINK']
                    ];

                    if((int) $arSelected['PARENT']['ID'] === Selected::get()){

                        foreach(Selected::getList() as $arParent){
                            if((int) $arParent['ID'] === Selected::get()){
                                $arSelected['PARENT']['LINK'] = SITE_DIR . $arParent['CODE'] . '/';
                            }
                        }

                    }
                }

                return $arSelected;
            }

        }

        return null;
    }
}