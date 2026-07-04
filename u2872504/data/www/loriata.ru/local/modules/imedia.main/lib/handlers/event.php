<?php

namespace Imedia\Main\Handlers;

class Event
{
    public static function onBeforeAdd(string &$event, string &$lid = null, array &$arFields = [])
    {
        switch ($event) {
            case 'CATALOG_PRODUCT_SUBSCRIBE_NOTIFY':
            case 'CATALOG_PRODUCT_SUBSCRIBE_NOTIFY_REPEATED':
                $arFields['UNSUBSCRIBE_URL'] = str_replace('/personal/subscribe/', '/catalog/unsubscribe/', $arFields['UNSUBSCRIBE_URL']);
                break;

            default:
                break;
        }
    }
}
