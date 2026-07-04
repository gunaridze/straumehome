<?php

namespace Ipolh\SDEK\Bitrix\Controller;

use Ipolh\SDEK\Core\Entity\BasicEntity;
use Ipolh\SDEK\Core\Entity\BasicResponse;
use Ipolh\SDEK\Core\Entity\Collection;
use Ipolh\SDEK\Legacy\transitApplication;
use Ipolh\SDEK\SDEK\SdekApplication;

class Order extends abstractController
{

    protected $order;
    protected $result;

    /**
     * orderController constructor.
     * @param SdekApplication|transitApplication|null $application
     * @param \Ipolh\SDEK\Core\Order\Order $order
     */
    public function __construct($application,$order = false)
    {
        parent::__construct($application);

        if ($order) {
            $this->order = $order;
        }
    }

    /**
     * @return BasicResponse - uid - UDID, state - state of request (checkOrderSendState), cdekNumber - sdek_id
     */
    public function sendOrder()
    {
        $obReturn     = new BasicResponse();

        $resultOfMake = $this->application->orderMake($this->order,1,'4b1d17d262bdf16e36b9070934c74d47');
        $obResult = new BasicEntity();

        // preCheck
        $arErrors = array();
        $appErrors = $this->getApplicationErrors();
        if(!empty($appErrors)){
            $arErrors['sending'] = $appErrors;
        }

        $uid     = false;
        if($resultOfMake->isSuccess()){
            $obReturn->setSuccess(true);

            $response = $resultOfMake->getResponse();

            if($response->getEntity()){
                if($response->getEntity()->getUuid()){
                    $uid = $response->getEntity()->getUuid();
                }
            }

            if($response->getRequests()){
                $response->getRequests()->reset();
                while($obRequest = $response->getRequests()->getNext()){
                    if($obRequest->getState() == 'INVALID'){
                        $obRequest->getErrors()->reset();
                        while($error = $obRequest->getErrors()->getNext()){
                            $arErrors[$error->getCode()]= $error->getMessage();
                        }
                        $uid = false;
                    }
                }
            }

            $obResult->setField('uid',$uid);
            $obResult->setField('state',false);
            $obResult->setField('cdekNumber',false);

            if($uid){
                $invoiceCheck = $this->checkOrderSendState($uid);

                $errs = $invoiceCheck->getError();
                if(!empty($errs)){
                    $arErrors['checking'] = array();
                    $errs->reset();
                    while($obErr = $errs->getNext()){
                        $arErrors['checking'][]= $obErr;
                    }
                }

                if($invoiceCheck->isSuccess()){
                    $response = $invoiceCheck->getResponse();
                    if($response->getField('state')){
                        $obResult->setField('state',$response->getField('state'));
                        if($response->getField('state') === 'SUCCESSFUL'){
                            $obResult->setField('cdekNumber',$response->getField('cdekNumber'));
                        }
                    }
                }
            }
        } else {
            $obReturn->setSuccess(false);
        }

        // dealing errors
        if(!empty($arErrors)){
            $obError = new Collection('error');
            foreach ($arErrors as $code => $errs){
                switch($code){
                    case 'sending'  :
                        foreach ($errs as $errText){
                            $obError->add($errText.' ('.$code.')');
                        }
                    break;
                    case 'checking' :
                        foreach ($errs as $errText){
                            $obError->add($errText);
                        }
                    break;
                    default :
                        $obError->add($errs . ' (' . $code . ')');
                    break;
                }
            }
            $obReturn->setError($obError);
        }
        $obReturn->setResponse($obResult);

        return $obReturn;
    }

    /**
     * @param $uid
     * @return BasicResponse state - SUCCESSFUL|ACCEPTED|WAITING|INVALID, cdekNumber - sdek_id
     */
    public function checkOrderSendState($uid){
        $obReturn     = new BasicResponse();
        $obResponse   = $this->application->orderInfoByUuid($uid);

        $arErrors = array();
        $appErrors = $this->getApplicationErrors();
        if(!empty($appErrors)){
            $arErrors['checking'] = $appErrors;
        }
        $cNumber = false;
        $state   = false;

        if($obResponse->isSuccess()){
            $obReturn->setSuccess(true);

            $response = $obResponse->getResponse();

            if($response->getEntity()){
                if($response->getEntity()->getCdekNumber()){
                    $cNumber = $response->getEntity()->getCdekNumber();
                }
            }

            if($response->getRequests()){
                $response->getRequests()->reset();
                while($obRequest = $response->getRequests()->getNext()){
                    if($obRequest->getType() === 'CREATE') {
                        $state = $obRequest->getState();
                        if ($state == 'INVALID') {
                            $obRequest->getErrors()->reset();
                            while (
                            $error = $obRequest->getErrors()->getNext()) {
                                $arErrors[$error->getCode()] = $error->getMessage();
                            }
                            $cNumber = false;
                        } elseif ($state !== 'SUCCESSFUL') {
                            $cNumber = false;
                        }
                    }
                }
            }
        } else {
            $obReturn->setSuccess(false);
        }

        $obResult = new BasicEntity();
        $obResult->setField('cdekNumber',$cNumber);
        $obResult->setField('state',$state);
        $obReturn->setResponse($obResult);

        // dealing errors
        if(!empty($arErrors)){
            $obError = new Collection('error');
            foreach ($arErrors as $code => $errs){
                switch($code){
                    case 'checking' :
                        foreach ($errs as $errText){
                            $obError->add($errText.' ('.$code.')');
                        }
                    break;
                    default :
                        $obError->add($errs . ' (' . $code . ')');
                    break;
                }
            }
            $obReturn->setError($obError);
        }

        return $obReturn;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return \Ipolh\SDEK\Core\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Ipolh\SDEK\Core\Order\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return array
     * Returns array of application errors
     */
    protected function getApplicationErrors()
    {
        $arErrors = array();
        if($this->application) {
            $errors = $this->application->getErrorCollection();
            $errors->reset();
            while (
            $error = $errors->getNext()) {
                $arErrors [] = $error->getMessage();
            }
        }
        return $arErrors;
    }
}