<?php
namespace Imedia\Main\Helpers\Sitemap;

use Bitrix\Seo;
use Bitrix\Main\Text\Converter;
use Bitrix\Seo\SitemapFile;
use Imedia\Main\Helpers\Catalog\Menu;

class File extends SitemapFile
{
    protected array $priorityConfig;

    const ENTRY_TPL = '<url><loc>%s</loc><lastmod>%s</lastmod><priority>%s</priority></url>';
    const DEFAULT_PRIORITY = 0.9;

    protected static ?array $rules = null;

    public function __construct($fileName, $settings)
    {
        parent::__construct($fileName, $settings);

        $this->priorityConfig = [];

        $arMenu = Menu::get();
        foreach($arMenu as $arItem){
            $this->priorityConfig[$arItem['LINK']] = 1;
        }

    }

    public function addEntry($entry)
    {
        if(!$this->isDisallow($entry['XML_LOC'])){
            if ($this->isSplitNeeded()){
                $this->split();
                $this->addEntry($entry);
            } else {
                if (!$this->partChanged){
                    $this->addHeader();
                }
                $this->putContents(
                    sprintf(
                        self::ENTRY_TPL,
                        Converter::getXmlConverter()->encode($entry['XML_LOC']),
                        Converter::getXmlConverter()->encode($entry['XML_LASTMOD']),
                        Converter::getXmlConverter()->encode($entry['XML_PRIORITY'])
                    ), self::APPEND
                );
            }
        }
    }

    public function addFileEntry(\Bitrix\Main\IO\File $f)
    {
        if($f->isExists() && !$f->isSystem()){
            $e = [];
            $this->addEntry(
                [
                    'XML_LOC' => $this->settings['PROTOCOL'].'://'.\CBXPunycode::toASCII($this->settings['DOMAIN'], $e).$this->getFileUrl($f),
                    'XML_LASTMOD' => date('c', $f->getModificationTime()),
                    'XML_PRIORITY' => static::DEFAULT_PRIORITY
                ]
            );
        }
    }

    public function addIBlockEntry($url, $modifiedDate)
    {
        $e = [];
        $this->addEntry(
            [
                'XML_LOC' => $this->settings['PROTOCOL'].'://'.\CBXPunycode::toASCII($this->settings['DOMAIN'], $e).$url,
                'XML_LASTMOD' => date('c', $modifiedDate - \CTimeZone::getOffset()),
                'XML_PRIORITY' => ($this->priorityConfig[$url]) ?: static::DEFAULT_PRIORITY
            ]
        );
    }

    /**
     * @return array
     */
    protected function getRules(): array
    {
        if(!static::$rules){

            $robotsFile = new Seo\RobotsFile($this->settings['SITE_ID']);
            $allRulesArr = $robotsFile->getRules('Disallow');
            static::$rules = [];
            foreach($allRulesArr as $al){
                static::$rules[] = $al[1];
            }

        }

        return static::$rules;
    }

    /**
     * @param string $uri
     * @return bool
     */
    protected function isDisallow(string $uri): bool
    {
        $match = false;
        foreach($this->getRules() as $rule){

            $issetAnyCharacterAtTheEnd = substr($rule, -1) === '*';
            $rule = str_replace(['*', '/'], ['(.*?)', '\/'], $rule);
            if(!$issetAnyCharacterAtTheEnd){
                $rule .= '(.*?)';
            }

            $match = (bool) preg_match('/' . $rule . '/', $uri);
            if($match){
                break;
            }
        }

        return $match;
    }
}