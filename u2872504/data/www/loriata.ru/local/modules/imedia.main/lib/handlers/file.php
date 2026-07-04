<?php
namespace Imedia\Main\Handlers;

use Imedia\Main\Helpers\File as FileHelper;
use Imedia\Main\Helpers\Image\Share;

class File
{
    public static function onDelete(array $arFile)
    {
        $arFile['PATH'] = FileHelper::getPath($arFile);

        Share::remove($arFile['PATH']);
    }
}