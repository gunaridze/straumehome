<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "");
$APPLICATION->SetPageProperty("title", "");
$APPLICATION->SetTitle("Отписаться от товара");
?>
<?$APPLICATION->IncludeComponent("imedia:catalog.product.unsubscribe", "",
	[],
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>