<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$data = [
    'NAME' => Loc::getMessage('SALE_HPS_SBP_SBER'),
    'SORT' => 500,
    'IS_AVAILABLE' => $isAvailable,
    'CODES' => [
        'CLIENT_ID' => [
            'NAME' => Loc::getMessage('SALE_HPS_SBP_SBER_CLIENT_ID'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBP_SBER_CLIENT_ID_DESC'),
            'SORT' => 100,
            'GROUP' => Loc::getMessage('SALE_HPS_SBP_SBER_CONNECT_SETTINGS')
        ],
        'CLIENT_SECRET' => [
            'NAME' => Loc::getMessage('SALE_HPS_SBP_SBER_CLIENT_SECRET'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBP_SBER_CLIENT_SECRET_DESC'),
            'SORT' => 200,
            'GROUP' => Loc::getMessage('SALE_HPS_SBP_SBER_CONNECT_SETTINGS')
        ],
        'CLIENT_CERT' => [
            'NAME' => Loc::getMessage('SALE_HPS_SBP_SBER_CLIENT_CERT'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBP_SBER_CLIENT_CERT_DESC'),
            'SORT' => 200,
            'GROUP' => Loc::getMessage('SALE_HPS_SBP_SBER_CONNECT_SETTINGS')
        ],
        'PRIVATE_KEY' => [
            'NAME' => Loc::getMessage('SALE_HPS_SBP_SBER_PRIVATE_KEY'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBP_SBER_PRIVATE_KEY_DESC'),
            'SORT' => 300,
            'GROUP' => Loc::getMessage('SALE_HPS_SBP_SBER_CONNECT_SETTINGS')
        ],
        'TERMINAL_ID' => [
            'NAME' => Loc::getMessage('SALE_HPS_SBP_SBER_TERMINAL_ID'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBP_SBER_TERMINAL_ID_DESC'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('SALE_HPS_SBP_SBER_CONNECT_SETTINGS')
        ],
        'PS_CHANGE_STATUS_PAY' => [
            'NAME' => Loc::getMessage('SALE_HPS_SBP_SBER_CHANGE_STATUS_PAY'),
            'SORT' => 1000,
            'GROUP' => 'GENERAL_SETTINGS',
            'INPUT' => [
                'TYPE' => 'Y/N',
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'INPUT',
                'PROVIDER_VALUE' => 'Y',
            ],
        ],
        'AUTO_CANCEL' => [
            'NAME' => Loc::getMessage('SALE_HPS_SBP_SBER_AUTO_CANCEL'),
            'SORT' => 1100,
            'GROUP' => 'GENERAL_SETTINGS'
        ]
    ]
];

