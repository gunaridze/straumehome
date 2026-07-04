<?php
namespace Imedia\Main\Helpers\Page;

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;

class Schema
{
    private static array $schema = [];

    public static function add(string $key, array $arParams = []): void
    {
        static::$schema[$key] = $arParams;
    }

    public static function set(): void
    {
        $asset = Asset::getInstance();

        foreach(self::$schema as $entity){
            $asset->addString('<script type="application/ld+json">'. Json::encode($entity) .'</script>');
        }
    }
}