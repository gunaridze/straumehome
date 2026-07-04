<?php
namespace Yandex\Metrika;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

IncludeModuleLangFile(__FILE__);

class Checker {
	public static $MODULE_ID = 'yandex.metrika';

	public static function getFileName () {
		return $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::$MODULE_ID.'/logs.txt';
	}

	public static function checkModule2ModuleEvents(){
		$checks = [
			["main","OnProlog", self::$MODULE_ID, "\\Yandex\\Metrika\\Handlers", "onProlog"],
			["main","OnBeforeEndBufferContent", self::$MODULE_ID, "\\Yandex\\Metrika\\Handlers", "onBeforeEndBufferContent"],
			["sale","OnSaleBasketSaved",self::$MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketSaved"],
			["sale","OnSaleBasketBeforeSaved",self::$MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketBeforeSaved"],
			["sale","OnSaleBasketItemRefreshData",self::$MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketItemRefreshData"],
			["sale","OnSaleBasketItemBeforeSaved",self::$MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketItemBeforeSaved"],
			["sale","OnSaleOrderSaved",self::$MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleOrderSaved"],
		];

		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$toModuleId = $sqlHelper->forSql(self::$MODULE_ID);

		$result = $con->query("SELECT * FROM b_module_to_module WHERE TO_MODULE_ID='$toModuleId'");
		$rows = $result->fetchAll();

		foreach ($checks as $check) {
			list($fromModule, $eventName, $toModule, $className, $methodName) = $check;
			$exists = false;
			foreach ($rows as $row) {
				if ($row['MESSAGE_ID'] === $eventName) {
					$exists = true;
				}
			}

			if (!$exists) {
				self::log(sprintf(GetMessage('YANDEX_METRIKA_NO_EVENT_HANDLER'), $eventName));
			}
		}
	}

	public static function log($text){
		$text = "[".date('d.m.Y h:i:s')."] ".$text.PHP_EOL;
		file_put_contents(self::getFileName(), $text, FILE_APPEND);
	}

	public static function logMsg($type, $data = []){
		if (empty($data)) {
			$data[] = '';
		}

		self::log(sprintf(GetMessage($type), ...$data));
	}

	public static function getLogs(){
		$text = '';

		if (is_file(self::getFileName())) {
			$text = file_get_contents(self::getFileName());
		}

		if (empty($text)) {
			$text = GetMessage('YANDEX_METRIKA_NO_LOGS');
		}

		return nl2br($text);
	}
}