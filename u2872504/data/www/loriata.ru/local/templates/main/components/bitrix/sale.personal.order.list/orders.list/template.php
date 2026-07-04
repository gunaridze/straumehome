<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest();
$finished = $request['filter_history'];

Loc::loadMessages(__FILE__);

?>
<?php if (!empty($arResult['ERRORS']['FATAL'])): ?>
    <div class="profile-page__notifies">
        <?php foreach ($arResult['ERRORS']['FATAL'] as $error):?>
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
        <?php endforeach?>
    </div>
<?php else: ?>
    <div class="tabs profile-page__tabs">
        <a href="<?= $arResult['CURRENT_PAGE'] ?>"
           class="tab profile-page__tab  <?= $finished ? '' : 'tab--active' ?>">
            <?= Loc::getMessage('SPOL_TPL_CURRENT_ORDERS') ?>
        </a>
        <a href="?filter_history=Y" class="tab profile-page__tab  <?= $finished ? 'tab--active' : '' ?>">
            <?= Loc::getMessage('SPOL_TPL_HISTORY_ORDERS') ?>
        </a>
    </div>
    <?php if (!empty($arResult['ERRORS']['NONFATAL'])): ?>
        <div class="profile-page__notifies">
            <?php foreach ($arResult['ERRORS']['NONFATAL'] as $error):?>
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
            <?php endforeach?>
        </div>
    <?php endif ?>
    <?php
    if (empty($arResult['ORDERS'])) {
        if ($_REQUEST["filter_history"] == 'Y') {
            if ($_REQUEST["show_canceled"] == 'Y') {
                ?>
                <div><?= Loc::getMessage('SPOL_TPL_EMPTY_CANCELED_ORDER') ?></div>
                <?php
            } else {
                ?>
                <div><?= Loc::getMessage('SPOL_TPL_EMPTY_HISTORY_ORDER_LIST') ?></div>
                <?php
            }
        } else {
            ?>
            <div><?= Loc::getMessage('SPOL_TPL_EMPTY_ORDER_LIST') ?></div>
            <?php
        }
    }
    ?>
    <?php if (!empty($arResult['ORDERS'])): ?>
        <div class="tabs-content tabs-content--active">
            <div class="profile-page__orders order-table">
                <?php
                foreach ($arResult['ORDERS'] as $arOrder):
                    /*$dateDelivery = $arResult['DELIVERY_PROPS'][$arOrder['ORDER']['ID']]['DELIVERY_DATE']['VALUE'];
                    if ($dateDelivery) {
                        $objDate = new \Bitrix\Main\Type\DateTime($dateDelivery, 'd.m.Y H:i:s');
                    }*/
                    ?>
                    <div class="order-item order-table__item">
                        <header class="order-item__header">
                            <div class="order-item__header-wrap">
                                <div class="order-item__title"><?= Loc::getMessage('SPOL_TPL_ORDER_FROM') ?>
                                    <time datetime="<?= date(
                                        'Y-m-d',
                                        $arOrder['ORDER']['DATE_INSERT']
                                    ) ?>"><?= $arOrder['ORDER']['DATE_INSERT_FORMATED'] ?></time>
                                    , №<?= $arOrder['ORDER']['ACCOUNT_NUMBER'] ?></div>
                                <div class="order-item__price"><?= $arOrder['ORDER']['FORMATED_PRICE'] ?></div>
                            </div>
                            <div class="order-item__header-buttons">
                                <span class="btn order-item__status-label"><?= $arResult['INFO']['STATUS'][$arOrder['ORDER']['STATUS_ID']]['NAME'] ?></span>
                                <?php if($arResult['ORDER_PROPERTIES'][$arOrder['ORDER']['ID']]['TRACKING_LINK']):?>
                                    <a
                                            class="order-item__follow-link"
                                            href="<?=$arResult['ORDER_PROPERTIES'][$arOrder['ORDER']['ID']]['TRACKING_LINK']?>"
                                            target="_blank"
                                            rel="nofollow"
                                            title="<?= Loc::getMessage('SPOL_TPL_ORDER_TRACK') ?>"
                                    >
                                        <span><?= Loc::getMessage('SPOL_TPL_ORDER_TRACK') ?></span>
                                        <svg viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3 10.295L6.79 6.5L7.20508 6L6.79008 5.5L3 1.705L3.705 1L8.705 6L3.705 11L3 10.295Z" fill="#101112"/>
                                        </svg>
                                    </a>
                                <?php endif?>
                            </div>
                        </header>
                        <div class="order-item__body">
                            <div class="order-item__content">
                                <?php if($arResult['ORDER_PROPERTIES'][$arOrder['ORDER']['ID']]['DELIVERY_DATE']):
                                    $objDate = new \Bitrix\Main\Type\DateTime($arResult['ORDER_PROPERTIES'][$arOrder['ORDER']['ID']]['DELIVERY_DATE']);
                                    ?>
                                    <div class="order-item__delivery-date">
                                        <?= Loc::getMessage('SPOL_TPL_DELIVERY_DATE') ?>:
                                        <time datetime="datetime=<?= $objDate->format('Y-m-d') ?>">
                                            <?= $objDate->format('d.m.Y') ?>
                                        </time>
                                    </div>
                                <?php endif?>
                                <?php foreach($arOrder['SHIPMENT'] as $arShipment):?>
                                    <div class="order-item__delivery-place">
                                        <?= Loc::getMessage('SPOL_TPL_DELIVERY') ?>:
                                        <span><?= $arShipment['DELIVERY_NAME'] ?></span>
                                    </div>
                                <?php endforeach?>
                                <a
                                        href="<?= $arOrder['ORDER']['URL_TO_DETAIL'] ?>"
                                        class="order-item__order-details"
                                        title="<?= Loc::getMessage('SPOL_TPL_MORE_ON_ORDER') ?>"
                                >
                                    <?= Loc::getMessage('SPOL_TPL_MORE_ON_ORDER') ?>
                                </a>
                            </div>
                            <div class="order-item__images">
                                <?php foreach ($arOrder['BASKET_ITEMS'] as $arItem): ?>
                                    <?php if ($arResult['PICTURES'][$arItem['PRODUCT_ID']]['RESIZE'][0]['SIZES']['DEFAULT']): ?>
                                        <picture class="order-item__img">
                                            <img
                                                src="<?= $arResult['PICTURES'][$arItem['PRODUCT_ID']]['RESIZE'][0]['SIZES']['DEFAULT'] ?>"
                                                alt="<?= $arResult['PICTURES'][$arItem['PRODUCT_ID']]['ALT'] ?>"
                                                width="<?= $arResult['PICTURES'][$arItem['PRODUCT_ID']]['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'] ?>"
                                                height="<?= $arResult['PICTURES'][$arItem['PRODUCT_ID']]['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT'] ?>"
                                                srcset="<?= $arResult['PICTURES'][$arItem['PRODUCT_ID']]['RESIZE'][0]['SIZES']['DEFAULT_2X'] ?> 2x"
                                                loading="lazy"
                                            >
                                        </picture>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <nav class="pagination" aria-label="<?= Loc::getMessage('SPOL_TPL_PAGINATION') ?>">
                <?= $arResult['NAV_STRING'] ?>
            </nav>
        </div>
    <?php endif; ?>
<?php endif;