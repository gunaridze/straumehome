<?php
namespace Imedia\Component;

use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Controller\PhoneAuth;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Sale;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Location\GeoIp;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PersonType;
use Bitrix\Sale\Result;
use Bitrix\Sale\Services\Company;
use Bitrix\Sale\Shipment;
use Bitrix\Main\UserTable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Catalog\PriceTable;
use Bitrix\Main\Application;
use Imedia\Main\Helpers\Location;
use Imedia\Main\Helpers\Catalog\Price as PriceHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */

Loc::loadMessages(__FILE__);

if (!Loader::includeModule("sale"))
{
	ShowError(Loc::getMessage("SOA_MODULE_NOT_INSTALL"));

	return;
}

\CBitrixComponent::includeComponentClass('bitrix:sale.order.ajax');

class SaleOrderAjax extends \SaleOrderAjax
{
    protected static ?array $userInfo = null;
    protected $calculateBasket;

    /**
     * Initializes order properties from request, user profile, default values.
     * Checks properties (if order saves) and sets to the order.
     * Execution of 'OnSaleComponentOrderProperties' event.
     *
     * @param Order $order
     * @param       $isPersonTypeChanged
     */
    protected function initProperties(Order $order, $isPersonTypeChanged)
    {
        $arResult =& $this->arResult;
        $orderProperties = $this->getPropertyValuesFromRequest();
        $orderProperties = $this->addLastLocationPropertyValues($orderProperties);

        $this->initUserProfiles($order, $isPersonTypeChanged);

        $firstLoad = $this->request->getRequestMethod() === 'GET';
        $justAuthorized = $this->request->get('do_authorize') === 'Y'
            || $this->request->get('do_register') === 'Y'
            || $this->request->get('SMS_CODE');

        $isProfileChanged = $this->arUserResult['PROFILE_CHANGE'] === 'Y';
        $haveProfileId = (int)$this->arUserResult['PROFILE_ID'] > 0;

        $shouldUseProfile = ($firstLoad || $justAuthorized || $isPersonTypeChanged || $isProfileChanged);
        $willUseProfile = $shouldUseProfile && $haveProfileId;

        $profileProperties = [];

        if ($haveProfileId)
        {
            $profileProperties = Sale\OrderUserProperties::getProfileValues((int)$this->arUserResult['PROFILE_ID']);
        }

        $ipAddress = '';

        if ($this->arParams['SPOT_LOCATION_BY_GEOIP'] === 'Y')
        {
            $ipAddress = \Bitrix\Main\Service\GeoIp\Manager::getRealIp();
        }

        foreach ($this->getFullPropertyList($order) as $property)
        {
            if ($property['USER_PROPS'] === 'Y')
            {
                if ($isProfileChanged && !$haveProfileId)
                {
                    $curVal = '';
                }
                elseif (
                    $willUseProfile
                    || (
                        !isset($orderProperties[$property['ID']])
                        && isset($profileProperties[$property['ID']])
                    )
                )
                {
                    $curVal = $profileProperties[$property['ID']];
                }
                elseif (isset($orderProperties[$property['ID']]))
                {
                    $curVal = $orderProperties[$property['ID']];
                }
                else
                {
                    $curVal = '';
                }
            }
            else
            {
                $curVal = isset($orderProperties[$property['ID']]) ? $orderProperties[$property['ID']] : '';
            }

            if ($arResult['HAVE_PREPAYMENT'] && !empty($arResult['PREPAY_ORDER_PROPS'][$property['CODE']]))
            {
                if ($property['TYPE'] === 'LOCATION')
                {
                    $cityName = ToUpper($arResult['PREPAY_ORDER_PROPS'][$property['CODE']]);
                    $arLocation = LocationTable::getList([
                        'select' => ['CODE'],
                        'filter' => ['NAME.NAME_UPPER' => $cityName],
                    ])
                        ->fetch()
                    ;

                    if (!empty($arLocation))
                    {
                        $curVal = $arLocation['CODE'];
                    }
                }
                else
                {
                    $curVal = $arResult['PREPAY_ORDER_PROPS'][$property['CODE']];
                }
            }

            if ($property['TYPE'] === 'LOCATION' && empty($curVal) && !empty($ipAddress))
            {
                $locCode = GeoIp::getLocationCode($ipAddress);

                if (!empty($locCode))
                {
                    $curVal = $locCode;
                }
            }
            elseif ($property['IS_ZIP'] === 'Y' && empty($curVal) && !empty($ipAddress))
            {
                $zip = GeoIp::getZipCode($ipAddress);

                if (!empty($zip))
                {
                    $curVal = $zip;
                }
            }
            elseif ($property['IS_PHONE'] === 'Y' && !empty($curVal))
            {
                $curVal = $this->getNormalizedPhone($curVal);
            }

            if (empty($curVal))
            {
                // getting default value for all properties except LOCATION
                // (LOCATION - just for first load or person type change or new profile)
                if ($property['TYPE'] !== 'LOCATION' || !$willUseProfile)
                {
                    global $USER;

                    if ($shouldUseProfile && $USER->IsAuthorized())
                    {
                        $curVal = $this->getValueFromCUser($property);
                    }

                    if (empty($curVal) && !empty($property['DEFAULT_VALUE']))
                    {
                        $curVal = $property['DEFAULT_VALUE'];
                    }
                }
            }

            if ($property['TYPE'] === 'LOCATION')
            {
                $selectedLocation = Location::getSelected();
                $curVal = $selectedLocation['CODE'];
            }

            $this->arUserResult['ORDER_PROP'][$property['ID']] = $curVal;
        }

        $this->checkZipProperty($order, $willUseProfile);
        $this->checkAltLocationProperty($order, $willUseProfile, $profileProperties);

        foreach (GetModuleEvents('sale', 'OnSaleComponentOrderProperties', true) as $arEvent)
        {
            ExecuteModuleEventEx($arEvent, [&$this->arUserResult, $this->request, &$this->arParams, &$this->arResult]);
        }

        $this->setOrderProperties($order);
    }

