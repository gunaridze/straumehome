<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
<nav class="page-nav">
    <ul class="page-nav__list">
<?
foreach($arResult as $arItem):
	if($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1) 
		continue;
?>
	<?if($arItem["SELECTED"]):?>
        <li class="page-nav__item"><a href="<?=$arItem["LINK"]?>" class="page-nav__link active"><?=$arItem["TEXT"]?></a></li>
	<?else:?>
        <li class="page-nav__item"><a href="<?=$arItem["LINK"]?>" class="page-nav__link"><?=$arItem["TEXT"]?></a></li>
	<?endif?>
<?endforeach?>
    </ul>
</nav>
<?endif?>


