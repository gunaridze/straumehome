<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\EventManager;
use Imedia\Main\Handlers;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler('main', 'OnPageStart', 'loadMainModule', 1);
function loadMainModule()
{
    Loader::includeModule('imedia.main');
}

if(SITE_ID === 's1'){
    /**
     * page
     */
    $eventManager->addEventHandler('main', 'OnProlog', [Handlers\Page::class, 'onProlog']);
    $eventManager->addEventHandler('main', 'OnEpilog', [Handlers\Page::class, 'onEpilog']);
    $eventManager->addEventHandler('main', 'OnEndBufferContent', [Handlers\Page::class, 'onEndBufferContent']);

    /**
     * order
     */
    $eventManager->addEventHandler('sale', 'OnSaleComponentOrderJsData', [Handlers\Sale\Order::class, 'onSaleComponentOrderJsData']);
}

/**
 * iblock
 */
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockDelete', [Handlers\Iblock\Iblock::class, 'onBeforeIBlockDelete']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockAdd', [Handlers\Iblock\Iblock::class, 'onAfterIBlockAdd']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockUpdate', [Handlers\Iblock\Iblock::class, 'onAfterIBlockUpdate']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockDelete', [Handlers\Iblock\Iblock::class, 'onAfterIBlockDelete']);

/**
 * fields
 */
$eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList', [Handlers\Fields\IblockStore::class, 'GetUserTypeDescription']);
$eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList', [Handlers\Fields\IblockProperty::class, 'GetUserTypeDescription']);
$eventManager->addEventHandler('main', 'OnUserTypeBuildList', [Handlers\Fields\Location::class, 'GetUserTypeDescription'], false, 500);
$eventManager->addEventHandler('main', 'OnUserTypeBuildList', [Handlers\Fields\Text::class, 'GetUserTypeDescription'], false, 500);

/**
 * iblock element
 */
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementAdd', [Handlers\Iblock\Element::class, 'onBeforeIBlockElementAdd']);
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementUpdate', [Handlers\Iblock\Element::class, 'onBeforeIBlockElementUpdate']);
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementDelete', [Handlers\Iblock\Element::class, 'onBeforeIBlockElementDelete']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', [Handlers\Iblock\Element::class, 'onAfterIBlockElementAdd']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', [Handlers\Iblock\Element::class, 'onAfterIBlockElementUpdate']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementDelete', [Handlers\Iblock\Element::class, 'onAfterIBlockElementDelete']);

/**
 * iblock section
 */
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockSectionUpdate', [Handlers\Iblock\Section::class, 'onBeforeIBlockSectionUpdate']);
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockSectionDelete', [Handlers\Iblock\Section::class, 'onBeforeIBlockSectionDelete']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockSectionAdd', [Handlers\Iblock\Section::class, 'onAfterIBlockSectionAdd']);
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockSectionUpdate', [Handlers\Iblock\Section::class, 'onBeforeIBlockSectionUpdate']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockSectionUpdate', [Handlers\Iblock\Section::class, 'onAfterIBlockSectionUpdate']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockSectionDelete', [Handlers\Iblock\Section::class, 'onAfterIBlockSectionDelete']);

/**
 * price group
 */
$eventManager->addEventHandler('catalog', 'OnGroupAdd', [Handlers\Catalog\PriceGroup::class, 'onAdd']);
$eventManager->addEventHandler('catalog', 'OnGroupUpdate', [Handlers\Catalog\PriceGroup::class, 'onUpdate']);
$eventManager->addEventHandler('catalog', 'OnGroupDelete', [Handlers\Catalog\PriceGroup::class, 'onDelete']);

/**
 * event
 */
$eventManager->addEventHandler('main', 'OnBeforeEventAdd', [Handlers\Event::class, 'onBeforeAdd']);

/**
 * user
 */
$eventManager->addEventHandler('main', 'OnBeforeUserAdd', [Handlers\User::class, 'onBeforeUserAdd']);
$eventManager->addEventHandler('main', 'OnBeforeUserUpdate', [Handlers\User::class, 'onBeforeUserUpdate']);
$eventManager->addEventHandler('main', 'OnAfterUserAdd', [Handlers\User::class, 'onAfterUserAdd']);

/**
 * order
 */
$eventManager->addEventHandler('sale', 'OnSaleOrderSaved', [Handlers\Sale\Order::class, 'onSaleOrderSaved']);
$eventManager->addEventHandler('sale', 'OnSaleOrderBeforeSaved', [Handlers\Sale\Order::class, 'onSaleOrderBeforeSaved']);

/**
 * file
 */
$eventManager->addEventHandler('main', 'OnPhysicalFileDelete', [Handlers\File::class, 'onDelete']);

/**
 * remove events
 */
$removeEvents = [
    [
        'sale',
        'OnSaleComponentOrderOneStepProcess',
        ['TO_MODULE_ID' => 'ipol.sdek']
    ]
];

/**
 * calculate grender property
 */
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementUpdate', 'SetCustomPropertyValue');
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementAdd', 'SetCustomPropertyValue');

function SetCustomPropertyValue(&$arFields) {
    if ($arFields['IBLOCK_ID'] == [1]) {
        $genderProperty = $arFields['PROPERTY_VALUES'][21];

        switch (strtolower($genderProperty)) {
            case 'для девочек':
            case 'для женщин':
            case 'женский':
                $arFields['PROPERTY_VALUES'][1093] = 216319; // ID значения для женщин/девочек
                break;
            case 'для мальчиков':
            case 'для мужчин':
            case 'мужской':
                $arFields['PROPERTY_VALUES'][1093] = 216318; // ID значения для мужчин/мальчиков
                break;
            case 'для детей':
            case 'унисекс':
                $arFields['PROPERTY_VALUES'][1093] = 216320; // ID значения для мужчин/мальчиков
                break;
            default:
                // Свойство не заполняется, если не найдено совпадение
                break;
        }
    }
}

foreach($removeEvents as $event){
    $handlers = $eventManager->findEventHandlers(...$event);
    foreach($handlers as $handler){
        $handler->unRegisterEventHandler(
            $event[0],
            $event[1],
            $handler['TO_MODULE_ID'],
            $handler['TO_CLASS'],
            $handler['TO_METHOD']
        );
    }
}