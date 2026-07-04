<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arResult */
/** @var array $arParams */

?>
<script type="text/javascript">
    window.dataLayer.push(JSON.parse('<?= \Bitrix\Main\Web\Json::encode($arResult['ACTION']); ?>'));
</script>
<?