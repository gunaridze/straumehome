<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$data = [
    'NAME' => Loc::getMessage('SALE_HPS_PAYTURE'),
    'SORT' => 500,
    'IS_AVAILABLE' => $isAvailable,
    'CODES' => [
        'KEY' => [
            'NAME' => Loc::getMessage('SALE_HPS_PAYTURE_KEY'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_PAYTURE_KEY_DESC'),
            'SORT' => 100,
            'GROUP' => Loc::getMessage('SALE_HPS_PAYTURE_CONNECT_SETTINGS')
        ],
        'PASSWORD' => [
            'NAME' => Loc::getMessage('SALE_HPS_PAYTURE_PASSWORD'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_PAYTURE_PASSWORD_DESC'),
            'SORT' => 200,
            'GROUP' => Loc::getMessage('SALE_HPS_PAYTURE_CONNECT_SETTINGS')
        ],
        'IS_TEST' => [
            'NAME' => Loc::getMessage('SALE_HPS_PAYTURE_IS_TEST'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_PAYTURE_IS_TEST_DESC'),
            'SORT' => 300,
            'INPUT' => [
                'TYPE' => 'Y/N'
            ],
            'GROUP' => Loc::getMessage('SALE_HPS_PAYTURE_CONNECT_SETTINGS')
        ],
        'ENVIRONMENT_PROD' => [
            'NAME' => Loc::getMessage('SALE_HPS_PAYTURE_ENVIRONMENT_PROD'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_PAYTURE_ENVIRONMENT_PROD_DESC'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('SALE_HPS_PAYTURE_CONNECT_SETTINGS')
        ],
        'ENVIRONMENT_DEV' => [
            'NAME' => Loc::getMessage('SALE_HPS_PAYTURE_ENVIRONMENT_DEV'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_PAYTURE_ENVIRONMENT_DEV_DESC'),
            'SORT' => 500,
            'GROUP' => Loc::getMessage('SALE_HPS_PAYTURE_CONNECT_SETTINGS')
        ],
        'RETURN_URL' => [
            'NAME' => Loc::getMessage('SALE_HPS_PAYTURE_RETURN_URL'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_PAYTURE_RETURN_URL_DESC'),
            'SORT' => 600,
            'GROUP' => Loc::getMessage('SALE_HPS_PAYTURE_CONNECT_SETTINGS')
        ],
        'PS_CHANGE_STATUS_PAY' => [
            'NAME' => Loc::getMessage('SALE_HPS_PAYTURE_CHANGE_STATUS_PAY'),
            'SORT' => 1000,
            'GROUP' => 'GENERAL_SETTINGS',
            'INPUT' => [
                'TYPE' => 'Y/N',
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'INPUT',
                'PROVIDER_VALUE' => 'Y',
            ],
        ]
    ]
];