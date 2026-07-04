<?php
namespace Imedia\Main\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Engine\Response;
use Bitrix\Main\Localization\Loc;
use Imedia\Main\Helpers\Sale\Cart as CartHelper;

class Cart extends Controller
{
    public function configureActions(): array
    {
        return [
            'add' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ],
            'clear' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ],
            'refresh' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ],
            'remove' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ],
            'get' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ]
        ];
    }

    public function addAction(int $productId, int $quantity)
    {
        try{
            $result = CartHelper::add($productId, $quantity);

            if($result->isSuccess()){
                return [
                    'type' => 'success',
                    'message' => Loc::getMessage('IMEDIA_MAIN_CONTROLLER_CART_ADD')
                ];
            } else {

                foreach($result->getErrorCollection() as $error){

                    switch($error->getCode()){
                        case CartHelper::ERROR_CODE_FEWER_AVAILABLE:
                            $type = 'warn';
                            break;
                        default:
                            $type = 'error';
                            break;
                    }

                    return [
                        'type' => $type,
                        'message' => $error->getMessage()
                    ];

                }

            }


        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function clearAction()
    {
        try{
            CartHelper::clear();
            return [];
        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function refreshAction()
    {
        try{
            return CartHelper::refresh();
        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function removeAction(int $id)
    {
        try{
            CartHelper::remove($id);
            return [];
        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return Response\AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    public function getAction()
    {
        return new Response\Component(
            'imedia:sale.basket.basket',
            '',
            [
                "ACTION_VARIABLE" => "basketAction",
                "AUTO_CALCULATION" => "Y",
                "BASKET_IMAGES_SCALING" => "adaptive",
                "COLUMNS_LIST_EXT" => array(
                    0 => "PREVIEW_PICTURE",
                    1 => "DISCOUNT",
                    2 => "DELETE",
                    3 => "SUM",
                ),
                "COLUMNS_LIST_MOBILE" => array(
                    0 => "PREVIEW_PICTURE",
                    1 => "DISCOUNT",
                    2 => "DELETE",
                    3 => "SUM",
                ),
                "COMPATIBLE_MODE" => "N",
                "CORRECT_RATIO" => "Y",
                "DEFERRED_REFRESH" => "Y",
                "DISCOUNT_PERCENT_POSITION" => "bottom-right",
                "DISPLAY_MODE" => "extended",
                "EMPTY_BASKET_HINT_PATH" => "/",
                "GIFTS_BLOCK_TITLE" => "Выберите один из подарков",
                "GIFTS_CONVERT_CURRENCY" => "N",
                "GIFTS_HIDE_BLOCK_TITLE" => "N",
                "GIFTS_HIDE_NOT_AVAILABLE" => "N",
                "GIFTS_MESS_BTN_BUY" => "Выбрать",
                "GIFTS_MESS_BTN_DETAIL" => "Подробнее",
                "GIFTS_PAGE_ELEMENT_COUNT" => "0",
                "GIFTS_PLACE" => "BOTTOM",
                "GIFTS_PRODUCT_PROPS_VARIABLE" => "prop",
                "GIFTS_PRODUCT_QUANTITY_VARIABLE" => "quantity",
                "GIFTS_SHOW_DISCOUNT_PERCENT" => "Y",
                "GIFTS_SHOW_OLD_PRICE" => "N",
                "GIFTS_TEXT_LABEL_GIFT" => "Подарок",
                "HIDE_COUPON" => "N",
                "LABEL_PROP" => array(),
                "PATH_TO_ORDER" => "/order/",
                "PRICE_DISPLAY_MODE" => "Y",
                "PRICE_VAT_SHOW_VALUE" => "N",
                "PRODUCT_BLOCKS_ORDER" => "props,sku,columns",
                "QUANTITY_FLOAT" => "N",
                "SET_TITLE" => "N",
                "SHOW_DISCOUNT_PERCENT" => "Y",
                "SHOW_FILTER" => "N",
                "SHOW_RESTORE" => "N",
                "TEMPLATE_THEME" => "blue",
                "TOTAL_BLOCK_DISPLAY" => array(
                    0 => "top",
                ),
                "USE_DYNAMIC_SCROLL" => "N",
                "USE_ENHANCED_ECOMMERCE" => "N",
                "USE_GIFTS" => "Y",
                "USE_PREPAYMENT" => "N",
                "USE_PRICE_ANIMATION" => "N",
                "COMPONENT_TEMPLATE" => ".default",
                "COMPOSITE_FRAME_MODE" => "A",
                "COMPOSITE_FRAME_TYPE" => "AUTO",
                "LABEL_PROP_MOBILE" => array(),
                "LABEL_PROP_POSITION" => "top-left"
            ]
        );
    }
}