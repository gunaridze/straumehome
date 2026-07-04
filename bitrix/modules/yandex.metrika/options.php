<?

$module_id = "yandex.metrika";
$module_version = "1.0.8";
IncludeModuleLangFile(__FILE__);

\CJSCore::Init(array("jquery"));

$currentSiteId = false;
if (!empty($_GET['site_id'])) {
	$currentSiteId = $_GET['site_id'];
}

$sites = [];
$sitesIdsRes = \Bitrix\Main\SiteTable::getList(
	array(
		'select' => array('NAME', 'LID', 'DEF')
	)
);
while ($row = $sitesIdsRes->fetch()) {
	$sites[] = $row;

	if (!$currentSiteId && $row['DEF'] === 'Y') {
		$currentSiteId = $row['LID'];
	}
}

$catalogProps = [];
if (\Bitrix\Main\Loader::includeModule('catalog')) {
	\Bitrix\Main\Loader::includeModule('iblock');

	$catalogIblocks = \Bitrix\Catalog\CatalogIblockTable::getList(
		[
			'select' => ['IBLOCK_ID'],
			'filter' => ['PRODUCT_IBLOCK_ID' => 0]
		]
	)->fetchAll();

	foreach ($catalogIblocks as $catalogData) {
		$props = \Bitrix\Iblock\PropertyTable::getList(
			[
				'select' => ['ID', 'NAME', 'CODE'],
				'filter' => [
					'ACTIVE' => 'Y',
					'IBLOCK_ID' => $catalogData['IBLOCK_ID'],
					'!PROPERTY_TYPE' => ['F', 'N']
				]
			]
		)->fetchAll();

		usort(
			$props,
			function ($a, $b) {
				return strcmp($a['NAME'], $b['NAME']);
			}
		);

		$catalogProps[$catalogData['IBLOCK_ID']] = $props;
	}
}


$fieldsList = [
	[
		'label' => GetMessage("YANDEX_METRIKA_COUNTERS"),
		'option' => new \Yandex\Metrika\Options\Counters('counters'),
		'group' => 'main'
	]

];

if (!empty($catalogProps)) {
    $isFirst = true;
    $propsIblockIds = array_keys($catalogProps);
    $lastIblockId = end($propsIblockIds);
    foreach ($catalogProps as $iblockId => $iblockProps) {
        $isLast = $lastIblockId === $iblockId;
        $iblockData = CIBlock::GetByID($iblockId)->GetNext();
		if(empty($iblockData) || $iblockData['ACTIVE'] !== 'Y') continue;

		$options = [
            '' => GetMessage('YANDEX_METRIKA_SELECT_PROPERTY')
        ];

		foreach ($iblockProps as $prop) {
			$options[$prop['CODE']] = "({$prop['ID']}) ".$prop['NAME'];
        }

        $fieldsList[] = [
            'label' => sprintf(GetMessage("YANDEX_METRIKA_BRAND_PROPERTY"), $iblockData['NAME']),
            'before' => $isFirst ? sprintf(
                GetMessage("YANDEX_METRIKA_BRAND_PROPERTY_DESCRIPTION"),
                GetMessage("YANDEX_METRIKA_BRAND_PROPERTY_DESCRIPTION_LINK")
            ) : '',
            'after' => $isLast ? sprintf(
                GetMessage('YANDEX_METRIKA_ABOUT_DETAIL'),
                GetMessage('YANDEX_METRIKA_ABOUT_DETAIL_LINK')
            ) : '',
            'option' => new \Yandex\Metrika\Options\Select(
                'brand_property_'.$iblockId, [
                     'options' => $options,
                     'default' => ''
                 ]
            ),
            'group' => 'additional'
        ];

		$isFirst = false;
    }
}


$fieldsList[] = [
    'label' => GetMessage("YANDEX_METRIKA_MASKS_PROPERTY"),
    'before' => GetMessage("YANDEX_METRIKA_MASKS_DESCRIPTION"),
    'option' => new \Yandex\Metrika\Options\Masks(
        'counters_masks', [
            'options' => [],
            'default' => ''
        ]
    ),
    'group' => 'additional'
];

