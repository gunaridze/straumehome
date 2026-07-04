<?php

namespace Yandex\Metrika;

use http\Exception;

class Fields {
	public $moduleId = 'yandex.metrika';
	protected $fields = [];
	protected $siteId;

	public function __construct($groups = []) {
		$this->registerFields($groups);
	}

	public function setSiteId($siteId){
		$this->siteId = $siteId;
	}

	public function registerFields(array $fields){
		foreach ($fields as $field) {
			$this->registerField($field);
		}
	}

	public function registerField($field) {
		$defaults = [
			'group' => 'main',
		];

		$option = isset($field['option']) ? $field['option'] : null;
		if (!is_a($option, '\\Yandex\\Metrika\\Options\\BaseOption')) {
			throw new \Exception('Param "option" must be object of \\Yandex\\Metrika\\Options\\BaseOption');
		}

		$field = array_merge($defaults, $field);
		$field['name'] = $option->name;
		$field['type'] = get_class($option);

		$this->fields[$option->name] = $field;
	}

	public function printGroup($groupName){
		foreach ($this->fields as $field) {
			if ($field['group'] === $groupName) {
				$this->printField($field);
			}
		}
	}

	public function printField($field){
		$option = $field['option'];
		$id = $option->getInputId($this->siteId);
		$label = strip_tags($field['label'], '<br><p><span><i><b><u><a><sup><sub>');
		$before = $field['before'] ? strip_tags($field['before'], '<br><p><span><i><b><u><a><sup><sub>') : '';
		$after = $field['after'] ? strip_tags($field['after'], '<br><p><span><i><b><u><a><sup><sub>') : '';
		?>
		<div class="yam__field">
            <div class="yam__field-label">
                <? echo $label; ?>
            </div>

            <? if ($before) { ?>
                <div class="yam__field-text yam__field-text_before">
                    <?= $before; ?>
                </div>
            <? } ?>

            <div class="yam__field-input">
				<?php
					$this->printOption($option);
				?>
            </div>

            <? if ($after) { ?>
                <div class="yam__field-text yam__field-text_after">
                    <?= $after; ?>
                </div>
            <? } ?>

        </div>
		<?php
	}

	public function printOption($option){
		if (is_string($option)) {
			$option = $this->getOptionBy('name', $option);
		}

		if (!$option) {
			return;
		}

		$option->printHtml($this->siteId);
	}

	public function save(){
		foreach ($this->fields as $field) {
			$field['option']->save($this->siteId);
		}
	}

	public function getOptionBy($by, $value){
		$field = $this->getFieldBy($by, $value);

		if ($field && $field['option']) {
			return $field['option'];
		}

		return false;
	}

	public function getFieldBy($by, $value){
		$key = array_search($value, array_column($this->fields, $by));

		return $key === false ? false : $this->fields[$key];
	}
}