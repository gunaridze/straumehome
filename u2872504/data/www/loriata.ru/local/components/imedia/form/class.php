<?php
namespace Imedia\Component;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\ErrorCollection;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class Form extends \CBitrixComponent implements Controllerable, Errorable
{
    /** @var ErrorCollection */
    protected $errorCollection;

    public function configureActions()
    {
        return [
            'submit' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf()
                ]
            ]
        ];
    }

    public function submitAction()
    {
        try {

            $this->includeComponentLang('class.php');

            if (empty($this->arParams['FORM_CODE'])) {
                $result = new Result();
                $result->addError( new Error( Loc::getMessage('IMEDIA_FORM_FORM_CODE_EMPTY') ) );
                return AjaxJson::createError( $result->getErrorCollection() );
            }

            Loader::includeModule('form');

            $arForm = \CForm::GetBySID($this->arParams['FORM_CODE'])->Fetch();
            if(empty($arForm)){
                $result = new Result();
                $result->addError( new Error( Loc::getMessage('IMEDIA_FORM_FORM_NOT_FOUND') ) );
                return AjaxJson::createError( $result->getErrorCollection() );
            }

            $arFields = [];

            $query = \CFormField::GetList($arForm['ID'], 'N');
            while ($question = $query->fetch()) {
                $answer = \CFormAnswer::GetList($question['ID'])->fetch();

                switch ($answer['FIELD_TYPE']) {
                    case 'checkbox':
                    case 'multiselect':
                        $input = 'form_' . $answer['FIELD_TYPE'] . '_' . $question['SID'];
                        break;
                    default:
                        $input = 'form_' . $answer['FIELD_TYPE'] . '_' . $answer['ID'];
                        break;
                }

                $arFields[$input] = $_REQUEST[$question['SID']];
            }

            $arErrors = \CForm::Check($arForm['ID'], $arFields, false, 'Y', 'Y');
            if (!empty($arErrors)) {
                $result = new Result();
                $result->addError( new Error( implode(', ', $arErrors) ) );
                return AjaxJson::createError( $result->getErrorCollection() );
            }

            $resultId = \CFormResult::Add($arForm['ID'], $arFields);
            if (!$resultId) {
                global $strError;
                $result = new Result();
                $result->addError( new Error( Loc::getMessage('IMEDIA_FORM_RESULT_ADD_ERROR',['#ERROR#' => $strError]) ) );
                return AjaxJson::createError( $result->getErrorCollection() );
            }

            \CFormCRM::onResultAdded($arForm['ID'], $resultId);
            \CFormResult::SetEvent($resultId);
            \CFormResult::Mail($resultId);

            return [];

        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    protected function listKeysSignedParameters()
    {
        return [
            'CACHE_TYPE',
            'CACHE_TIME',
            'CACHE_GROUPS',
            'FORM_CODE'
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();
        if (empty($arParams['FORM_CODE'])){
            $this->errorCollection->setError(new Error(Loc::getMessage('IMEDIA_FORM_FORM_CODE_EMPTY')));
            return $arParams;
        }

        $this->arParams = $arParams;

        return $this->arParams;
    }

    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');
            $this->includeComponentTemplate();
        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }
    }

    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }
}