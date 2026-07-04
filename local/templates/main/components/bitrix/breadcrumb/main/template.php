<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @global CMain $APPLICATION
 */

global $APPLICATION;

if(empty($arResult)){
    return '';
}

$catalogLink = SITE_DIR . 'catalog/';

$strReturn = '';

$strReturn .= '<nav class="breadcrumbs" aria-label="breadcrumbs"><div class="container"><ol class="breadcrumbs__list" itemprop="http://schema.org/breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">';

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++){

    if($arResult[$index]['LINK'] === $catalogLink){
        continue;
    }

    $title = htmlspecialcharsex($arResult[$index]['TITLE']);

    $strReturn .= '<li class="breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';

    if($arResult[$index]['LINK'] <> '' && $index != $itemSize-1){
        $strReturn .= '
			<a href="'.$arResult[$index]['LINK'].'" title="'.$title.'" itemprop="item">
			    <div itemprop="name">'.$title.'</div>
            </a>
		';
    } else {
        $strReturn .= '
            <span itemprop="name">'.$title.'</span>	    
		';
    }

    $strReturn .= '<meta itemprop="position" content="' . ($index + 1) . '" />';
    $strReturn .= '</li>';

}

$strReturn .= '</ol></div></nav>';

return $strReturn;
