<?php
namespace Imedia\Main\Helpers\Image;

use Bitrix\Main\Application;

class Share
{
    public static function create(string $originSrc): ?string
    {
        $relativeShareImagePath = static::getRelativeShareImagePath($originSrc);
        $shareImagePath = Application::getDocumentRoot() . $relativeShareImagePath;

        if(file_exists($shareImagePath)){
            return $relativeShareImagePath;
        }

        $image = self::createGdImage(Application::getDocumentRoot() . $originSrc);
        if(!$image){
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $sideSize = min($width, $height);

        $xPos = ($width > $height) ? floor(($width - $sideSize) * 0.5) : 0;

        $shareImage = imagecrop($image, ['x' => $xPos, 'y' => 0, 'width' => $sideSize, 'height' => $sideSize]);
        imagedestroy($image);
        if(!$shareImage){
            return null;
        }

        imagesavealpha($shareImage, true);
        $isSuccess = imagepng($shareImage, $shareImagePath);
        imagedestroy($shareImage);

        return $isSuccess ? $relativeShareImagePath : null;
    }

    protected static function createGdImage(string $filename)
    {
        if(!file_exists($filename)){
            return null;
        }

        switch (mime_content_type($filename)) {
            case 'image/png':
                return imagecreatefrompng($filename);
            case 'image/jpeg':
                return imagecreatefromjpeg($filename);
            case 'image/gif':
                return imagecreatefromgif($filename);
            default:
                break;
        }

        return null;
    }

    protected static function getRelativeShareImagePath(string $originSrc): string
    {
        return $originSrc . '_share.png';
    }

    public static function remove(string $originSrc): void
    {
        $relativeShareImagePath = static::getRelativeShareImagePath($originSrc);
        $shareImagePath = Application::getDocumentRoot() . $relativeShareImagePath;

        if(file_exists($shareImagePath)){
            unlink($shareImagePath);
        }
    }
}
