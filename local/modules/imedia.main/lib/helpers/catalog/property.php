<?php
namespace Imedia\Main\Helpers\Catalog;

class Property
{
    public static function getCode(string $key): string
    {
        $arBridge = [
            'CML2_LINK' => 'CML2_LINK',
            'GTIN' => 'GTIN',
            'SKU' => 'CML2_ARTICLE',
            'COLOR' => 'TSVET',
            'LINK_CODE' => 'KONFIG_TSVETA',
            'SIZE' => 'RAZMER',
            'BRAND' => 'BRAND',
            'BRAND_NAME' => 'BREND',
            'NEW' => 'NEW',
            'HIT' => 'HIT',
            'SALE' => 'SALE',
            'PRICE_MIN' => 'PRICE_MIN',
            'PRICE_MAX' => 'PRICE_MAX',
            'DISCOUNT_MAX' => 'DISCOUNT_MAX',
            'INTERNAL_CODE' => 'KODL'
        ];

        return $arBridge[$key] ?? $key;
    }

    public static function getLabels(): array
    {
        return [
            static::getCode('NEW')
        ];
    }

    public static function getPrice(): array
    {
        return [
            Price::getName(Price::GROUP_BASE),
            Price::getName(Price::GROUP_SALE)
        ];
    }
}