<?php
namespace Sale\Handlers\Delivery;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Main\Error;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Shipment;
use Imedia\Main\Helpers\Sale\Store;

Loc::loadMessages(__FILE__);

/*
 * @package Bitrix\Sale\Delivery\Services
 */
class PickupHandler extends Base
{
    protected static $isCalculatePriceImmediately = true;
    protected static $whetherAdminExtraServicesShow = true;

    /**
     * @param array $initParams
     * @throws \Bitrix\Main\ArgumentTypeException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);

        //Default value
        if(!isset($this->config['MAIN']['PRICE']))
            $this->config['MAIN']['PRICE'] = 0;

        if(!isset($initParams['CURRENCY'])){
            $initParams['CURRENCY'] = 'RUB';
        }

        if(
            !isset($this->config['MAIN']['PERIOD']) 
            || !is_array($this->config['MAIN']['PERIOD'])
        ){
            $this->config['MAIN']['PERIOD'] = [];
            $this->config['MAIN']['PERIOD']['FROM'] = '0';
            $this->config['MAIN']['PERIOD']['TO'] = '0';
            $this->config['MAIN']['PERIOD']['TYPE'] = 'D';
        }
    }

    /**
     * @return string Class title.
     */
    public static function getClassTitle()
    {
        return Loc::getMessage('SALE_DLVR_HANDL_PICKUP_TITLE');
    }

    /**
     * @return string Class, service description.
     */
    public static function getClassDescription()
    {
        return Loc::getMessage('SALE_DLVR_HANDL_PICKUP_DESCRIPTION');
    }

    /**
     * @param \Bitrix\Sale\Shipment|null $shipment
     * @return CalculationResult
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function calculateConcrete(Shipment $shipment = null, $extraServices = [])
    {
        $result = new CalculationResult();

        try{

            if (!$shipment) {
                $result->addError(new Error(Loc::getMessage('SALE_DLVR_HANDL_PICKUP_ERROR_SHIPMENT')));
                return $result;
            }

            $order = $shipment->getCollection()->getOrder();

            $locationProperty = $order->getPropertyCollection()->getDeliveryLocation();
            if(!$locationProperty){
                $result->addError(new Error(Loc::getMessage('SALE_DLVR_HANDL_PICKUP_ERROR_LOCATION_PROPERTY')));
                return $result;
            }

            $locationCode = $locationProperty->getValue();
            if(!$locationCode){
                $result->addError(new Error(Loc::getMessage('SALE_DLVR_HANDL_PICKUP_ERROR_LOCATION_VALUE')));
                return $result;
            }

            $arParentLocation = LocationTable::getList(
                [
                    'select' => [
                        'LOCATION_CODE' => 'PARENTS.CODE'
                    ],
                    'filter' => [
                        '=CODE' => $locationCode,
                        '=PARENTS.TYPE.CODE' => 'CITY'
                    ],
                    'limit' => 1
                ]
            )->fetch();
            if($arParentLocation){
                $locationCode = $arParentLocation['LOCATION_CODE'];
            }

            $arStores = Store::getListFromDelivery(
                $this->getId(),
                $locationCode
            );

            if(empty($arStores)){
                $result->addError(new Error(Loc::getMessage('SALE_DLVR_HANDL_PICKUP_ERROR_NO_DELIVERY')));
                return $result;
            }

            $price = $this->config['MAIN']['PRICE'];

            if(Loader::includeModule('currency')){
                $rates = new \CCurrencyRates;
                $currency = $this->currency;
                $shipmentCurrency = $shipment->getCollection()->getOrder()->getCurrency();
                $price = $rates->convertCurrency($price,  $currency, $shipmentCurrency);
            }

            $result->setDeliveryPrice(
                roundEx(
                    $price,
                    SALE_VALUE_PRECISION
                )
            );

            $from = $this->config['MAIN']['PERIOD']['FROM'];
            $to = $this->config['MAIN']['PERIOD']['TO'];

            $result->setPeriodDescription($this->getPeriodText($from, $to));
            $result->setPeriodFrom($from);
            $result->setPeriodTo($to);
            $result->setPeriodType($this->config['MAIN']['PERIOD']['TYPE']);


        } catch (\Exception $e){
            $result->addError(new Error($e->getMessage()));
        }

        return $result;
    }

    /**
     * @return string Period text.
     */
    protected function getPeriodText($from, $to)
    {
        $result = '';

        $from = (int) $from;
        $to = (int) $to;

        if(
            ($from === $to)
            && ($from === 0)
        ){
            $result = Loc::getMessage('SALE_DLVR_HANDL_PICKUP_TODAY');
        } elseif (
            ($from === $to)
            && ($from === 1)
        ){
            $result = Loc::getMessage('SALE_DLVR_HANDL_PICKUP_TOMORROW');
        } else {

            if($from > 0){
                $result .= ' '.Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_FROM') . ' ' . $from;
            }

            if($to > 0){
                $result .= ' '.Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_TO') . ' ' . $to;
            }

            if($this->config['MAIN']['PERIOD']['TYPE'] === 'MIN'){
                $result .= ' '.Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_MIN').' ';
            } elseif($this->config['MAIN']['PERIOD']['TYPE'] === 'H'){
                $result .= ' '.Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_HOUR').' ';
            } elseif($this->config['MAIN']['PERIOD']['TYPE'] === 'M') {
                $result .= ' '.Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_MONTH').' ';
            } else {
                $result .= ' '.Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_DAY').' ';
            }

        }

        return $result;
    }

