<?php
namespace Imedia\Main\Handlers\Iblock;

use Imedia\Main\Helpers\Cache;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;

class Iblock
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

    public static function onAfterIBlockAdd(array $arFields)
    {
        $handler = self::getInstance();
        $handler->arFields = $arFields;
        $handler->onAfterUpdate();
    }

    public static function onAfterIBlockUpdate(array $arFields)
    {
        $handler = self::getInstance();
        $handler->arFields = $arFields;
        $handler->onAfterUpdate();
    }

    public static function onBeforeIBlockDelete(int $id)
    {
        $handler = self::getInstance();
        $handler->arFields = [
            'ID' => $id
        ];
    }

    public static function onAfterIBlockDelete()
    {
        $handler = self::getInstance();
        $handler->onAfterUpdate();
    }

    protected function onAfterUpdate(): void
    {
        Cache\Tagged::clear(IblockHelper::CACHE_TAG);
    }
}