$fields = new \Yandex\Metrika\Fields($fieldsList);

$fields->setSiteId($currentSiteId);

if ($_POST["ym-events"]) {

    $siteIds = [];

    $siteIds = array_keys($_POST["ym-events"]["param"]);

    $arEvents = [];
    foreach ($siteIds as $site) {
        foreach ($_POST["ym-events"]["param"][$site] as $k=>$v) {
            $arEvent = [];
            $arEvent["param"] 		= strlen($_POST["ym-events"]["param"][$site][$k]) > 0 ? $_POST["ym-events"]["param"][$site][$k] : false;
            $arEvent["selector"] 	= strlen(trim($_POST["ym-events"]["selector"][$site][$k])) > 0 ? trim($_POST["ym-events"]["selector"][$site][$k]) : false;
            $arEvent["event"] 		= strlen($_POST["ym-events"]["event"][$site][$k]) > 0 ? $_POST["ym-events"]["event"][$site][$k] : false;

            if ($arEvent["param"] || $arEvent["selector"] || $arEvent["event"]) {
                $arEvents[$site][] = $arEvent;
            }
        }
        \COption::SetOptionString($module_id, "events", serialize($arEvents[$site]), false, $site);
        \COption::SetOptionString($module_id, "datalayervar", strlen(trim($_POST["ym-datalayervar"][$site])) > 0 ? trim($_POST["ym-datalayervar"][$site]) : "dataLayer", false, $site);

    }
}
$arEvents = [];

foreach ($sites as $site) {
    $sEvents = \COption::GetOptionString($module_id, "events", false, $site['LID']);
    $datalayervar = \COption::GetOptionString($module_id, "datalayervar", "dataLayer", $site['LID']);

    if (!$sEvents) {
        $arEvents[$site['LID']] = [];
    } else {
        $arEvents[$site['LID']] = unserialize($sEvents);
    }

    $ardatalayervar[$site['LID']] = $datalayervar;
}

