<?php
namespace Imedia\Main\Helpers\Cache;

use Bitrix\Main\Application;

class Tagged
{
    public static function clear(string $id): void
    {
        $taggedCache = Application::getInstance()->getTaggedCache();
        $taggedCache->clearByTag($id);
    }
}