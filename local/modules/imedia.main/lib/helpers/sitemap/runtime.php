<?php
namespace Imedia\Main\Helpers\Sitemap;

use Bitrix\Main\Text\Converter;
use Bitrix\Main\IO;
use Bitrix\Seo\SitemapFile;

class Runtime extends File
{
    const PROGRESS_WIDTH = 500;

    protected $PID = 0;
    private $originalFile = NULL;

    public function __construct($PID, $fileName, $arSettings)
    {
        $this->PID = $PID;

        if ($this->partFile == '') {
            $this->partFile = $fileName;
        }

        $fileName = IO\Path::normalize($fileName);
        $lastSlashPosition = mb_strrpos($fileName, "/");
        $fileDirectory = '';
        if ($lastSlashPosition !== false) {
            $fileDirectory = mb_substr($fileName, 0, $lastSlashPosition + 1);
            $fileName = mb_substr($fileName, $lastSlashPosition + 1);
        }

        parent::__construct($fileDirectory . $this->getPrefix() . $fileName, $arSettings);
    }

    /**
     * Recreate file with same settings to new part
     *
     * @param string $fileName
     */
    protected function reInit($fileName)
    {
        $this->__construct($this->PID, $fileName, $this->settings);
    }

    public function putSitemapContent(SitemapFile $sitemapFile)
    {
        if ($this->isExists()){
            $this->delete();
        }

        if ($sitemapFile->isExists()) {
            $this->putContents($sitemapFile->getContents());
            $this->partChanged = true;
            $this->footerClosed = true;
        } else {
            $this->addHeader();
        }
    }


    public function setOriginalFile(SitemapFile $sitemapFile)
    {
        if (isset($sitemapFile)){
            $this->originalFile = $sitemapFile;
        }
    }

    public function appendEntry($entry)
    {
        if($this->isSplitNeeded()) {
            $this->split();
            $this->appendEntry($entry);
        } else {
            if(!$this->partChanged) {
                $this->addHeader();
                $offset = $this->getSize();
            } else {
                $offset = $this->getSize() - mb_strlen(self::FILE_FOOTER);
            }

            $fd = $this->open('r+');

            fseek($fd, $offset);
            fwrite($fd, sprintf(
                    self::ENTRY_TPL,
                    Converter::getXmlConverter()->encode($entry['XML_LOC']),
                    Converter::getXmlConverter()->encode($entry['XML_LASTMOD']),
                    Converter::getXmlConverter()->encode($entry['XML_PRIORITY']),
                ).self::FILE_FOOTER);
            fclose($fd);

            $this->footerClosed = true;
        }
    }

    /**
     * Overwrite parent method to creating temp-files and correctly work with multipart
     * Appends new IBlock entry to the existing finished sitemap
     *
     * @param string $url IBlock entry URL.
     * @param string $modifiedDate IBlock entry modify timestamp.
     *
     * @return void
     */
    public function appendIBlockEntry($url, $modifiedDate)
    {
        if(!$this->originalFile) {
            parent::appendIBlockEntry($url, $modifiedDate);
            return;
        }

        if ($this->originalFile->isExists()) {
            while ($this->originalFile->isSplitNeeded()) {
                $filename = $this->originalFile->split();
            }

            if (isset($filename) && $filename){
                $this->reInit($filename);
            }

            $this->putSitemapContent($this->originalFile);
            $e = [];
            $this->appendEntry(
                [
                    'XML_LOC' => $this->settings['PROTOCOL'] . '://' . \CBXPunycode::toASCII($this->settings['DOMAIN'], $e) . $url,
                    'XML_LASTMOD' => date('c', $modifiedDate - \CTimeZone::getOffset()),
                    'XML_PRIORITY' => static::DEFAULT_PRIORITY
                ]
            );
        } else {
            $this->addHeader();
            $this->addIBlockEntry($url, $modifiedDate);
            $this->addFooter();
        }
    }

    /**
     * Rename runtime file to original name. If runtime have part - rename them all
     */
    public function finish()
    {
        foreach ($this->partList as $key => $partName) {
            $f = new IO\File(IO\Path::combine($this->getDirectoryName(), $partName));
            $f->rename(str_replace($this->getPrefix(), '', $f->getPath()));
            $this->partList[$key] = $f->getName();
        }

        if ($this->isCurrentPartNotEmpty()) {
            if (!$this->footerClosed){
                $this->addFooter();
            }

            $this->rename(str_replace($this->getPrefix(), '', $this->getPath()));
        }
    }

    protected function getPrefix()
    {
        return '~' . $this->PID;
    }

    public static function showProgress($text, $title, $v)
    {
        $v = max($v, 0);

        if ($v < 100) {
            $msg = new \CAdminMessage(
                [
                    "TYPE" => "PROGRESS",
                    "HTML" => true,
                    "MESSAGE" => $title,
                    "DETAILS" => "#PROGRESS_BAR#<div style=\"width: " . self::PROGRESS_WIDTH . "px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-top: 20px;\">" . Converter::getHtmlConverter()->encode($text) . "</div>",
                    "PROGRESS_TOTAL" => 100,
                    "PROGRESS_VALUE" => $v,
                    "PROGRESS_TEMPLATE" => '#PROGRESS_PERCENT#',
                    "PROGRESS_WIDTH" => self::PROGRESS_WIDTH
                ]
            );
        } else {
            $msg = new \CAdminMessage(
                [
                    "TYPE" => "OK",
                    "MESSAGE" => $title,
                    "DETAILS" => $text
                ]
            );
        }

        return $msg->show();
    }
}