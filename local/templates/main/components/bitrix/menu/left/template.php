<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php if (!empty($arResult)):?>
    <ul class="page-nav__list">
        <?php foreach($arResult as $arItem):?>
            <li class="page-nav__item">
                <?php if($arItem["SELECTED"]): ?>
                    <div class="page-nav__link active"><?=$arItem["TEXT"]?></div>
                <?php else:?>
                    <a
                        href="<?=$arItem["LINK"]?>"
                        class="page-nav__link"
                        title="<?=$arItem["TEXT"]?>"
                    ><?=$arItem["TEXT"]?></a>
                <?php endif; ?>
            </li>
        <?php endforeach?>
    </ul>
<?php endif;