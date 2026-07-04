<?php
namespace Yandex\Metrika;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

class Ecommerce {
	public static $MODULE_ID = 'yandex.metrika';

	public static function registerAction($type, $products, $actionField = null){
		setcookie("ym_has_actions", "1", time()+3600*24, '/');
		return self::addActionToDB(self::buildAction($type, $products, $actionField));

	}

	public static function buildAction($type, $products, $actionField = null){
        if($type == 'ym-submit-leadform' || $type == 'ym-subscribe') {
            return [$type];
        }
        else {
            $currencyCode = \Bitrix\Currency\CurrencyManager::GetBaseCurrency();
            $actionData = [
                'products' => $products
            ];

            if ($actionField) {
                $actionData['actionField'] = $actionField;
            }

            $action = [
                'ecommerce' => [
                    'currencyCode' => $currencyCode,
                    $type => $actionData
                ]
            ];
        }
		return $action;
	}

	public static function prepareBasketItemsChanges($basketItemsChanges){
		$ecProducts = [];

		foreach ($basketItemsChanges as $changes) {
			$ecProducts[] = self::itemChangesToEcProduct($changes);
		}

		return $ecProducts;
	}

	public static function itemChangesToEcProduct($changes){
		extract($changes);
		$product = self::getBasketItemEcData($basketItem);
		$product['quantity'] = $quantity;

		if (!empty($coupon)) {
			$product['coupon'] = $coupon;
		}

		return $product;
	}

	public static function getBasketItemEcData($basketItem){
		if(!\CModule::IncludeModule("iblock")) {
			Checker::logMsg('YANDEX_METRIKA_NO_MODULE', ['iblock']);
			return [];
		}

		$additional = [
			'price' => $basketItem->getPrice()
		];

		$propsValues = self::getOfferPropsValuesByBasketItem($basketItem);
		if (!empty($propsValues)) {
			$additional['variant'] = implode(',', $propsValues);
		}

		$data = self::getEcProductData($basketItem->getProductId(), $additional, false);

		return $data;
	}

	public static function getEcProductData($productId, $additional = [], $variant = true) {
		if (!\CModule::IncludeModule("catalog")) {
			Checker::logMsg('YANDEX_METRIKA_NO_MODULE', ['catalog']);
			return [];
		}

		$offerId = 0;
		$offerIblockId = 0;

		$offerData = \CCatalogSku::GetProductInfo($productId);
		if (is_array($offerData)) {
			$productId = $offerData['ID'];
			$offerId = $productId;
			$offerIblockId = $offerData['OFFER_IBLOCK_ID'];
		}

		$productData = \CIBlockElement::GetByID($productId)->GetNext();
		if ($productData) {
			$data = [
				'id' => $productId,
				'name' => $productData['NAME']
			];

			if (!empty($offerId) && $variant) {
				$propsValues = self::getOfferPropsValues($offerId);
				if (!empty($propsValues)) {
					$data['variant'] = implode(',', $propsValues);
				}
			}

			$categoryPath = self::getCategoryPath($productData['IBLOCK_ID'], $productData['IBLOCK_SECTION_ID']);
			if (!empty($categoryPath)) {
				$data['category'] = $categoryPath;
			}

			$brandPropCode = \COption::GetOptionString(self::$MODULE_ID, 'brand_property_'.$productData['IBLOCK_ID'], '', SITE_ID);
			if ($brandPropCode) {
				$brandRes = \CIBlockElement::GetProperty(
					$productData['IBLOCK_ID'],
					$data['id'],
					array("sort" => "asc"),
					array("CODE" => $brandPropCode)
				);
				$brand = [];

				while ($brandProp = $brandRes->GetNext()) {
					$brand[] = self::getPropertyValue($brandProp);
				}

				if (!empty($brand)) {
					$brand = implode(',', $brand);
					$data['brand'] = $brand;
				}
			}

			$data['price'] = self::getProductPrice($offerId ? $offerId : $productId);

			return array_merge($data, $additional);
		}

		return null;
	}

	public static function getCategoryPath($iblockId, $sectionId){
		$categoryPath = [];
		$categoriesRes = \CIBlockSection::GetNavChain($iblockId, $sectionId);

		while ($category = $categoriesRes->GetNext()){
			$categoryPath[] = $category['NAME'];
		}

		$categoryPath = implode('/', $categoryPath);

		return $categoryPath;
	}
	
	public static function getPropertyValue($property){
		if ($property['PROPERTY_TYPE'] === 'G' && !empty($property['VALUE'])) {
			$brandElemData = \CIBlockSection::GetByID($property['VALUE'])->Fetch();
			$value = $brandElemData['NAME'];
		} elseif ($property['PROPERTY_TYPE'] === 'E' && !empty($property['VALUE'])) {
			$brandElemData = \CIBlockElement::GetByID($property['VALUE'])->Fetch();
			$value = $brandElemData['NAME'];
		} elseif ($property['PROPERTY_TYPE'] === 'L' && !empty($property['VALUE_ENUM'])) {
			$value = $property['VALUE_ENUM'];
		} elseif ($property['PROPERTY_TYPE'] === 'S') {
			if ($property['USER_TYPE'] === 'directory' && class_exists('\CIBlockFormatProperties')) {
				$bradPropData = \CIBlockFormatProperties::GetDisplayValue(null, $property);
				$value = $bradPropData['DISPLAY_VALUE'];
			} else {
				$value = $property['VALUE'];
			}
		} else {
			$value = $property['VALUE'];
		}

		return $value;
	}

