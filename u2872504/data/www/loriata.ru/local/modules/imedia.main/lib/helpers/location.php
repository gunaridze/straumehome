<?php
namespace Imedia\Main\Helpers;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Service\GeoIp\Manager;
use Bitrix\Main\Web\Cookie;
use Bitrix\Sale\Location\GeoIp;
use Bitrix\Sale\Location\LocationTable;

class Location
{
    protected const SESSION_CODE = 'IR_LOCATION';
    protected const COOKIE_CODE = 'IR_LOCATION';
    protected const CACHE_TTL = 864000;
    protected const MODULE_ID = 'imedia.main';
    protected const OPTION_DEFAULT = 'default_location';
    public const PARAM_CODE = 'location';

    protected static ?array $selected = null;

    public static function getSelected(): array
    {
        if(gettype(static::$selected) === 'NULL'){

            $session = Application::getInstance()->getSession();
            if(
                $session->has(static::SESSION_CODE)
                && is_array($selected = $session->get(static::SESSION_CODE))
            ){
                static::$selected = $selected;
                return static::$selected;
            }

            $request = Context::getCurrent()->getRequest();
            $cookieLocationCode = $request->getCookie(static::COOKIE_CODE);
            if($cookieLocationCode){
                $arLocation = static::getLocation($cookieLocationCode);
                if(!empty($arLocation)){
                    static::$selected = $arLocation;
                    static::saveInSession();
                    return static::$selected;
                }
            }

            /*Loader::includeModule('sale');
            $ip = Manager::getRealIp();
            $locationId = GeoIp::getLocationId($ip);
            if($locationId > 0){
                $location = LocationTable::getById($locationId)->fetch();
                $arLocation = static::getLocation($location['CODE']);
                if(!empty($arLocation)){
                    static::$selected = $arLocation;
                    static::saveInSession();
                    static::saveInCookie();
                    return static::$selected;
                }
            }*/

            $defaultLocationCode = (string) Option::get(static::MODULE_ID, static::OPTION_DEFAULT);
            $arLocation = static::getLocation($defaultLocationCode);

            static::$selected = $arLocation;
            static::saveInSession();
            static::saveInCookie();
        }

        return (array) static::$selected;
    }

    protected static function saveInSession(): void
    {
        $session = Application::getInstance()->getSession();
        $session->set(static::SESSION_CODE, static::$selected);
    }

    protected static function saveInCookie(): void
    {
        $cookieTtl = (int) Option::get(static::MODULE_ID, 'cookie_ttl');
        $expires = time() + $cookieTtl;
        $response = Context::getCurrent()->getResponse();
        $response->addCookie(
            new Cookie(
                static::COOKIE_CODE,
                static::$selected['CODE'],
                $expires
            )
        );
    }

    public static function search(
        string $query,
        string $type,
        int $limit = 20,
        bool $useLanguageGuess = false
    ): array
    {
        if(
            !Loader::includeModule('sale')
            || !Loader::includeModule('search')
        ){
            return [];
        }

        if ($useLanguageGuess) {

            $arLang = \CSearchLanguage::GuessLanguage($query);
            if(
                is_array($arLang)
                && ($arLang['from'] !== $arLang['to'])
            ){
                $query = \CSearchLanguage::ConvertKeyboardLayout(
                    $query,
                    $arLang['from'],
                    $arLang['to']
                );
            }

        }

        $items = [];

        switch($type){

            case 'street':
                break;

            case 'city':

                $query = LocationTable::getList([
                    'select' => [
                        'ID',
                        'CODE',
                        'LOCATION_NAME' => 'NAME.NAME',
                        'PARENT_NAME' => 'PARENT.NAME.NAME'
                    ],
                    'filter' => [
                        '%NAME.NAME' => $query,
                        '=TYPE.CODE' => ['CITY', 'VILLAGE'],
                        '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                        '=PARENT.NAME.LANGUAGE_ID' => LANGUAGE_ID
                    ],
                    'limit' => $limit,
                    'cache' => [
                        'ttl' => static::CACHE_TTL,
                        'cache_joins' => true
                    ]
                ]);

                while($row = $query->fetch()){

                    $arFullName = [$row['LOCATION_NAME']];
                    if($row['PARENT_NAME']){
                        $arFullName[] = $row['PARENT_NAME'];
                    }

                    $row['FULL_NAME'] = implode(', ', $arFullName);

                    $items[] = $row;

                }

                break;

            default:
                break;
        }

        return $items;
    }

    public static function getLocation(string $locationCode): ?array
    {
        if(!$locationCode){
            return null;
        }

        if(!Loader::includeModule('sale')){
            return null;
        }

        $arLocation = LocationTable::getList([
            'select' => [
                'ID',
                'CODE',
                'LOCATION_NAME' => 'NAME.NAME',
                'PARENT_NAME' => 'PARENT.NAME.NAME'
            ],
            'filter' => [
                '=CODE' => $locationCode,
                '=TYPE.CODE' => ['CITY', 'VILLAGE'],
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                '=PARENT.NAME.LANGUAGE_ID' => LANGUAGE_ID
            ],
            'limit' => 1,
            'cache' => [
                'ttl' => static::CACHE_TTL,
                'cache_joins' => true
            ]
        ])->fetch();

        if(!$arLocation){
            return null;
        }

        $arFullName = [$arLocation['LOCATION_NAME']];
        if($arLocation['PARENT_NAME']){
            $arFullName[] = $arLocation['PARENT_NAME'];
        }

        $arLocation['FULL_NAME'] = implode(', ', $arFullName);

        return $arLocation;

    }

    public static function setLocation(array $arLocation): void
    {
        $selected = static::getSelected();

        if($selected['CODE'] === $arLocation['CODE']){
            return;
        }

        static::$selected = $arLocation;
        static::saveInSession();
        static::saveInCookie();
    }

    public static function setDefaultLocation(): void
    {
        $defaultLocationCode = (string) Option::get(static::MODULE_ID, static::OPTION_DEFAULT);
        $arLocation = static::getLocation($defaultLocationCode);
        static::setLocation($arLocation);
    }
}