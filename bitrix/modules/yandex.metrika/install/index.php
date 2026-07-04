<?php

use Bitrix\Main\EventManager;

IncludeModuleLangFile(__FILE__);

if(class_exists("yandex_metrika")) return;
Class yandex_metrika extends CModule
{
	var $MODULE_ID = "yandex.metrika";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

	public function __construct()
	{
		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		elseif(defined('YANDEX_METRIKA_VERSION') && defined('YANDEX_METRIKA_VERSION_DATE'))
		{
			$this->MODULE_VERSION = YANDEX_METRIKA_VERSION;
			$this->MODULE_VERSION_DATE = YANDEX_METRIKA_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("YANDEX_METRIKA_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("YANDEX_METRIKA_MODULE_DESCRIPTION");
		$this->PARTNER_NAME = GetMessage("YANDEX_METRIKA_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("YANDEX_METRIKA_PARTNER_URI");

		$this->sites = $this->getActiveSites();
	}

	function DoInstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step, $errors, $public_dir, $stepData;

		$MODULE_RIGHT = $APPLICATION->GetGroupRight($this->MODULE_ID);
		if ($MODULE_RIGHT>="W") {

			$errors = false;

			$checkPHPVersion = version_compare(PHP_VERSION, '7.0.0') >= 0;
			$checkBitrixVersion = version_compare(SM_VERSION, '18.0.0') >= 0;

			if (!$checkPHPVersion || !$checkBitrixVersion) {
				$APPLICATION->IncludeAdminFile(
					GetMessage("YANDEX_METRIKA_INSTALL_TITLE"),
					$DOCUMENT_ROOT . "/bitrix/modules/yandex.metrika/install/step1.php"
				);
				return;
			}

			$this->InstallFiles();
			if (!$this->InstallDB()) {
				if($ex = $APPLICATION->GetException()) {
					$strError = $ex->GetString();

					echo $strError;
				}
				return false;
			}

			RegisterModule($this->MODULE_ID);

			$eventManager = \Bitrix\Main\EventManager::getInstance();
			$eventManager->registerEventHandler("main", "OnProlog", $this->MODULE_ID, "\\Yandex\\Metrika\\Handlers", "onProlog");
			$eventManager->registerEventHandler("main", "OnBeforeEndBufferContent", $this->MODULE_ID, "\\Yandex\\Metrika\\Handlers", "onBeforeEndBufferContent");
			$eventManager->registerEventHandler("sale","OnSaleBasketSaved",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketSaved");
			$eventManager->registerEventHandler("sale","OnSaleBasketBeforeSaved",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketBeforeSaved");
			$eventManager->registerEventHandler("sale","OnSaleBasketItemRefreshData",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketItemRefreshData");
			$eventManager->registerEventHandler("sale","OnSaleBasketItemBeforeSaved",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketItemBeforeSaved");
			$eventManager->registerEventHandler("sale","OnSaleOrderSaved",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleOrderSaved");
			$eventManager->registerEventHandler("iblock","OnAfterIBlockElementAdd",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","OnAfterIBlockUpdateHandler");
			$eventManager->registerEventHandler("form","onAfterResultAdd",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onAfterResultAddHandler");
            $eventManager->registerEventHandler("main", "OnBeforeEventSend",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onBeforeEventAdd");

			$stepData = [
				'fillingStates' => $this->fillCounters(),
				'sites' => $this->sites
			];

			$APPLICATION->IncludeAdminFile(
				GetMessage("YANDEX_METRIKA_INSTALL_TITLE"),
				$DOCUMENT_ROOT . "/bitrix/modules/yandex.metrika/install/step2.php"
			);
		}
	}

	function InstallDB()
	{
		global $APPLICATION, $DB, $errors;

		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/db/".mb_strtolower($DB->type)."/install.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		return true;
	}

	function UninstallDB()
	{
		global $APPLICATION, $DB, $errors;

		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/db/".mb_strtolower($DB->type)."/uninstall.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		return true;
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/{$this->MODULE_ID}", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/css", $_SERVER["DOCUMENT_ROOT"]."/bitrix/css/{$this->MODULE_ID}", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/{$this->MODULE_ID}", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/fonts", $_SERVER["DOCUMENT_ROOT"]."/bitrix/fonts/{$this->MODULE_ID}", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		}
		return true;
	}

	function UninstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/images/{$this->MODULE_ID}", true, true);
			DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/{$this->MODULE_ID}", true, true);
			DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/fonts/{$this->MODULE_ID}", true, true);
			DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/css/{$this->MODULE_ID}", true, true);
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", true, true);
		}
		return true;
	}

	function DoUninstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step, $errors, $public_dir;

		$MODULE_RIGHT = $APPLICATION->GetGroupRight($this->MODULE_ID);
		if ($MODULE_RIGHT>="W") {
			$this->UninstallDB();

			foreach ($this->sites as $siteId => $site) {
				\Bitrix\Main\Config\Option::delete(
					$this->MODULE_ID,
					[
						'name' => 'counters',
						'site_id' => $siteId
					]
				);
			}

			UnRegisterModule($this->MODULE_ID);
			$eventManager = \Bitrix\Main\EventManager::getInstance();
			$eventManager->unRegisterEventHandler("main", "OnProlog", $this->MODULE_ID, "\\Yandex\\Metrika\\Handlers", "onProlog");
			$eventManager->unRegisterEventHandler("main", "OnBeforeEndBufferContent", $this->MODULE_ID, "\\Yandex\\Metrika\\Handlers", "onBeforeEndBufferContent");
			$eventManager->unRegisterEventHandler("sale","OnSaleBasketSaved",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketSaved");
			$eventManager->unRegisterEventHandler("sale","OnSaleBasketBeforeSaved",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketBeforeSaved");
			$eventManager->unRegisterEventHandler("sale","OnSaleBasketItemRefreshData",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketItemRefreshData");
			$eventManager->unRegisterEventHandler("sale","OnSaleBasketItemBeforeSaved",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleBasketItemBeforeSaved");
			$eventManager->unRegisterEventHandler("sale","OnSaleOrderSaved",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onSaleOrderSaved");
			$eventManager->unRegisterEventHandler("iblock","OnAfterIBlockElementAdd",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","OnAfterIBlockUpdateHandler");
            $eventManager->unRegisterEventHandler("form","onAfterResultAdd",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onAfterResultAddHandler");
            $eventManager->unRegisterEventHandler("main", "OnBeforeEventSend",$this->MODULE_ID,"\\Yandex\\Metrika\\Handlers","onBeforeEventAdd");
		}
	}

	function fillCounters(){
		$sites = $this->getActiveSites();

		$states = [];
		foreach ($sites as $siteId => $site) {

			/*$siteCounters = $this->restoreCounters($siteId);

			if (!empty($siteCounters)) {
				$states[] = 'exists';
				continue;
			} else {
				$siteCounters = $this->findModulesCounters($siteId);
			}*/

			$siteCounters = $this->findModulesCounters($siteId);

			if ($siteCounters) {
				$states[] = 'found';
				$siteCounters = array_values($siteCounters);
				$siteCounters = json_encode($siteCounters);
				\Bitrix\Main\Config\Option::set($this->MODULE_ID, 'counters', $siteCounters, $siteId);
			}
		}

		return $states;
	}

	function findModulesCounters ($siteId) {
		$siteCounters = [];

		//intervolga.conversionpro
		$this->findIntervolgaConversionproCounters($siteId, $siteCounters);

		//concept.tagmanager
		$this->findConceptTagmanagerCounters($siteId, $siteCounters);

		//artofbx.yandexmetrika
		$this->findArtofbxYandexmetrikaCounters($siteId, $siteCounters);

		//arturgolubev.ecommerce
		$this->findArturgolubevEcommerceCounters($siteId, $siteCounters);

		return $siteCounters;
	}

	function restoreCounters($siteId){
		$siteCounters = \Bitrix\Main\Config\Option::get($this->MODULE_ID, 'counters', '', $siteId);

		if (empty($siteCounters) || !is_string($siteCounters)) {
			$siteCounters = '';
		}

		try {
			$siteCounters = json_decode($siteCounters, true);
		} catch (\Exception $e) {
			$siteCounters = [];
		}

		if (!is_array($siteCounters)) {
			$siteCounters = [];
		}

		foreach ($siteCounters as $key => $counter) {
			if (!$siteCounters[$key]['number']) {
				unset($siteCounters[$key]);
			}
		}

		return $siteCounters;
	}


	function getActiveSites(){
		$siteIds = [];
		$sitesIdsRes = \Bitrix\Main\SiteTable::getList(array(
		   'select' => array('*'),
		   'filter' => array(
		   		'ACTIVE' => 'Y'
		   )
	   ));

		while ($row = $sitesIdsRes->fetch()) {
			$siteIds[$row['LID']] = $row;
		}

		return $siteIds;
	}

	function findCounterInCode($code){
		if (!$code) {
			return false;
		}

		$regExp1 = '/(?:\s|;)ym\s*\(\s*(\d+)\s*,\s*"init"/';
		$regExp2 = '/mc\.yandex\.ru\/watch\/([\d]+)"/';

		preg_match($regExp1, $code, $matches);
		if ($matches[1]) {
			return $matches[1];
		}

		preg_match($regExp2, $code, $matches);
		if ($matches[1]) {
			return $matches[1];
		}

		return false;
	}

	function isNumber($number){
		return preg_match('/^\d+$/', $number);
	}


	//-------------finders------------------

	//intervolga.conversionpro
	function findIntervolgaConversionproCounters($siteId, &$siteCounters){
		$number = \Bitrix\Main\Config\Option::get('intervolga.conversionpro', 'metrika_id', '', $siteId);
		if (!empty($number) && $this->isNumber($number)) {
			$siteCounters[$number] = [
				'number' => $number,
				'webvisor' => 1
			];
		}
	}

	//concept.tagmanager
	function findConceptTagmanagerCounters($siteId, &$siteCounters){
		$code = \Bitrix\Main\Config\Option::get("concept.tagmanager", "tag_ya_code", '', $siteId);
		$code = htmlspecialcharsBack($code);

		$number = $this->findCounterInCode($code);

		if (!empty($number) && $this->isNumber($number)) {
			$siteCounters[$number] = [
				'number' => $number,
				'webvisor' => 1
			];
		}
	}

	//artofbx.yandexmetrika
	function findArtofbxYandexmetrikaCounters($siteId, &$siteCounters){
		$number = \Bitrix\Main\Config\Option::get("artofbx.yandexmetrika", "id", '', $siteId);

		if (!empty($number) && $this->isNumber($number)) {
			$siteCounters[$number] = [
				'number' => $number,
				'webvisor' => 1
			];
		}
	}

	//arturgolubev.ecommerce
	function findArturgolubevEcommerceCounters($siteId, &$siteCounters){
		$number = \Bitrix\Main\Config\Option::get("arturgolubev.ecommerce", "yandex_goal_counter_id_".$siteId, '');

		if (!empty($number) && $this->isNumber($number)) {
			$siteCounters[$number] = [
				'number' => $number,
				'webvisor' => 1
			];
		}
	}
}