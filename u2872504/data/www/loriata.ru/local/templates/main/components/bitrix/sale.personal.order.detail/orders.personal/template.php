<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
?>
<div class="profile-page__body">
    <?php if(!empty($arResult['ERRORS']['FATAL'])):?>
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
    <?else:?>
        <div class="order-details-table profile-page__order-table">
            <div class="order-details-table__item">
                <div class="order-details-table__item-title"><?=Loc::getMessage('SPOD_ORDER')?>:</div>
                <p><?=Loc::getMessage('SPOD_NUM_SIGN')?><?=$arResult['ACCOUNT_NUMBER']?></p>
                <p><?=Loc::getMessage('SPOD_FROM')?> <time datetime="<?=date('Y-m-d',$arResult['DATE_INSERT'])?>"><?=$arResult['DATE_INSERT_FORMATED']?></time></p>
            </div>
            <div class="order-details-table__item">
                <div class="order-details-table__item-title"><?=Loc::getMessage('SOPD_TPL_RECIPIENT_TITLE')?>:</div>
                <?php
                $arName = [];
                if($arResult['ORDER_PROPS']['NAME']['VALUE']){
                    $arName[] = $arResult['ORDER_PROPS']['NAME']['VALUE'];
                }
                if($arResult['ORDER_PROPS']['LAST_NAME']['VALUE']){
                    $arName[] = $arResult['ORDER_PROPS']['LAST_NAME']['VALUE'];
                }
                ?>
                <?php if(!empty($arName)):?>
                    <p><?=implode(' ', $arName)?></p>
                <?php endif?>
                <?php if($arResult['ORDER_PROPS']['EMAIL']['VALUE']):?>
                    <p><?=$arResult['ORDER_PROPS']['EMAIL']['VALUE']?></p>
                <?php endif?>
                <?php if($arResult['ORDER_PROPS']['PHONE']['VALUE']):?>
                    <p><?=$arResult['ORDER_PROPS']['PHONE']['VALUE']?></p>
                <?php endif?>
                <?php
                $arLocation = [];
                if($arResult['ORDER_PROPS']['ADDRESS']['VALUE']){
                    $arLocation[] = $arResult['ORDER_PROPS']['ADDRESS']['VALUE'];
                } else {

                    if($arResult['ORDER_PROPS']['ZIP']['VALUE']){
                        $arLocation[] = $arResult['ORDER_PROPS']['ZIP']['VALUE'];
                    }

                    if($arResult['ORDER_PROPS']['LOCATION']['VALUE']){
                        $arLocation[] = $arResult['ORDER_PROPS']['LOCATION']['VALUE'];
                    }

                    if($arResult['ORDER_PROPS']['STREET']['VALUE']){
                        $arLocation[] = $arResult['ORDER_PROPS']['STREET']['VALUE'];
                    }

                    if($arResult['ORDER_PROPS']['BUILDING']['VALUE']){
                        $arLocation[] = Loc::getMessage(
                            'T_ORDER_DETAIL_BUILDING',
                            ['#VALUE#' => $arResult['ORDER_PROPS']['BUILDING']['VALUE']]
                        );
                    }

                    if($arResult['ORDER_PROPS']['APARTMENT']['VALUE']){
                        $arLocation[] = Loc::getMessage(
                            'T_ORDER_DETAIL_APARTMENT',
                            ['#VALUE#' => $arResult['ORDER_PROPS']['APARTMENT']['VALUE']]
                        );
                    }

                }
                ?>
                <?php if(!empty($arLocation)):?>
                    <p><?=implode(', ', $arLocation)?></p>
                <?php endif?>
            </div>
            <?php if(!empty($arResult['SHIPMENT'])):?>
                <div class="order-details-table__item">
                    <div class="order-details-table__item-title"><?=Loc::getMessage('SOPD_TPL_DELIVERY_TITLE')?>:</div>
                    <?php foreach($arResult['SHIPMENT'] as $arShipment):?>
                        <p><?=$arShipment['DELIVERY']['NAME']?></p>
                    <?php endforeach?>
                </div>
            <?php endif?>
            <?php if(!empty($arResult['PAYMENT'])):?>
                <div class="order-details-table__item">
                    <div class="order-details-table__item-title"><?=Loc::getMessage('SOPD_TPL_PAID_SYSTEM_TITLE')?>:</div>
                    <?php foreach($arResult['PAYMENT'] as $arPayment):?>
                        <p><?=$arPayment['PAY_SYSTEM']['NAME']?></p>
                    <?php endforeach?>
                </div>
            <?php endif?>
            <div class="order-details-table__item">
                <div class="order-details-table__item-wrap">
                    <div class="order-details-table__item-title">
                        <?=Loc::getMessage('SPOD_SUMMARY')?>:
                        <span><?=$arResult['PRICE_FORMATED']?></span>
                    </div>
                    <payment-status :initial-paid="<?=($arResult['PAYED'] === 'Y') ? 'true' : 'false'?>"></payment-status>
                </div>
                <?php if($arResult['IS_ALLOW_PAY']):?>
                    <?php foreach($arResult['PAYMENT'] as $arPayment):
                        if(
                            ($arPayment['PAID'] === 'Y')
                            || ($arPayment['PAY_SYSTEM']['IS_CASH'] === 'Y')
                            || ($arPayment['PAY_SYSTEM']['ACTION_FILE'] === 'cash')
                        ){
                            continue;
                        }
                        ?>
                        <?php if($arPayment['PAY_SYSTEM']['PSA_NEW_WINDOW'] === 'Y'):?>
                            <a
                                href="<?=htmlspecialcharsbx($arPayment['PAY_SYSTEM']['PSA_ACTION_FILE'])?>"
                                class="btn order-details-table__btn"
                                title="<?=Loc::getMessage('SPOD_ORDER_PAY')?>"
                                target="_blank"
                                rel="nofollow"
                            ><?=Loc::getMessage('SPOD_ORDER_PAY')?></a>
                        <?php else: ?>
                            <?=$arPayment['BUFFERED_OUTPUT']?>
                        <?php endif?>
                    <?php endforeach?>
                <?php endif; ?>
            </div>
        </div>
        <?php if(!empty($arResult['BASKET'])):?>
        <div class="profile-page-goods">
            <div class="profile-page-goods__title product-title"><?=Loc::getMessage('SOPD_PRODUCTS_LIST_TITLE')?>:</div>
            <div class="profile-page-goods__list">
                <?php foreach ($arResult['BASKET'] as $arItem):?>
                    <div class="order-product">
                        <?php if($arItem['PICTURE']):?>
                            <a
                                href="<?=$arItem['DETAIL_PAGE_URL']?>"
                                class="order-product__img"
                                title="<?=$arItem['NAME']?>"
                            >
                                <picture>
                                    <img
                                        src="<?=$arItem['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']?>"
                                        alt="<?=$arItem['PICTURE']['ALT']?>"
                                        width="<?=$arItem['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH']?>"
                                        height="<?=$arItem['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']?>"
                                        srcset="<?=$arItem['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X']?> 2x"
                                        loading="lazy"
                                    >
                                </picture>
                            </a>
                        <?php endif;?>
                        <a
                            href="<?=$arItem['DETAIL_PAGE_URL']?>"
                            class="product-title order-product__title"
                            title="<?=$arItem['NAME']?>"
                        ><?=$arItem['NAME']?></a>
                        <dl class="product-list order-product__list">
                            <?php foreach ($arResult['PROPERTY_DESCRIPTION'][1] as $key => $prop):?>
                                <?php if($arItem[$key.'_VALUE']):?>
                                    <div>
                                        <dt><?=$prop['NAME']?>:</dt>
                                        <dd><?=$arItem[$key.'_VALUE']?></dd>
                                    </div>
                                <?php endif;?>
                            <?php endforeach;?>
                            <div>
                                <dt><?=Loc::getMessage('SPOD_QUANTITY')?>:</dt>
                                <dd><?=$arItem['QUANTITY']?></dd>
                            </div>
                        </dl>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
        <?php endif;?>
    <?php endif;?>
</div>