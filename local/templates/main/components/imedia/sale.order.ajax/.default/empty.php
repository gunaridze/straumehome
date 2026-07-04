<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
?>
<div class="empty-page catalog__empty">
    <img
        class="empty-page__img"
        src="<?=SITE_TEMPLATE_PATH?>/assets/images/icons/empty-catalog.svg"
        alt="empty"
        width="100"
        height="100"
    >
    <div class="empty-page__text"><?=Loc::getMessage('T_ORDER_BASKET_EMPTY')?></div>
</div>