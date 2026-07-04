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

use \Bitrix\Main\Localization\Loc;

$arPayments = [
    [
        'CODE' => 'mastercard',
        'STYLE' => 'width="33.4" height="27" style="width: 3.34rem; height: 2.7rem;"'
    ],
    [
        'CODE' => 'visa',
        'STYLE' => 'width="39.61" height="13.11" style="width: 3.961rem; height: 1.311rem;"'
    ],
    [
        'CODE' => 'mir',
        'STYLE' => 'width="52.04" height="15.43" style="width: 5.204rem; height: 1.543rem;"'
    ],
    [
        'CODE' => 'gpay',
        'STYLE' => 'width="38.84" height="18.51" style="width: 3.884rem; height: 1.851rem;"'
    ],
    [
        'CODE' => 'ipay',
        'STYLE' => 'width="40.39" height="16.2" style="width: 4.039rem; height: 1.62rem;"'
    ]
];
?>
<div class="footer__payment">
    <ul class="footer__payment-list">
        <?php foreach($arPayments as $arPayment):
            $imagePath = null;
            $defaultPath = SITE_TEMPLATE_PATH . '/assets/images/logo/payment/' . $arPayment['CODE'];
            foreach(['svg', 'png', 'jpg', 'jpeg'] as $ext){
                if(file_exists($_SERVER['DOCUMENT_ROOT'] . $defaultPath . '.' . $ext)){
                    $imagePath = $defaultPath . '.' . $ext;
                    break;
                }
            }

            if(!$imagePath){
                continue;
            }

            ?>
            <li class="footer__payment-item">
                <img
                    src="<?=$imagePath?>"
                    alt="<?=Loc::getMessage('T_FOOTER_PAYMENTS_' . strtoupper($arPayment['CODE']))?>"
                    loading="lazy"
                    <?=$arPayment['STYLE']?>
                >
            </li>
        <?php endforeach ?>
    </ul>
    <div class="footer__payment-descr"><?=Loc::getMessage('T_FOOTER_PAYMENTS_CERTIFICATE')?></div>
</div>