	/**
	 * Returns user property value from CUser
	 *
	 * @param    $property
	 * @return    string
	 */
	protected function getValueFromCUser($property)
	{
        $userInfo = static::getUserInfo();
		return (string) $userInfo[$property['CODE']];
	}

    protected function getUserInfo()
    {
        if(static::$userInfo === null){

            $userInfo = [];

            $userId = CurrentUser::get()->getId();

            if($userId > 0){

                $userInfo = UserTable::getList(
                    [
                        'select' => ['NAME', 'LAST_NAME', 'EMAIL', 'PERSONAL_PHONE'],
                        'filter' => ['=ID' => $userId],
                        'limit' => 1
                    ]
                )->fetch();

            }

            static::$userInfo = $userInfo;

        }

        return static::$userInfo;
    }

    /**
     * Set basket items data from order object to $this->arResult
     */
    protected function obtainBasket()
    {
        $arResult =& $this->arResult;

        $arResult["MAX_DIMENSIONS"] = $arResult["ITEMS_DIMENSIONS"] = [];
        $arResult["BASKET_ITEMS"] = [];

        $this->calculateBasket = $this->order->getBasket()->createClone();

        $discounts = $this->order->getDiscount();
        $showPrices = $discounts->getShowPrices();
        if (!empty($showPrices['BASKET']))
        {
            foreach ($showPrices['BASKET'] as $basketCode => $data)
            {
                $basketItem = $this->calculateBasket->getItemByBasketCode($basketCode);
                if ($basketItem instanceof Sale\BasketItemBase)
                {
                    $basketItem->setFieldNoDemand('BASE_PRICE', $data['SHOW_BASE_PRICE']);
                    $basketItem->setFieldNoDemand('PRICE', $data['SHOW_PRICE']);
                    $basketItem->setFieldNoDemand('DISCOUNT_PRICE', $data['SHOW_DISCOUNT']);
                }
            }
        }
        unset($showPrices);

        /** @var Sale\BasketItem $basketItem */
        foreach ($this->calculateBasket as $basketItem)
        {
            $arBasketItem = $basketItem->getFieldValues();
            if ($basketItem->getVatRate() > 0)
            {
                $arResult["bUsingVat"] = "Y";
                $arBasketItem["VAT_VALUE"] = $basketItem->getVat();
            }
            $arBasketItem["QUANTITY"] = $basketItem->getQuantity();
            $arBasketItem["PRICE_FORMATED"] = SaleFormatCurrency($basketItem->getPrice(), $this->order->getCurrency());
            $arBasketItem["WEIGHT_FORMATED"] = roundEx(doubleval($basketItem->getWeight() / $arResult["WEIGHT_KOEF"]), SALE_WEIGHT_PRECISION)." ".$arResult["WEIGHT_UNIT"];
            $arBasketItem["DISCOUNT_PRICE"] = $basketItem->getDiscountPrice();

            $arBasketItem["DISCOUNT_PRICE_PERCENT"] = 0;
            if ($arBasketItem['CUSTOM_PRICE'] != 'Y')
            {
                $arBasketItem['DISCOUNT_PRICE_PERCENT'] = Sale\Discount::calculateDiscountPercent(
                    $arBasketItem['BASE_PRICE'],
                    $arBasketItem['DISCOUNT_PRICE']
                );
                if ($arBasketItem['DISCOUNT_PRICE_PERCENT'] === null)
                    $arBasketItem['DISCOUNT_PRICE_PERCENT'] = 0;
            }
            $arBasketItem["DISCOUNT_PRICE_PERCENT_FORMATED"] = $arBasketItem['DISCOUNT_PRICE_PERCENT'].'%';

            $arBasketItem["BASE_PRICE_FORMATED"] = SaleFormatCurrency($basketItem->getBasePrice(), $this->order->getCurrency());

            $arDim = $basketItem->getField('DIMENSIONS');

            if (is_string($arDim))
            {
                $arDim = unserialize($basketItem->getField('DIMENSIONS'), ['allowed_classes' => false]);
            }

            if (is_array($arDim))
            {
                $arResult["MAX_DIMENSIONS"] = \CSaleDeliveryHelper::getMaxDimensions(
                    [
                        $arDim["WIDTH"],
                        $arDim["HEIGHT"],
                        $arDim["LENGTH"],
                    ],
                    $arResult["MAX_DIMENSIONS"]);

                $arResult["ITEMS_DIMENSIONS"][] = $arDim;
            }

            $arBasketItem["PROPS"] = [];
            /** @var Sale\BasketPropertiesCollection $propertyCollection */
            $propertyCollection = $basketItem->getPropertyCollection();
            $propList = $propertyCollection->getPropertyValues();
            foreach ($propList as $key => &$prop)
            {
                if ($prop['CODE'] == 'CATALOG.XML_ID' || $prop['CODE'] == 'PRODUCT.XML_ID' || $prop['CODE'] == 'SUM_OF_CHARGE')
                    continue;

                $prop = array_filter($prop, ["CSaleBasketHelper", "filterFields"]);
                $arBasketItem["PROPS"][] = $prop;
            }

            $this->arElementId[] = $arBasketItem["PRODUCT_ID"];
            $arBasketItem["SUM_NUM"] = $basketItem->getPrice() * $basketItem->getQuantity();
            $arBasketItem["SUM"] = SaleFormatCurrency(
                $arBasketItem["SUM_NUM"],
                $this->order->getCurrency()
            );

            $arBasketItem["SUM_BASE"] = $basketItem->getBasePrice() * $basketItem->getQuantity();
            $arBasketItem["SUM_BASE_FORMATED"] = SaleFormatCurrency(
                $arBasketItem["SUM_BASE"],
                $this->order->getCurrency()
            );

            $arBasketItem["SUM_DISCOUNT_DIFF"] = $arBasketItem["SUM_BASE"] - $arBasketItem["SUM_NUM"];
            $arBasketItem["SUM_DISCOUNT_DIFF_FORMATED"] = SaleFormatCurrency(
                $arBasketItem["SUM_DISCOUNT_DIFF"],
                $this->order->getCurrency()
            );

            $arResult["BASKET_ITEMS"][] = $arBasketItem;
        }
    }

