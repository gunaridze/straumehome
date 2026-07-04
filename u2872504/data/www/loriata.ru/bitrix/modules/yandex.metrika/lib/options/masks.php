<?php


namespace Yandex\Metrika\Options;


class Masks extends BaseOption
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
                    'mask' => ''
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
        <div class="yam-repeater-field yam-repeater-field_masks">
            <div class="yam-repeater-field__rows">
                <div class="yam-repeater-field__row yam-repeater-field__row_tpl">
                    <div class="yam-repeater-field__input-wrap">
                        <select class="yam-repeater-field__input custom-select__orig"
                                data-name="<?= htmlspecialchars($name); ?>[$][number]"
                                data-modifier="v1"
                        ></select>
                    </div>
                    <div class="yam-repeater-field__sep">=</div>
                    <div class="yam-repeater-field__input-wrap yam-repeater-field__input-wrap_fill">
                        <input class="yam-repeater-field__input text-field text-field_fill"
                               data-name="<? echo htmlspecialchars($name) ?>[$][mask]" size="50" maxlength="255"
                               type="text" placeholder="<?= GetMessage("YANDEX_METRIKA_MASK_PLACEHOLDER") ?>">
                    </div>
                    <button type="button" class="yam-repeater-field__remove-btn"></button>
                </div>

                <?php foreach ($value as $index => $data) {
                    ?>
                    <div class="yam-repeater-field__row">
                        <div class="yam-repeater-field__input-wrap">
                            <select class="yam-repeater-field__input custom-select__orig"
                                    data-name="<?= htmlspecialchars($name); ?>[$][number]"
                                    data-modifier="v1"
                                    name="<?= htmlspecialchars($name); ?>[<?php echo htmlspecialchars($index); ?>][number]"
                            >
                                <option value="<?php echo htmlspecialchars($data['number']); ?>" selected><?php echo htmlspecialchars($data['number']); ?></option>
                            </select>
                        </div>
                        <div class="yam-repeater-field__sep">=</div>
                        <div class="yam-repeater-field__input-wrap yam-repeater-field__input-wrap_fill">
                            <input class="yam-repeater-field__input text-field text-field_fill"
                                   type="text" size="50" maxlength="255"
                                   data-name="<? echo htmlspecialchars($name) ?>[$][mask]" size="50" maxlength="255"
                                   name="<? echo htmlspecialchars($name) ?>[<?php echo htmlspecialchars($index); ?>][mask]"
                                   placeholder="<?= GetMessage("YANDEX_METRIKA_MASK_PLACEHOLDER") ?>"
                                   value="<?php echo htmlspecialchars($data['mask']); ?>"
                            >
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