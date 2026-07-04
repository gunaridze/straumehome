<?php
if (file_exists(\Bitrix\Main\Loader::getDocumentRoot()."/local/vendor/autoload.php")){
    require_once(\Bitrix\Main\Loader::getDocumentRoot()."/local/vendor/autoload.php");
}

if(file_exists(__DIR__ . '/events.php')){
    require_once __DIR__ . '/events.php';
}