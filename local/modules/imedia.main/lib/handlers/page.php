<?php
namespace Imedia\Main\Handlers;

use Bitrix\Main\Context;
use Bitrix\Main\Web\Cookie;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Imedia\Main\Helpers\Catalog\Menu;
use Imedia\Main\Helpers\Catalog\Selected;
use Imedia\Main\App;
use Imedia\Main\Helpers\Common;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Location;
use Imedia\Main\Helpers\Page\Meta;
use Imedia\Main\Helpers\Page\Schema;
use Imedia\Main\Helpers\Catalog\Section;

class Page
{
    /**
     * @var array
     */
    protected $arFields = [];

    /**
     * @var array
     */
    protected $access = [
        'template' => false,
    ];

    /**
     * @var array
     */
    protected $errors = [];

    private string $content = '';


    /**
     * @var self
     */
    private static $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function onProlog()
    {
        $handler = self::getInstance();
        $handler->handleMainpageRedirect();
        $handler->handleSelectedCatalogChange();
        $handler->handleLocation();
    }

    public static function onEpilog()
    {
        $request = Context::getCurrent()->getRequest();
        if($request->isAjaxRequest()){
            return;
        }

        $handler = self::getInstance();
        Meta::set();
        $handler->setTitle();
        $handler->setTitleAdditional();
        Schema::set();
        $handler->setPage404();
    }

    public static function onEndBufferContent(&$content)
    {
        $handler = self::getInstance();
        $handler->content = $content;

        $request = Context::getCurrent()->getRequest();
        if(!$request->isAjaxRequest()){
            $handler->removeScripts();
            $handler->removeStyles();
            $handler->removeScriptsType();
            $handler->insertBlocks();
        }

        //$handler->minifyContent();
        $content = $handler->content;
    }

    private function handleMainpageRedirect(): void
    {
        if(Common::isBot()){
            return;
        }

        $app = App::getInstance();

        if(!$app->isFront()){
            return;
        }

        $currentUser = CurrentUser::get();
        if($currentUser->isAdmin()){
            return;
        }

        $request = Context::getCurrent()->getRequest();
        $cookieCatalog = $request->getCookie(Selected::COOKIE_CODE);
        $selectedCatalog = (int) $cookieCatalog;
        if(!($selectedCatalog > 0)){
            return;
        }

        $catalogIndexList = Selected::getList();
        foreach($catalogIndexList as $arSection){
            if((int) $arSection['ID'] === $selectedCatalog){
                LocalRedirect(SITE_DIR . $arSection['CODE'] . '/');
            }
        }
    }

    private function handleSelectedCatalogChange(): void
    {
        if(Common::isBot()){
            return;
        }

        $app = App::getInstance();

        $newSelected = null;

        $relativePath = $app->getRelativePath();
        $catalogPath = 'catalog/';

        $catalogIndexList = Selected::getList();
        foreach ($catalogIndexList as $arSection) {

            if ($relativePath === SITE_DIR . $arSection['CODE'] . '/') {
                $newSelected = (int) $arSection['ID'];
                break;
            }

        }

        if(
            !$newSelected
            && (strpos($relativePath, SITE_DIR . $catalogPath) === 0)
        ){

            $catalogSefFolder = SITE_DIR . $catalogPath;
            $patternSefFolder = str_replace('/', '\/', $catalogSefFolder);
            preg_match('/'.$patternSefFolder.'([^\/]+)/', $relativePath, $match);
            $sectionCode = $match[1];

            foreach([Section::CODE_SALE, Section::CODE_NEW] as $code) {
                if (!str_ends_with($sectionCode, '_' . $code)) {
                    continue;
                }

                $sectionCode = preg_replace('/_'.$code.'$/', '', $sectionCode);
                break;
            }

            if(!$sectionCode){
                return;
            }

            $arMainParentSection = Section::getMainParentFromCode($sectionCode, IblockHelper::getId('CATALOG'));
            $newSelected = $arMainParentSection['ID'];

        }

        if($newSelected && (Selected::get() !== $newSelected)){

            Selected::set($newSelected);

            $request = Context::getCurrent()->getRequest();
            $cookieSelectedCatalog = $request->getCookie(Selected::COOKIE_CODE);

            if(!$cookieSelectedCatalog){
                $response = Context::getCurrent()->getResponse();
                $response->addCookie(
                    new Cookie(
                        Selected::COOKIE_CODE,
                        $newSelected,
                        time()+86400*30
                    )
                );
            }

        }
    }

