<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService\Sender\Base;
use Imedia\Sms\Events;
use Imedia\Sms\Provider;

Loc::loadMessages(__FILE__);

try {
    Loader::includeModule('messageservice');
    Loader::includeSharewareModule('imedia.sms');
} catch (LoaderException $e) {
}

$context = Context::getCurrent();
$post = $context->getRequest()->getPostList()->toArray();

if (is_array($post['settings']) && (count($post['settings']) > 0)) {
    foreach ($post['settings'] as $name => $val) {
        if (isset($val)) {
            Option::set('imedia.sms', $name, $val);
        } else {
            Option::delete('imedia.sms', ['name' => $name]);
        }
    }
}

$providers = [];
$services = new Events();
foreach ($services->registerProvider() as $provider) {
    if ($provider instanceof Provider\HasPreferencesInterface) {
        $providers[] = $provider;
    }
}

$tabs = [];
/** @var $provider Base */
foreach ($providers as $provider) {
    $tabs[] = [
        'DIV' => $provider->getId(),
        'TAB' => $provider->getName(),
        'TITLE' => Loc::getMessage(
            'IMEDIA_SMS_OPTIONS_TAB_TITLE',
            [
                '#NAME#' => $provider->getName(),
                '#SHORT_NAME#' => $provider->getShortName(),
            ]
        )
    ];
}

$tabControl = new CAdminTabControl('tabControl', $tabs);
$tabControl->Begin();

echo '<form name="imedia.sms" method="POST" action="'.$APPLICATION->GetCurPage(
    ).'?mid=imedia.sms&lang='.LANGUAGE_ID.'" enctype="multipart/form-data">'.bitrix_sessid_post();

/** @var $provider Base */
foreach ($providers as $provider) {
    $tabControl->BeginNextTab();
    ?>
    <?php if ($provider instanceof Provider\HasBalanceInterface && $provider->canUse()):
        $balance = $provider->getBalance();
        ?>
        <tr>
            <td colspan="2">
                <?php
                $message = new CAdminMessage(Loc::getMessage('IMEDIA_SMS_OPTIONS_BALANCE', ['#SUM#' => $balance]));
                $message->ShowNote(Loc::getMessage('IMEDIA_SMS_OPTIONS_BALANCE', ['#SUM#' => $balance]));
                ?>
            </td>
        </tr>
    <?php endif ?>
    <?php if($provider instanceof Provider\HasPreferencesInterface): ?>
        <tr class="heading">
            <td colspan="2"><?= Loc::getMessage('IMEDIA_SMS_OPTIONS_CONNECTION') ?></td>
        </tr>
        <?php foreach($provider->getOptions() as $option):
            $field = $provider->getId() . '_' . $option['ID'];
            ?>
            <tr>
                <td width="40%" nowrap="" class="adm-detail-content-cell-l">
                    <label for="imedia_sms_<?= $field ?>"><?= $option['TITLE'] ?></label>
                </td>
                <td width="60%" class="adm-detail-content-cell-r">
                    <input
                            type="text"
                            id="imedia_sms_<?= $field ?>"
                            name="settings[<?= $field ?>]"
                            value="<?= Option::get('imedia.sms', $field) ?>"
                    />
                </td>
            </tr>
        <?php endforeach ?>
    <?php endif ?>
    <?php if ($provider instanceof Provider\HasSenderInterface):
        $senderField = $provider->getId().'_sender';
        ?>
        <tr>
            <td width="40%" nowrap="" class="adm-detail-content-cell-l">
                <label for="imedia_sms_<?= $senderField ?>"><?= $provider->getSenderTitle() ?></label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="text" id="imedia_sms_<?= $senderField ?>"
                       name="settings[<?= $senderField ?>]"
                       value="<?= Option::get('imedia.sms', $senderField) ?>"/>
            </td>
        </tr>
    <?php endif ?>
    <?php
}

$tabControl->End();

$tabControl->Buttons();

echo '<input type="hidden" name="update" value="Y" />';
echo '<input type="submit" name="save" value="'.Loc::getMessage('IMEDIA_SMS_OPTIONS_SAVE').'" class="adm-btn-save" />';
echo '<input type="reset" name="reset" value="'.Loc::getMessage('IMEDIA_SMS_OPTIONS_RESET').'" />';
echo '</form>';
$tabControl->End();
