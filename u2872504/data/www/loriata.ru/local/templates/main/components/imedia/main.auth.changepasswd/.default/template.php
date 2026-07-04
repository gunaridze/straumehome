<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true){
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$fields = $arResult['FIELDS'];

if(!is_array($arResult['SUCCESS'])){
    $arResult['SUCCESS'] = ($arResult['SUCCESS']) ? [$arResult['SUCCESS']] : [];
}
?>
<?php if(!empty($arResult['ERRORS']) || !empty($arResult['SUCCESS'])):?>
    <div class="profile-page__notifies">
        <?php foreach ($arResult['ERRORS'] as $error): ?>
            <div class="notify profile-notify error">
                <img
                        class="notify__icon"
                        src="<?=SITE_TEMPLATE_PATH?>/assets/images/icons/error.svg"
                        alt="error"
                        width="24"
                        height="24"
                >
                <?=$error?>
            </div>
        <?php endforeach ?>
        <?php foreach ($arResult['SUCCESS'] as $success): ?>
            <div class="notify profile-notify success">
                <img
                        class="notify__icon"
                        src="<?=SITE_TEMPLATE_PATH?>/assets/images/icons/success.svg"
                        alt="error"
                        width="24"
                        height="24"
                >
                <?=$success?>
            </div>
        <?php endforeach ?>
    </div>
<?php endif?>
<div style="max-width: 600px;">
    <form name="bform" method="post" target="_top" action="<?= POST_FORM_ACTION_URI;?>">
        <label class="input input-label">
            <span class="input__label focus"><?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_LOGIN');?></span>
            <input type="text" name="<?= $fields['login'];?>" maxlength="255" value="<?= \htmlspecialcharsbx($arResult['LAST_LOGIN']);?>" />
        </label>
        <br>
        <label class="input input-label">
            <span class="input__label focus"><?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_CHECKWORD');?></span>
            <input type="text" name="<?= $fields['checkword'];?>" maxlength="255" value="<?= \htmlspecialcharsbx($arResult[$fields['checkword']]);?>" />
        </label>
        <br>
        <label class="input input-label">
            <span class="input__label focus"><?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_PASS');?></span>
            <input type="password" name="<?= $fields['password'];?>" value="<?= \htmlspecialcharsbx($arResult[$fields['password']]);?>" maxlength="255" autocomplete="off" />
        </label>
        <br>
        <label class="input input-label">
            <span class="input__label focus"><?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_PASS2');?></span>
            <input type="password" name="<?= $fields['confirm_password'];?>" value="<?= \htmlspecialcharsbx($arResult[$fields['confirm_password']]);?>" maxlength="255" autocomplete="off" />
        </label>
        <?php if ($arResult['CAPTCHA_CODE']):?>
            <br>
            <input type="hidden" name="captcha_sid" value="<?= \htmlspecialcharsbx($arResult['CAPTCHA_CODE']);?>" />
            <label class="input input-label">
                <span class="input__label focus"><?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_CAPTCHA');?></span>
                <div class="bx-captcha"><img src="/bitrix/tools/captcha.php?captcha_sid=<?= \htmlspecialcharsbx($arResult['CAPTCHA_CODE']);?>" width="180" height="40" alt="CAPTCHA" /></div>
                <input type="text" name="captcha_word" maxlength="50" value="" autocomplete="off" />
            </label>
        <?php endif;?>
        <br>
        <input type="submit" class="btn feedback__form-btn" name="<?= $fields['action'];?>" value="<?= Loc::getMessage('MAIN_AUTH_CHD_FIELD_SUBMIT');?>">
        <br>
        <p><?= $arResult['GROUP_POLICY']['PASSWORD_REQUIREMENTS'];?></p>
    </form>
</div>