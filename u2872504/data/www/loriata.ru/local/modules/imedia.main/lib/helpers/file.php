<?php
namespace Imedia\Main\Helpers;

use Bitrix\Main\FileTable;
use Bitrix\Main\Config\Option;

class File
{
    public static function getPathFromFiles(array $ids): array
    {
        if(empty($ids)){
            return [];
        }

        $data = [];

        $uploadDir = Option::get('main', 'upload_dir', 'upload');

        $query = FileTable::getList(
            [
                'select' => ['ID', 'FILE_NAME', 'SUBDIR'],
                'filter' => ['=ID' => $ids]
            ]
        );
        while($row = $query->fetch()){
            $data[ $row['ID'] ] = static::getPath($row, $uploadDir);
        }

        return $data;
    }

    /**
     * @param array $arFile
     * @param string|null $uploadDir
     * @return string
     */
    public static function getPath(array $arFile, string $uploadDir = null): string
    {
        if(!$uploadDir){
            $uploadDir = Option::get('main', 'upload_dir', 'upload');
        }

        $filepath = '/' . $uploadDir . '/' . $arFile['SUBDIR'] . '/' . $arFile['FILE_NAME'];
        $filepath = str_replace('//', '/', $filepath);
        if(defined('BX_IMG_SERVER')){
            $filepath = BX_IMG_SERVER.$filepath;
        }

        return $filepath;
    }
}