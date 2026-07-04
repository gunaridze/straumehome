<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use Imedia\Main\Helpers\Image\Resize;
use Imedia\Main\Helpers\Catalog\Property;

\CBitrixComponent::includeComponentClass('bitrix:sale.basket.basket');
class BasketComponent extends CBitrixBasketComponent
{
    const PICTURE_WIDTH = 188;
    const PICTURE_HEIGHT = 165;

	protected function getBasketItemsArray($filterItems = null)
	{
        $basketItems = parent::getBasketItemsArray($filterItems);

		return $basketItems;
	}

	protected function fillItemsWithProperties()
	{
		$productIndexMap = [];
		$iblockToProductMap = [];
		$productsData = [];

		$elementIterator = CIBlockElement::GetList(
			[],
			['=ID' => $this->storage['ELEMENT_IDS']],
			false,
			false,
			[
                'ID',
                'IBLOCK_ID',
                'PREVIEW_PICTURE',
                'DETAIL_PICTURE',
                'DETAIL_PAGE_URL',
                'PROPERTY_' . Property::getCode('CML2_LINK')
            ]
		);
		if ($this->arParams['DETAIL_URL'] !== '')
		{
			$elementIterator->SetUrlTemplates($this->arParams['DETAIL_URL']);
		}
		while ($product = $elementIterator->GetNext(true, false))
		{
			$productIndexMap[$product['ID']] = [];
			$iblockToProductMap[$product['IBLOCK_ID']][] = $product['ID'];
			$productsData[$product['ID']] = $product;
		}

		foreach ($iblockToProductMap as $iblockId => $productIds)
		{
			$codes = [];

			if (!empty($this->arIblockProps[$iblockId]))
			{
				$codes = array_keys($this->arIblockProps[$iblockId]);
			}

			$imageCode = $this->arParams['ADDITIONAL_PICT_PROP'][$iblockId];
			if (!empty($imageCode) && !in_array($imageCode, $codes))
			{
				$codes[] = $imageCode;
			}

			if (!empty($this->arParams['LABEL_PROP']))
			{
				$codes = array_merge($codes, $this->arParams['LABEL_PROP']);
			}

			if (!empty($codes))
			{
				CIBlockElement::GetPropertyValuesArray(
					$productIndexMap, $iblockId,
					['ID' => $productIds],
					['CODE' => $codes]
				);
			}
		}

		unset($iblockToProductMap);

		// getting compatible iblock properties and additional images arrays
		$additionalImages = [];
		foreach ($productIndexMap as $productId => $productProperties)
		{
			if (!empty($productProperties) && is_array($productProperties))
			{
				$productIblockId = $productsData[$productId]['IBLOCK_ID'];
				$additionalImage = $this->getAdditionalImageForProduct($productIblockId, $productProperties);
				if ((int)$additionalImage > 0)
				{
					$additionalImages[$productId] = $additionalImage;
				}

				foreach ($productProperties as $code => $property)
				{
					if (!empty($this->arIblockProps[$productIblockId]) && array_key_exists($code, $this->arIblockProps[$productIblockId]))
					{
						$temporary = [];

						if ($property['PROPERTY_TYPE'] === 'S' && $property['USER_TYPE'] === 'HTML')
						{
							$temporary['PROPERTY_'.$code.'_VALUE'] = '';

							if (!empty($property['~VALUE']))
							{
								if ($property['MULTIPLE'] === 'N')
								{
									$property['~VALUE'] = [$property['~VALUE']];
								}

								foreach ($property['~VALUE'] as $value)
								{
									if (!empty($temporary['PROPERTY_'.$code.'_VALUE']))
									{
										$temporary['PROPERTY_'.$code.'_VALUE'] .= ', ';
									}

									$temporary['PROPERTY_'.$code.'_VALUE'] .= ($value['TYPE'] === 'HTML'
										? $value['TEXT']
										: htmlspecialcharsbx($value['TEXT'])
									);
								}
							}

							$temporary['PROPERTY_'.$code.'_VALUE_HTML'] = true;
						}
						else
						{
							if (!empty($property['~VALUE']) && is_array($property['~VALUE']))
							{
								$temporary['PROPERTY_'.$code.'_VALUE'] = implode(', ', $property['~VALUE']);
							}
							else
							{
								$temporary['PROPERTY_'.$code.'_VALUE'] = $property['~VALUE'];
							}
						}

						if (!empty($property['PROPERTY_VALUE_ID']) && is_array($property['PROPERTY_VALUE_ID']))
						{
							$temporary['PROPERTY_'.$code.'_VALUE_ID'] = implode(', ', $property['PROPERTY_VALUE_ID']);
						}
						else
						{
							$temporary['PROPERTY_'.$code.'_VALUE_ID'] = $property['PROPERTY_VALUE_ID'];
						}

						if ($property['PROPERTY_TYPE'] === 'L')
						{
							$temporary['PROPERTY_'.$code.'_ENUM_ID'] = $property['VALUE_ENUM_ID'];
						}

						if ($this->isCompatibleMode())
						{
							$this->makeCompatibleArray($temporary);
						}

						$productsData[$productId] += $temporary;
					}
				}

				if (!empty($this->arParams['LABEL_PROP']))
				{
					$this->modifyLabels($productsData[$productId], $productProperties);
				}
			}
		}

		unset($productIndexMap);

        $dimensions = [static::PICTURE_WIDTH, static::PICTURE_HEIGHT];
        $sizes = [
            'DEFAULT' => $dimensions,
            'DEFAULT_2X' => array_map(function($item){
                return $item * 2;
            }, $dimensions)
        ];

        foreach($sizes as $i => $size){
            $sizes[$i][] = BX_RESIZE_IMAGE_PROPORTIONAL_ALT;
        }

		foreach ($this->basketItems as &$item)
		{
			$productId = $item['PRODUCT_ID'];

			if (!empty($productsData[$productId]) && is_array($productsData[$productId]))
			{
				foreach ($productsData[$productId] as $code => $value)
				{
					if ($value === null)
						continue;

					if (
						$code === 'PREVIEW_PICTURE'
						|| $code === 'DETAIL_PICTURE'
						|| $code === 'DETAIL_PAGE_URL'
						|| mb_strpos($code, 'PROPERTY_') !== false
					)
					{
						$item[$code] = $value;
					}
				}
			}

			// if sku element doesn't have value of some property - we'll show parent element value instead
			$parentId = isset($this->storage['SKU_TO_PARENT'][$productId]) ? $this->storage['SKU_TO_PARENT'][$productId] : 0;
			if ((int)$parentId > 0)
			{
				$parentDetailUrl = $productsData[$parentId]['DETAIL_PAGE_URL'] ?? '';
				if ($parentDetailUrl !== '')
				{
					$item['DETAIL_PAGE_URL'] = $parentDetailUrl;
				}

				foreach ($this->arCustomSelectFields as $field)
				{
					$fieldVal = (mb_substr($field, -6) === '_VALUE' ? $field : $field.'_VALUE');

					// can be array or string
					if (
						(!isset($item[$fieldVal]) || empty($item[$fieldVal]))
						&& (isset($productsData[$parentId][$fieldVal]) && !empty($productsData[$parentId][$fieldVal]))
					)
					{
						$item[$fieldVal] = $productsData[$parentId][$fieldVal];
					}
				}
			}

			if (!empty($productsData[$productId]['PREVIEW_TEXT']))
			{
				$item['PREVIEW_TEXT'] = $productsData[$productId]['PREVIEW_TEXT'];
				$item['PREVIEW_TEXT_TYPE'] = $productsData[$productId]['PREVIEW_TEXT_TYPE'];
			}
			elseif (!empty($productsData[$parentId]['PREVIEW_TEXT']))
			{
				$item['PREVIEW_TEXT'] = $productsData[$parentId]['PREVIEW_TEXT'];
				$item['PREVIEW_TEXT_TYPE'] = $productsData[$parentId]['PREVIEW_TEXT_TYPE'];
			}

			if (!empty($productsData[$productId]['PREVIEW_PICTURE']))
			{
				$item['PREVIEW_PICTURE'] = $productsData[$productId]['PREVIEW_PICTURE'];
			}
			elseif (!empty($productsData[$parentId]['PREVIEW_PICTURE']))
			{
				$item['PREVIEW_PICTURE'] = $productsData[$parentId]['PREVIEW_PICTURE'];
			}

			if (!empty($productsData[$productId]['DETAIL_PICTURE']))
			{
				$item['DETAIL_PICTURE'] = $productsData[$productId]['DETAIL_PICTURE'];
			}
			elseif (!empty($productsData[$parentId]['DETAIL_PICTURE']))
			{
				$item['DETAIL_PICTURE'] = $productsData[$parentId]['DETAIL_PICTURE'];
			}

			if (!empty($productsData[$productId]['LABEL_ARRAY_VALUE']))
			{
				$item['LABEL_ARRAY_VALUE'] = $productsData[$productId]['LABEL_ARRAY_VALUE'];
			}
			elseif (!empty($productsData[$parentId]['LABEL_ARRAY_VALUE']))
			{
				$item['LABEL_ARRAY_VALUE'] = $productsData[$parentId]['LABEL_ARRAY_VALUE'];
			}

			// format property values
			foreach ($item as $key => $value)
			{
				if ((mb_strpos($key, 'PROPERTY_', 0) === 0) && (mb_strrpos($key, '_VALUE') == mb_strlen($key) - 6))
				{
					$iblockId = $productsData[$productId]['IBLOCK_ID'];
					$code = ToUpper(str_replace(['PROPERTY_', '_VALUE'], '', $key));

					$propData = isset($this->arIblockProps[$iblockId][$code])
						? $this->arIblockProps[$iblockId][$code]
						: $this->arIblockProps[$this->storage['PARENTS'][$productId]['IBLOCK_ID']][$code];

					if ($propData['PROPERTY_TYPE'] === 'F')
					{
						$this->makeFileSources($item, $propData);
					}

					// display linked property type
					if ($propData['PROPERTY_TYPE'] === 'E')
					{
						$this->makeLinkedProperty($item, $propData);
					}

					if ($propData['PROPERTY_TYPE'] === 'S' && $propData['USER_TYPE'] === 'directory')
					{
						$this->makeDirectoryProperty($item, $propData);
					}

					$item[$key] = CSaleHelper::getIblockPropInfo(
						$value,
						$propData,
						['width' => self::IMAGE_SIZE_STANDARD, 'height' => self::IMAGE_SIZE_STANDARD]
					);
				}
			}

			// image replace priority (if has SKU):
			// 1. offer 'PREVIEW_PICTURE' or 'DETAIL_PICTURE'
			// 2. offer additional picture from parameters
			// 3. parent product 'PREVIEW_PICTURE' or 'DETAIL_PICTURE'
			// 4. parent product additional picture from parameters
			if (
				empty($productsData[$productId]['PREVIEW_PICTURE'])
				&& empty($productsData[$productId]['DETAIL_PICTURE'])
				&& isset($additionalImages[$productId])
			)
			{
				$item['PREVIEW_PICTURE'] = $additionalImages[$productId];
			}
			elseif (
				empty($item['PREVIEW_PICTURE'])
				&& empty($item['DETAIL_PICTURE'])
				&& $additionalImages[$parentId]
			)
			{
				$item['PREVIEW_PICTURE'] = $additionalImages[$parentId];
			}

            $item['PICTURE'] = [];
            if(empty($item['PREVIEW_PICTURE'])){
                $item['PREVIEW_PICTURE'] = $item['DETAIL_PICTURE'];
            }
            if(!empty($item['PREVIEW_PICTURE'])){

                $img = new Resize($sizes);
                $img->add($item['PREVIEW_PICTURE']);
                $arImage = $img->getResizeArray();
                $item['PICTURE'] = [
                    'src' => $arImage['RESIZE'][0]['SIZES']['DEFAULT'],
                    'src2x' => $arImage['RESIZE'][0]['SIZES']['DEFAULT_2X'],
                    'width' => $arImage['RESIZE'][0]['DIMENSIONS']['DEFAULT']['WIDTH'],
                    'height' => $arImage['RESIZE'][0]['DIMENSIONS']['DEFAULT']['HEIGHT']
                ];

            }

            $parentId = $productsData[$productId]['PROPERTY_' . Property::getCode('CML2_LINK') . '_VALUE'];
            $item['PARENT_ID'] = ($parentId) ?: $productId;
		}

		unset($item);
	}
}