<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('imedia.main');
echo '<pre>';
$test = new \Imedia\Main\Controller\Order();
$res = $test->sdekPickupAction();
$data = json_decode($res->getContent(),true)['data']['html'];
echo var_export(json_decode($data,true));
echo var_export(get_class_methods(get_class($res)));