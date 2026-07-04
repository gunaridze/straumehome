<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\PhoneNumber\Format;
use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Sale\Location\LocationTable;
use Imedia\Main\Helpers\Sale\Cart;
use Imedia\Main\Helpers\Sale\Order;
use Imedia\Main\Helpers\Iblock\Iblock as IblockHelper;
use Imedia\Main\Helpers\Catalog\Property;
use Imedia\Main\Helpers\Image\Resize;

$session = Application::getInstance()->getSession();
$lastOrderId = ($session->has('LAST_ORDER_ID')) ? (int) $session->get('LAST_ORDER_ID') : null;

if($lastOrderId && ($lastOrderId === (int) $arResult['ORDER']['ID'])){
    $session->remove('LAST_ORDER_ID');

    if($arResult['ORDER']['IS_ALLOW_PAY'] === 'Y'){
        foreach($arResult['PAYMENT'] as $arPayment){

            if(
                ($arPayment['PAID'] === 'Y')
                || ($arResult['PAY_SYSTEM_LIST'][$arPayment['PAY_SYSTEM_ID']]['IS_CASH'] === 'Y')
                || ($arResult['PAY_SYSTEM_LIST'][$arPayment['PAY_SYSTEM_ID']]['ACTION_FILE'] === 'cash')
            ){
                continue;
            }

            if($arResult['PAY_SYSTEM_LIST'][$arPayment['PAY_SYSTEM_ID']]['PAYMENT_URL']){
                LocalRedirect(
                    $arResult['PAY_SYSTEM_LIST'][$arPayment['PAY_SYSTEM_ID']]['PAYMENT_URL'],
                    true
                );
                die();
            }

        }
    }
}

global $APPLICATION;

$APPLICATION->SetTitle(Loc::getMessage('T_ORDER_CONFIRM_H1'));
$APPLICATION->SetPageProperty('title', Loc::getMessage('T_ORDER_CONFIRM_TITLE'));
$APPLICATION->AddChainItem(Loc::getMessage('T_ORDER_CONFIRM_CHAIN'));

$order = null;
if($arResult['ORDER']['ID'] > 0){
    $order = Order::load($arResult['ORDER']['ID']);
}

