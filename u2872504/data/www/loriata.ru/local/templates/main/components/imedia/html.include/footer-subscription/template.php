<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
?>
<div class="footer__subscribe">
    <div class="footer__col-title"><?=Loc::getMessage('T_FOOTER_SUBSCRIPTION_TITLE')?></div>
    <subscription-footer class="subscribe-form">
        <div class="form-row subscribe-form__wrap">
            <input
                class="form-row__input"
                type="email"
                name="email"
                placeholder="<?=Loc::getMessage('T_FOOTER_SUBSCRIPTION_FIELD_PLACEHOLDER_EMAIL')?>"
                required
            >
            <button
                class="form-row__btn"
                type="submit"
                aria-label="<?=Loc::getMessage('T_FOOTER_SUBSCRIPTION_SUBMIT')?>"
            ></button>
        </div>
        <label class="form-agree">
            <input class="check-box" type="checkbox" required>
            <span class="check-style"></span>
            <span class="check-text">
                <?=Loc::getMessage('T_FOOTER_SUBSCRIPTION_TERMS_OF_USE', ['#SITE_DIR#' => SITE_DIR])?>
            </span>
        </label>
        <template slot="terms">
            <span class="check-text">
                <?=Loc::getMessage('T_FOOTER_SUBSCRIPTION_TERMS_OF_USE', ['#SITE_DIR#' => SITE_DIR])?>
            </span>
        </template>
    </subscription-footer>
</div>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		var subscriptionForm = document.querySelector('.subscribe-form');

		subscriptionForm.addEventListener('submit', function () {
			var emailInput = document.querySelector('.subscribe-form input[type="email"]');
			var email = emailInput.value;

			if (email) { // Проверяем, не пуст ли email
				rrApi.setProfile({
					"email": email,
					"isAgreedToReceiveMarketingMail": true
				});

				rrApi.setEmail(email);
			}
		});
	});
</script>
<?php
$arMessages = [
    'T_FOOTER_SUBSCRIPTION_FIELD_PLACEHOLDER_EMAIL',
    'T_FOOTER_SUBSCRIPTION_SUBMIT'
];
$messages = [];
foreach($arMessages as $code){
    $messages[$code] = Loc::getMessage($code);
}
?>
<script>
    BX.message(<?=Json::encode($messages)?>)
</script>