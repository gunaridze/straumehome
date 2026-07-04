<?php

namespace Yandex\Metrika;

class Helpers {
	public static $MODULE_ID = 'yandex.metrika';

	public static function hasRights($letter = 'W'){
		global $USER, $APPLICATION;
		if (!is_object($USER)) $USER = new CUser;
		if ($USER->IsAdmin()) return true;
		$MODULE_RIGHT = $APPLICATION->GetGroupRight("yandex.metrika");
		if ($MODULE_RIGHT>=$letter) return true;
	}

	public static function getAssetName($name, $path){
		$locale = LANGUAGE_ID;
		$filename = pathinfo($name, PATHINFO_FILENAME);
		$extension = pathinfo($name, PATHINFO_EXTENSION);
		$assetName = $filename.'-'.$locale.'.'.$extension;

		if (!is_file($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.self::$MODULE_ID.DIRECTORY_SEPARATOR.$assetName)) {
			$assetName = $name;
		}

		return $assetName;
	}

	public static function getAssetPath($name, $path){
		$assetName = self::getAssetName($name);

		return $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.self::$MODULE_ID.DIRECTORY_SEPARATOR.$assetName;
	}

	public static function getAssetUrl($name, $path){
		$assetName = self::getAssetName($name, $path);

		return preg_replace('/[\/\\\]+/','/',  '/'.$path.'/'.self::$MODULE_ID.'/'.$assetName);
	}

	public static function getModulePath(){
		return implode(DIRECTORY_SEPARATOR, [
			$_SERVER['DOCUMENT_ROOT'],
			'bitrix',
			'modules',
			self::$MODULE_ID
		]);
	}

	public static function getModuleUrl(){
		return '/bitrix/modules/'.self::$MODULE_ID;
	}

	public static function getClientIP(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = array_values(array_filter(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));
            $ip = end($ip);
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	public static function getRIP(){
		$ip = self::getClientIP();
		$intIp = ip2long($ip);

		$rip = ($intIp ^ 1597463007);
		return $rip;
	}

	public static function resetYMCookies(){
		$domain = $_SERVER["SERVER_NAME"];
		$baseDomain = self::getBaseDomain($domain);
		$expires = time() + 31536000;
		$path = '/';
		$domain = '.'.$baseDomain;
		$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';


		if (isset($_COOKIE['_ym_uid'])) {
			setcookie('_ym_uid', $_COOKIE['_ym_uid'], $expires, $path, $domain, $secure);
		}

		if (isset($_COOKIE['_ym_d'])) {
			setcookie('_ym_d', time(), $expires, $path, $domain, $secure);
		}
	}

    public static function getBitrixVersion(){
        if(!defined('SM_VERSION')) {
            return 'undefined';
        }

        $version = SM_VERSION;
        $version = explode('.', $version);
        $version = array_slice($version, 0, 2);

        return implode('.', $version);
    }

	public static function getBaseDomain($host){
		$myhost = strtolower(trim($host));
		$count = substr_count($myhost, '.');
		if($count === 2){
			if(strlen(explode('.', $myhost)[1]) > 3) $myhost = explode('.', $myhost, 2)[1];
		} else if($count > 2){
			$myhost = self::getBaseDomain(explode('.', $myhost, 2)[1]);
		}
		return $myhost;
	}
}