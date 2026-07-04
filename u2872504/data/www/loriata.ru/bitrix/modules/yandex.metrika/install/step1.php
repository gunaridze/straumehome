<?if(!check_bitrix_sessid()) return;?>
<?
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/install.php");


$moduleId = 'yandex.metrika';
$settingsPageUrl = '/bitrix/admin/settings.php?lang='.LANGUAGE_ID.'&mid=yandex.metrika';
$checkPHPVersion = version_compare(PHP_VERSION, '7.0.0') >= 0;
$checkBitrixVersion = version_compare(SM_VERSION, '18.0.0') >= 0;

	if (!$checkBitrixVersion) {
		?>
        <div class="adm-info-message-wrap adm-info-message-red">
            <div class="adm-info-message">
                <div class="adm-info-message-title"><?= GetMessage('YANDEX_METRIKA_BAD_BITRIX_VERSION'); ?></div>
                <div class="adm-info-message-icon"></div>
            </div>
        </div>
		<?php
	}



	if (!$checkPHPVersion) {
		?>
        <div class="adm-info-message-wrap adm-info-message-red">
            <div class="adm-info-message">
                <div class="adm-info-message-title"><?= GetMessage('YANDEX_METRIKA_BAD_PHP_VERSION'); ?></div>
                <div class="adm-info-message-icon"></div>
            </div>
        </div>
		<?php
	}
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
    <input type="hidden" name="lang" value="<?echo LANG?>">
    <input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
</form>