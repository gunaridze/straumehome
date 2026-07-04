<?php


namespace Yandex\Metrika\Options;


class Select extends BaseOption
{
	public function printHtml($siteId)
	{
		$value = $this->getValue($siteId);
		$valueLabel = isset($this->settings['options'][$value]) ? $this->settings['options'][$value] : reset(
			$this->settings['options']
		);

		?>
        <div class="custom-select">
			<?php $this->printNativeSelect($siteId); ?>
            <div class="custom-select__active">
                <div class="custom-select__active-value"><?= htmlspecialchars($valueLabel); ?></div>
            </div>
            <div class="custom-select__options">
				<?php
				foreach ($this->settings['options'] as $key => $label) {
					$active = $value === $key ? 'active' : '';
					?>
                    <div class="custom-select__option <?= $active; ?>" data-value="<?= htmlspecialchars($key); ?>">
						<?= htmlspecialchars($label); ?>
                    </div>
				<?php } ?>
            </div>
        </div>
		<?php
	}

	public function printNativeSelect($siteId)
	{
		$value = $this->getValue($siteId);
		$id = $this->getInputId($siteId);
		$name = $this->getBaseInputName($siteId);

		?>
        <select class="custom-select__orig" id="<? echo htmlspecialchars($id) ?>"
                name="<? echo htmlspecialchars($name) ?>">
			<?php
			foreach ($this->settings['options'] as $key => $label) {
				$selected = $key === $value ? 'selected="selected"' : '';
				?>
                <option value="<?= htmlspecialchars($key); ?>" <?= $selected; ?>><?= htmlspecialchars(
						$value
					); ?></option>
				<?php
			}
			?>
        </select>
		<?php
	}

	public function prepareRequestValue($requestValue)
	{
		return $requestValue;
	}
}