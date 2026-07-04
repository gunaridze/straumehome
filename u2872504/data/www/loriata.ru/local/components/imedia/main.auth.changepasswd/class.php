<?php

namespace Imedia\Component;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

\CBitrixComponent::includeComponentClass('bitrix:main.auth.changepasswd');

class MainAuthChangepasswd extends \MainChangePasswdComponent
{
    public function executeComponent($applyTemplate = true)
    {
        // init vars
        $request = Application::getInstance()->getContext()->getRequest();

        // tpl vars
        $this->arResult['SUCCESS'] = null;
        $this->arResult['FIELDS'] = $this->formFields;
        $this->arResult['LAST_LOGIN'] = $request->getCookie(
            'LOGIN'
        );
        $this->arResult['AUTH_AUTH_URL'] = $this->checkParam(
            'AUTH_AUTH_URL',
            ''
        );
        $this->arResult['AUTH_REGISTER_URL'] = $this->checkParam(
            'AUTH_REGISTER_URL',
            ''
        );
        if ($this->getOption('captcha_restoring_password', 'N') == 'Y'){
            $this->arResult['CAPTCHA_CODE'] = $this->getApplication()->CaptchaGetCode();
        } else {
            $this->arResult['CAPTCHA_CODE'] = '';
        }

        // processing
        if ($this->requestField('action')){
            $this->actionRequest();
        }

        $this->arResult['ERRORS'] = $this->getErrors();

        // replace last_login with request data
        $request = Application::getInstance()->getContext()->getRequest();
        if (
            !$request->isPost() &&
            $request->get('USER_LOGIN')
        ){
            $this->arResult['LAST_LOGIN'] = \CUtil::ConvertToLangCharset(
                $request->get('USER_LOGIN')
            );
        } else if ($request->getPost('USER_LOGIN')){
            $this->arResult['LAST_LOGIN'] = $request->getPost('USER_LOGIN');
        }

        // tpl vars
        $this->arResult['GROUP_POLICY'] = $this->getGroupPolicy();
        $this->arResult['SECURE_AUTH'] = $this->isSecureAuth();
        $this->requestForTpl();

        $this->IncludeComponentTemplate();
    }
}