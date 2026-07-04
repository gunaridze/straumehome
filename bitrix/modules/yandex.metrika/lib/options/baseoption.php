<?php

namespace Yandex\Metrika\Options;

use http\Exception;

abstract class BaseOption{
	public $moduleId = 'yandex.metrika';
	public $name;
	public $settings = [];

	public function __construct($name, $settings = []){
		if (empty($name)) {
			throw new \Exception('Name of BaseOption cant be empty');
		}

		$this->name = $name;
		$this->settings = $settings;
	}

	public function getValue($siteId) {
		return \COption::GetOptionString($this->moduleId, $this->name, null, $siteId, true);
	}

	public function getRequestValue($siteId) {
		if (!isset(
			$_REQUEST['options'],
			$_REQUEST['options'][$siteId],
			$_REQUEST['options'][$siteId][$this->name]
		)) {
			return null;
		}

		return $_REQUEST['options'][$siteId][$this->name];
	}

	public function getHtml($siteId){
		ob_start();
		$this->printHtml($siteId);
		return ob_get_clean();
	}

	public function save($siteId){
		$requestValue = $this->getRequestValue($siteId);
		$preparedValue = $this->prepareRequestValue($requestValue);
		\COption::SetOptionString($this->moduleId, $this->name, $preparedValue, false, $siteId);
	}

	public function getBaseInputName($siteId){
		return htmlspecialchars("options[$siteId][{$this->name}]");
	}

	public function getInputId($siteId){
		return htmlspecialchars("options_{$siteId}_{$this->name}");
	}

	abstract public function printHtml($siteId);
	abstract public function prepareRequestValue($requestValue);
}