$arEventsParams = array(
	["code"	=> "ym-register", "name" =>  GetMessage('YANDEX_METRIKA_EVENT_PARAM_REGISTER')],
	["code"	=> "ym-submit-contacts", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_SUBMIT-CONTACTS')],
	["code"	=> "ym-confirm-contact", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_CONFIRM-CONTACT')],
	["code"	=> "ym-login", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_LOGIN')],
	["code"	=> "ym-subscribe", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_YM-SUBSCRIBE')],
	["code"	=> "ym-open-leadform", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_OPEN-LEADFORM')],
	["code"	=> "ym-submit-leadform", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_SUBMIT-LEADFORM')],
	["code"	=> "ym-open-chat", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_OPEN-CHAT')],
	["code"	=> "ym-send-message", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_SEND-MESSAGE')],
	["code"	=> "ym-add-to-wishlist", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_ADD-TO-WISHLIST')],
	["code"	=> "ym-begin-checkout", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_YM-BEGIN-CHECKOUT')],
	["code"	=> "ym-add-payment-info", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_ADD-PAYMENT-INFO')],
	["code"	=> "ym-purchase", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_PURCHASE')],
	["code"	=> "ym-agree-meeting", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_AGREE-MEETING')],
	["code"	=> "ym-add-to-cart", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_ADD-TO-CART')],
	["code"	=> "ym-begin-checkout", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_BEGIN-CHECKOUT')],
	["code"	=> "ym-show-contacts", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_SHOW-CONTACTS')],
	["code"	=> "ym-successful-lead", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_SUCCESSFUL-LEAD')],
	["code"	=> "ym-get-response", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_GET-RESPONSE')],
	["code"	=> "ym-contact-constractor", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_CONTACT-CONSTRACTOR')],
	["code"	=> "ym-agree-constractor", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_AGREE-CONSTRACTOR')],
	["code"	=> "ym-complete-order", "name" => GetMessage('YANDEX_METRIKA_EVENT_PARAM_COMPLETE-ORDER')],
);

//https://docs.google.com/spreadsheets/d/17LbNNlycd4k_RMRlZ-gu9vBfFMTLIx4W/edit#gid=679710484


?>

	<script>
		const ymEvents = <?=json_encode($arEvents)?>;

        BX.message({
            'YANDEX_METRIKA_EVENT_PARAM_JS_EVENT_PARAM': '<?=GetMessageJS("YANDEX_METRIKA_EVENT_PARAM_JS_EVENT_PARAM")?>',
            'YANDEX_METRIKA_EVENT_DESCR_H': '<?=GetMessageJS("YANDEX_METRIKA_EVENT_DESCR_H")?>',
            'YANDEX_METRIKA_SHOW_LOGS': '<?=GetMessageJS("YANDEX_METRIKA_SHOW_LOGS")?>',
            'YANDEX_METRIKA_HIDE_LOGS': '<?=GetMessageJS("YANDEX_METRIKA_HIDE_LOGS")?>',
        });
	</script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script type="text/javascript" src="<?php echo "/bitrix/js/$module_id/admin.js?v=".time(); ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo "/bitrix/css/$module_id/admin.css?v=".time(); ?>">
<?php

if (\Yandex\Metrika\Helpers::hasRights('R')) {
	if ($REQUEST_METHOD == "POST" && \Yandex\Metrika\Helpers::hasRights()) {
		foreach ($sites as $site) {
			$fields->setSiteId($site['LID']);
			$fields->save();
		}
	}


	\Yandex\Metrika\Checker::checkModule2ModuleEvents();

	$tabControl = new CAdminTabControl("tabControl", []);
	?>

    <form method="POST" action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialchars(
		$mid
	) ?>&lang=<?= LANGUAGE_ID ?>&site_id=<?= htmlspecialchars($currentSiteId); ?>"><?= bitrix_sessid_post() ?><?
		?>
        <div class="yam">
            <div class="yam__wrapper">
                <div class="yam__header">
                    <img class="yam__logo"
                         src="<?php echo \Yandex\Metrika\Helpers::getAssetUrl(GetMessage('YANDEX_METRIKA_LOGO_PATH'), 'bitrix/images'); ?>"
                         alt="Yandex.Metrica">
                </div>
                <div class="yam__description">
					<?php
					printf(
						GetMessage('YANDEX_METRIKA_INTRO'),
						'https://metrika.yandex.ru/list',
						'https://metrika.yandex.ru/'
					);
					?>
                </div>
                <div class="yam__sites">
                    <div class="yam__field">
                        <label class="yam__field-label">
							<?php
							echo GetMessage('YANDEX_METRIKA_SELECT_SITE')
							?>
                        </label>
                        <div class="yam__field-input yam__field-input_sites">
							<?php
							foreach ($sites as $site) {
								$active = $site['LID'] === $currentSiteId ? 'active' : '';
								?>
                                <div class="yam__site <?= $active; ?>"
                                     data-site="<?= htmlspecialchars($site['LID']); ?>"><?= htmlspecialchars(
										$site['NAME']
									); ?></div>
								<?php
							}
							?>
                        </div>
                    </div>
                </div>
				<?php foreach ($sites as $site) {
					$active = $site['LID'] === $currentSiteId ? 'active' : '';
					$fields->setSiteId($site['LID']);
					?>
                    <div class="yam__settings yam__settings_site_<?= htmlspecialchars(
						$site['LID']
					); ?> <?= $active; ?>">
						<?php
						$fields->printGroup('main');
						?>
                        <div class="yam__spoiler">
                            <div class="yam-spoiler yam-spoiler_primary">
                                <div class="yam-spoiler__btn"><?php echo GetMessage(
										'YANDEX_METRIKA_ADDITIONAL_GROUP'
									); ?>
                                    <div class="yam-spoiler__arrow">
                                        <svg width="17" height="9" viewBox="0 0 17 9" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M7.99992 6.60061L14.2929 0.307608C14.3852 0.212098 14.4955 0.135915 14.6175 0.0835057C14.7395 0.0310967 14.8707 0.00351091 15.0035 0.00235709C15.1363 0.00120328 15.268 0.0265052 15.3909 0.0767861C15.5138 0.127067 15.6254 0.20132 15.7193 0.295213C15.8132 0.389106 15.8875 0.500757 15.9377 0.623653C15.988 0.74655 16.0133 0.878229 16.0122 1.01101C16.011 1.14379 15.9834 1.27501 15.931 1.39701C15.8786 1.51902 15.8024 1.62936 15.7069 1.72161L9.06092 8.36861C8.92162 8.508 8.75622 8.61857 8.57417 8.69401C8.39212 8.76946 8.19698 8.80829 7.99992 8.80829C7.80286 8.80829 7.60772 8.76946 7.42567 8.69401C7.24362 8.61857 7.07822 8.508 6.93892 8.36861L0.292919 1.72161C0.110761 1.53301 0.00996641 1.2804 0.0122448 1.01821C0.0145233 0.75601 0.119692 0.505197 0.305101 0.319789C0.490509 0.134381 0.741321 0.0292122 1.00352 0.0269338C1.26571 0.0246553 1.51832 0.12545 1.70692 0.307608L7.99992 6.60061Z"
                                                  fill="currentColor"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="yam-spoiler__content">
									<?php
									$fields->printGroup('additional');
                                    ?>

                                    <div class="yam__description" style="border: none;">
                                        <label class="yam__field-label"><?php echo GetMessage('YANDEX_METRIKA_TARGET_CONFIG'); ?></label>
                                        <?php echo GetMessage('YANDEX_METRIKA_TARGET_CONFIG_DESCR'); ?>
                                    </div>

                                    <div class="ym-events_row ym-events_row-labels">
                                        <div class="yam__field-label" style="display: inline-block; width: 200px; margin-right: 20px">
                                            <?php echo GetMessage('YANDEX_METRIKA_TARGET'); ?>
                                        </div>
                                        <div class="yam__field-label" style="display: inline-block; width: 260px; margin-right: 20px">
                                            <?php echo GetMessage('YANDEX_METRIKA_SELECTOR'); ?>
                                        </div>
                                        <div class="yam__field-label">
                                            <?php echo GetMessage('YANDEX_METRIKA_EVENT_DESCR_H'); ?>
                                        </div>
                                    </div>

                                    <div class="ym-events_row-tempate-wrapper">

                                        <div class="ym-events_row ym-events_row-tempate">
                                            <div class="ym-events_field_wrapper ym-events_field_wrapper_param" style="display: inline-block; width: 200px; margin-right: 20px">
                                                <select class="select2-param" name="ym-events[param][<?=$site['LID']?>][]">
                                                    <option value="" data-title="title text">Параметр цели</option>
                                                    <?foreach ($arEventsParams as $arEventsParam):?>
                                                        <option value="<?=$arEventsParam["code"]?>" data-title="<?=$arEventsParam["name"]?>"><?=$arEventsParam["code"]?></option>
                                                    <?endforeach?>
                                                </select>
                                            </div>

                                            <div class="ym-events_field_wrapper ym-events_field_wrapper_selector" style="display: inline-block; width: 260px; margin-right: 20px">
                                                <div class="yam-custom-input">
                                                    <input type="text" class="input-selector" name="ym-events[selector][<?=$site['LID']?>][]" placeholder="<?php echo GetMessage('YANDEX_METRIKA_SELECTOR_NAME'); ?>">

                                                    <img class="custom-input__icon" src="/bitrix/modules/yandex.metrika/install/images/info.svg" alt="">
                                                    <div class="custom-input__message">
												<span>
													<?php echo GetMessage('YANDEX_METRIKA_SELECTOR_DESCR_H'); ?>
												</span>
                                                        <span>
                                                    <?php echo GetMessage('YANDEX_METRIKA_SELECTOR_DESCR'); ?>
												</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="ym-events_field_wrapper ym-events_field_wrapper_event  autocomplete-select yam-custom-input" style="display: inline-block; width: 160px; margin-right: 20px">
                                                <select class="select2-event" name="ym-events[event][<?=$site['LID']?>][]">
                                                    <option value="" data-title=""><?php echo GetMessage('YANDEX_METRIKA_EVENT_DESCR_H'); ?></option>
                                                    <option value="click" data-title="<?php echo GetMessage('YANDEX_METRIKA_EVENT_CLICK'); ?>">click</option>
                                                    <option value="submit" data-title="<?php echo GetMessage('YANDEX_METRIKA_EVENT_SUBMIT'); ?>">submit</option>
                                                    <option value="mouseover" data-title="<?php echo GetMessage('YANDEX_METRIKA_EVENT_MOUSEOVER'); ?>">mouseover</option>
                                                    <option value="mouseout" data-title="<?php echo GetMessage('YANDEX_METRIKA_EVENT_MOUSEOUT'); ?>">mouseout</option>
                                                    <option value="focus" data-title="<?php echo GetMessage('YANDEX_METRIKA_EVENT_FOCUS'); ?>">focus</option>
                                                    <option value="blur" data-title="<?php echo GetMessage('YANDEX_METRIKA_EVENT_BLUR'); ?>">blur</option>
                                                    <option value="change" data-title="<?php echo GetMessage('YANDEX_METRIKA_EVENT_CHANGE'); ?>">change</option>
                                                </select>

                                                <img class="custom-input__icon" src="/bitrix/modules/yandex.metrika/install/images/info.svg" alt="">
                                                <div class="custom-input__message">
												<span>
													<?php echo GetMessage('YANDEX_METRIKA_EVENT_DESCR_H'); ?>
												</span>
                                                    <span>
                                                    <?php echo GetMessage('YANDEX_METRIKA_EVENT_DESCR'); ?>
												</span>
                                                </div>

                                            </div>
                                            <div class="ym-events_field_wrapper" style="display: inline-block;">
                                                <button type="button" class="ym-events_remove js_ym_events_remove"></button>
                                            </div>

                                        </div>
                                    </div>

                                    <br/>
                                    <div class="ym-events_rows">
                                    </div>


                                    <div class="yam-repeater-field__add-wrap">
                                        <button type="button" class="yam-events_add-btn button button_default" data-siteid="<?=$site['LID']?>"><?= GetMessage('YANDEX_METRIKA_ADD'); ?></button>
                                    </div>
                                    <div class="yam__field">
                                        <div class="yam__field-label">
                                            <label><?php echo GetMessage('YANDEX_METRIKA_DATALAYER_NAME'); ?></label>
                                        </div>
                                        <div class="yam__field-text yam__field-text_before">
                                            <?php echo GetMessage('YANDEX_METRIKA_DATALAYER_DESCRIPTION'); ?>
                                        </div>
                                        <div class="yam__field-input">
                                            <input name="ym-datalayervar[<?=$site['LID']?>]" type="text" class="yam-repeater-field__input text-field text-field_fill" value="<?=$ardatalayervar[$site['LID']];?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            


                        </div>
                    </div>
				<?php } ?>


                <div class="yam__submit-buttons">
                    <button type="submit" name="save"
                            class="button button_primary"><?= GetMessage('YANDEX_METRIKA_SAVE'); ?></button>
                    <div class="button button_transparent" data-toggle=".yam__logs">
						<?php echo GetMessage('YANDEX_METRIKA_VIEW_LOGS'); ?>
                    </div>
                </div>

                <div class="yam__logs">
                    <div class="info-block info-block_logs">
                        <button type="button" class="yam-logs_copy"></button>
						<span><?= \Yandex\Metrika\Checker::getLogs(); ?><br>-----------<br>php:<?=phpversion();?>; BX:<?=SM_VERSION?>; <?php echo GetMessage('YANDEX_METRIKA_MODULE'); ?>:<?=$module_version;?></span>
                    </div>
                </div>
            </div>
        </div>

    </form>
<? } ?>