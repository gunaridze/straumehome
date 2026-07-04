<?php
namespace Imedia\Main\Helpers\Sitemap;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;
use Imedia\Main\Helpers\Debug\Logger;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Iblock\Property;
use Imedia\Main\Helpers\File;

class Images
{
    protected Logger\Logger $logger;

    protected function __construct()
    {
        $this->logger = new Logger\Logger();
        $this->logger->routes->attach(new Logger\Route\File(
            [
                'isEnable' => true,
                'logDir' => str_replace('Imedia\\Main', '', static::class)
            ]
        ));
    }

    public static function process()
    {
        $process = new static();

        try{
            $data = $process->getData();
            $process->createFile($data);
        } catch (\Exception $e){
            $process->logger->critical($e->getMessage());
        }

        return '\\' . static::class . '::process();';
    }

    protected function getData(): array
    {
        $data = [];
        $pictureIds = [];

        Loader::includeModule('iblock');

        $iblockId = IblockHelper::getId('CATALOG');

        $arFilter = [
            '=IBLOCK_ID' => $iblockId,
            '=ACTIVE' => 'Y'
        ];

        $arSelect = [
            'ID',
            'NAME',
            'DETAIL_PAGE_URL',
            'DETAIL_PICTURE'
        ];

        $query = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while($row = $query->GetNext(true, false)){

            $data[ $row['ID'] ] = [
                'NAME' => $row['NAME'],
                'LINK' => $row['DETAIL_PAGE_URL'],
                'PICTURES' => []
            ];

            if($row['DETAIL_PICTURE']){
                $data[ $row['ID'] ]['PICTURES'][] = $row['DETAIL_PICTURE'];
                $pictureIds[] = $row['DETAIL_PICTURE'];
            }

        }

        if(empty($data)){
            return [];
        }

        $arProperties = Property::getPropertyValuesArray(array_keys($data), $iblockId, ['=CODE' => 'GALLERY']);
        foreach($arProperties as $elementId => $arElementProperties){
            foreach($arElementProperties['GALLERY']['VALUE'] as $fileId){
                $data[ $elementId ]['PICTURES'][] = $fileId;
                $pictureIds[] = $fileId;
            }
        }

        $arImages = File::getPathFromFiles($pictureIds);

        foreach($data as $elementId => $arItem){

            $pictures = [];

            foreach($arItem['PICTURES'] as $fileId){
                if(isset($arImages[$fileId])){
                    $pictures[] = $arImages[$fileId];
                }
            }

            if(empty($pictures)){
                unset($data[$elementId]);
            } else {
                $data[$elementId]['PICTURES'] = $pictures;
            }

        }

        return $data;
    }

    protected function createFile(array $data): void
    {
        $arSite = SiteTable::getList(
            [
                'select' => ['SERVER_NAME', 'DOC_ROOT'],
                'filter' => ['=DEF' => true],
                'limit' => 1
            ]
        )->fetch();

        $content = '<?xml version="1.0" encoding="UTF-8"?>';
        $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        foreach($data as $arItem){

            $content .= '<url>';
            $content .= '<loc>https://' . $arSite['SERVER_NAME'] . $arItem['LINK'].'</loc>';

            foreach($arItem['PICTURES'] as $picture){
                $content .= '<image:image>';
                $content .= '<image:loc>https://' . $arSite['SERVER_NAME'] . $picture.'</image:loc>';
                $content .= '</image:image>';
            }

            $content .= '</url>';
        }

        $content .= '</urlset>';

        $documentRoot = ($arSite['DOC_ROOT']) ?: Application::getDocumentRoot();

        file_put_contents($documentRoot . '/sitemap_image.xml', $content);
    }
}