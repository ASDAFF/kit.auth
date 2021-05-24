<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if(!empty($arResult['ERRORS']['FATAL'])):?>

    <?foreach($arResult['ERRORS']['FATAL'] as $error):?>
        <?=ShowError($error)?>
    <?endforeach?>

<?else:?>

    <?if(!empty($arResult['ERRORS']['NONFATAL'])):?>

        <?foreach($arResult['ERRORS']['NONFATAL'] as $error):?>
            <?=ShowError($error)?>
        <?endforeach?>

    <?endif?>


    <div class="personal_list_order">
        <div class="main-ui-filter-search-wrapper">
            <?
            $APPLICATION->IncludeComponent(
                "bitrix:main.ui.filter",
                "b2bcabinet",
                array(
                    "FILTER_ID" => "ORDER_LIST",
                    "GRID_ID" => "ORDER_LIST",
                    "FILTER" => array(
                        array("id"=>"ID", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_ID')),
                        array("id"=>"DATE_INSERT", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_DATE'), "type"=>"date"),
                        array("id"=>"STATUS_ID", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_STATUS'), "type"=>"list", "items"=>$arResult["ORDER_STATUS"]),
                        array("id"=>"PAYED", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_PAYED'), "type"=>"list", "items"=>["Y"=>GetMessage('SPOL_YES'), "N"=>GetMessage('SPOL_NO')]),
                        array("id"=>"PAY_SYSTEM_ID", "name"=>GetMessage('B2B_SPOL_ORDER_FIELD_NAME_PAYMENT_METHOD'), "type"=>"list", "items"=>$arResult["PAY_SYSTEM"]),
                        array("id"=>"DELIVERY_ID", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_SHIPMENT_METHOD'), "type"=>"list", "items"=>$arResult["DELIVERY"]),
                        array("id" => "BUYER","name"=>GetMessage("SPOL_ORDER_FIELD_NAME_BUYER"),"type"=>"list","items"=>$arResult["BUYERS"]),
                    ),
                    "ENABLE_LIVE_SEARCH" => true,
                    "ENABLE_LABEL" => true,
                    "COMPONENT_TEMPLATE" => "b2bcabinet"
                ),
                false
            );
            ?>
        </div>
        <?
        $APPLICATION->IncludeComponent(
            'bitrix:main.ui.grid',
            '',
            array(
                'GRID_ID'   => 'ORDER_LIST',
                'HEADERS' => array(
                    array("id"=>"ID", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_ID'), "sort"=>"ID", "default"=>true, "editable"=>false),
                    array("id"=>"DATE_INSERT", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_DATE'), "sort"=>"DATE_INSERT", "default"=>true, "editable"=>false),
                    array("id"=>"STATUS", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_STATUS'), "sort"=>"STATUS", "default"=>true, "editable"=>true),
                    array("id"=>"FORMATED_PRICE", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_FORMATED_PRICE'), "default"=>true, "sort"=>"PRICE"),
                    array("id"=>"PAYED", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_PAYED'), "sort"=>"PAYED"),
                    array("id"=>"PAYMENT_METHOD", "name"=>GetMessage('B2B_SPOL_ORDER_FIELD_NAME_PAYMENT_METHOD'), "sort"=>"PAY_SYSTEM_ID"),
                    array("id"=>"SHIPMENT_METHOD", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_SHIPMENT_METHOD'), "sort"=>"DELIVERY_ID"),
                    array("id"=>"ITEMS", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_ITEMS')),
                    array("id"=>"BUYER", "name"=>GetMessage('SPOL_ORDER_FIELD_NAME_BUYER')),
                ),
                'ROWS'      => $arResult['ROWS'],
                'FILTER_STATUS_NAME' => $arResult['FILTER_STATUS_NAME'],
                'AJAX_MODE'           => 'Y',

                "AJAX_OPTION_JUMP"    => "N",
                "AJAX_OPTION_STYLE"   => "N",
                "AJAX_OPTION_HISTORY" => "N",

                "ALLOW_COLUMNS_SORT"      => true,
                "ALLOW_ROWS_SORT"         => $arParams['ALLOW_COLUMNS_SORT'],
                "ALLOW_COLUMNS_RESIZE"    => true,
                "ALLOW_HORIZONTAL_SCROLL" => true,
                "ALLOW_SORT"              => true,
                "ALLOW_PIN_HEADER"        => true,
                "ACTION_PANEL"            => $arResult['GROUP_ACTIONS'],

                "SHOW_CHECK_ALL_CHECKBOXES" => false,
                "SHOW_ROW_CHECKBOXES"       => false,
                "SHOW_ROW_ACTIONS_MENU"     => true,
                "SHOW_GRID_SETTINGS_MENU"   => true,
                "SHOW_NAVIGATION_PANEL"     => true,
                "SHOW_PAGINATION"           => true,
                "SHOW_SELECTED_COUNTER"     => false,
                "SHOW_TOTAL_COUNTER"        => true,
                "SHOW_PAGESIZE"             => true,
                "SHOW_ACTION_PANEL"         => true,

                "ENABLE_COLLAPSIBLE_ROWS" => true,
                'ALLOW_SAVE_ROWS_STATE'=>true,

                "SHOW_MORE_BUTTON" => false,
                '~NAV_PARAMS'       => $arResult['GET_LIST_PARAMS']['NAV_PARAMS'],
                'NAV_OBJECT'       => $arResult['NAV_OBJECT'],
                'NAV_STRING'       => $arResult['NAV_STRING'],
                "TOTAL_ROWS_COUNT"  => count($arResult['ROWS']),
                "CURRENT_PAGE" => $arResult[ 'CURRENT_PAGE' ],
                "PAGE_SIZES" => $arParams['ORDERS_PER_PAGE'],
                "DEFAULT_PAGE_SIZE" => 50
            ),
            $component,
            array('HIDE_ICONS' => 'Y')
        );
        ?>
    </div>
<?endif?>
<style>
    .main-grid-wrapper
    {
        padding: 5px;
    }
    .nicescroll-rails-hr
    {
        position: relative;
    }
</style>
<script>
    // $('.main-grid-container').niceScroll({emulatetouch: true, bouncescroll: false, cursoropacitymin: 1, enabletranslate3d: true, cursorfixedheight: '100', scrollspeed: 25, mousescrollstep: 10,  cursorwidth: '8px', horizrailenabled: true, cursordragontouch: true});

</script>