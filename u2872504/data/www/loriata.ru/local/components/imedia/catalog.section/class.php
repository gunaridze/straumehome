<?php
namespace Imedia\Component;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Iblock\Component\ElementList;
use Bitrix\Catalog;
use CIBlockSection;
use Imedia\Main\Helpers\Component\ProductList;
use Imedia\Main\Helpers\Iblock\Seo;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CIntranetToolbar $INTRANET_TOOLBAR
 */

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

\CBitrixComponent::includeComponentClass('bitrix:catalog.section');

class CatalogSection extends \CatalogSectionComponent
{
    use ProductList;

    protected function initElementList()
    {
        if($this->arParams['ASYNC'] !== 'Y'){
            parent::initElementList();
        }
    }

    protected function initSectionResult()
    {
        $success = true;
        $selectFields = array();

        if (!empty($this->arParams['SECTION_USER_FIELDS']) && is_array($this->arParams['SECTION_USER_FIELDS']))
        {
            foreach ($this->arParams['SECTION_USER_FIELDS'] as $field)
            {
                if (is_string($field) && preg_match('/^UF_/', $field))
                {
                    $selectFields[] = $field;
                }
            }
        }

        if (preg_match('/^UF_/', $this->arParams['META_KEYWORDS']))
        {
            $selectFields[] = $this->arParams['META_KEYWORDS'];
        }

        if (preg_match('/^UF_/', $this->arParams['META_DESCRIPTION']))
        {
            $selectFields[] = $this->arParams['META_DESCRIPTION'];
        }

        if (preg_match('/^UF_/', $this->arParams['BROWSER_TITLE']))
        {
            $selectFields[] = $this->arParams['BROWSER_TITLE'];
        }

        if (preg_match('/^UF_/', $this->arParams['BACKGROUND_IMAGE']))
        {
            $selectFields[] = $this->arParams['BACKGROUND_IMAGE'];
        }

        $filterFields = array(
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'IBLOCK_ACTIVE' => 'Y',
            'ACTIVE' => 'Y',
            'GLOBAL_ACTIVE' => 'Y',
        );

        // Hidden tricky parameter USED to display linked
        // by default it is not set
        if (isset($this->arParams['BY_LINK']) && $this->arParams['BY_LINK'] === 'Y')
        {
            $sectionResult = array(
                'ID' => 0,
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            );
        }
        elseif ($this->arParams['SECTION_ID'] > 0)
        {
            $filterFields['ID'] = $this->arParams['SECTION_ID'];
            $sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
            $sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
            $sectionResult = $sectionIterator->GetNext();
        }
        elseif ($this->arParams['SECTION_CODE'] <> '')
        {
            $filterFields['=CODE'] = $this->arParams['SECTION_CODE'];
            $sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
            $sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
            $sectionResult = $sectionIterator->GetNext();
        }
        elseif (isset($this->arParams['SECTION_CODE_PATH']) && $this->arParams['SECTION_CODE_PATH'] <> '')
        {
            $sectionId = CIBlockFindTools::GetSectionIDByCodePath($this->arParams['IBLOCK_ID'], $this->arParams['SECTION_CODE_PATH']);
            if ($sectionId)
            {
                $filterFields['ID'] = $sectionId;
                $sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
                $sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
                $sectionResult = $sectionIterator->GetNext();
            }
        }
        else	// Root section (no section filter)
        {
            $sectionResult = array(
                'ID' => 0,
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            );
        }

        if (empty($sectionResult))
        {
            $success = false;
            $this->abortResultCache();
            $this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_SECTION_NOT_FOUND'), self::ERROR_404));
        }
        else
        {
            $this->arResult = array_merge($this->arResult, $sectionResult);
            if ($this->arResult['ID'] > 0 && $this->arParams['ADD_SECTIONS_CHAIN'])
            {
                $this->arResult['PATH'] = array();
                $pathIterator = CIBlockSection::GetNavChain(
                    $this->arResult['IBLOCK_ID'],
                    $this->arResult['ID'],
                    array(
                        'ID', 'CODE', 'XML_ID', 'EXTERNAL_ID', 'IBLOCK_ID',
                        'IBLOCK_SECTION_ID', 'SORT', 'NAME', 'ACTIVE',
                        'DEPTH_LEVEL', 'SECTION_PAGE_URL'
                    )
                );
                $pathIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
                while ($path = $pathIterator->GetNext())
                {
                    $ipropValues = new Iblock\InheritedProperty\SectionValues($this->arParams['IBLOCK_ID'], $path['ID']);
                    $path['IPROPERTY_VALUES'] = $ipropValues->getValues();
                    $this->arResult['PATH'][] = $path;

                    if(
                        ((int) $path['DEPTH_LEVEL'] === 1)
                        && $this->arParams['SECTION_VIRTUAL']
                    ){
                        $this->arResult['PATH'][] = [
                            '~SECTION_PAGE_URL' => $this->arParams['SECTION_VIRTUAL_PATH'],
                            'NAME' => Loc::getMessage('CATALOG_SECTION_VIRTUAL_' . $this->arParams['SECTION_VIRTUAL'])
                        ];
                    }

                }

                if ($this->arParams['SECTIONS_CHAIN_START_FROM'] > 0)
                {
                    $this->arResult['PATH'] = array_slice($this->arResult['PATH'], $this->arParams['SECTIONS_CHAIN_START_FROM']);
                }
            }
        }

        return $success;
    }