	public static function getOfferPropsValues($id, $propsKeys = []){
		if (!\CModule::IncludeModule("catalog")) {
			Checker::logMsg('YANDEX_METRIKA_NO_MODULE', ['catalog']);
			return [];
		}
		$offerData = \CCatalogSku::GetProductInfo($id);

		if (!$offerData) {
			return [];
		}

		$iblockId = $offerData['OFFER_IBLOCK_ID'];

		if (empty($propsKeys)) {
			$propsKeys = \Bitrix\Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes(
				$iblockId,
				['CODE' => 'Y']
			);
		}

		$db_props = \CIBlockElement::GetProperty(
			$iblockId,
			$id,
			array("sort" => "asc"),
			array("ACTIVE" => 'Y', "EMPTY" => 'N')
		);

		while($ar_props = $db_props->GetNext()) {
			if (in_array($ar_props['CODE'], $propsKeys)) {
				$propsValues[$ar_props['CODE']] = self::getPropertyValue($ar_props);
			}
		}

		ksort($propsValues);

		return $propsValues;
	}

	public static function getOfferPropsValuesByBasketItem($basketItem, $propsKeys = []){
		if (!\CModule::IncludeModule("catalog")) {
			Checker::logMsg('YANDEX_METRIKA_NO_MODULE', ['catalog']);
			return [];
		}

		$id = $basketItem->getProductId();
		$offerData = \CCatalogSku::GetProductInfo($id);

		if (!$offerData) {
			return [];
		}

		$iblockId = $offerData['OFFER_IBLOCK_ID'];

		if (empty($propsKeys)) {
			$propsKeys = \Bitrix\Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes(
				$iblockId,
				['CODE' => 'Y']
			);
		}

		$basketPropertyCollection = $basketItem->getPropertyCollection();
		$props = $basketPropertyCollection->getPropertyValues();
		$propsValues = [];

		foreach ($propsKeys as $propsKey) {
			if (empty($props[$propsKey])) {
				continue;
			}

			$propsValues[$propsKey] = $props[$propsKey]['VALUE'];
		}

		ksort($propsValues);

		return $propsValues;
	}

	public static function getProductPrice($productID, $qty = 1){
		global $USER;
		$arPrice = \CCatalogProduct::GetOptimalPrice($productID, $qty, $USER->GetUserGroupArray(), 'N');
		if (!$arPrice || count($arPrice) <= 0)
		{
			if ($nearestQuantity = \CCatalogProduct::GetNearestQuantityPrice($productID, $qty, $USER->GetUserGroupArray()))
			{
				$qty = $nearestQuantity;
				$arPrice = \CCatalogProduct::GetOptimalPrice($productID, $qty, $USER->GetUserGroupArray(), 'N');
			}
		}

		return $arPrice['DISCOUNT_PRICE'];
	}

	public static function addActionToDB($data, $UID = null){
		global $DB;


		if (!$UID) {
			$UID = !empty($_COOKIE['_ym_uid']) ? $_COOKIE['_ym_uid'] : 0;
		}

		if (!$UID) {
			return array();
		}

		$UID = $DB->ForSql($UID);
		$EC_ACTION = $DB->ForSql(serialize($data));
		$SID = $DB->ForSql(SITE_ID);


		$ID = $DB->Insert('yandex_metrika_actions', array(
			'UID' => "'{$UID}'",
			'EC_ACTION' => "'{$EC_ACTION}'",
			'SID' => "'{$SID}'",
		));

		if (!$ID) {
			Checker::logMsg('YANDEX_METRIKA_ACTION_NOT_SAVED');
		}

		return $ID;
	}

	public static function getDBActions($UID = null){
		global $DB;

		if (!$UID) {
			$UID = !empty($_COOKIE['_ym_uid']) ? $_COOKIE['_ym_uid'] : 0;
		}

		$UID = $DB->ForSql($UID);
		$SID = $DB->ForSql(SITE_ID);

		$actions = array();

		$res = $DB->Query("
			SELECT *
			FROM yandex_metrika_actions
			WHERE UID = '{$UID}' AND SID = '{$SID}'
		");

		while ($row = $res->Fetch()) {
			try {
				$action = unserialize($row["EC_ACTION"]);
			} catch (\Exception $e) {
				continue;
			}


			$actions[$row["ID"]] = $action;
		}

		return $actions;
	}

	public static function clearDBActions($actionsIds, $UID = null) {
		global $DB;

		if (empty($actionsIds)) {
			return;
		}

		if (!$UID) {
			$UID = !empty($_COOKIE['_ym_uid']) ? $_COOKIE['_ym_uid'] : 0;
		}

		if (!$UID) {
			return;
		}

		$UID = $DB->ForSql($UID);
		$SID = $DB->ForSql(SITE_ID);
		$actionsIds = implode(',', array_map('intval', $actionsIds));

		$res = $DB->Query("
			DELETE FROM yandex_metrika_actions
			WHERE UID = '{$UID}' AND SID = '{$SID}' AND ID IN ({$actionsIds})
		");
	}

	public static function addActionToSession($data){
		$_SESSION['yandexMetrikaEcommerceActions'][] = $data;
		return $data;
	}

	public static function getActions($excludes = []){
		return $_SESSION['yandexMetrikaEcommerceActions'];
	}

	public static function clearActions($excludes = []) {
		foreach ($_SESSION['yandexMetrikaEcommerceActions'] as $id => $action) {
			foreach ($excludes as $exclude) {
				if (!empty($action['ecommerce'][$exclude])) {
					continue 2;
				}
			}

			unset($_SESSION['yandexMetrikaEcommerceActions'][$id]);
		}
	}

	public static function removeActionsByIds($ids) {
		foreach ($_SESSION['yandexMetrikaEcommerceActions'] as $id => $action) {
			if (in_array($id, $ids)) {
				unset($_SESSION['yandexMetrikaEcommerceActions'][$id]);
			}
		}
	}
}