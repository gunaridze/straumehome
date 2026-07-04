<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

?>
<form method="post" name="form1" action="<?= POST_FORM_ACTION_URI ?>" enctype="multipart/form-data" role="form"
      class="profile-personal profile-personal profile-page__form">
    <?= $arResult["BX_SESSION_CHECK"] ?>
    <input type="hidden" name="lang" value="<?= LANG ?>"/>
    <input type="hidden" name="ID" value="<?= $arResult["ID"] ?>"/>
    <input type="hidden" name="LOGIN" value="<?= $arResult["arUser"]["LOGIN"] ?>"/>

    <input class="input" type="text" name="NAME" maxlength="50" id="main-profile-name"
           value="<?= $arResult["arUser"]["NAME"] ?>"/>
    <input class="input" type="text" name="LAST_NAME" maxlength="50" id="main-profile-last-name"
           value="<?= $arResult["arUser"]["LAST_NAME"] ?>"/>
    <input class="input" type="tel" name="PERSONAL_PHONE" id="main-profile-personal-phone"
           value="<?= $arResult["arUser"]["PERSONAL_PHONE"] ?>">
    <input class="input" type="text" name="EMAIL" maxlength="50" id="main-profile-email"
           value="<?= $arResult["arUser"]["EMAIL"] ?>">

    <div class="profile-personal__row-field profile-personal__gender">
        <div class="profile-personal__field-title"><?= Loc::getMessage('GENDER') ?></div>
        <div class="profile-personal__gender-radios">
            <label class="profile-personal__gender-radio">
                <input class="radio-box" type="radio" name="PERSONAL_GENDER"
                       value="F" <?= $arResult['arUser']['PERSONAL_GENDER'] == 'F' ? 'checked' : '' ?> >
                <span class="radio-style">
                        <span class="radio-text"><?= Loc::getMessage('FEMALE') ?></span>
                    </span>
            </label>
            <label class="profile-personal__gender-radio">
                <input class="radio-box" type="radio" name="PERSONAL_GENDER"
                       value="M" <?= $arResult['arUser']['PERSONAL_GENDER'] == 'M' ? 'checked' : '' ?>>
                <span class="radio-style">
                        <span class="radio-text"><?= Loc::getMessage('MALE') ?></span>
                    </span>
            </label>
        </div>
    </div>
    <div class="profile-personal__row-field profile-personal__password">
        <div class="profile-personal__field-title"><?= Loc::getMessage('PASSWORD') ?></div>
        <a data-fancybox href="#password-change"
           class="input profile-personal__change-password-btn"><?= Loc::getMessage('CHANGE') ?></a>
    </div>

    <input type="submit" name="save" class="btn profile-personal__btn" value="<?= Loc::getMessage('SAVE') ?>">
</form>
<?php if($arResult["strProfileError"] || ($arResult['DATA_SAVED'] === 'Y')): ?>
    <div class="profile-page__notifies">
        <?php if ($arResult["strProfileError"]): ?>
            <div class="notify profile-notify error">
                <img class="notify__icon" src="<?= SITE_TEMPLATE_PATH ?>/assets/images/icons/error.svg" alt="иконка"
                     width="24" height="24">
                <?php ShowError(Loc::getMessage('MESSAGE_ERROR')); ?>
            </div>
        <?php endif; ?>
        <?php if ($arResult['DATA_SAVED'] === 'Y'): ?>
            <div class="notify profile-notify success">
                <img class="notify__icon" src="<?= SITE_TEMPLATE_PATH ?>/assets/images/icons/success.svg" alt="иконка"
                     width="24" height="24">
                <?php ShowNote(Loc::getMessage('MESSAGE_SUCCESS')); ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif ?>
<div style="display: none;" id="password-change" class="popup password-change">
    <div class="password-change__title"><?=Loc::getMessage('CHANGE_PASSWORD_TITLE')?></div>
    <form  method="post" name="form2" action="<?=POST_FORM_ACTION_URI?>" class="password-change__form">
        <?=$arResult["BX_SESSION_CHECK"]?>
        <input type="hidden" name="lang" value="<?=LANG?>" />
        <input type="hidden" name="ID" value="<?=$arResult["ID"]?>" />
        <input type="hidden" name="LOGIN" value="<?=$arResult["arUser"]["LOGIN"]?>" />
        <div class="input-wrap">
            <input id="new_password" class="input" type="password" name="NEW_PASSWORD" placeholder="<?=Loc::getMessage('NEW_PASSWORD')?>">
            <button class="pass-btn show" type="button"></button>
        </div>
        <div class="input-wrap">
            <input class="input" type="password" name="NEW_PASSWORD_CONFIRM" placeholder="<?=Loc::getMessage('NEW_PASSWORD_CONFIRM')?>">
            <button class="pass-btn show" type="button"></button>
        </div>
        <input type="submit" name="save" class="btn password-change__form-btn" value="<?=Loc::getMessage('NEW_PASSWORD_SAVE')?>">
    </form>
</div>