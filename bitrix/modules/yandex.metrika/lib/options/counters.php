<?php


namespace Yandex\Metrika\Options;


class Counters extends BaseOption
{
	public function getValue($siteId)
	{
		$value = parent::getValue($siteId);

		if (empty($value) || !is_string($value)) {
			$value = '';
		}

		try {
			$value = \Bitrix\Main\Web\Json::decode($value);
		} catch (\Exception $e) {
			$value = [];
		}

		if (empty($value)) {
			$value = [
				[
					'number' => '',
					'webvisor' => 1
				]
			];
		}

		return $value;
	}

	public function printHtml($siteId)
	{
		$value = $this->getValue($siteId);
		$name = $this->getBaseInputName($siteId);
		?>
        <div class="yam-repeater-field yam-repeater-field_counters">
            <div class="yam-repeater-field__rows">
                <div class="yam-repeater-field__row yam-repeater-field__row_tpl">
                    <div class="yam-repeater-field__input-wrap">
                        <div class="text-field">
                            <input type="text" data-input-type="number"
                                   class="yam-repeater-field__input text-field__input"
                                   data-name="<?= htmlspecialchars($name); ?>[$][number]"
                                   value="" placeholder="<?= GetMessage("COUNTER_NUMBER") ?>">
                            <div class="text-field__reset"></div>
                        </div>
                    </div>
                    <div class="yam-repeater-field__input-wrap">
                        <label class="styled-checkbox">
                            <input type="checkbox" class="yam-repeater-field__input styled-checkbox__input"
                                   data-name="<?= htmlspecialchars($name); ?>[$][webvisor]" value="1" checked="checked"><span
                                    class="styled-checkbox__text"><?= GetMessage(
									"YANDEX_METRIKA_SESSION_REPLAY"
								) ?></span>
                        </label>
                    </div>
                    <button type="button" class="yam-repeater-field__remove-btn ym-events_remove "></button>
                </div>

				<?php foreach ($value as $index => $data) {
					$webvisor = !empty($data['webvisor']);
					?>
                    <div class="yam-repeater-field__row">
                        <div class="yam-repeater-field__input-wrap">
                            <div class="text-field">
                                <input type="text" data-input-type="number"
                                       class="yam-repeater-field__input text-field__input"
                                       data-name="<?= htmlspecialchars($name); ?>[$][number]"
                                       name="<?= htmlspecialchars($name); ?>[<?php echo htmlspecialchars(
										   $index
									   ); ?>][number]" value="<?php echo htmlspecialchars($data['number']); ?>"
                                        placeholder="<?= GetMessage("COUNTER_NUMBER") ?>">
                                <div class="text-field__reset"></div>
                            </div>
                        </div>
                        <div class="yam-repeater-field__input-wrap">
                            <label class="styled-checkbox">
                                <input type="checkbox" class="yam-repeater-field__input styled-checkbox__input"
                                       data-name="<?= htmlspecialchars($name); ?>[$][webvisor]"
                                       name="<?= htmlspecialchars($name); ?>[<?php echo htmlspecialchars(
										   $index
									   ); ?>][webvisor]" value="1" <?php echo $webvisor ? 'checked="checked"' : ''; ?>>
                                <span class="styled-checkbox__text"><?= GetMessage(
										"YANDEX_METRIKA_SESSION_REPLAY"
									) ?></span>
                            </label>
                        </div>
                        <button type="button" class="yam-repeater-field__remove-btn"></button>
                    </div>
				<? } ?>
            </div>
            <div class="yam-repeater-field__add-wrap">
                <button type="button" class="yam-repeater-field__add-btn button button_default"><?= GetMessage(
						"YANDEX_METRIKA_ADD"
					) ?></button>
            </div>
        </div>
		<?php
	}

	public function prepareRequestValue($requestValue)
	{
		if (is_array($requestValue)) {
			$requestValue = \Bitrix\Main\Web\Json::encode($requestValue);
		}

		return $requestValue;
	}
}