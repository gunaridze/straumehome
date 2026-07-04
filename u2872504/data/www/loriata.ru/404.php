<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<div class="not-found-page__inner">
    <?php
    $APPLICATION->IncludeComponent(
        'imedia:html.include',
        'page-404',
        [],
        false,
        ['HIDE_ICONS' => true]
    );
    ?>
    <?php
    $APPLICATION->IncludeComponent(
            'imedia:catalog.selected',
        'page-404',
        [],
        false,
        ['HIDE_ICONS' => true]
    );
    ?>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");