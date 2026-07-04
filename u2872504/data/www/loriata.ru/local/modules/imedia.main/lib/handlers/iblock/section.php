<?php
namespace Imedia\Main\Handlers\Iblock;

use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Context;
use Imedia\Main\Helpers\Cache;
use Imedia\Main\Helpers\Catalog;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Catalog\Section as SectionHelper;

class Section
{
    /**
     * @var array
     */
    public $arFields = [];

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

    public static function getInstance(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function onBeforeIBlockSectionUpdate(array &$arFields)
    {
        $handler = self::getInstance();
        $handler->arFields = $arFields;

        $iblockCode = IblockHelper::getCode((int) $handler->arFields['IBLOCK_ID']);
        switch($iblockCode){
            case 'CATALOG':

                $request = Context::getCurrent()->getRequest();
                if($request->get('mode') === 'import'){

                    if(isset($handler->arFields['PICTURE'])){
                        unset($handler->arFields['PICTURE']);
                    }

                    if(isset($handler->arFields['CODE'])){
                        unset($handler->arFields['CODE']);
                    }

                }

                break;
            default:
                break;
        }

        $arFields = $handler->arFields;
    }

    public static function onAfterIBlockSectionAdd(array $arFields)
    {
        $handler = self::getInstance();
        $handler->arFields = $arFields;
        $handler->onAfterUpdate();
    }

    public static function onAfterIBlockSectionUpdate(array $arFields)
    {
        $handler = self::getInstance();
        $handler->arFields = $arFields;
        $handler->onAfterUpdate();
    }

    public static function onBeforeIBlockSectionDelete(int $id)
    {
        $handler = self::getInstance();
        $handler->arFields = SectionTable::getList(
            [
                'select' => ['ID', 'IBLOCK_ID'],
                'filter' => [
                    '=ID' => $id
                ],
                'limit' => 1
            ]
        )->fetch();
    }

    public static function onAfterIBlockSectionDelete()
    {
        $handler = self::getInstance();
        $handler->onAfterUpdate();
    }

    protected function onAfterUpdate(): void
    {
        $iblockCode = IblockHelper::getCode((int) $this->arFields['IBLOCK_ID']);
        switch($iblockCode){
            case 'CATALOG':
                Cache\Tagged::clear(Catalog\Selected::CACHE_TAG);
                Cache\Tagged::clear(Catalog\Menu::CACHE_SECTIONS_TAG);
                break;
            default:
                break;
        }

        Cache\Tagged::clear('iblock_section_id_' . $this->arFields['ID']);
    }
}