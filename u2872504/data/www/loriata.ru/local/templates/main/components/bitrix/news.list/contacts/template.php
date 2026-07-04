<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

$mapConfig = [
    'features' => [],
    'selected' => null
];
?>
<?php if(!empty($arResult['ITEMS'])):?>
    <div class="tabs contacts__tabs tabs-parent">
        <?php foreach($arResult['ITEMS'] as $key => $arItem):?>
            <a
                href="#<?=$arItem['ID']?>"
                class="tab contacts__tab<?=($key === 0) ? ' tab--active' : ''?>"
                title="<?=$arItem['NAME']?>"
            ><?=$arItem['NAME']?></a>
        <?php endforeach?>
    </div>
    <?php foreach($arResult['ITEMS'] as $key => $arItem):

        if($key === 0){
            $mapConfig['selected'] = $arItem['ID'];
        }

        if($arItem['PROPERTIES']['MAP']['VALUE']){

            $mapConfig['features'][] = [
                'type' => 'Feature',
                'id' => 'point_' . $arItem['ID'],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => explode(',', $arItem['PROPERTIES']['MAP']['VALUE'])
                ],
                'options' => [
                    'iconLayout' => 'default#image',
                    'iconImageSize' => [68, 55],
                    'iconOffset' => [-10, -25]
                ],
                'properties' => [
                    'itemId' => $arItem['ID']
                ]
            ];

        }

        ?>
        <div id="<?=$arItem['ID']?>" class="tabs-content<?=($key === 0) ? ' tabs-content--active' : ''?>">
            <div class="contacts__grid" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                <?php if(
                        $arItem['PROPERTIES']['CALLCENTER_PHONE']['VALUE']
                        || $arItem['PROPERTIES']['CALLCENTER_EMAIL']['VALUE']
                ):?>
                    <section class="contact-item contacts__item">
                        <div class="contact-item__title"><?=Loc::getMessage('T_CONTACTS_CALLCENTER')?></div>
                        <div class="contact-item__content">
                            <?php if($arItem['PROPERTIES']['CALLCENTER_PHONE']['VALUE']):?>
                                <p>
                                    <?=Loc::getMessage('T_CONTACTS_PHONE')?>
                                    <?php foreach($arItem['PROPERTIES']['CALLCENTER_PHONE']['VALUE'] as $value):
                                        $phone = preg_replace('/[^\d+]/', '', $value);
                                        ?>
                                        <a
                                            href="tel:<?=$phone?>"
                                            title="<?=$value?>"
                                            target="_blank"
                                        ><?=$value?></a>
                                    <?php endforeach?>
                                </p>
                            <?php endif?>
                            <?php if($arItem['PROPERTIES']['CALLCENTER_EMAIL']['VALUE']):?>
                                <p>
                                    <?=Loc::getMessage('T_CONTACTS_EMAIL')?>
                                    <?php foreach($arItem['PROPERTIES']['CALLCENTER_EMAIL']['VALUE'] as $value):?>
                                        <a
                                            href="mailto:<?=$value?>"
                                            title="<?=$value?>"
                                            target="_blank"
                                        ><?=$value?></a>
                                    <?php endforeach?>
                                </p>
                            <?php endif?>
                        </div>
                    </section>
                <?php endif?>
                <?php if($arItem['DISPLAY_PROPERTIES']['HOURS']['DISPLAY_VALUE']):?>
                    <section class="contact-item contacts__item">
                        <div class="contact-item__title"><?=Loc::getMessage('T_CONTACTS_HOURS')?></div>
                        <div class="contact-item__content">
                            <?=$arItem['DISPLAY_PROPERTIES']['HOURS']['DISPLAY_VALUE']?>
                        </div>
                    </section>
                <?php endif?>
                <?php if($arItem['DISPLAY_PROPERTIES']['ADDRESS']['DISPLAY_VALUE']):?>
                    <section class="contact-item contacts__item">
                        <div class="contact-item__title"><?=Loc::getMessage('T_CONTACTS_ADDRESS')?></div>
                        <div class="contact-item__content contact-item__address">
                            <?=$arItem['DISPLAY_PROPERTIES']['ADDRESS']['DISPLAY_VALUE']?>
                        </div>
                    </section>
                <?php endif?>
                <?php if($arItem['DISPLAY_PROPERTIES']['REQUISITES']['DISPLAY_VALUE']):?>
                    <section class="contact-item contacts__item">
                        <div class="contact-item__title"><?=Loc::getMessage('T_CONTACTS_REQUISITES')?></div>
                        <div class="contact-item__content">
                            <?=$arItem['DISPLAY_PROPERTIES']['REQUISITES']['DISPLAY_VALUE']?>
                        </div>
                    </section>
                <?php endif?>
            </div>
        </div>
    <?php endforeach?>
    <contacts
            default-selected="<?=$mapConfig['selected']?>"
            :default-features='<?=Json::encode($mapConfig['features'])?>'
    ></contacts>
<?php endif;