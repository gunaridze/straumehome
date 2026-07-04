<?php
namespace Imedia\Main\Helpers\Page;

use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
use Imedia\Main\App;

class Meta
{
    private static array $arMetaParams = [];

    public static function add(array $arParams = []): void
    {
        static::$arMetaParams = array_merge(static::$arMetaParams, $arParams);
    }

    public static function set(): void
    {
        global $APPLICATION;

        $app = App::getInstance();
        $asset = Asset::getInstance();

        $domain = $app->getSiteParam('SERVER_NAME') ?: $_SERVER['HTTP_HOST'];
        $server = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
        $server .= $domain;

        $arMainMeta = [
            'h1',
            'title',
            'description',
            'keywords'
        ];

        foreach($arMainMeta as $key){
            if(static::$arMetaParams[$key]){
                if($key === 'h1'){
                    $APPLICATION->SetTitle(static::$arMetaParams[$key]);
                } else {
                    $APPLICATION->SetPageProperty($key, static::$arMetaParams[$key]);
                }
            }
        }

        $PageH1 = $APPLICATION->GetTitle();
        $PageMetaTitleBrowser = $APPLICATION->GetPageProperty('title');
        $DirMetaTitleBrowser = $APPLICATION->GetDirProperty('title');
        $PageMetaDescription = $APPLICATION->GetPageProperty('description');
        $DirMetaDescription = $APPLICATION->GetDirProperty('description');

        if(!$PageMetaTitleBrowser){
            $PageMetaTitleBrowser = $DirMetaTitleBrowser;
        }

        if(static::$arMetaParams['title']){
            $PageMetaTitleBrowser = static::$arMetaParams['title'];
        }

        if(!$PageMetaTitleBrowser){
            $PageMetaTitleBrowser = $PageH1;
        }

        $request = Application::getInstance()->getContext()->getRequest();
        $pagen = null;
        $queryList = $request->getQueryList()->toArray();

        $url = $server . $app->getRelativePath();
        $canonicalExists = false;

        $siteName = $app->getSiteParam('SITE_NAME') ?: $app->getSiteParam('NAME');

        if (!\CSite::inDir(SITE_DIR . 'index.php')) {
            if (!strlen($PageMetaTitleBrowser)) {
                if (!strlen($DirMetaTitleBrowser)) {
                    $PageMetaTitleBrowser = $PageH1 . ((strlen($PageH1) && strlen($siteName)) ? ' - ' : '') . $siteName;
                }
            }
        } else {
            if (!strlen($PageMetaTitleBrowser)) {
                if (!strlen($DirMetaTitleBrowser)) {
                    $PageMetaTitleBrowser = $siteName . ((strlen($siteName) && strlen($PageH1)) ? ' - ' : '') . $PageH1;
                }
            }
        }

        if(!defined('ERROR_404')){

            foreach($queryList as $key => $value){
                if(strpos($key, 'PAGEN_') === 0){
                    if($pagen){
                        $pagen = null;
                        break;
                    } else {
                        $pagen = (int) $value;
                    }
                }
            }

            if($pagen > 1){
                $addPageInfo = Loc::getMessage('IMEDIA_MAIN_META_PAGE', ['#VALUE#' => $pagen]);
                $PageMetaTitleBrowser = $PageMetaTitleBrowser . $addPageInfo;
                $PageMetaDescription = $PageMetaDescription . $addPageInfo;
                $APPLICATION->SetPageProperty('description', $PageMetaDescription);
            }
        }

        $APPLICATION->SetPageProperty('title', $PageMetaTitleBrowser);

        static::$arMetaParams['og:site_name'] = $siteName;
        static::$arMetaParams['twitter:site'] = $siteName;

        static::$arMetaParams['twitter:domain'] = $domain;

        if (!strlen(static::$arMetaParams['og:title'])) {
            static::$arMetaParams['og:title'] = $PageMetaTitleBrowser;
            static::$arMetaParams['twitter:title'] = $PageMetaTitleBrowser;
            static::$arMetaParams['name'] = $PageMetaTitleBrowser;
        }
        if (!strlen(static::$arMetaParams['og:type'])) {
            static::$arMetaParams['og:type'] = 'article';
        }

        if (!strlen(static::$arMetaParams['og:image'])) {
            static::$arMetaParams['og:image'] = $server . SITE_DIR . 'logo.png';
            static::$arMetaParams['twitter:image:src'] = $server . SITE_DIR . 'logo.png';
            static::$arMetaParams['image'] = $server . SITE_DIR . 'logo.png';
        } else{
            if (strpos(static::$arMetaParams['og:image'], $server) === false) {
                static::$arMetaParams['og:image'] = $server . static::$arMetaParams['og:image'];
            }

            static::$arMetaParams['twitter:image:src'] = static::$arMetaParams['og:image'];
            static::$arMetaParams['image'] = static::$arMetaParams['og:image'];
        }

        if (!strlen(static::$arMetaParams['og:url'])) {
            static::$arMetaParams['og:url'] = $url;
        }
        if (!strlen(static::$arMetaParams['og:description'])) {
            static::$arMetaParams['og:description'] = (strlen($PageMetaDescription) ? $PageMetaDescription : $DirMetaDescription);
            static::$arMetaParams['twitter:description'] = (strlen($PageMetaDescription) ? $PageMetaDescription : $DirMetaDescription);
            static::$arMetaParams['description'] = (strlen($PageMetaDescription) ? $PageMetaDescription : $DirMetaDescription);
        }

        foreach (static::$arMetaParams as $metaName => $metaValue) {
            if (strlen($metaValue = strip_tags($metaValue))) {
                if (strpos($metaName, 'twitter') !== false) {
                    $asset->addString('<meta name="' . $metaName . '" content="' . $metaValue . '" />', true);
                } elseif($metaName === 'canonical'){

                    if(strpos($metaValue, '//') === false){
                        $metaValue = $server . $metaValue;
                    }

                    $asset->addString('<link href="' . $metaValue . '" rel="canonical" />', true);
                    $canonicalExists = true;

                } else {
                    $asset->addString('<meta property="' . $metaName . '" content="' . $metaValue . '" />', true);
                    if ($metaName === 'og:image') {
                        $asset->addString('<link rel="image_src" href="' . $metaValue . '"  />', true);
                    }
                }
            }
        }

        if(
            !$canonicalExists
            && !($APPLICATION->GetPageProperty('canonical'))
        ){
            $asset->addString('<link href="' . $url . '" rel="canonical" />', true);
        }

        if(
            preg_match("/\/filter\/(.*)\/apply.*/", $app->getRelativePath())
            || in_array('sort', array_keys($queryList), true)
            || in_array('order', array_keys($queryList), true)
        ){
            $APPLICATION->SetPageProperty('robots', 'noindex, nofollow');
        }
    }
}