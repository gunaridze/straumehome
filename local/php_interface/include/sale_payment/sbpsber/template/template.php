<?php
use Bitrix\Main\Localization\Loc;

/** @var array $params */

Loc::loadMessages(__FILE__);
?>
<payment-sbp-sber
    payment-id="<?=$params['PAYMENT_ID']?>"
    pay-system-id="<?=$params['PAY_SYSTEM_ID']?>"
    payment-url="<?=$params['PAYMENT_URL']?>"
></payment-sbp-sber>