    private function setTitle(): void
    {
        global $APPLICATION;

        $titleType = $APPLICATION->GetProperty('title-type');
        $additionalClassList = $APPLICATION->GetProperty('title-classes');

        switch($titleType){
            case 'hidden':
                $title = '<h1 class="visually-hidden" id="pagetitle">' . $APPLICATION->GetTitle(false) . '</h1>';;
                break;
            case 'removed':
                $title = '';
                break;
            case 'section':
                $titleSection = $APPLICATION->GetProperty('title-section') ?: $APPLICATION->GetTitle(false);
                $title = '<h2 class="title page__title '.$additionalClassList.'">' . $titleSection . '</h2>';
                break;
            case 'seo':
                $title = '<h1 class="seo-title '.$additionalClassList.'" id="pagetitle">' . $APPLICATION->GetTitle(false) . '</h1>';
                break;
            case 'catalog-section':
                $title = '
                    <div class="catalog__top page__title">
                        <h1 class="title catalog__title '.$additionalClassList.'" id="pagetitle">' . $APPLICATION->GetTitle(false) . '</h1>
                        <div class="catalog__view">
                            <button class="catalog__view-btn grid-btn catalog__view-btn--active" type="button">
                                <svg viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="0.5" y="0.5" width="7" height="7" rx="0.5" stroke="#C0C0C0"></rect>
                                    <rect x="11.5" y="0.5" width="7" height="7" rx="0.5" stroke="#C0C0C0"></rect>
                                    <rect x="11.5" y="11.5" width="7" height="7" rx="0.5" stroke="#C0C0C0"></rect>
                                    <rect x="0.5" y="11.5" width="7" height="7" rx="0.5" stroke="#C0C0C0"></rect>
                                </svg>
                            </button>
                            <button class="catalog__view-btn col-btn catalog__view-btn--active" type="button">
                                <svg viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="0.5" y="0.5" width="7" height="18" rx="0.5" stroke="#C0C0C0"></rect>
                                    <rect x="11.5" y="0.5" width="7" height="18" rx="0.5" stroke="#C0C0C0"></rect>
                                </svg>
                            </button>
                            <button class="catalog__view-btn row-btn" type="button">
                                <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="0.5" y="0.5" width="17" height="6.57895" rx="0.5" stroke="#C0C0C0"></rect>
                                    <rect x="0.5" y="10.9214" width="17" height="6.57895" rx="0.5" stroke="#C0C0C0"></rect>
                                </svg>
                            </button>
                        </div>
                    </div>
                ';
                break;
            default:
                $title = '<h1 class="title page__title '.$additionalClassList.'" id="pagetitle">' . $APPLICATION->GetTitle(false) . '</h1>';
                break;
        }

        $APPLICATION->AddViewContent('title', $title);

        $subtitle = $APPLICATION->GetProperty('subtitle');
        if($subtitle){
            $APPLICATION->AddViewContent('subtitle', '<div class="page__subtitle">'.$subtitle.'</div>');
        }
    }

    private function setTitleAdditional()
    {
        global $APPLICATION;

        $additionalClassList = $APPLICATION->GetProperty('title-additional-classes');
        $title = '<h1 class="profile-page__title '.$additionalClassList.'" id="pagetitle">' . $APPLICATION->GetTitle(false) . '</h1>';

        $APPLICATION->AddViewContent('title-additional', $title);
    }

