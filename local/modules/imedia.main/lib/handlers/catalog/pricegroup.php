<?php
namespace Imedia\Main\Handlers\Catalog;

use Imedia\Main\Helpers\Cache;
use Imedia\Main\Helpers\Catalog\Price;

class PriceGroup
{
    public static function onAdd(int $id, array $arFields)
    {
        static::clearCache();
    }

    public static function onUpdate(int $id, array $arFields)
    {
        static::clearCache();
    }

    public static function onDelete(int $id)
    {
        static::clearCache();
    }

    protected static function clearCache()
    {
        Cache\Tagged::clear(Price::CACHE_TAG);
    }
}