<?php

namespace Ipolh\SDEK;


use Ipolh\SDEK\Bitrix\Controller\Order;
use Ipolh\SDEK\Core\Entity\BasicResponse;
use Ipolh\SDEK\Core\Entity\Collection;

class StatusHandler extends abstractGeneral
{
    /**
     * Choose all orders with uid's and without sdek_id to fill it or cry loud
     */
    public static function getSendedOrdersState()
    {
        $obOrders = \sqlSdekOrders::select(array(),array('!SDEK_UID'=>false));
        $arOrders = array();
        while ($obOrder = $obOrders->getNext()){
            if(!$obOrder['SDEK_ID']){
                $acc = \sqlSdekLogs::getById($obOrder['ACCOUNT']);

                if($acc && $acc['ACTIVE'] === 'Y') {
                    $arOrders [$obOrder['ID']] = array(
                        'uid' => $obOrder['SDEK_UID'],
                        'acc' => $acc['ACCOUNT'],
                        'scr' => $acc['SECURE'],
                        'accID' => $obOrder['ACCOUNT'],
                        'oid' => $obOrder['ORDER_ID'],
                        'src' => $obOrder['SOURCE']
                    );
                }
            }
        }

        $arControllers = array();

        if(!empty($arOrders)){
            foreach ($arOrders as $dbId => $arOrder){
                if(!array_key_exists($arOrder['accID'],$arControllers)) {
                    $app = self::makeApplication($arOrder['acc'], $arOrder['scr']);
                    $arControllers[$arOrder['accID']] = new Order($app);
                }

                $result = self::_getSenderOrderState($arOrder,$arControllers[$arOrder['accID']]);
            }
        }
    }

    /**
     * @param $oId
     * @param string $mode
     * @return BasicResponse
     * checks either order with corr oId was accepted by sdek - or smth is wrong
     */
    public static function getSendedOrderStateByOid($oId, $mode='order')
    {
        $arOrder = ($mode==='order') ? \sqlSdekOrders::GetByOI($oId) : \sqlSdekOrders::GetBySI($oId);
        if($arOrder && $arOrder['SDEK_UID']){
            $result = self::getSendedOrderState($arOrder['SDEK_UID']);
        } else {
            $result = new BasicResponse();
            $errCol = new Collection('error');
            $errCol->add('No order with filled uid found with id'.$oId);
            $result->setSuccess(false)->setResponse(false)->setError($errCol);
        }

        return $result;
    }

    /**
     * Checks either order with uid was accepted by sdek - or something is wrong
     * @param $uid
     * @return BasicResponse
     */
    public static function getSendedOrderState($uid)
    {
        $obOrder = \sqlSdekOrders::GetByUId($uid);
        if($obOrder){
            $acc = \sqlSdekLogs::getById($obOrder['ACCOUNT']);

            $arOrder = array(
                'uid' => $obOrder['SDEK_UID'],
                'acc' => $acc['ACCOUNT'],
                'scr' => $acc['SECURE'],
                'accID' => $obOrder['ACCOUNT'],
                'oid' => $obOrder['ORDER_ID'],
                'src' => $obOrder['SOURCE']
            );

            $app = self::makeApplication($arOrder['acc'], $arOrder['scr']);
            $controller = new Order($app);

            $result = self::_getSenderOrderState($arOrder,$controller);
        } else {
            $result = new BasicResponse();
            $errCol = new Collection('error');
            $errCol->add('No order found with uid '.$uid);
            $result->setSuccess(false)->setResponse(false)->setError($errCol);
        }

        return $result;
    }

