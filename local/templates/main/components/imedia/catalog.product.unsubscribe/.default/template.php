<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc; ?>

<?php if (!empty($arResult['ERROR'])): ?>
    <div class="notify profile-notify error">
        <img class="notify__icon" src="<?= SITE_TEMPLATE_PATH ?>/assets/images/icons/error.svg" alt="notify_error"
             width="24" height="24">
        <?= $arResult['ERROR'] ?>
    </div>
<?php else: ?>
    <div class="notify profile-notify success">
        <img class="notify__icon" src="<?= SITE_TEMPLATE_PATH ?>/assets/images/icons/success.svg" alt="notify_success"
             width="24" height="24">
        <?= Loc::getMessage('T_IMEDIA_CATALOG_PRODUCT_UNSUBSCRIBE_TPL_SUCCESS') ?>
    </div>
<?php endif ?>