    public function isCompatible(\Bitrix\Sale\Shipment $shipment)
    {
        $calcResult = self::calculateConcrete($shipment);
        return $calcResult->isSuccess();
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getConfigStructure()
    {
        $currency = $this->currency;

        if(Loader::includeModule('currency')){
            $currencyList = CurrencyManager::getCurrencyList();
            if (isset($currencyList[$this->currency])){
                $currency = $currencyList[$this->currency];
            }
            unset($currencyList);
        }

        $result = [
            'MAIN' => [
                'TITLE' => Loc::getMessage('SALE_DLVR_HANDL_PICKUP_TAB_MAIN'),
                'DESCRIPTION' => Loc::getMessage('SALE_DLVR_HANDL_PICKUP_TAB_MAIN_DESCR'),
                'ITEMS' => [
                    'CURRENCY' => [
                        'TYPE' => 'DELIVERY_READ_ONLY',
                        'NAME' => Loc::getMessage('SALE_DLVR_HANDL_PICKUP_CURRENCY'),
                        'VALUE' => $this->currency,
                        'VALUE_VIEW' => $currency
                    ],
                    'PRICE' => [
                        'TYPE' => 'NUMBER',
                        'MIN' => 0,
                        'NAME' => Loc::getMessage('SALE_DLVR_HANDL_PICKUP_PRICE')
                    ],
                    'PERIOD' => [
                        'TYPE' => 'DELIVERY_PERIOD',
                        'NAME' => Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_DLV'),
                        'ITEMS' => [
                            'FROM' => [
                                'TYPE' => 'NUMBER',
                                'MIN' => 0,
                                'NAME' => ''
                            ],
                            'TO' => [
                                'TYPE' => 'NUMBER',
                                'MIN' => 0,
                                'NAME' => '&nbsp;-&nbsp;'
                            ],
                            'TYPE' => [
                                'TYPE' => 'ENUM',
                                'OPTIONS' => [
                                    'MIN' => Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_MIN'),
                                    'H' => Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_HOUR'),
                                    'D' => Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_DAY'),
                                    'M' => Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_MONTH')
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $result;
    }

    public static function getAdminFieldsList()
    {
        $result = parent::getAdminFieldsList();
        $result['STORES'] = true;
        return $result;
    }

    public function prepareFieldsForSaving(array $fields)
    {
        if(
            (
                !isset($fields['CODE'])
                || ((int) $fields['CODE'] < 0)
            )
            && isset($fields['ID'])
            && ((int) $fields['ID'] > 0)
        ){
            $fields['CODE'] = $fields['ID'];
        }

        return parent::prepareFieldsForSaving($fields);
    }

    public function isCalculatePriceImmediately()
    {
        return self::$isCalculatePriceImmediately;
    }

    public static function whetherAdminExtraServicesShow()
    {
        return self::$whetherAdminExtraServicesShow;
    }

    public static function isHandlerCompatible()
    {
        return true;
    }
}