    /**
     * Set order total prices data from order object to $this->arResult
     */
    protected function obtainTotal()
    {
        $arResult =& $this->arResult;

        $locationAltPropDisplayManual = $this->request->get('LOCATION_ALT_PROP_DISPLAY_MANUAL');
        if (!empty($locationAltPropDisplayManual) && is_array($locationAltPropDisplayManual))
        {
            foreach ($locationAltPropDisplayManual as $propId => $switch)
            {
                if (intval($propId))
                {
                    $arResult['LOCATION_ALT_PROP_DISPLAY_MANUAL'][intval($propId)] = !!$switch;
                }
            }
        }

        $basket = $this->calculateBasket;

        $arResult['BASKET_POSITIONS'] = $basket->count();

        $arResult['ORDER_PRICE'] = $basket->getPrice();
        $arResult['ORDER_PRICE_FORMATED'] = SaleFormatCurrency($arResult['ORDER_PRICE'], $this->order->getCurrency());

        $arResult['ORDER_WEIGHT'] = $basket->getWeight();
        $arResult['ORDER_WEIGHT_FORMATED'] = roundEx(floatval($arResult['ORDER_WEIGHT'] / $arResult['WEIGHT_KOEF']), SALE_WEIGHT_PRECISION).' '.$arResult['WEIGHT_UNIT'];

        //$arResult['PRICE_WITHOUT_DISCOUNT_VALUE'] = $basket->getBasePrice();
        //$arResult['PRICE_WITHOUT_DISCOUNT'] = SaleFormatCurrency($arResult['PRICE_WITHOUT_DISCOUNT_VALUE'], $this->order->getCurrency());

        //$arResult['BASKET_PRICE_DISCOUNT_DIFF_VALUE'] = $basket->getBasePrice() - $basket->getPrice();
        //$arResult['BASKET_PRICE_DISCOUNT_DIFF'] = SaleFormatCurrency($arResult['BASKET_PRICE_DISCOUNT_DIFF_VALUE'], $this->order->getCurrency());

        $arResult['DISCOUNT_PRICE'] = Sale\PriceMaths::roundPrecision(
            $this->order->getDiscountPrice() + ($arResult['PRICE_WITHOUT_DISCOUNT_VALUE'] - $arResult['ORDER_PRICE'])
        );
        $arResult['DISCOUNT_PRICE_FORMATED'] = SaleFormatCurrency($arResult['DISCOUNT_PRICE'], $this->order->getCurrency());

        $arResult['DELIVERY_PRICE'] = Sale\PriceMaths::roundPrecision($this->order->getDeliveryPrice());
        $arResult['DELIVERY_PRICE_FORMATED'] = SaleFormatCurrency($arResult['DELIVERY_PRICE'], $this->order->getCurrency());

        $arResult['ORDER_TOTAL_PRICE'] = Sale\PriceMaths::roundPrecision($this->order->getPrice());
        $arResult['ORDER_TOTAL_PRICE_FORMATED'] = SaleFormatCurrency($arResult['ORDER_TOTAL_PRICE'], $this->order->getCurrency());

        $arResult['DISCOUNT_LIST'] = [];

        $priceTypeBase = PriceHelper::getId(PriceHelper::GROUP_BASE);

        $productIdsForBasePrices = [];
        foreach($arResult['BASKET_ITEMS'] as $row){
            if((int) $row['PRICE_TYPE_ID'] !== $priceTypeBase){
                $productIdsForBasePrices[] = $row['PRODUCT_ID'];
            }
        }

        $arBasePrices = [];
        if(!empty($productIdsForBasePrices)){

            $query = PriceTable::getList(
                [
                    'select' => ['PRICE_SCALE', 'PRODUCT_ID'],
                    'filter' => [
                        '=PRODUCT_ID' => $productIdsForBasePrices,
                        '=CATALOG_GROUP_ID' => $priceTypeBase
                    ],
                    'limit' => count($productIdsForBasePrices)
                ]
            );
            while($row = $query->fetch()){
                $arBasePrices[ $row['PRODUCT_ID'] ] = round($row['PRICE_SCALE'], 2);
            }

        }

        $basketBasePrice = 0;
        foreach($arResult['BASKET_ITEMS'] as $row){

            $basePrice = $arBasePrices[ $row['PRODUCT_ID'] ] ?: $row['BASE_PRICE'];
            if($basePrice && ($basePrice > $row['BASE_PRICE'])){
                $row['BASE_PRICE'] = $basePrice;
            }

            $basketBasePrice += $row['BASE_PRICE'] * $row['QUANTITY'];

        }

        $arResult['PRICE_WITHOUT_DISCOUNT_VALUE'] = $basketBasePrice;
        $arResult['PRICE_WITHOUT_DISCOUNT'] = SaleFormatCurrency(
            $arResult['PRICE_WITHOUT_DISCOUNT_VALUE'],
            $this->order->getCurrency()
        );

        $discounts = $this->order->getDiscount();
        $applyResult = $discounts->getApplyResult();

        $arResult['DISCOUNT_LIST'] = $applyResult['DISCOUNT_LIST'] ?: [];
        $otherDiscountSum = 0;

        foreach($arResult['DISCOUNT_LIST'] as $key => $discount){

            $sum = 0;

            foreach($applyResult['ORDER'] as $applyDiscount){

                if(
                    (int) $applyDiscount['DISCOUNT_ID'] !== (int) $discount['ID']
                    || !is_array($applyDiscount['RESULT']['BASKET'])
                ){
                    continue;
                }

                foreach($applyDiscount['RESULT']['BASKET'] as $applyDiscountResult){

                    if(!is_array($applyDiscountResult['DESCR_DATA'])){
                        continue;
                    }                        

                    $basketItem = $this->calculateBasket->getItemByBasketCode($applyDiscountResult['BASKET_ID']);

                    if (!($basketItem instanceof Sale\BasketItemBase)){
                        continue;
                    }                        

                    foreach($applyDiscountResult['DESCR_DATA'] as $applyDiscountResultValue){

                        if($applyDiscountResultValue['RESULT_UNIT'] !== $this->order->getCurrency()){
                            continue;
                        }                            

                        $sum += (float) ($applyDiscountResultValue['RESULT_VALUE'] * $basketItem->getQuantity());

                    }

                }

            }

            $arResult['DISCOUNT_LIST'][$key]['SUM'] = [
                'RAW' => $sum,
                'FORMATTED' => SaleFormatCurrency($sum, $this->order->getCurrency())
            ];

            if($discount['USE_COUPONS'] === 'Y'){
                $otherDiscountSum += $sum;
            }

        }

        sort($arResult['DISCOUNT_LIST']);

        $arResult['BASKET_PRICE_DISCOUNT_DIFF_VALUE'] = Sale\PriceMaths::roundPrecision(
            $arResult['PRICE_WITHOUT_DISCOUNT_VALUE']
            - $otherDiscountSum
            - $arResult['ORDER_PRICE']
        );
        $arResult['BASKET_PRICE_DISCOUNT_DIFF'] = SaleFormatCurrency(
            $arResult['BASKET_PRICE_DISCOUNT_DIFF_VALUE'],
            $this->order->getCurrency()
        );
    }

