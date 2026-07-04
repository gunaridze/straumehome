<?php
namespace Imedia\Main\Helpers\Image;

class Resize
{
    private array $_sizes;
    private array $_images;
    private array $_imagesInfo;
    private array $_resizeImages;

    public function __construct(array $arSizes)
    {
        if (!is_array(current($arSizes))) {
            $arSizes['DEFAULT'] = $arSizes;
        }

        $arReturnSizes = [];
        foreach ($arSizes as $key => $arSize) {
            if (
                (int)$arSize[0] > 0
                && (int)$arSize[1] > 0
            ) {
                $arReturnSizes[$key] = [
                    'WIDTH' => (int) $arSize[0],
                    'HEIGHT' => (int) $arSize[1],
                    'TYPE' => $arSize[2],
                ];
            }
        }

        if (!empty($arReturnSizes)) {
            $this->_sizes = $arReturnSizes;
        } else {
            return false;
        }

        $this->_images = [];
        $this->_imagesInfo = [];
        $this->_resizeImages = [];
    }

    public function add($image): void
    {
        if (is_array($image)) {
            if ($image['ID']) {
                $this->_add($image);
            } else {
                foreach ($image as $id) {
                    $this->_add($id);
                }
            }
        } else {
            $this->_add((int) $image);
        }
    }

    private function _add($image): void
    {
        $arFile = (is_array($image)) ? $image : \CFile::GetFileArray($image);
        if ($arFile['ID'] > 0) {
            if (!in_array($arFile['ID'], $this->_images)) {
                $this->_images[] = $arFile['ID'];
                $this->_imagesInfo[$arFile['ID']] = $arFile;
            }
        }
    }

    public function getResizeArray(): array
    {
        $this->_getResize();
        $arResult['RESIZE'] = $this->_makeResultArray();
        return $arResult;
    }

    private function _getResize()
    {
        foreach ($this->_images as $id) {
            foreach ($this->_sizes as $key => $arSize) {
                $this->_resizeImages[$id][$key] = \CFile::ResizeImageGet($id, [
                    'width' => $arSize['WIDTH'],
                    'height' => $arSize['HEIGHT'],
                ], $arSize['TYPE'], true, false, false, 100);
            }
        }
    }

    private function _makeResultArray()
    {
        $arResult = [];

        foreach ($this->_images as $id) {
            $arImage = [
                'SIZES' => [],
                'META' => [
                    'ALT' => ($this->_imagesInfo[$id]['ALT']) ?: $this->_imagesInfo[$id]['DESCRIPTION'],
                    'TITLE' => ($this->_imagesInfo[$id]['TITLE']) ?: $this->_imagesInfo[$id]['DESCRIPTION'],
                ],
            ];

            foreach ($this->_sizes as $key => $arSize) {
                $arImage['SIZES'][$key] = $this->_resizeImages[$id][$key]['src'];
                $arImage['DIMENSIONS'][$key] = [
                    'WIDTH' => $this->_resizeImages[$id][$key]['width'],
                    'HEIGHT' => $this->_resizeImages[$id][$key]['height']
                ];
            }

            $arImage['SIZES']['ORIGINAL'] = $this->_imagesInfo[$id]['SRC'];

            $arResult[] = $arImage;

        }

        return $arResult;
    }

    public function setResizeArray(&$arResult): array
    {
        $arResize = $this->getResizeArray();

        if (!is_array($arResult)) {
            $arResult = [];
        }

        return $arResult + $arResize;
    }

    public static function setSelfResizeArray(&$arResult, array $arSizes): array
    {
        $image = new static($arSizes);
        $image->add($arResult);
        $arResult = $image->setResizeArray($arResult);
        return $arResult;
    }
}