<?php


namespace Yandex\Metrika\Options;


class Text extends BaseOption
{
	public function printHtml($siteId)
	{
		$value = $this->getValue($siteId);
		$id = $this->getInputId($siteId);
		$name = $this->getBaseInputName($siteId);
		?>
            <input class="text-field" id="<?echo htmlspecialchars($id)?>" value="<?echo htmlspecialchars($value)?>" name="<?echo htmlspecialchars($name)?>" size="50" maxlength="255" type="text">
        <?php
	}

	public function prepareRequestValue($requestValue)
	{
		return $requestValue;
	}
}