    private function insertBlocks()
    {
        global $APPLICATION;

        if (
            strpos($APPLICATION->GetCurPage(), '/bitrix/') !== false
            || strpos($APPLICATION->GetCurPage(), '/local/') !== false
        ) {
            return;
        }

        preg_match_all('/{spoilers_[0-9]+}/', $this->content, $matches, PREG_PATTERN_ORDER);
        $ids = preg_replace('/[^0-9]/', '', $matches[0]);
        $ids = array_unique($ids);

        foreach ($ids as $id) {
            ob_start();

            $APPLICATION->IncludeComponent(
                'bitrix:news.list',
                "spoilers",
                [
                    "ACTIVE_DATE_FORMAT" => "d.m.y",
                    "ADD_SECTIONS_CHAIN" => "N",
                    "AJAX_MODE" => "N",
                    "AJAX_OPTION_ADDITIONAL" => "",
                    "AJAX_OPTION_HISTORY" => "N",
                    "AJAX_OPTION_JUMP" => "N",
                    "AJAX_OPTION_STYLE" => "Y",
                    "CACHE_FILTER" => "N",
                    "CACHE_GROUPS" => "Y",
                    "CACHE_TIME" => "36000000",
                    "CACHE_TYPE" => "A",
                    "CHECK_DATES" => "Y",
                    "DETAIL_URL" => "",
                    "DISPLAY_BOTTOM_PAGER" => "N",
                    "DISPLAY_DATE" => "Y",
                    "DISPLAY_NAME" => "N",
                    "DISPLAY_PICTURE" => "Y",
                    "DISPLAY_PREVIEW_TEXT" => "Y",
                    "DISPLAY_TOP_PAGER" => "N",
                    "FIELD_CODE" => array("", ""),
                    "FILTER_NAME" => "",
                    "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                    "IBLOCK_ID" => IblockHelper::getId('SPOILERS'),
                    "IBLOCK_TYPE" => 'content',
                    "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                    "INCLUDE_SUBSECTIONS" => "N",
                    "MESSAGE_404" => "",
                    "NEWS_COUNT" => "50",
                    "PAGER_BASE_LINK_ENABLE" => "N",
                    "PAGER_DESC_NUMBERING" => "N",
                    "PAGER_DESC_NUMBERING_CACHE_TIME" => "3600000000",
                    "PAGER_SHOW_ALL" => "N",
                    "PAGER_SHOW_ALWAYS" => "N",
                    "PAGER_TEMPLATE" => ".default",
                    "PAGER_TITLE" => "Новости",
                    "PARENT_SECTION" => $id,
                    "PARENT_SECTION_CODE" => "",
                    "PREVIEW_TRUNCATE_LEN" => "",
                    "PROPERTY_CODE" => ['TITLE'],
                    "SET_BROWSER_TITLE" => "N",
                    "SET_LAST_MODIFIED" => "N",
                    "SET_META_DESCRIPTION" => "N",
                    "SET_META_KEYWORDS" => "N",
                    "SET_STATUS_404" => "N",
                    "SET_TITLE" => "N",
                    "SHOW_404" => "N",
                    "SORT_BY1" => "SORT",
                    "SORT_BY2" => "ACTIVE_FROM",
                    "SORT_ORDER1" => "ASC",
                    "SORT_ORDER2" => "DESC",
                    "STRICT_SECTION_CHECK" => "N"
                ],
                true
            );

            $this->content = str_replace('{spoilers_' . $id . '}', ob_get_clean(), $this->content);
        }

        preg_match_all('/{slider_[0-9]+}/', $this->content, $matches, PREG_PATTERN_ORDER);
        $ids = preg_replace('/[^0-9]/', '', $matches[0]);
        $ids = array_unique($ids);

        foreach($ids as $id) {
            ob_start();

            $filterNameSlider = 'arFilterSlider' . $id;
            $GLOBALS[$filterNameSlider] = [
                'ID' => $id
            ];

            $APPLICATION->IncludeComponent(
                'bitrix:news.list',
                "slider",
                [
                    "ACTIVE_DATE_FORMAT" => "d.m.y",
                    "ADD_SECTIONS_CHAIN" => "N",
                    "AJAX_MODE" => "N",
                    "AJAX_OPTION_ADDITIONAL" => "",
                    "AJAX_OPTION_HISTORY" => "N",
                    "AJAX_OPTION_JUMP" => "N",
                    "AJAX_OPTION_STYLE" => "Y",
                    "CACHE_FILTER" => "Y",
                    "CACHE_GROUPS" => "Y",
                    "CACHE_TIME" => "36000000",
                    "CACHE_TYPE" => "A",
                    "CHECK_DATES" => "Y",
                    "DETAIL_URL" => "",
                    "DISPLAY_BOTTOM_PAGER" => "N",
                    "DISPLAY_DATE" => "Y",
                    "DISPLAY_NAME" => "N",
                    "DISPLAY_PICTURE" => "Y",
                    "DISPLAY_PREVIEW_TEXT" => "Y",
                    "DISPLAY_TOP_PAGER" => "N",
                    "FIELD_CODE" => array("", ""),
                    "FILTER_NAME" => $filterNameSlider,
                    "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                    "IBLOCK_ID" => IblockHelper::getId('SLIDERS'),
                    "IBLOCK_TYPE" => 'content',
                    "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                    "INCLUDE_SUBSECTIONS" => "N",
                    "MESSAGE_404" => "",
                    "NEWS_COUNT" => "1",
                    "PAGER_BASE_LINK_ENABLE" => "N",
                    "PAGER_DESC_NUMBERING" => "N",
                    "PAGER_DESC_NUMBERING_CACHE_TIME" => "3600000000",
                    "PAGER_SHOW_ALL" => "N",
                    "PAGER_SHOW_ALWAYS" => "N",
                    "PAGER_TEMPLATE" => ".default",
                    "PAGER_TITLE" => "Новости",
                    "PARENT_SECTION" => '',
                    "PARENT_SECTION_CODE" => "",
                    "PREVIEW_TRUNCATE_LEN" => "",
                    "PROPERTY_CODE" => ['GALLERY'],
                    "SET_BROWSER_TITLE" => "N",
                    "SET_LAST_MODIFIED" => "N",
                    "SET_META_DESCRIPTION" => "N",
                    "SET_META_KEYWORDS" => "N",
                    "SET_STATUS_404" => "N",
                    "SET_TITLE" => "N",
                    "SHOW_404" => "N",
                    "SORT_BY1" => "SORT",
                    "SORT_BY2" => "ACTIVE_FROM",
                    "SORT_ORDER1" => "ASC",
                    "SORT_ORDER2" => "DESC",
                    "STRICT_SECTION_CHECK" => "N"
                ],
                true
            );

            $this->content = str_replace('{slider_' . $id . '}', ob_get_clean(), $this->content);
        }
    }

