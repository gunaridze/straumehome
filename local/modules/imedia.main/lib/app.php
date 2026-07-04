<?php
namespace Imedia\Main;

use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\SiteTable;
use Imedia\Main\Helpers\Catalog\Selected;

class App
{
    private string $_fullPath;
    private string $_relativePath;
    private bool $_isFront;
    private bool $_is404;
    private string $_siteId;
    private string $_lang;
    private array $_arSite;
    private bool $_isIndexCatalog;
    private static $_instance;

    private function __construct()
    {
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        $this->_fullPath = $request->getRequestedPageDirectory();
        $this->_relativePath = $this->_fullPath;

        $this->_isFront = ($request->getRequestedPage() === SITE_DIR . 'index.php');
        $this->_is404 = (defined('ERROR_404') && ERROR_404 === 'Y');

        $this->_isIndexCatalog = false;
        $catalogIndexList = Selected::getList();
        foreach ($catalogIndexList as $arSection) {

            if ($this->_relativePath === SITE_DIR . $arSection['CODE'] . '/') {
                $this->_isIndexCatalog = true;
                break;
            }

        }

        $this->_siteId = $context->getSite();
        $this->_lang = $context->getLanguage();
        if ($this->_siteId) {
            $this->_arSite = SiteTable::getByID($this->_siteId)->Fetch();
        }
    }

    public function __clone()
    {
        trigger_error('Clone not allowed', E_USER_ERROR);
    }

    public function __wakeup()
    {
        trigger_error('Unserializing not allowed', E_USER_ERROR);
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        if (!isset(self::$_instance)) {
            $className = __CLASS__;
            self::$_instance = new $className;
        }

        return self::$_instance;
    }

    public function setIs404(bool $value): void
    {
        $this->_is404 = $value;
    }

    public function includeContent(string $name = null, bool $fromRoot = false)
    {
        if (!$name) {
            $name = 'index_blocks';
        }

        $file = $_SERVER['DOCUMENT_ROOT'];
        if(!$fromRoot){
            $file .= $this->_relativePath;
        }

        $file .= '/' . $name . '.php';

        if (file_exists($file)) {
            @include_once $file;
        }
    }

    public function isFront(): bool
    {
        return $this->_isFront;
    }

    public function getFullPath(): string
    {
        return $this->_fullPath;
    }

    public function getRelativePath(): string
    {
        return $this->_relativePath;
    }

    public function is404(): bool
    {
        return $this->_is404;
    }

    public function isIndexCatalog(): bool
    {
        return $this->_isIndexCatalog;
    }

    public static function showDefaultHead()
    {
        echo '
        <meta content="user-scalable=no,initial-scale=1.0,maximum-scale=1.0,width=device-width" name="viewport">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="format-detection" content="telephone=no">
        <meta name="format-detection" content="address=no">
        <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="msthemecompatible" content="no">
        <meta name="HandheldFriendly" content="True">
        <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="167x167" href="/apple-touch-icon-167x167.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
        <link rel="apple-touch-icon" sizes="1024x1024" href="/apple-touch-icon-1024x1024.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="228x228" href="/coast-228x228.png">
        <link rel="manifest" href="/manifest.json">
        <link rel="shortcut icon" href="/favicon.ico">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="yandex-tableau-widget" href="/yandex-browser-manifest.json">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title">
        <meta name="application-name">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="msapplication-TileColor" content="#fff">
        <meta name="msapplication-TileImage" content="/mstile-144x144.png">
        <meta name="msapplication-config" content="/browserconfig.xml">
        <meta name="theme-color" content="#101112">
        ';
    }

    public static function showHead()
    {
        static::showDefaultHead();

        global $APPLICATION;

        echo '<meta http-equiv="Content-Type" content="text/html; charset='.LANG_CHARSET.'">'."\n";
        $APPLICATION->ShowMeta("robots", false);
        $APPLICATION->ShowMeta("description", false);
        $APPLICATION->ShowLink("canonical", null);
        $APPLICATION->ShowCSS(true);
        $APPLICATION->ShowHeadStrings();
        $APPLICATION->ShowHeadScripts();
    }

    public static function setAsset()
    {
        $asset = Asset::getInstance();

        $asset->addString("<link rel='preconnect' href='https://fonts.googleapis.com'>");
        $asset->addString("<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>");
        $asset->addString('<link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">');

        $asset->addCss(SITE_TEMPLATE_PATH . '/assets/libs/css/jquery.fancybox.min.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/assets/libs/css/animate.min.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/assets/libs/css/swiper-bundle.min.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/assets/styles/app.min.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/assets/add/css/vendors.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/assets/add/css/app.css');

        if(!Helpers\Common::isBot()){
            $asset->addJs(SITE_TEMPLATE_PATH . '/assets/libs/js/jquery.min.js');
            $asset->addJs(SITE_TEMPLATE_PATH . '/assets/libs/js/swiper-bundle.min.js');
            $asset->addJs(SITE_TEMPLATE_PATH . '/assets/libs/js/jquery.validate.min.js');
            $asset->addJs(SITE_TEMPLATE_PATH . '/assets/libs/js/jquery.fancybox.min.js');
            $asset->addJs(SITE_TEMPLATE_PATH . '/assets/libs/js/wow.min.js');
            $asset->addJs(SITE_TEMPLATE_PATH . '/assets/js/app.min.js');
            $asset->addJs(SITE_TEMPLATE_PATH . '/assets/add/js/vendors.js');
            $asset->addJs(SITE_TEMPLATE_PATH . '/assets/add/js/app.js');
        }
    }

    public function getSiteParam(string $code): string
    {
        return ($this->_arSite[$code]) ?: '';
    }

    public static function jsCoreInit($array = null)
    {
        if (!Helpers\Common::isBot()) {
            \CJSCore::Init($array);
        }
    }
}