    /**
     * Calculates all available deliveries for order object.
     * Uses cloned order not to harm real order.
     * Execution of 'OnSaleComponentOrderDeliveriesCalculated' event
     *
     * @param Order $order
     * @throws Main\NotSupportedException
     */
    protected function calculateDeliveries(Order $order)
    {
        $this->arResult['DELIVERY'] = [];

        if (!empty($this->arDeliveryServiceAll))
        {
            /** @var Order $orderClone */
            $orderClone = null;
            $anotherDeliveryCalculated = false;
            /** @var Shipment $shipment */
            $shipment = $this->getCurrentShipment($order);

            foreach ($this->arDeliveryServiceAll as $deliveryId => $deliveryObj)
            {
                $calcResult = false;
                $calcOrder = false;
                $arDelivery = [];

                if ((int)$shipment->getDeliveryId() === $deliveryId)
                {
                    $arDelivery['CHECKED'] = 'Y';
                    $mustBeCalculated = true;
                    $calcResult = $deliveryObj->calculate($shipment);
                    $calcOrder = $order;
                }
                else
                {
                    $mustBeCalculated = $this->arParams['DELIVERY_NO_AJAX'] === 'Y'
                        || ($this->arParams['DELIVERY_NO_AJAX'] === 'H' && $deliveryObj->isCalculatePriceImmediately());

                    if ($mustBeCalculated)
                    {
                        $anotherDeliveryCalculated = true;

                        if (empty($orderClone))
                        {
                            $orderClone = $this->getOrderClone($order);
                        }

                        $orderClone->isStartField();

                        $clonedShipment = $this->getCurrentShipment($orderClone);
                        $clonedShipment->setField('DELIVERY_ID', $deliveryId);

                        $calculationResult = $orderClone->getShipmentCollection()->calculateDelivery();
                        if ($calculationResult->isSuccess())
                        {
                            $calcDeliveries = $calculationResult->get('CALCULATED_DELIVERIES');
                            $calcResult = reset($calcDeliveries);
                        }
                        else
                        {
                            $calcResult = new Delivery\CalculationResult();
                            $calcResult->addErrors($calculationResult->getErrors());
                        }

                        $orderClone->doFinalAction(true);

                        $calcOrder = $orderClone;
                    }
                }

                if ($mustBeCalculated)
                {
                    if ($calcResult->isSuccess())
                    {
                        $arDelivery['PRICE'] = Sale\PriceMaths::roundPrecision($calcResult->getPrice());
                        $arDelivery['PRICE_FORMATED'] = SaleFormatCurrency($arDelivery['PRICE'], $calcOrder->getCurrency());

                        $currentCalcDeliveryPrice = Sale\PriceMaths::roundPrecision($calcOrder->getDeliveryPrice());
                        if ($currentCalcDeliveryPrice >= 0 && $arDelivery['PRICE'] != $currentCalcDeliveryPrice)
                        {
                            $arDelivery['DELIVERY_DISCOUNT_PRICE'] = $currentCalcDeliveryPrice;
                            $arDelivery['DELIVERY_DISCOUNT_PRICE_FORMATED'] = SaleFormatCurrency($arDelivery['DELIVERY_DISCOUNT_PRICE'], $calcOrder->getCurrency());
                        }

                        if ($calcResult->getPeriodDescription() <> '')
                        {
                            $dateTo = new \Bitrix\Main\Type\DateTime();
                            $dateTo->add($calcResult->getPeriodTo() . ' day');

                            $arDelivery['PERIOD_TEXT'] = $calcResult->getPeriodDescription();
                            $arDelivery['PERIOD'] = [
                                'FROM' => [
                                    'RAW' => (int) $calcResult->getPeriodFrom()
                                ],
                                'TO' => [
                                    'RAW' => (int) $calcResult->getPeriodTo(),
                                    'DATE' => mb_strtolower(\FormatDate('d F', $dateTo)),
                                    'DATE_FULL' => mb_strtolower(\FormatDate('d F, l', $dateTo))
                                ]
                            ];
                        }
                    }
                    else
                    {
                        if (count($calcResult->getErrorMessages()) > 0)
                        {
                            foreach ($calcResult->getErrorMessages() as $message)
                            {
                                $arDelivery['CALCULATE_ERRORS'] .= $message.'<br>';
                            }
                        }
                        else
                        {
                            $arDelivery['CALCULATE_ERRORS'] = Loc::getMessage('SOA_DELIVERY_CALCULATE_ERROR');
                        }
                    }

                    $arDelivery['CALCULATE_DESCRIPTION'] = $calcResult->getDescription();
                }

                $this->arResult['DELIVERY'][$deliveryId] = $arDelivery;
            }

            // for discounts: last delivery calculation need to be on real order with selected delivery
            if ($anotherDeliveryCalculated)
            {
                $order->doFinalAction(true);
            }
        }

        $eventParameters = [
            $order, &$this->arUserResult, $this->request,
            &$this->arParams, &$this->arResult, &$this->arDeliveryServiceAll, &$this->arPaySystemServiceAll,
        ];
        foreach (GetModuleEvents('sale', 'OnSaleComponentOrderDeliveriesCalculated', true) as $arEvent)
        {
            ExecuteModuleEventEx($arEvent, $eventParameters);
        }
    }

