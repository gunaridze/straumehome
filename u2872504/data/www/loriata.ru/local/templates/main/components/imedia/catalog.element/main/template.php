<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Imedia\Main\Helpers\Catalog\Property;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);

$mainId = $this->GetEditAreaId($arResult['ID']);
$obName = $templateData['JS_OBJ'] = 'ob' . preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);
$itemIds = [
    'ID' => $mainId
];

$name = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
    : $arResult['NAME'];

$title = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE']
    : $arResult['NAME'];

$alt = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT']
    : $arResult['NAME'];

$haveOffers = !empty($arResult['OFFERS']);
if ($haveOffers) {
    $actualItem = $arResult['OFFERS'][$arResult['OFFERS_SELECTED']] ?? reset($arResult['OFFERS']);
} else {
    $actualItem = $arResult;
}

$price = $actualItem['OPTIMAL_PRICE'];
$showDiscount = $price['PERCENT'] > 0;

ob_start() ?>

<div class="product-card__inner" id="<?= $itemIds['ID'] ?>" itemscope itemtype="http://schema.org/Product">
    <div class="product-card__slider swiper">
        <div class="swiper-wrapper">
            <?php foreach ($arResult['GALLERY'] as $arItem):
                if ($arItem['CONTENT_TYPE'] == 'video'):?>
                    <div class="swiper-slide product-card__slider-item">
                        <iframe src="<?= $arItem['SRC'] ?>" frameborder="0" allow="autoplay; fullscreen"
                                allowfullscreen=""></iframe>
                    </div>
                <?php else: ?>
                    <a data-fancybox="gallery" href="<?= $arItem['RESIZE'][0]['SIZES']['ORIGINAL'] ?>"
                       class="swiper-slide product-card__slider-item">
                        <picture>
                            <img src="<?= $arItem['RESIZE'][0]['SIZES']['DEFAULT'] ?>"
                                 alt="<?= $alt ?>"
                                 width="<?= $arItem['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'] ?>"
                                 height="<?= $arItem['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT'] ?>"
                                 srcset="<?= $arItem['RESIZE'][0]['SIZES']['DEFAULT_2X'] ?> 2x"
                                 loading="lazy" itemprop="image">
                        </picture>
                    </a>
                <?php endif ?>
            <?php endforeach ?>
        </div>
        <div class="swiper-button-prev product-card__slider-arrow product-card__slider-arrow--prev">
            <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11.3426 21.4595C11.5504 21.4595 11.7582 21.3829 11.9223 21.2188C12.2395 20.9017 12.2395 20.3767 11.9223 20.0595L5.86289 14.0001L11.9223 7.94072C12.2395 7.62354 12.2395 7.09854 11.9223 6.78135C11.6051 6.46416 11.0801 6.46416 10.7629 6.78135L4.12382 13.4204C3.80664 13.7376 3.80664 14.2626 4.12382 14.5798L10.7629 21.2188C10.927 21.3829 11.1348 21.4595 11.3426 21.4595Z"
                      fill="#101112"/>
                <path d="M4.88906 14.8203H23.2969C23.7453 14.8203 24.1172 14.4484 24.1172 14C24.1172 13.5516 23.7453 13.1797 23.2969 13.1797H4.88906C4.44062 13.1797 4.06875 13.5516 4.06875 14C4.06875 14.4484 4.44062 14.8203 4.88906 14.8203Z"
                      fill="#101112"/>
            </svg>
        </div>
        <div class="swiper-button-next product-card__slider-arrow product-card__slider-arrow--next">
            <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16.6574 21.4595C16.4496 21.4595 16.2418 21.3829 16.0777 21.2188C15.7605 20.9017 15.7605 20.3767 16.0777 20.0595L22.1371 14.0001L16.0777 7.94072C15.7605 7.62354 15.7605 7.09854 16.0777 6.78135C16.3949 6.46416 16.9199 6.46416 17.2371 6.78135L23.8762 13.4204C24.1934 13.7376 24.1934 14.2626 23.8762 14.5798L17.2371 21.2188C17.073 21.3829 16.8652 21.4595 16.6574 21.4595Z"
                      fill="#101112"/>
                <path d="M23.1109 14.8203H4.70312C4.25469 14.8203 3.88281 14.4484 3.88281 14C3.88281 13.5516 4.25469 13.1797 4.70312 13.1797H23.1109C23.5594 13.1797 23.9312 13.5516 23.9312 14C23.9312 14.4484 23.5594 14.8203 23.1109 14.8203Z"
                      fill="#101112"/>
            </svg>
        </div>
    </div>
    <div class="product-card__content">
        <div class="product-card__top-wrap">
            <h1 class="product-card__title" itemprop="name">
                <?= $arResult['NAME'] ?>
            </h1>
            <?php if (!empty($arResult['BRAND'])): ?>
                <img class="product-card__logo"
                     src="<?= $arResult['BRAND']['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT'] ?>"
                     alt="<?= $arResult['BRAND']['NAME'] ?>"
                     width="<?= $arResult['BRAND']['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'] ?>"
                     height="<?= $arResult['BRAND']['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT'] ?>"
                     srcset="<?= $arResult['BRAND']['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X'] ?> 2x"
                     loading="lazy">
            <?php endif ?>
        </div>
        <div class="product-card__prices-wrap">
            <div class="prices product-card__prices">
                <div class="old-price product-card__old-price"<?= ($showDiscount ? '' : ' style="display: none;"') ?>>
                    <?= ($showDiscount ? $price['PRINT_BASE_PRICE'] : '') ?>
                </div>
                <div class="new-price">
                    <?= $price['PRINT_PRICE'] ?>
                </div>
            </div>
            <div class="labels product-card__labels">
                <span class="label label--discount"<?= ($showDiscount ? '' : ' style="display: none;"') ?>>
                    <?= ($showDiscount ? $price['PERCENT'] . ' &#37' : '') ?>
                </span>
                <?php foreach($arResult['LABELS'] as $arLabel): ?>
                    <span class="label label--<?= $arLabel['code'] ?>">
                        <?= $arLabel['label'] ?>
                    </span>
                <?php endforeach ?>
            </div>
        </div>
        <section class="product-card__section">
            <div class="product-card__section-title">
                <?= Loc::getMessage('CT_BCE_CATALOG_ABOUT_PRODUCTS') ?>
            </div>
            <?php if (!empty($arResult['DETAIL_TEXT'])): ?>
                <div itemprop="description">
                    <?= $arResult['MIN_DETAIL'] ?: $arResult['DETAIL_TEXT'] ?>
                </div>
            <?php endif ?>
            <?php if (!empty($arResult['MIN_DETAIL'])): ?>
                <button class="scroll-link" data-link="details" type="button">
                    <?= Loc::getMessage('CT_BCE_CATALOG_MORE') ?>
                </button>
            <?php endif ?>
        </section>
        <?php if (!empty($arResult['DELIVERY_METHODS'])): ?>
            <section class="product-card__section product-card-delivery">
                <div class="product-card__section-title">
                    <?= Loc::getMessage('CT_BCE_CATALOG_DELIVERY_METHODS') ?>
                </div>
                <div class="product-card-delivery__row">
                    <?php foreach ($arResult['DELIVERY_METHODS'] as $arItem): ?>
                        <div class="product-card-delivery__item">
                            <?php if (!empty($arItem['FILE'])): ?>
                                <img class="product-card-delivery__item-img" src="<?= $arItem['FILE'] ?>"
                                     alt="<?= $arItem['NAME'] ?>" loading="lazy">
                            <?php endif ?>
                            <div class="product-card-delivery__item-text">
                                <?= $arItem['NAME'] . ' - ' . $arItem['DESCRIPTION'] ?>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
                <button class="scroll-link" data-link="delivery" type="button">
                    <?= Loc::getMessage('CT_BCE_CATALOG_MORE') ?>
                </button>
            </section>
        <?php endif ?>
        <form class="product-card-form">
            <?php if (!empty($arResult['LINK_PRODUCTS'])): ?>
                <fieldset class="product-color product-card-form__color">
                    <div class="product-color__row">
                        <legend class="product-card-form__title product-color__title">
                            <?= $arResult['PROPERTIES']['COLOR_REF']['NAME'] ?>:
                        </legend>
                        <?php foreach ($arResult['LINK_PRODUCTS'] as $arElement):?>
                            <?php if((int) $arElement['ID'] === (int) $arResult['ID']):?>
                                <div
                                        class="product-color__item"
                                        href="<?= $arElement['DETAIL_PAGE_URL'] ?>"
                                        title="<?= $arElement['NAME'] ?>"
                                >
                                    <span class="radio-style radio-style--checked" style="border-color: #9b9b9b;">
                                        <span style="background: <?= (!empty($arElement['COLOR_PICTURE']) ? 'url(\'' . $arElement['COLOR_PICTURE'] . '\') no-repeat; background-size: 100%;' : '#9b9b9b') ?>"></span>
                                    </span>
                                </div>
                            <?php else:?>
                                <a
                                    class="product-color__item"
                                    href="<?= $arElement['DETAIL_PAGE_URL'] ?>"
                                    title="<?= $arElement['NAME'] ?>"
                                >
                                    <span class="radio-style" style="border-color: #9b9b9b;">
                                        <span style="background: <?= (!empty($arElement['COLOR_PICTURE']) ? 'url(\'' . $arElement['COLOR_PICTURE'] . '\') no-repeat; background-size: 100%;' : '#9b9b9b') ?>"></span>
                                    </span>
                                </a>
                            <?php endif?>

                        <?php endforeach ?>
                    </div>
                </fieldset>
            <?php endif ?>
            <?php if ($haveOffers):
                $skuPropSize = $arResult['SKU_PROPS'][Property::getCode('SIZE')];
                if (!empty($skuPropSize) && $arResult['OFFERS_PROP'][$skuPropSize['CODE']]):?>
                    <div class="product-sizes">
                        <div class="product-card-form__title product-sizes__title">
                            <?= $skuPropSize['NAME'] ?>:
                        </div>
                        <div class="product-sizes__row">
                            <?php foreach ($skuPropSize['VALUES'] as $value):
                                if ($value['ID'] == 0)
                                    continue; ?>
                                <label class="product-sizes__item">
                                    <input class="radio-box" type="radio" name="PROP_<?= $skuPropSize['ID'] ?>"
                                           value="<?= $value['ID'] ?>">
                                    <span class="radio-text">
                                        <?= $value['NAME'] ?>
                                    </span>
                                </label>
                            <?php endforeach ?>
                        </div>
                        <a href="javascript:void(0)" class="popup-link popup-link__size-table">
                            <?= Loc::getMessage('CT_BCE_CATALOG_TABLE_SIZES') ?>
                        </a>
                    </div>
                <?php endif ?>
            <?php endif ?>
            <div class="product-card-form__buttons">
                <?php if($haveOffers): ?>
                    <a
                        type="button"
                        class="btn product-card-form__btn product-cart-btn"
                        href="#choose-size"
                        data-fancybox
                    >
                        <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.9933 15.1668H4.99996C3.85329 15.1668 2.99329 14.8602 2.45996 14.2535C1.92662 13.6468 1.71995 12.7668 1.85995 11.6268L2.45996 6.62683C2.63329 5.1535 3.00663 3.8335 5.60662 3.8335H10.4066C13 3.8335 13.3733 5.1535 13.5533 6.62683L14.1533 11.6268C14.2866 12.7668 14.0866 13.6535 13.5533 14.2535C13 14.8602 12.1466 15.1668 10.9933 15.1668Z"
                                  fill="white"></path>
                            <path d="M10.6673 5.8335C10.394 5.8335 10.1673 5.60683 10.1673 5.3335V3.00016C10.1673 2.28016 9.72065 1.8335 9.00065 1.8335H7.00065C6.28065 1.8335 5.83398 2.28016 5.83398 3.00016V5.3335C5.83398 5.60683 5.60732 5.8335 5.33398 5.8335C5.06065 5.8335 4.83398 5.60683 4.83398 5.3335V3.00016C4.83398 1.72683 5.72732 0.833496 7.00065 0.833496H9.00065C10.274 0.833496 11.1673 1.72683 11.1673 3.00016V5.3335C11.1673 5.60683 10.9407 5.8335 10.6673 5.8335Z"
                                  fill="white"></path>
                        </svg>
                        <?= Loc::getMessage('CT_BCE_CATALOG_ADD_TO_BASKET') ?>
                    </a>
                    <?php foreach($arResult['OFFERS'] as $arOffer): ?>
                        <add-to-cart product-id="<?= $arOffer['ID'] ?>"
                                     class="btn product-card-form__btn product-cart-btn product-cart-btn__hidden"
                                     data-id="<?= $arOffer['ID'] ?>"<?= (!$arOffer['CAN_BUY'] ? ' disabled' : '') ?>>
                            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.9933 15.1668H4.99996C3.85329 15.1668 2.99329 14.8602 2.45996 14.2535C1.92662 13.6468 1.71995 12.7668 1.85995 11.6268L2.45996 6.62683C2.63329 5.1535 3.00663 3.8335 5.60662 3.8335H10.4066C13 3.8335 13.3733 5.1535 13.5533 6.62683L14.1533 11.6268C14.2866 12.7668 14.0866 13.6535 13.5533 14.2535C13 14.8602 12.1466 15.1668 10.9933 15.1668Z"
                                      fill="white"></path>
                                <path d="M10.6673 5.8335C10.394 5.8335 10.1673 5.60683 10.1673 5.3335V3.00016C10.1673 2.28016 9.72065 1.8335 9.00065 1.8335H7.00065C6.28065 1.8335 5.83398 2.28016 5.83398 3.00016V5.3335C5.83398 5.60683 5.60732 5.8335 5.33398 5.8335C5.06065 5.8335 4.83398 5.60683 4.83398 5.3335V3.00016C4.83398 1.72683 5.72732 0.833496 7.00065 0.833496H9.00065C10.274 0.833496 11.1673 1.72683 11.1673 3.00016V5.3335C11.1673 5.60683 10.9407 5.8335 10.6673 5.8335Z"
                                      fill="white"></path>
                            </svg>
                            <?= Loc::getMessage('CT_BCE_CATALOG_ADD_TO_BASKET') ?>
                        </add-to-cart>
                    <?php endforeach ?>
                <?php else: ?>
                    <add-to-cart product-id="<?= $actualItem['ID'] ?>"
                                 class="btn product-card-form__btn product-cart-btn"<?= (!$actualItem['CAN_BUY'] ? ' disabled' : '') ?>>
                        <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.9933 15.1668H4.99996C3.85329 15.1668 2.99329 14.8602 2.45996 14.2535C1.92662 13.6468 1.71995 12.7668 1.85995 11.6268L2.45996 6.62683C2.63329 5.1535 3.00663 3.8335 5.60662 3.8335H10.4066C13 3.8335 13.3733 5.1535 13.5533 6.62683L14.1533 11.6268C14.2866 12.7668 14.0866 13.6535 13.5533 14.2535C13 14.8602 12.1466 15.1668 10.9933 15.1668Z"
                                  fill="white"></path>
                            <path d="M10.6673 5.8335C10.394 5.8335 10.1673 5.60683 10.1673 5.3335V3.00016C10.1673 2.28016 9.72065 1.8335 9.00065 1.8335H7.00065C6.28065 1.8335 5.83398 2.28016 5.83398 3.00016V5.3335C5.83398 5.60683 5.60732 5.8335 5.33398 5.8335C5.06065 5.8335 4.83398 5.60683 4.83398 5.3335V3.00016C4.83398 1.72683 5.72732 0.833496 7.00065 0.833496H9.00065C10.274 0.833496 11.1673 1.72683 11.1673 3.00016V5.3335C11.1673 5.60683 10.9407 5.8335 10.6673 5.8335Z"
                                  fill="white"></path>
                        </svg>
                        <?= Loc::getMessage('CT_BCE_CATALOG_ADD_TO_BASKET') ?>
                    </add-to-cart>
                <?php endif ?>
                <favorite-button product-id="<?= $arResult['ID'] ?>"
                                 class="btn product-card-form__btn product-favorite-btn btn--border">
                    <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.99984 18.0418C9.7415 18.0418 9.4915 18.0085 9.28317 17.9335C6.09984 16.8418 1.0415 12.9668 1.0415 7.24183C1.0415 4.32516 3.39984 1.9585 6.29984 1.9585C7.70817 1.9585 9.02484 2.5085 9.99984 3.49183C10.9748 2.5085 12.2915 1.9585 13.6998 1.9585C16.5998 1.9585 18.9582 4.3335 18.9582 7.24183C18.9582 12.9752 13.8998 16.8418 10.7165 17.9335C10.5082 18.0085 10.2582 18.0418 9.99984 18.0418ZM6.29984 3.2085C4.0915 3.2085 2.2915 5.01683 2.2915 7.24183C2.2915 12.9335 7.7665 16.1002 9.6915 16.7585C9.8415 16.8085 10.1665 16.8085 10.3165 16.7585C12.2332 16.1002 17.7165 12.9418 17.7165 7.24183C17.7165 5.01683 15.9165 3.2085 13.7082 3.2085C12.4415 3.2085 11.2665 3.80016 10.5082 4.82516C10.2748 5.14183 9.7415 5.14183 9.50817 4.82516C8.73317 3.79183 7.5665 3.2085 6.29984 3.2085Z"
                              fill="#101112"></path>
                    </svg>
                    <?= Loc::getMessage('CT_BCE_CATALOG_ADD_TO_FAVORITE') ?>
                </favorite-button>
            </div>
        </form>
    </div>
    <div class="product-card__links">
        <ul class="product-links">
            <?php if (!empty($arResult['BRAND'])): ?>
                <li class="product-links__item">
                    <a href="<?= str_replace('/catalog/', $arResult['BRAND']['DETAIL_PAGE_URL'], $arResult['SECTION']['SECTION_PAGE_URL']) ?>"
                       class="product-links__item-link">
                        <div class="product-links__item-img">
                            <img
                                    src="<?= $arResult['BRAND']['PICTURE_ROUND']['RESIZE'][0]['SIZES']['DEFAULT'] ?>"
                                    alt="<?= $arResult['BRAND']['NAME'] ?>"
                                    width="<?= $arResult['BRAND']['PICTURE_ROUND']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'] ?>"
                                    height="<?= $arResult['BRAND']['PICTURE_ROUND']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT'] ?>"
                                    srcset="<?= $arResult['BRAND']['PICTURE_ROUND']['RESIZE'][0]['SIZES']['DEFAULT_2X'] ?> 2x"
                                    loading="lazy">
                        </div>
                        <span class="product-links__item-title">
                            <?= Loc::getMessage('CT_BCE_CATALOG_ALL') . '&nbsp' . mb_strtolower($arResult['SECTION']['NAME']) . '&nbsp' . mb_strtoupper($arResult['BRAND']['NAME']) ?>
                        </span>
                        <span class="product-links__item-subtitle">
                            <?= Loc::getMessage('CT_BCE_CATALOG_SECTION_BRAND') ?>
                        </span>
                    </a>
                </li>
                <li class="product-links__item">
                    <a href="<?= $arResult['BRAND']['DETAIL_PAGE_URL'] ?>" class="product-links__item-link">
                        <div class="product-links__item-img">
                            <img
                                    src="<?= $arResult['BRAND']['PICTURE_ROUND']['RESIZE'][0]['SIZES']['DEFAULT'] ?>"
                                    alt="<?= $arResult['BRAND']['NAME'] ?>"
                                    width="<?= $arResult['BRAND']['PICTURE_ROUND']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'] ?>"
                                    height="<?= $arResult['BRAND']['PICTURE_ROUND']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT'] ?>"
                                    srcset="<?= $arResult['BRAND']['PICTURE_ROUND']['RESIZE'][0]['SIZES']['DEFAULT_2X'] ?> 2x"
                                    loading="lazy">
                        </div>
                        <span class="product-links__item-title">
                            <?= Loc::getMessage('CT_BCE_CATALOG_ALL_PRODUCTS') . '&nbsp' . mb_strtoupper($arResult['BRAND']['NAME']) ?>
                        </span>
                        <span class="product-links__item-subtitle">
                            <?= Loc::getMessage('CT_BCE_CATALOG_BRAND') ?>
                        </span>
                    </a>
                </li>
            <?php endif ?>
            <li class="product-links__item">
                <a href="<?= $arResult['SECTION']['SECTION_PAGE_URL'] ?>" class="product-links__item-link">
                    <div class="product-links__item-img product-links__item-img--cover">
                        <img
                                src="<?= $arResult['SECTION']['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT'] ?>"
                                alt="<?= $arResult['SECTION']['NAME'] ?>"
                                width="<?= $arResult['SECTION']['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'] ?>"
                                height="<?= $arResult['SECTION']['PICTURE']['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT'] ?>"
                                srcset="<?= $arResult['SECTION']['PICTURE']['RESIZE'][0]['SIZES']['DEFAULT_2X'] ?> 2x"
                                loading="lazy">
                    </div>
                    <span class="product-links__item-title">
                        <?= Loc::getMessage('CT_BCE_CATALOG_ALL') . '&nbsp' . mb_strtolower($arResult['SECTION']['NAME']) ?>
                    </span>
                    <span class="product-links__item-subtitle">
                        <?= Loc::getMessage('CT_BCE_CATALOG_SECTION') ?>
                    </span>
                </a>
            </li>
            <li class="product-links__item">
                <a href="javascript:;" class="product-links__item-link">
                    <div class="product-links__item-img">
                        <img
                                src="<?= SITE_TEMPLATE_PATH ?>/assets/images/icons/certificate.svg" alt="certificate"
                                loading="lazy">
                    </div>
                    <span class="product-links__item-title">
                        <?= Loc::getMessage('CT_BCE_CATALOG_ORIGINAL_BRANDS') ?>
                    </span>
                    <span class="product-links__item-subtitle">
                        <?= Loc::getMessage('CT_BCE_CATALOG_WARRANTY') ?>
                    </span>
                </a>
            </li>
        </ul>
    </div>
    <div class="product-card__info">
        <div class="product-card__tabs-wrap">
            <div class="product-card__tabs tabs-parent">
                <a href="#details" class="tab product-card__tab tab--active">
                    <?= Loc::getMessage('CT_BCE_CATALOG_DETAIL') ?>
                </a>
                <a href="#delivery" class="tab product-card__tab">
                    <?= $arResult['PROPERTIES']['DELIVERY']['NAME'] ?>
                </a>
                <a href="#payment" class="tab product-card__tab">
                    <?= $arResult['PROPERTIES']['PAYMENT']['NAME'] ?>
                </a>
                <a href="#shops-availability" class="tab product-card__tab" style="display: none;">
                    <?= Loc::getMessage('CT_BCE_CATALOG_SHOPS_AVAILABILITY') ?>
                </a>
                <?php if (!empty($arResult['PROPERTIES']['VIDEO']['VALUE'])): ?>
                    <a href="#video" class="tab product-card__tab">
                        <?= $arResult['PROPERTIES']['VIDEO']['NAME'] ?>
                    </a>
                <?php endif ?>
            </div>
        </div>
        <div id="details" class="tabs-content product-details tabs-content--active">
            <?php if (!empty($arResult['DETAIL_TEXT'])): ?>
                <section class="product-details__section">
                    <div itemprop="description">
                        <?= $arResult['DETAIL_TEXT']?>
                    </div>
                </section>
            <?php endif ?>
            <?php if (!empty($actualItem['PROPS_GROUPS'])):
                foreach ($actualItem['PROPS_GROUPS'] as $arGroup):?>
                    <section class="product-details__section">
                        <div class="product-details__section-top">
                            <div class="product-details__section-title">
                                <?= $arGroup['NAME'] ?>
                            </div>
                            <button class="product-details__hide-btn product-details__hide-btn--active"
                                    type="button">
                                <?= Loc::getMessage('CT_BCE_CATALOG_HIDE') ?>
                            </button>
                        </div>
                        <dl class="product-details__list">
                            <?php foreach ($arGroup['PROPERTIES'] as $arProp): ?>
                                <div class="product-details__list-item">
                                    <dt>
                                        <?= $arProp['NAME'] ?>
                                        <?php if (!empty($arProp['FILTER_HINT'])): ?>
                                            <div class="tooltip">
                                                <button class="tooltip__btn" type="button"
                                                        aria-label="Открыть подсказку">
                                                    <svg viewBox="0 0 17 21" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M16.2273 10.7726C16.2273 15.0157 12.771 18.4614 8.5 18.4614C4.22904 18.4614 0.772727 15.0157 0.772727 10.7726C0.772727 6.52946 4.22904 3.08376 8.5 3.08376C12.771 3.08376 16.2273 6.52946 16.2273 10.7726Z"
                                                              stroke="#C0C0C0" stroke-width="1.54545"/>
                                                        <path d="M7.75542 7.93005V6.18291H9.51569V7.93005H7.75542ZM7.75542 15.7725V8.88901H9.51569V15.7725H7.75542Z"
                                                              fill="#C0C0C0"/>
                                                    </svg>
                                                </button>
                                                <div class="tooltip__content">
                                                    <?= Loc::getMessage('CT_BCE_CATALOG_TOOLTIP_TEXT') . '&nbsp' . $arProp['FILTER_HINT'] ?>
                                                </div>
                                            </div>
                                        <?php endif ?>
                                    </dt>
                                    <dd data-prop="<?= $arProp['ID'] ?>">
                                        <?= (is_array($arProp['DISPLAY_VALUE']) ? implode(' / ', $arProp['DISPLAY_VALUE']) : strip_tags($arProp['DISPLAY_VALUE'])) ?>
                                    </dd>
                                </div>
                            <?php endforeach ?>
                        </dl>
                    </section>
                <?php endforeach ?>
            <?php else: ?>
                <dl class="product-details__list">
                    <?php foreach ($actualItem['DISPLAY_PROPERTIES'] as $arProp): ?>
                        <div class="product-details__list-item">
                            <dt>
                                <?= $arProp['NAME'] ?>
                                <?php if (!empty($arProp['FILTER_HINT'])): ?>
                                    <div class="tooltip">
                                        <button class="tooltip__btn" type="button" aria-label="Открыть подсказку">
                                            <svg viewBox="0 0 17 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M16.2273 10.7726C16.2273 15.0157 12.771 18.4614 8.5 18.4614C4.22904 18.4614 0.772727 15.0157 0.772727 10.7726C0.772727 6.52946 4.22904 3.08376 8.5 3.08376C12.771 3.08376 16.2273 6.52946 16.2273 10.7726Z"
                                                      stroke="#C0C0C0" stroke-width="1.54545"/>
                                                <path d="M7.75542 7.93005V6.18291H9.51569V7.93005H7.75542ZM7.75542 15.7725V8.88901H9.51569V15.7725H7.75542Z"
                                                      fill="#C0C0C0"/>
                                            </svg>
                                        </button>
                                        <div class="tooltip__content">
                                            <?= Loc::getMessage('CT_BCE_CATALOG_TOOLTIP_TEXT') . '&nbsp' . $arProp['FILTER_HINT'] ?>
                                        </div>
                                    </div>
                                <?php endif ?>
                            </dt>
                            <dd data-prop="<?= $arProp['ID'] ?>">
                                <?= (is_array($arProp['DISPLAY_VALUE']) ? implode(' / ', $arProp['DISPLAY_VALUE']) : strip_tags($arProp['DISPLAY_VALUE'])) ?>
                            </dd>
                        </div>
                    <?php endforeach ?>
                </dl>
            <?php endif ?>
            <?php if (!empty($arResult['CLEAN_CARE'])): ?>
                <section class="product-details__section">
                    <div class="product-details__section-top">
                        <div class="product-details__section-title">
                            <?= $arResult['PROPERTIES']['CLEAN_CARE']['NAME'] ?>
                        </div>
                        <button class="product-details__hide-btn product-details__hide-btn--active" type="button">
                            <?= Loc::getMessage('CT_BCE_CATALOG_HIDE') ?>
                        </button>
                    </div>
                    <div class="product-details__list-item">
                        <p>
                            <?php foreach ($arResult['CLEAN_CARE'] as $key => $arItem) {
                                echo $arItem['NAME'] . ($key != count($arResult['CLEAN_CARE']) - 1 ? '. ' : '');
                            } ?>
                        </p>
                        <ul class="product-details__icons-list">
                            <?php foreach ($arResult['CLEAN_CARE'] as $arItem):
                                if (!$arItem['FILE'])
                                    continue; ?>
                                <li>
                                    <img src="<?= $arItem['FILE'] ?>" alt="<?= $arItem['NAME'] ?>" loading="lazy">
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </section>
            <?php endif ?>
        </div>
        <div id="delivery" class="tabs-content">
            <?php if (!empty($arResult['PROPERTIES']['DELIVERY']['VALUE'])): ?>
                <?= $arResult['PROPERTIES']['DELIVERY']['~VALUE']['TEXT'] ?>
            <?php else: ?>
                #DELIVERY#
            <?php endif ?>
        </div>
        <div id="payment" class="tabs-content">
            <?php if (!empty($arResult['PROPERTIES']['PAYMENT']['VALUE'])): ?>
                <?= $arResult['PROPERTIES']['PAYMENT']['~VALUE']['TEXT'] ?>
            <?php else: ?>
                #PAYMENT#
            <?php endif ?>
        </div>
        <div id="shops-availability" class="shops-availability tabs-content"></div>
        <?php if (!empty($arResult['PROPERTIES']['VIDEO']['VALUE'])): ?>
            <div id="video" class="tabs-content">
                <div class="product-card__videos">
                    <?php foreach ($arResult['PROPERTIES']['VIDEO']['VALUE'] as $arVal): ?>
                        <div class="product-card__video">
                            <iframe src="https://www.youtube.com/embed/<?= $arVal ?>" title="YouTube video player"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endif ?>
    </div>
    <?php if (!empty($arResult['BRAND'])): ?>
        <div itemprop="brand" itemtype="https://schema.org/Brand" itemscope>
            <meta itemprop="name" content="<?= $arResult['BRAND']['NAME'] ?>" />
        </div>
    <?php endif ?>
    <meta itemprop="category" content="<?= $arResult['CATEGORY_PATH'] ?>" />
    <?php if ($haveOffers): ?>
        <?php if (count($arResult['OFFERS']) > 1):

            $priceList = [];

            foreach ($arResult['OFFERS'] as $arOffer) {
                $priceList[] = $arOffer['OPTIMAL_PRICE']['PRICE'];
            } ?>
            <div itemprop="offers" itemscope itemtype="https://schema.org/AggregateOffer">
                <meta itemprop="lowPrice" content="<?= min($priceList) ?>" />
                <meta itemprop="highPrice" content="<?= max($priceList) ?>" />
                <meta itemprop="offerCount" content="<?= count($arResult['OFFERS']) ?>" />
                <meta itemprop="priceCurrency" content="<?= $arResult['BASE_CURRENCY'] ?>" />
        <?php endif ?>
        <?php foreach ($arResult['OFFERS'] as $arOffer): ?>
            <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                <meta itemprop="price" content="<?= $arOffer['OPTIMAL_PRICE']['PRICE'] ?>" />
                <meta itemprop="priceCurrency" content="<?= $arOffer['OPTIMAL_PRICE']['CURRENCY'] ?>" />
                <link itemprop="availability" href="http://schema.org/<?= ($arOffer['CAN_BUY'] ? 'InStock' : 'OutOfStock') ?>" />
                <?php if ($arOffer['PROPERTIES'][$arResult['PROPERTY_CODE_GTIN']]['VALUE']): ?>
                    <meta itemprop="gtin" content="<?= $arOffer['PROPERTIES'][$arResult['PROPERTY_CODE_GTIN']]['VALUE'] ?>" />
                <?php endif ?>
                <?php if ($arOffer['PROPERTIES'][$arResult['PROPERTY_CODE_SKU']]['VALUE']): ?>
                    <meta itemprop="sku" content="<?= $arOffer['PROPERTIES'][$arResult['PROPERTY_CODE_SKU']]['VALUE'] ?>" />
                <?php endif ?>
                <link itemprop="url" href="<?= $arOffer['DETAIL_PAGE_URL'] ?>" />
            </div>
        <?php endforeach ?>
        <?php if (count($arResult['OFFERS']) > 1): ?>
            </div><?php // AggregateOffer ?>
        <?php endif ?>
    <?php else: ?>
        <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
            <meta itemprop="price" content="<?= $price['PRICE'] ?>" />
            <meta itemprop="priceCurrency" content="<?= $price['CURRENCY'] ?>" />
            <link itemprop="availability" href="http://schema.org/<?= ($actualItem['CAN_BUY'] ? 'InStock' : 'OutOfStock') ?>" />
            <?php if ($arResult['PROPERTIES'][$arResult['PROPERTY_CODE_GTIN']]['VALUE']): ?>
                <meta itemprop="gtin" content="<?= $arResult['PROPERTIES'][$arResult['PROPERTY_CODE_GTIN']]['VALUE'] ?>" />
            <?php endif ?>
            <?php if ($arResult['PROPERTIES'][$arResult['PROPERTY_CODE_SKU']]['VALUE']): ?>
                <meta itemprop="sku" content="<?= $arResult['PROPERTIES'][$arResult['PROPERTY_CODE_SKU']]['VALUE'] ?>" />
            <?php endif ?>
            <link itemprop="url" href="<?= $arResult['DETAIL_PAGE_URL'] ?>" />
        </div>
    <?php endif ?>