    protected function removeScripts()
    {
        if(Common::isBot()){
            $this->content = preg_replace('/<script(.*?)src="(.*?)(core|pull)(.*?)">(.*?)<\/script>/', '', $this->content);
        }
    }

    protected function removeStyles()
    {
        if(CurrentUser::get()->isAdmin()){
            return;
        }

        $this->content = preg_replace('/<link(.*?)href="(.*?)(intranet|opensans|kernel_main|popup)(.*?).css(.*?)"(.*?)\/>/', '', $this->content);

        if(Common::isBot()){
            $this->content = preg_replace('/<link(.*?)href="(.*?)(core|sticker|panel|themes)(.*?).css(.*?)"(.*?)\/>/', '', $this->content);
        }
    }

    protected function removeScriptsType()
    {
        $this->content = str_replace('type="text/javascript"', '', $this->content);
    }

    protected function minifyContent()
    {
        if(CurrentUser::get()->isAdmin()){
            return;
        }

        global $APPLICATION;

        if(
            (strpos($APPLICATION->GetCurDir(), '/bitrix/') !== false)
            || ($APPLICATION->GetProperty('save_kernel') === 'Y')
        ){
            return;
        }

        $search = [
            '/\>[^\S ]+/s',
            '/[^\S ]+\</s',
            '/(\s)+/s'
        ];

        $replace = [
            '>',
            '<',
            '\\1'
        ];

        $this->content = preg_replace($search, $replace, $this->content);

    }

    protected function handleLocation()
    {
        $request = Context::getCurrent()->getRequest();

        if($request->isAdminSection()){
            return;
        }

        if(Common::isBot()){
            Location::setDefaultLocation();
            return;
        }

        $selectedLocation = Location::getSelected();

        $newLocation = $request->get(Location::PARAM_CODE);
        if(
            !$newLocation
            || ($newLocation === $selectedLocation['CODE'])
        ){
            return;
        }

        $arNewLocation = Location::getLocation($newLocation);
        if(!empty($arNewLocation)){
            Location::setLocation($arNewLocation);
        }
    }

    protected function setPage404(): void
    {
        $page404 = '/404.php';

        global $APPLICATION;

        if (
            (strpos($APPLICATION->GetCurPage(), $page404) === false)
            && defined('ERROR_404')
            && (ERROR_404 === 'Y')
        ) {
            $app = App::getInstance();
            $app->setIs404(true);

            $APPLICATION->RestartBuffer();
            \CHTTP::SetStatus('404 Not Found');
            include($_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/header.php');
            include($_SERVER['DOCUMENT_ROOT'] . $page404);
            include($_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/footer.php');
        }
    }
}