    /**
     * Checks state for chosen order, sets stuff for tracking number, adds in table
     * @param $arOrder - array of type uid,oid,src
     * @param Order $controller
     */
    protected static function _getSenderOrderState($arOrder, $controller)
    {
        /** @var BasicResponse $check */
        $check = $controller->checkOrderSendState($arOrder['uid']);

        $obRet = new BasicResponse();
        $obRet->setSuccess($check->isSuccess());

        if($check->isSuccess())
        {
            $sdekNumber = $check->getResponse()->getField('cdekNumber');
            if(
                $check->getResponse()->getField('state') === 'SUCCESSFUL' &&
                $sdekNumber
            ){
                \sqlSdekOrders::updateStatus(array(
                    "ORDER_ID" => $arOrder['oid'],
                    "SOURCE"   => $arOrder['src'],
                    "STATUS"   => "OK",
                    "SDEK_ID"  => $sdekNumber,
                    "MESSAGE"  => "",
                    "OK"       => true
                ));
                $obRet->setResponse($sdekNumber);

                $statusOption = !$arOrder['src'] ? 'statusOK' : 'stShipmentOK';
                $status = \Ipolh\SDEK\option::get($statusOption);
                if ($status) {
                    if (!$arOrder['src']) {
                        $order = \CSaleOrder::GetByID($arOrder['oid']);
                        if ($order['STATUS_ID'] != $status) {
                            \CSaleOrder::StatusOrder($arOrder['oid'], $status);
                        }
                    } else if (\sdekHelper::isConverted()) {
                        $shipment = \Bitrix\Sale\Shipment::getList(array('filter' => array('ID' => $arOrder['oid'])))->Fetch();
                        if ($shipment['STATUS_ID'] != $status) {
                            $order = \Bitrix\Sale\Order::load($shipment['ORDER_ID']);
                            $shipmentCollection = $order->getShipmentCollection();
                            $shipment = $shipmentCollection->getItemById($arOrder['oid']);
                            $shipment->setField('STATUS_ID', $status);
                            $order->save();
                        }
                    }
                }
                \sdekdriver::setOrderTrackingNumber($arOrder['oid'],(!$arOrder['src'])?'order':'shipment',$sdekNumber);
            } elseif($check->getResponse()->getField('state') === 'INVALID'){
                $obRet->setError($check->getError());
                $obRet->setResponse(false);

                $arErrors = array();
                if($check->getError()){
                    $check->getError()->reset();
                    while($obErr = $check->getError()->getNext()){
                        $arErrors [] = $obErr;
                    }
                }

                \sqlSdekOrders::updateStatus(array(
                    "ORDER_ID" => $arOrder['oid'],
                    "SOURCE"   => $arOrder['src'],
                    "STATUS"   => "ERROR",
                    "SDEK_ID"  => false,
                    "SDEK_UID" => '',
                    "MESSAGE"  => serialize(\sdekHelper::zaDEjsonit($arErrors)),
                    "OK"       => false
                ));
            }
        } else {
            $obRet->setError($check->getError());
        }

        return $obRet;
    }

    public static function checkCdekNumber(){
        $oId    = ($_REQUEST['mode'] === 'order') ? $_REQUEST['orderId'] : $_REQUEST['shipment'];
        $return = self::getSendedOrderStateByOid($oId,$_REQUEST['mode']);

        $obReturn = array('cdek_number'=>false,'error'=>false);
        if($return->isSuccess() && $return->getResponse()){
            $obReturn['cdek_number'] = $return->getResponse();
        } elseif($return->getError()){
            $arError = array();
            $return->getError()->reset();
            while ($obErr = $return->getError()->getNext()){
                $arError []= $obErr;
            }

            $obReturn['error'] = $arError;
        }

        echo json_encode(\sdekHelper::zaDEjsonit($obReturn));
    }

    /**
     * @return int
     * returns number of active orders - which we need to check via syncronization
     */
    public static function getNumberOfActiveOrders(){
        $orderStatusesUptime = (int)\Ipolh\SDEK\option::get('orderStatusesUptime');
        if ($orderStatusesUptime < 1)
            $orderStatusesUptime = 60;

        $dbOrders = \sqlSdekOrders::select(
            array('UPTIME', 'ASC'),
            array('OK' => true, 'STATUS' => array('OK', 'DELETE', 'STORE', 'TRANZT', 'CORIER', 'PVZ'), '>UPTIME' => strtotime('-'.$orderStatusesUptime.' days'))
        );

        return $dbOrders->SelectedRowsCount();
    }
}