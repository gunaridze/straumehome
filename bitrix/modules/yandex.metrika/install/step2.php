<?if(!check_bitrix_sessid()) return;?>
<?
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/install.php");

$moduleId = 'yandex.metrika';
$settingsPageUrl = '/bitrix/admin/settings.php?lang='.LANGUAGE_ID.'&mid=yandex.metrika';


if (in_array('found', $stepData['fillingStates'])) {
    ?>
    <div class="adm-info-message-wrap adm-info-message-green">
        <div class="adm-info-message">
            <div class="adm-info-message-title"><?= sprintf(GetMessage('YANDEX_METRIKA_SETTINGS_FOUND'), $settingsPageUrl); ?></div>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
    <?php
} elseif(empty($stepData['fillingStates'])) {
	?>
    <div class="adm-info-message-wrap">
        <div class="adm-info-message">
            <div class="adm-info-message-title">
				<?= sprintf(
					GetMessage('YANDEX_METRIKA_SETTINGS_NOT_FOUND'),
					'https://yandex.ru/support/metrica/general/creating-counter.html',
					'https://yandex.ru/support/metrica/general/tag-id.html',
					$settingsPageUrl
				); ?>
            </div>
        </div>
    </div>
	<?php
}
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
</form>