    /**
     * Set delivery data from shipment object and delivery services object to $this->arResult
     * Execution of 'OnSaleComponentOrderOneStepDelivery' event
     *
     * @throws Main\NotSupportedException
     */
    protected function obtainDelivery()
    {
        $arResult =& $this->arResult;

        $arStoreId = [];
        /** @var Shipment $shipment */
        $shipment = $this->getCurrentShipment($this->order);

        if (!empty($this->arDeliveryServiceAll))
        {
            $deliveryIds = [];
            foreach ($this->arDeliveryServiceAll as $deliveryObj){
                $deliveryIds[] = $deliveryObj->getId();
            }

            $arDeliveryData = [];
            $query = Delivery\Services\Table::getList(
                [
                    'select' => ['ID', 'XML_ID'],
                    'filter' => [
                        '=ID' => $deliveryIds
                    ]
                ]
            );
            while($row = $query->fetch()){
                $arDeliveryData[$row['ID']] = $row;
            }

            foreach ($this->arDeliveryServiceAll as $deliveryObj)
            {
                $arDelivery =& $this->arResult["DELIVERY"][$deliveryObj->getId()];

                $arDelivery['ID'] = $deliveryObj->getId();
                $arDelivery['XML_ID'] = $arDeliveryData[$deliveryObj->getId()]['XML_ID'];
                $arDelivery['NAME'] = $deliveryObj->isProfile() ? $deliveryObj->getNameWithParent() : $deliveryObj->getName();
                $arDelivery['OWN_NAME'] = $deliveryObj->getName();
                $arDelivery['DESCRIPTION'] = $this->sanitize($deliveryObj->getDescription());
                $arDelivery['FIELD_NAME'] = 'DELIVERY_ID';
                $arDelivery["CURRENCY"] = $this->order->getCurrency();
                $arDelivery['SORT'] = $deliveryObj->getSort();
                $arDelivery['EXTRA_SERVICES'] = $deliveryObj->getExtraServices()->getItems();
                $arDelivery['STORE'] = Delivery\ExtraServices\Manager::getStoresList($deliveryObj->getId());

                /*if (intval($deliveryObj->getLogotip()) > 0)
                    $arDelivery["LOGOTIP"] = CFile::GetFileArray($deliveryObj->getLogotip());

                if (!empty($arDelivery['STORE']) && is_array($arDelivery['STORE']))
                {
                    foreach ($arDelivery['STORE'] as $val)
                        $arStoreId[$val] = $val;
                }*/

                $buyerStore = $this->request->get('BUYER_STORE');
                if (!empty($buyerStore) && !empty($arDelivery['STORE']) && is_array($arDelivery['STORE']) && in_array($buyerStore, $arDelivery['STORE']))
                {
                    $this->arUserResult['DELIVERY_STORE'] = $arDelivery["ID"];
                }
            }
        }

        //$arResult["BUYER_STORE"] = $shipment->getStoreId();

        /*$arStore = [];
        $dbList = CCatalogStore::GetList(
            ["SORT" => "DESC", "ID" => "DESC"],
            ["ACTIVE" => "Y", "ID" => $arStoreId, "ISSUING_CENTER" => "Y", "+SITE_ID" => $this->getSiteId()],
            false,
            false,
            ["ID", "TITLE", "ADDRESS", "DESCRIPTION", "IMAGE_ID", "PHONE", "SCHEDULE", "GPS_N", "GPS_S", "ISSUING_CENTER", "SITE_ID"]
        );
        while ($arStoreTmp = $dbList->Fetch())
        {
            if ($arStoreTmp["IMAGE_ID"] > 0)
                $arStoreTmp["IMAGE_ID"] = CFile::GetFileArray($arStoreTmp["IMAGE_ID"]);
            else
                $arStoreTmp["IMAGE_ID"] = null;

            $arStore[$arStoreTmp["ID"]] = $arStoreTmp;
        }

        $arResult["STORE_LIST"] = $arStore;*/

        $arResult["DELIVERY_EXTRA"] = [];
        $deliveryExtra = $this->request->get('DELIVERY_EXTRA');
        if (is_array($deliveryExtra) && !empty($deliveryExtra[$this->arUserResult["DELIVERY_ID"]]))
            $arResult["DELIVERY_EXTRA"] = $deliveryExtra[$this->arUserResult["DELIVERY_ID"]];

        $this->executeEvent('OnSaleComponentOrderOneStepDelivery', $this->order);
    }

    private function sanitize(string $html): string
    {
        static $sanitizer = null;

        if ($sanitizer === null)
        {
            $sanitizer = new \CBXSanitizer;
            $sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
        }

        return $sanitizer->sanitizeHtml($html);
    }

    protected function saveOrder($saveToSession = false)
    {
        parent::saveOrder($saveToSession);

        if (empty($this->arResult["ERROR"])){

            $session = Application::getInstance()->getSession();
            $session->set('LAST_ORDER_ID', $this->arResult['ORDER_ID']);

        }
    }
}