<script type="text/javascript"> 
    (window["rrApiOnReady"] = window["rrApiOnReady"] || []).push(function() {
        try { 
            rrApi.groupView([<?= $arResult['ID']; ?>]); 
        } catch(e) {}
    })
</script>
</div>

<?php foreach($arResult['SECTION']['PATH'] as $path) {
    $sectionIds[] = $path['ID'];
}

$jsParams = [
    'VISUAL' => $itemIds,
    'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
    'PRODUCT' => [
        'ID' => $arResult['ID'],
        'SECTION_IDS' => $sectionIds,
        'SIZE_TABLE' => $arResult['PROPERTIES']['SIZE_TABLE']['VALUE']
    ]
];

if ($haveOffers) {
    $jsParams['OFFERS'] = $arResult['JS_OFFERS'];
    $jsParams['OFFER_SELECTED'] = $arResult['OFFERS_SELECTED'];
} ?>

<script type="text/javascript">
    BX.message({
        SUBSCRIBE_TITLE: '<?=GetMessageJS("CT_BCE_CATALOG_SUBSCRIBE_TITLE")?>',
        SUBSCRIBE_SUBTITLE: '<?=GetMessageJS("CT_BCE_CATALOG_SUBSCRIBE_SUBTITLE")?>',
        SUBSCRIBE_EMAIL: '<?=GetMessageJS("CT_BCE_CATALOG_SUBSCRIBE_EMAIL")?>',
        SUBSCRIBE_BTN: '<?=GetMessageJS("CT_BCE_CATALOG_SUBSCRIBE_BTN")?>',
        SUBSCRIBE_AGREEMENT: '<?=GetMessageJS("CT_BCE_CATALOG_SUBSCRIBE_AGREEMENT", ["#LINK#" => SITE_DIR . "privacy-policy/"])?>',
        ALREADY_SUBSCRIBED: '<?=GetMessageJS("CT_BCE_CATALOG_ALREADY_SUBSCRIBED")?>',
        SUBSCRIBE_SUCCESS: '<?=GetMessageJS("CT_BCE_CATALOG_SUBSCRIBE_SUCCESS")?>',
        TABLE_SIZES: '<?=GetMessageJS("CT_BCE_CATALOG_TABLE_SIZES")?>',
        ADD_TO_BASKET: '<?=GetMessageJS("CT_BCE_CATALOG_ADD_TO_BASKET")?>',
        ADDED_TO_BASKET: '<?=GetMessageJS("CT_BCE_CATALOG_ADDED_TO_BASKET")?>',
        ADD_TO_FAVORITE: '<?=GetMessageJS("CT_BCE_CATALOG_ADD_TO_FAVORITE")?>',
        ADDED_TO_FAVORITE: '<?=GetMessageJS("CT_BCE_CATALOG_ADDED_TO_FAVORITE")?>',
        SHOPS_AVAILABILITY_IN_STOCK: '<?=GetMessageJS("CT_BCE_CATALOG_SHOPS_AVAILABILITY_IN_STOCK")?>'
    });
    var <?=$obName?> = new JCCatalogElement(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
</script>
<?php if($haveOffers):?>
    <product-size-selector :data='<?=Json::encode($arResult['DATA'])?>'></product-size-selector>
<?php endif ?>
<?php $component->arResult['CACHED_TPL'] = @ob_get_contents();
ob_get_clean();