if($order && ($order->getId() > 0)){

    $APPLICATION->SetPageProperty('subtitle', Loc::getMessage('T_ORDER_CONFIRM_DESCRIPTION'));

    $itemsIds = [];
    foreach($order->getBasket() as $item){
        $itemsIds[] = $item->getProductId();
    }

    $arItems = [];
    if(!empty($itemsIds)){

        Loader::includeModule('iblock');

        $arProperties = [];
        $parentBridge = [];

        \CIBlockElement::GetPropertyValuesArray(
            $arProperties,
            IblockHelper::getId('OFFERS'),
            ['ID' => $itemsIds],
            ['CODE' => Property::getCode('CML2_LINK')],
            [
                'PROPERTY_FIELDS' => ['ID', 'VALUE'],
                'GET_RAW_DATA' => 'Y'
            ]
        );

        foreach($arProperties as $offerId => $arOfferProperties){

            $parentId = $arOfferProperties[Property::getCode('CML2_LINK')]['VALUE'];
            if(!$parentId){
                continue;
            }

            $parentBridge[$offerId] = $parentId;
            $itemsIds[] = $parentId;

        }

        $dimensions = [
            'width' => 100,
            'height' => 126
        ];

        $sizes = [
            'DEFAULT' => [$dimensions['width'], $dimensions['height'], BX_RESIZE_IMAGE_EXACT],
            'DEFAULT_2X' => [$dimensions['width'] * 2, $dimensions['height'] * 2, BX_RESIZE_IMAGE_EXACT]
        ];

        $arFilter = [
            '=IBLOCK_ID' => [
                IblockHelper::getId('CATALOG'),
                IblockHelper::getId('OFFERS')
            ],
            '=ID' => array_unique($itemsIds)
        ];

        $arSelect = ['ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'DETAIL_PAGE_URL'];

        $query = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while($row = $query->GetNext(true, false)){

            $picture = $row['PREVIEW_PICTURE'] ?: $row['DETAIL_PICTURE'];

            $row['PICTURE'] = ($picture) ? Resize::setSelfResizeArray(
                $picture,
                $sizes
            ) : null;

            $arItems[$row['ID']] = $row;

        }

    }
    ?>
    <div class="order-success-table">
        <div class="order-success-table__head order-details-table">
            <div class="order-details-table__item">
                <div class="order-details-table__item-title"><?=Loc::getMessage('T_ORDER_CONFIRM_ORDER_TITLE')?></div>
                <p><?=Loc::getMessage('T_ORDER_CONFIRM_ORDER_ACCOUNT_NUMBER', ['#VALUE#' => $order->getField('ACCOUNT_NUMBER')])?></p>
                <p><?=Loc::getMessage('T_ORDER_CONFIRM_ORDER_DATE', ['#VALUE#' => \FormatDate('d F Y', $order->getDateInsert())])?></p>
            </div>
            <div class="order-details-table__item">
                <div class="order-details-table__item-title"><?=Loc::getMessage('T_ORDER_CONFIRM_ORDER_CONTACTS')?></div>
                <?php
                $arName = [];
                $arNameProperties = ['LAST_NAME', 'NAME'];
                foreach($arNameProperties as $code){
                    $property = Order::getPropertyByCode($code, $order->getPropertyCollection());
                    if($property && $property->getValue()){
                        $arName[] = $property->getValue();
                    }
                }
                ?>
                <?php if(!empty($arName)):?>
                    <p><?=implode(' ', $arName)?></p>
                <?php endif?>
                <?php $propertyEmail = Order::getPropertyByCode('EMAIL', $order->getPropertyCollection())?>
                <?php if($propertyEmail && $propertyEmail->getValue()):?>
                    <p><?=$propertyEmail->getValue()?></p>
                <?php endif?>
                <?php $propertyPhone = Order::getPropertyByCode('PERSONAL_PHONE', $order->getPropertyCollection())?>
                <?php if($propertyPhone && $propertyPhone->getValue()):
                    $parsedPhone = Parser::getInstance()->parse($propertyPhone->getValue());
                    ?>
                    <p><?=$parsedPhone->format(Format::INTERNATIONAL)?></p>
                <?php endif?>
                <?php
                $arAddress = [];

                $propertyAddress = Order::getPropertyByCode('ADDRESS', $order->getPropertyCollection());
                if($propertyAddress && $propertyAddress->getValue()){

                    $arAddress[] = $propertyAddress->getValue();

                } else {

                    $propertyZip = Order::getPropertyByCode('ZIP', $order->getPropertyCollection());
                    if($propertyZip && $propertyZip->getValue()){
                        $arAddress[] = $propertyZip->getValue();
                    }

                    $propertyLocation = $order->getPropertyCollection()->getDeliveryLocation();
                    if($propertyLocation && $propertyLocation->getValue()){

                        $query = LocationTable::getList(
                            [
                                'filter' => [
                                    '=CODE' => $propertyLocation->getValue(),
                                    '=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                    '=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                    'PARENTS.TYPE.CODE' => [
                                        'REGION',
                                        'SUBREGION',
                                        'VILLAGE_COUNCIL',
                                        'CITY',
                                        'STREET'
                                    ]
                                ],
                                'select' => [
                                    'LOCATION_ID' => 'PARENTS.ID',
                                    'LOCATION_NAME' => 'PARENTS.NAME.NAME',
                                    'LOCATION_TYPE_CODE' => 'PARENTS.TYPE.CODE',
                                ],
                                'order' => [
                                    'PARENTS.DEPTH_LEVEL' => 'asc'
                                ]
                            ]
                        );
                        while($row = $query->fetch()){
                            $arAddress[] = $row['LOCATION_NAME'];
                        }

                    }

                    $propertyStreet = Order::getPropertyByCode('STREET', $order->getPropertyCollection());
                    if($propertyStreet && $propertyStreet->getValue()){
                        $arAddress[] = $propertyStreet->getValue();
                    }

                    $propertyBuilding = Order::getPropertyByCode('BUILDING', $order->getPropertyCollection());
                    if($propertyBuilding && $propertyBuilding->getValue()){
                        $arAddress[] = Loc::getMessage('T_ORDER_CONFIRM_BUILDING', ['#VALUE#' => $propertyBuilding->getValue()]);
                    }

                    $propertyApartment = Order::getPropertyByCode('APARTMENT', $order->getPropertyCollection());
                    if($propertyApartment && $propertyApartment->getValue()){
                        $arAddress[] = Loc::getMessage('T_ORDER_CONFIRM_APARTMENT', ['#VALUE#' => $propertyApartment->getValue()]);
                    }

                }

                ?>
                <?php if(!empty($arAddress)):?>
                    <p><?=implode(', ', $arAddress)?></p>
                <?php endif?>
            </div>
            <div class="order-details-table__item">
                <div class="order-details-table__item-title"><?=Loc::getMessage('T_ORDER_CONFIRM_ORDER_DELIVERY')?></div>
                <?php foreach($order->getShipmentCollection()->getNotSystemItems() as $shipment):?>
                    <p><?=$shipment->getDeliveryName() ?></p>
                <?php endforeach?>
            </div>
            <div class="order-details-table__item">
                <div class="order-details-table__item-title"><?=Loc::getMessage('T_ORDER_CONFIRM_ORDER_PAYMENT')?></div>
                <?php foreach($order->getPaymentCollection() as $payment):?>
                    <p><?=$payment->getPaymentSystemName()?></p>
                <?php endforeach?>
            </div>
            <div class="order-details-table__item">
                <div>
                    <div class="order-details-table__item-title">
                        <?=Loc::getMessage('T_ORDER_CONFIRM_ORDER_TOTAL')?>
                        <span><?=\CCurrencyLang::CurrencyFormat($order->getField('PRICE'), $order->getField('CURRENCY'))?></span>
                    </div>
                    <payment-status :initial-paid="<?=($order->isPaid()) ? 'true' : 'false'?>"></payment-status>
                </div>
            </div>
            <div class="order-details-table__item">
                <?php if($arResult['ORDER']['IS_ALLOW_PAY'] === 'Y'):?>

                    <?php foreach($arResult['PAYMENT'] as $arPayment):
                        if(
                            ($arPayment['PAID'] === 'Y')
                            || ($arResult['PAY_SYSTEM_LIST'][$arPayment['PAY_SYSTEM_ID']]['IS_CASH'] === 'Y')
                            || ($arResult['PAY_SYSTEM_LIST'][$arPayment['PAY_SYSTEM_ID']]['ACTION_FILE'] === 'cash')
                        ){
                            continue;
                        }
                        ?>
                        <?php if($arResult['PAY_SYSTEM_LIST'][$arPayment['PAY_SYSTEM_ID']]['PSA_NEW_WINDOW'] === 'Y'):?>
                            <a
                                    href="<?=htmlspecialcharsbx($arResult['PAY_SYSTEM_LIST'][$arPayment['PAY_SYSTEM_ID']]['PSA_ACTION_FILE'])?>"
                                    class="btn order-details-table__btn"
                                    title="<?=Loc::getMessage('T_ORDER_CONFIRM_PAY')?>"
                                    target="_blank"
                                    rel="nofollow"
                            ><?=Loc::getMessage('SPOD_ORDER_PAY')?></a>
                        <?php else: ?>
                            <?=$arResult['PAY_SYSTEM_LIST'][$arPayment['PAY_SYSTEM_ID']]['BUFFERED_OUTPUT']?>
                        <?php endif?>
                    <?php endforeach?>
                <?php endif?>
            </div>
        </div>
    </div>
    <div class="order-success-table__body">
        <div class="product-title order-success-table__title"><?=Loc::getMessage('T_ORDER_CONFIRM_ORDER_ITEMS')?></div>
        <div class="order-success-table__products">
            <?php foreach($order->getBasket() as $item):

                $arItem = $arItems[$item->getProductId()];

                if(
                    !$arItem['PICTURE']
                    && isset($parentBridge[$item->getProductId()])
                    && isset($arItems[$parentBridge[$item->getProductId()]])
                ){
                    $arItem['PICTURE'] = $arItems[$parentBridge[$item->getProductId()]]['PICTURE'];
                }

                ?>
                <div class="order-product">
                    <?php if($arItem['PICTURE']):?>
                        <a
                            href="<?=($arItem['DETAIL_PAGE_URL']) ?: 'javascript:;'?>"
                            class="order-product__img"
                            title="<?=$item->getField('NAME')?>"
                        >
                            <picture>
                                <img
                                    src="<?=$arItem['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT']?>"
                                    width="<?=$arItem['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH']?>"
                                    height="<?=$arItem['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']?>"
                                    srcset="<?=$arItem['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X']?> 2x"
                                    alt="<?=$item->getField('NAME')?>"
                                    loading="lazy"
                                >
                            </picture>
                        </a>
                    <?php endif?>
                    <a
                        href="<?=($arItem['DETAIL_PAGE_URL']) ?: 'javascript:;'?>"
                        class="product-title order-product__title"
                        title="<?=$item->getField('NAME')?>"
                    ><?=$item->getField('NAME')?></a>
                    <dl class="product-list order-product__list">
                        <?php
                        $offerProperties = Cart::getOfferProperties();
                        foreach($item->getPropertyCollection() as $property):
                            if(
                                !in_array($property->getField('CODE'), $offerProperties, true)
                                || !$property->getField('VALUE')
                            ){
                                continue;
                            }
                            ?>
                            <div>
                                <dt><?=$property->getField('NAME')?>:</dt>
                                <dd><?=$property->getField('VALUE')?></dd>
                            </div>
                        <?php endforeach?>
                        <div>
                            <dt><?=Loc::getMessage('T_ORDER_CONFIRM_ITEM_QUANTITY')?></dt>
                            <dd><?=$item->getQuantity()?></dd>
                        </div>
                    </dl>
                </div>
            <?php endforeach?>
        </div>
    </div>
    <?php

} else {
    ?>
    <div class="notify profile-notify error">
        <img
            class="notify__icon"
            src="<?=SITE_TEMPLATE_PATH?>/assets/images/icons/error.svg"
            alt="error"
            width="24"
            height="24"
        >
        <?=Loc::getMessage('T_ORDER_CONFIRM_ACCESS_DENIED')?>
    </div>
    <?php
}