    protected function initMetaData()
    {
        global $APPLICATION;

        if ($this->arParams['SET_TITLE'])
        {
            if (isset($this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE']) && $this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] != '')
            {
                $APPLICATION->SetTitle($this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'], $this->storage['TITLE_OPTIONS']);
            }
            elseif (isset($this->arResult['NAME']))
            {
                $APPLICATION->SetTitle($this->arResult['NAME'], $this->storage['TITLE_OPTIONS']);
            }
        }

        if ($this->arParams['SET_BROWSER_TITLE'] === 'Y')
        {
            $browserTitle = Main\Type\Collection::firstNotEmpty(
                $this->arResult, $this->arParams['BROWSER_TITLE'],
                $this->arResult['IPROPERTY_VALUES'], 'SECTION_META_TITLE'
            );
            if (is_array($browserTitle))
            {
                $APPLICATION->SetPageProperty('title', implode(' ', $browserTitle), $this->storage['TITLE_OPTIONS']);
            }
            elseif ($browserTitle != '')
            {
                $APPLICATION->SetPageProperty('title', $browserTitle, $this->storage['TITLE_OPTIONS']);
            }
        }

        if ($this->arParams['SET_META_KEYWORDS'] === 'Y')
        {
            $metaKeywords = Main\Type\Collection::firstNotEmpty(
                $this->arResult, $this->arParams['META_KEYWORDS'],
                $this->arResult['IPROPERTY_VALUES'], 'SECTION_META_KEYWORDS'
            );
            if (is_array($metaKeywords))
            {
                $APPLICATION->SetPageProperty('keywords', implode(' ', $metaKeywords), $this->storage['TITLE_OPTIONS']);
            }
            elseif ($metaKeywords != '')
            {
                $APPLICATION->SetPageProperty('keywords', $metaKeywords, $this->storage['TITLE_OPTIONS']);
            }
        }

        if ($this->arParams['SET_META_DESCRIPTION'] === 'Y')
        {
            $metaDescription = Main\Type\Collection::firstNotEmpty(
                $this->arResult, $this->arParams['META_DESCRIPTION'],
                $this->arResult['IPROPERTY_VALUES'], 'SECTION_META_DESCRIPTION'
            );

            if(is_array($metaDescription)){
                $metaDescription = implode(' ', $metaDescription);
            }

            if($metaDescription){
                $processor = Seo\Factory::create('section');
                $metaDescription = $processor->process($metaDescription, array_merge($this->arResult, [
                    'IBLOCK_ID' => $this->arParams['IBLOCK_ID']
                ]));

                $APPLICATION->SetPageProperty('description', $metaDescription, $this->storage['TITLE_OPTIONS']);
            }
        }

        if (!empty($this->arResult['BACKGROUND_IMAGE']) && is_array($this->arResult['BACKGROUND_IMAGE']))
        {
            $APPLICATION->SetPageProperty(
                'backgroundImage',
                'style="background-image: url(\''.\CHTTP::urnEncode($this->arResult['BACKGROUND_IMAGE']['SRC'], 'UTF-8').'\')"'
            );
        }

        if ($this->arParams['ADD_SECTIONS_CHAIN'] && is_array($this->arResult['PATH']))
        {
            foreach ($this->arResult['PATH'] as $path)
            {
                $APPLICATION->AddChainItem($path['NAME'], $path['~SECTION_PAGE_URL']);
            }
        }

        if ($this->arParams['SET_LAST_MODIFIED'] && $this->arResult['ITEMS_TIMESTAMP_X'])
        {
            Main\Context::getCurrent()->getResponse()->setLastModified($this->arResult['ITEMS_TIMESTAMP_X']);
        }
    }
} 