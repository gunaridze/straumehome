<?php
use Bitrix\Main\Localization\Loc;

/** @var array $params */

Loc::loadMessages(__FILE__);
?>
<a
    href="<?=$params['PAYMENT_URL']?>"
    class="btn order-details-table__btn"
    title="<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_PAYTURE_WIDGET_BUTTON')?>"
    rel="nofollow"
><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_PAYTURE_WIDGET_BUTTON')?></a>