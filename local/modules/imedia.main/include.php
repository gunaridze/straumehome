<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Diag;
use Imedia\Main\Helpers\Debug\Debug;

if(!function_exists('printr')){
    function printr($arr, bool $getFile = false): void
    {
        echo Debug::printr($arr, $getFile);
    }
}

if(!function_exists('dmp')){
    function dmp($data): void
    {
        Diag\Debug::writeToFile($data);
    }
}

Loader::registerAutoLoadClasses(
    'imedia.main',
    [
        'CImedia' => 'classes/general/cimedia.php',
    ]
);