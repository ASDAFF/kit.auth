<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Page\Asset,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Loader,
    Bitrix\Main\Web\Json;

Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/components/bitrix/main.ui.filter/b2bcabinet/js/settings.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/components/bitrix/main.ui.filter/b2bcabinet/js/search.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/components/bitrix/main.ui.filter/b2bcabinet/js/utils.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/components/bitrix/main.ui.filter/b2bcabinet/js/api.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/components/bitrix/main.ui.filter/b2bcabinet/js/destination-selector.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/components/bitrix/main.ui.filter/b2bcabinet/js/field-controller.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/components/bitrix/main.ui.filter/b2bcabinet/js/main-ui-control-custom-entity.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/components/bitrix/main.ui.filter/b2bcabinet/js/presets.js");


if ($arParams['GUEST_MODE'] !== 'Y')
{
    Asset::getInstance()->addJs("/bitrix/components/bitrix/sale.order.payment.change/templates/.default/script.js");
    Asset::getInstance()->addCss("/bitrix/components/bitrix/sale.order.payment.change/templates/.default/style.css");
}

Loader::includeModule("catalog");
Loader::includeModule("sale");

CJSCore::Init(array('clipboard', 'fx'));
$protocol = CMain::IsHTTPS() ? 'https://' : 'http://';
$methodIstall = Option::get('sotbit.b2bcabinet', 'method_install', '', SITE_ID) == 'AS_TEMPLATE' ?
    SITE_DIR.'b2bcabinet/' :
    SITE_DIR;

$sotbitBillLink = $protocol . $_SERVER['SERVER_NAME'] . $methodIstall;

$order = \Bitrix\Sale\Order::load($arResult["ID"]);
$paymentCollection = $order->getPaymentCollection();

foreach ($paymentCollection as $i => $payment) {
    $id = $payment->getField('ID');

    foreach ($arResult['PAYMENT'] as $k => $pay)
    {
        if($pay['ID'] == $id)
        {
            $key = $k;
            break;
        }
    }

    $paymentData[$arResult['PAYMENT'][$key]['ACCOUNT_NUMBER']] = array(
        "payment" => $arResult['PAYMENT'][$key]['ACCOUNT_NUMBER'],
        "order" => $arResult['PAYMENT'][$key]['ORDER_ID'],
        "allow_inner" => $arResult['PAYMENT'][$key]['ALLOW_INNER'],
        "only_inner_full" => $arParams['ONLY_INNER_FULL']
    );
}
$APPLICATION->AddChainItem(Loc::getMessage('SPOD_ORDER') ." ". Loc::getMessage('SPOD_NUM_SIGN') . htmlspecialcharsbx($arResult["ACCOUNT_NUMBER"]));
$APPLICATION->SetTitle(Loc::getMessage('SPOD_LIST_MY_ORDER_TITLE'));

if (!empty($arResult['ERRORS']['FATAL'])) {
    foreach ($arResult['ERRORS']['FATAL'] as $error) {
        ShowError($error);
    }

    $component = $this->__component;

    if ($arParams['AUTH_FORM_IN_TEMPLATE'] && isset($arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED])) {
        $APPLICATION->AuthForm('', false, false, 'N', false);
    }
} else {
    if (!empty($arResult['ERRORS']['NONFATAL'])) {
        foreach ($arResult['ERRORS']['NONFATAL'] as $error) {
            ShowError($error);
        }
    }
    ?>
    <div class="blank_detail">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline blank_detail-card_headers">
                        <h6 class="card-title">
                            <?= Loc::getMessage('SPOD_LIST_MY_ORDER_TITLE_SINGLE') ?>
                        </h6>
                        <div class="header-elements">
                            <div class="list-icons">
                                <a class="list-icons-item" data-action="collapse"></a>
                                <a class="list-icons-item" data-action="reload"></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="blank_detail-menu">
                            <ul class="nav nav-tabs nav-tabs-highlight">
                                <li class="nav-item">
                                    <a href="#basic-tab1" class="nav-link active show" data-toggle="tab"><?=Loc::getMessage('SPOD_TAB_COMMON')?></a>
                                </li>
                                <li class="nav-item">
                                    <a href="#basic-tab2" class="nav-link" data-toggle="tab"><?=Loc::getMessage('SPOD_TAB_GOODS')?></a>
                                </li>
                                <li class="nav-item">
                                    <a href="#basic-tab3" class="nav-link" data-toggle="tab"><?=Loc::getMessage('SPOD_TAB_DOCS')?></a></li>
                                <li class="nav-item">
                                    <a href="#basic-tab4" class="nav-link" data-toggle="tab"><?=Loc::getMessage('SPOD_TAB_PAYS')?></a>
                                </li>
                                <li class="nav-item">
                                    <a href="#basic-tab5" class="nav-link" data-toggle="tab"><?=Loc::getMessage('SPOD_TAB_SHIPMENTS')?></a></li>
                                <li class="nav-item">
                                    <a href="#basic-tab6" class="nav-link" data-toggle="tab"><?=Loc::getMessage('SPOD_TAB_SUPPORT')?></a>
                                </li>
                            </ul>
                            <div class="btn-group blank_detail-dropdown_menu">
                                <button type="button" class="btn btn-primary b2b_detail_order__second__tab__btn" data-toggle="dropdown" aria-expanded="false">
                                    <?=Loc::getMessage('SPOD_ACTIONS')?>
                                </button>
                                <div class="dropdown-menu b2b_detail_order__second__tab__btn__block" x-placement="bottom-end">
                                    <a href="<?=$arResult['URL_TO_CANCEL']?>" class="dropdown-item"><?=Loc::getMessage('SPOD_ORDER_CANCEL')?></a>
                                    <a href="<?=$arResult['URL_TO_COPY']?>" class="dropdown-item"><?=Loc::getMessage('SPOD_ORDER_REPEAT')?></a>
                                </div>
                            </div>
                        </div>
                        <div class="tab-content">
                            <!--basic-tab1-->
                            <div class="tab-pane fade show active" id="basic-tab1">
                                <div class="row">
                                    <div class="flex-column">
                                        <div class="col-md-12 my-2">
                                            <div class="card">
                                                <div class="card-header header-elements-inline blank_detail-card_headers">
                                                    <h6 class="card-title"><?= Loc::getMessage('SPOD_SUB_ORDER_TITLE', array(
                                                            "#ACCOUNT_NUMBER#"=> htmlspecialcharsbx($arResult["ACCOUNT_NUMBER"]),
                                                            "#DATE_ORDER_CREATE#"=> $arResult["DATE_INSERT_FORMATED"]
                                                        ))?></h6>
                                                    <div class="header-elements">
                                                        <div class="list-icons">
                                                            <a class="list-icons-item" data-action="collapse"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body bg-light mb-0">
                                                    <dl class="row mb-0">
                                                        <dt class="col-sm-3">
                                                            <?= Loc::getMessage('SPOD_ORDER_STATUS', array(
                                                                '#DATE_ORDER_CREATE#' => $arResult["DATE_INSERT_FORMATED"]
                                                            )) ?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?
                                                            if ($arResult['CANCELED'] !== 'Y') {
                                                                echo htmlspecialcharsbx($arResult["STATUS"]["NAME"] . " (".Loc::getMessage('SPOD_FROM')." " . $arResult["DATE_INSERT_FORMATED"] . ")");
                                                            } else {
                                                                echo Loc::getMessage('SPOD_ORDER_CANCELED');
                                                            }
                                                            ?>
                                                        </dd>
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage("SPOD_ORDER_PRICE")?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?= $arResult["PRICE_FORMATED"]?>
                                                        </dd>
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage('SPOD_ORDER_CANCELED');?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=($arResult['CANCELED'] == "N" ? Loc::getMessage("SPOD_NO") : Loc::getMessage("SPOD_YES"))?>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 my-2">
                                            <div class="card">
                                                <div class="card-header header-elements-inline blank_detail-card_headers">
                                                    <h6 class="card-title"><?= Loc::getMessage("SPOD_USER_BUYER")?></h6>
                                                    <div class="header-elements">
                                                        <div class="list-icons">
                                                            <a class="list-icons-item" data-action="collapse"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body bg-light mb-0">
                                                    <dl class="row mb-0">
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage("SPOD_ACCOUNT")?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=$arResult['USER']['LOGIN'];?>
                                                        </dd>
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage("SPOD_PERSON_TYPE_NAME")?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?= $arResult["PERSON_TYPE"]["NAME"]?>
                                                        </dd>
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage('SPOD_EMAIL');?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=$arResult['USER']["EMAIL"]?>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 my-2">
                                            <div class="card">
                                                <div class="card-header header-elements-inline blank_detail-card_headers">
                                                    <h6 class="card-title"><?= Loc::getMessage("SPOD_COMPANY_DATA")?></h6>
                                                    <div class="header-elements">
                                                        <div class="list-icons">
                                                            <a class="list-icons-item" data-action="collapse"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body bg-light mb-0">
                                                    <dl class="row mb-0">
                                                        <dt class="col-sm-3">
                                                            <?=
                                                            (
                                                            isset($arResult['ORDER_PROPS']['FIO']) && !empty($arResult['ORDER_PROPS']['FIO']) ?
                                                                $arResult['ORDER_PROPS']['FIO']['NAME'] : $arResult['ORDER_PROPS']['COMPANY']['NAME']
                                                            )
                                                            ?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=
                                                            (
                                                            isset($arResult['ORDER_PROPS']['FIO']) && !empty($arResult['ORDER_PROPS']['FIO']) ?
                                                                $arResult['ORDER_PROPS']['FIO']['VALUE'] : $arResult['ORDER_PROPS']['COMPANY']['VALUE']
                                                            )
                                                            ?>
                                                        </dd>
                                                        <dt class="col-sm-3">
                                                            <?=($arResult['ORDER_PROPS']['INN']['NAME'])?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=($arResult['ORDER_PROPS']['INN']['VALUE'])?>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 my-2">
                                            <div class="card">
                                                <div class="card-header header-elements-inline blank_detail-card_headers">
                                                    <h6 class="card-title"><?= Loc::getMessage("SPOD_COMPANY_POST_ADDRESS")?></h6>
                                                    <div class="header-elements">
                                                        <div class="list-icons">
                                                            <a class="list-icons-item" data-action="collapse"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body bg-light mb-0">
                                                    <dl class="row mb-0">
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage('SPOL_ZIP_CODE')?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=(
                                                            isset($arResult['ORDER_PROPS']['POST_ZIP']['VALUE']) && !empty($arResult['ORDER_PROPS']['POST_ZIP']['VALUE']) ?
                                                                $arResult['ORDER_PROPS']['POST_ZIP']['VALUE'] :
                                                                $arResult['ORDER_PROPS']['ZIP']['VALUE']
                                                            )?>
                                                        </dd>
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage('SPOL_CITY')?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=(
                                                            isset($arResult['ORDER_PROPS']['POST_CITY']['VALUE']) && !empty($arResult['ORDER_PROPS']['POST_CITY']['VALUE']) ?
                                                                $arResult['ORDER_PROPS']['POST_CITY']['VALUE'] :
                                                                $arResult['ORDER_PROPS']['CITY']['VALUE']
                                                            )?>
                                                        </dd>
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage('SPOL_ADDRESS_WITHOUT_CITY')?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=(
                                                            isset($arResult['ORDER_PROPS']['POST_ADDRESS']['VALUE']) && !empty($arResult['ORDER_PROPS']['POST_ADDRESS']['VALUE']) ?
                                                                $arResult['ORDER_PROPS']['POST_ADDRESS']['VALUE'] :
                                                                $arResult['ORDER_PROPS']['ADDRESS']['VALUE']
                                                            )?>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-column">
                                        <div class="col-md-12 my-2">
                                            <div class="card">
                                                <div class="card-header header-elements-inline blank_detail-card_headers">
                                                    <h6 class="card-title"><?= Loc::getMessage("SPOD_COMPANY_ADDRESS")?></h6>
                                                    <div class="header-elements">
                                                        <div class="list-icons">
                                                            <a class="list-icons-item" data-action="collapse"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body bg-light mb-0">
                                                    <dl class="row mb-0">
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage('SPOL_ZIP_CODE')?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=$arResult['ORDER_PROPS']['UR_ZIP']['VALUE']?>
                                                        </dd>
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage('SPOL_CITY')?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=$arResult['ORDER_PROPS']['UR_CITY']['VALUE']?>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 my-2">
                                            <div class="card">
                                                <div class="card-header header-elements-inline blank_detail-card_headers">
                                                    <h6 class="card-title"><?= Loc::getMessage("SPOD_ORDER_PAYMENT")?></h6>
                                                    <div class="header-elements">
                                                        <div class="list-icons">
                                                            <a class="list-icons-item" data-action="collapse"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body bg-light mb-0">
                                                    <dl class="row mb-0 payment-wrapper">
                                                        <dt class="col-sm-3">
                                                            <?= Loc::getMessage("SPOD_PAY_SYSTEM")?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=$arResult['PAY_SYSTEM']['NAME']?>
                                                        </dd>
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage("SPOD_ORDER_PAYED")?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=( $arResult['PAYED'] == 'Y' ? Loc::getMessage("SPOD_YES") : Loc::getMessage("SPOD_NO") )?>
                                                        </dd>
                                                        <dt class="col-sm-3 sale-order-detail-payment-options-methods-info">
                                                            <button
                                                                    class="sale-order-detail-payment-options-methods-info-change-link btn btn-light"
                                                                    id="<?=$arResult['PAYMENT'][$key]['ACCOUNT_NUMBER'] ?>"
                                                            >
                                                                <?=Loc::getMessage("SPOD_CHANGE_PAYMENT_TYPE")?>
                                                            </button>
                                                        </dt>
                                                        <dd class="col-sm-9"></dd>
                                                        <?if($arResult['PAY_SYSTEM']['ACTION_FILE'] == 'billsotbit'):?>
                                                            <dt class="col-sm-3">
                                                                <?=Loc::getMessage("SPOD_CHECK_BILL")?>
                                                            </dt>
                                                            <dd class="col-sm-9">
                                                                <?=Loc::getMessage("SHOW_BILL", array(
                                                                    '#ORDER_ID#' => $arResult["ID"],
                                                                    '#PAYMENT_ID#' => key($arResult["PAYMENT"]),
                                                                    '#DATE#' =>	$arResult["DATE_INSERT_FORMATED"],
                                                                    '#TYPE_TEMPLATE#' => $sotbitBillLink
                                                                ))?>
                                                            </dd>
                                                            <dt class="col-sm-3">
                                                                <?=Loc::getMessage("SPOD_DOWNLOAD_BILL")?>
                                                            </dt>
                                                            <dd class="col-sm-9">
                                                                <?=Loc::getMessage("DOWNLOAD_BILL", array(
                                                                    '#ORDER_ID#' => $arResult["ID"],
                                                                    '#PAYMENT_ID#' => key($arResult["PAYMENT"]),
                                                                    '#DATE#' =>	$arResult["DATE_INSERT_FORMATED"],
                                                                    '#TYPE_TEMPLATE#' => $sotbitBillLink
                                                                ))?>
                                                            </dd>
                                                        <?endif;?>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 my-2">
                                            <div class="card">
                                                <div class="card-header header-elements-inline blank_detail-card_headers">
                                                    <h6 class="card-title"><?= Loc::getMessage("SPOD_ORDER_SHIPMENT")?></h6>
                                                    <div class="header-elements">
                                                        <div class="list-icons">
                                                            <a class="list-icons-item" data-action="collapse"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body bg-light mb-0">
                                                    <dl class="row mb-0">
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage("SPOD_ORDER_DELIVERY")?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=$arResult['DELIVERY']['NAME']?>
                                                        </dd>
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage("SPOD_ORDER_SHIPMENT_STATUS")?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=$arResult['STATUS']['NAME']?>
                                                        </dd>
                                                        <dt class="col-sm-3">
                                                            <?=Loc::getMessage("SPOD_DELIVERY")?>
                                                        </dt>
                                                        <dd class="col-sm-9">
                                                            <?=$arResult['PRICE_DELIVERY_FORMATED']?>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="blank_detail_table">
                                    <?
                                    $APPLICATION->IncludeComponent(
                                        "bitrix:main.ui.filter",
                                        "b2b_order_detail", [
                                        'FILTER_ID' => 'PRODUCT_LIST',
                                        'GRID_ID' => 'PRODUCT_LIST',
                                        'FILTER' => [
                                            [
                                                'id' => 'NAME',
                                                'name' =>Loc::getMessage('SPOD_NAME'),
                                                'type' => 'string'
                                            ],
                                            [
                                                'id' => 'ARTICLE',
                                                'name' =>Loc::getMessage('SPOD_ARTICLE'),
                                                'type' => 'string'
                                            ],
                                            [
                                                'id' => 'QUANTITY',
                                                'name' =>Loc::getMessage('SPOD_QUANTITY'),
                                                'type' => 'string'
                                            ],
                                            [
                                                'id' => 'PRICE',
                                                'name' =>Loc::getMessage('SPOD_PRICE'),
                                                'type' => 'string'
                                            ],
                                            [
                                                'id' => 'SUM',
                                                'name' =>Loc::getMessage('SPOD_ORDER_PRICE_WITHOUT_DOTS'),
                                                'type' => 'string'
                                            ],
                                        ],
                                        'ENABLE_LIVE_SEARCH' => true,
                                        'ENABLE_LABEL' => true
                                    ]);
                                    ?>
                                    <?
                                    $APPLICATION->IncludeComponent(
                                        'bitrix:main.ui.grid',
                                        '',
                                        array(
                                            'GRID_ID' => 'PRODUCT_LIST',
                                            'HEADERS' => array(
                                                array(
                                                    "id" => "NAME",
                                                    "name" =>Loc::getMessage('SPOD_NAME'),
                                                    "sort" => "NAME",
                                                    "default" => true
                                                ),
                                                array(
                                                    "id" => "ARTICLE",
                                                    "name" =>Loc::getMessage('SPOD_ARTICLE'),
                                                    "sort" => "ARTICLE",
                                                    "default" => true
                                                ),
                                                array(
                                                    "id" => "QUANTITY",
                                                    "name" =>Loc::getMessage('SPOD_QUANTITY'),
                                                    "sort" => "QUANTITY",
                                                    "default" => true
                                                ),
                                                array(
                                                    "id" => "DISCOUNT",
                                                    "name" =>Loc::getMessage('SPOD_DISCOUNT'),
                                                    "sort" => "DISCOUNT",
                                                    "default" => true
                                                ),
                                                array(
                                                    "id" => "PRICE",
                                                    "name" =>Loc::getMessage('SPOD_PRICE'),
                                                    "sort" => "PRICE",
                                                    "default" => true
                                                ),
                                                array(
                                                    "id" => "SUM",
                                                    "name" =>Loc::getMessage('SPOD_ORDER_PRICE_WITHOUT_DOTS'),
                                                    "sort" => "SUM",
                                                    "default" => true
                                                ),
                                            ),
                                            'ROWS' => $arResult['PRODUCT_ROWS'],
                                            'FILTER_STATUS_NAME' => '',
                                            'AJAX_MODE' => 'Y',
                                            "AJAX_OPTION_JUMP" => "N",
                                            "AJAX_OPTION_STYLE" => "N",
                                            "AJAX_OPTION_HISTORY" => "N",

                                            "ALLOW_COLUMNS_SORT" => true,
                                            "ALLOW_ROWS_SORT" => array(),
                                            "ALLOW_COLUMNS_RESIZE" => true,
                                            "ALLOW_HORIZONTAL_SCROLL" => true,
                                            "ALLOW_SORT" => false,
                                            "ALLOW_PIN_HEADER" => true,
                                            "ACTION_PANEL" => array(),

                                            "SHOW_CHECK_ALL_CHECKBOXES" => false,
                                            "SHOW_ROW_CHECKBOXES" => false,
                                            "SHOW_ROW_ACTIONS_MENU" => true,
                                            "SHOW_GRID_SETTINGS_MENU" => true,
                                            "SHOW_NAVIGATION_PANEL" => true,
                                            "SHOW_PAGINATION" => true,
                                            "SHOW_SELECTED_COUNTER" => false,
                                            "SHOW_TOTAL_COUNTER" => true,
                                            "SHOW_PAGESIZE" => true,
                                            "SHOW_ACTION_PANEL" => true,

                                            "ENABLE_COLLAPSIBLE_ROWS" => true,
                                            'ALLOW_SAVE_ROWS_STATE' => true,

                                            "SHOW_MORE_BUTTON" => false,
                                            '~NAV_PARAMS' => $arResult['GET_LIST_PARAMS']['NAV_PARAMS'],
                                            'NAV_OBJECT' => $arResult['NAV_OBJECT'],
                                            'NAV_STRING' => $arResult['NAV_STRING'],
                                            "TOTAL_ROWS_COUNT" => count($arResult['PRODUCT_ROWS']),
                                            "CURRENT_PAGE" => $arResult['CURRENT_PAGE'],
                                            "PAGE_SIZES" => 20,
                                            "DEFAULT_PAGE_SIZE" => 50
                                        ),
                                        $component,
                                        array('HIDE_ICONS' => 'Y')
                                    );
                                    ?>
                                </div>
                                <div class="blank_detail-total">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tbody>
                                            <tr>
                                                <th rowspan="2" class="text-center"><h4><?=Loc::getMessage("SPOD_ORDER_BASKET")?></h4></th>
                                                <th class="text-center"><?=Loc::getMessage("SPOD_QUANTITY")?></th>
                                                <th class="text-center"><?=Loc::getMessage("SPOD_ORDER_PRICE_WITHOUT_DOTS")?></th>
                                                <th class="text-center"><?=Loc::getMessage("SPOD_TAX")?></th>
                                                <th class="text-center"><?=Loc::getMessage("SPOD_WEIGHT")?></th>
                                                <th class="text-center"><?=Loc::getMessage("SPOD_DELIVERY")?></th>
                                                <th class="text-center"><?=Loc::getMessage("SPOD_SUMMARY")?></th>
                                            </tr>
                                            <tr>
                                                <td class="text-center"><?=$arResult['FULL_QUANTITY']?></td>
                                                <td class="text-center"><?=$arResult['PRODUCT_SUM_FORMATED']?></td>
                                                <td class="text-center"><?=$arResult['TAX_VALUE_FORMATED']?></td>
                                                <td class="text-center"><?=$arResult['ORDER_WEIGHT']?><?=Loc::getMessage('SPOD_WEIGHT_MEASURE')?></td>
                                                <td class="text-center"><?=$arResult['PRICE_DELIVERY_FORMATED']?></td>
                                                <td class="text-center"><?=$arResult['PRICE_FORMATED']?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!--basic-tab2-->
                            <div class="tab-pane fade" id="basic-tab2">
                                <div class="blank_detail_table">
                                    <?
                                    $APPLICATION->IncludeComponent(
                                        "bitrix:main.ui.filter",
                                        "b2b_order_detail", [
                                        'FILTER_ID' => 'PRODUCT_LIST_2',
                                        'GRID_ID' => 'PRODUCT_LIST_2',
                                        'FILTER' => [
                                            [
                                                'id' => 'NAME',
                                                'name' =>Loc::getMessage('SPOD_NAME'),
                                                'type' => 'string'
                                            ],
                                            [
                                                'id' => 'ARTICLE',
                                                'name' =>Loc::getMessage('SPOD_ARTICLE'),
                                                'type' => 'string'
                                            ],
                                            [
                                                'id' => 'QUANTITY',
                                                'name' =>Loc::getMessage('SPOD_QUANTITY'),
                                                'type' => 'string'
                                            ],
                                            [
                                                'id' => 'SUM',
                                                'name' =>Loc::getMessage('SPOD_PRICE'),
                                                'type' => 'string'
                                            ],
                                        ],
                                        'ENABLE_LIVE_SEARCH' => true,
                                        'ENABLE_LABEL' => true
                                    ]);
                                    ?>
                                    <?
                                    $APPLICATION->IncludeComponent(
                                        'bitrix:main.ui.grid',
                                        '',
                                        array(
                                            'GRID_ID' => 'PRODUCT_LIST_2',
                                            'HEADERS' => array(
                                                array(
                                                    "id" => "NAME",
                                                    "name" =>Loc::getMessage('SPOD_NAME'),
                                                    "sort" => "NAME",
                                                    "default" => true
                                                ),
                                                array(
                                                    "id" => "ARTICLE",
                                                    "name" =>Loc::getMessage('SPOD_ARTICLE'),
                                                    "sort" => "ARTICLE",
                                                    "default" => true
                                                ),
                                                array(
                                                    "id" => "QUANTITY",
                                                    "name" =>Loc::getMessage('SPOD_QUANTITY'),
                                                    "sort" => "QUANTITY",
                                                    "default" => true
                                                ),
                                                array(
                                                    "id" => "SUM",
                                                    "name" =>Loc::getMessage('SPOD_PRICE'),
                                                    "sort" => "SUM",
                                                    "default" => true
                                                ),
                                            ),
                                            'ROWS' => $arResult['PRODUCT_2_ROWS'],
                                            'FILTER_STATUS_NAME' => '',
                                            'AJAX_MODE' => 'Y',
                                            "AJAX_OPTION_JUMP" => "N",
                                            "AJAX_OPTION_STYLE" => "N",
                                            "AJAX_OPTION_HISTORY" => "N",

                                            "ALLOW_COLUMNS_SORT" => true,
                                            "ALLOW_ROWS_SORT" => array(),
                                            "ALLOW_COLUMNS_RESIZE" => true,
                                            "ALLOW_HORIZONTAL_SCROLL" => true,
                                            "ALLOW_SORT" => false,
                                            "ALLOW_PIN_HEADER" => true,
                                            "ACTION_PANEL" => array(),

                                            "SHOW_CHECK_ALL_CHECKBOXES" => false,
                                            "SHOW_ROW_CHECKBOXES" => false,
                                            "SHOW_ROW_ACTIONS_MENU" => true,
                                            "SHOW_GRID_SETTINGS_MENU" => true,
                                            "SHOW_NAVIGATION_PANEL" => true,
                                            "SHOW_PAGINATION" => true,
                                            "SHOW_SELECTED_COUNTER" => false,
                                            "SHOW_TOTAL_COUNTER" => true,
                                            "SHOW_PAGESIZE" => true,
                                            "SHOW_ACTION_PANEL" => true,

                                            "ENABLE_COLLAPSIBLE_ROWS" => true,
                                            'ALLOW_SAVE_ROWS_STATE' => true,

                                            "SHOW_MORE_BUTTON" => false,
                                            '~NAV_PARAMS' => $arResult['GET_LIST_PARAMS']['NAV_PARAMS'],
                                            'NAV_OBJECT' => $arResult['NAV_OBJECT'],
                                            'NAV_STRING' => $arResult['NAV_STRING'],
                                            "TOTAL_ROWS_COUNT" => count($arResult['PRODUCT_2_ROWS']),
                                            "CURRENT_PAGE" => $arResult['CURRENT_PAGE'],
                                            "PAGE_SIZES" => 20,
                                            "DEFAULT_PAGE_SIZE" => 50
                                        ),
                                        $component,
                                        array('HIDE_ICONS' => 'Y')
                                    );
                                    ?>
                                </div>
                            </div>
                            <!--basic-tab3-->
                            <div class="tab-pane fade" id="basic-tab3">
                                <?
                                /*$APPLICATION->IncludeComponent(
                                    "bitrix:main.ui.filter",
                                    "b2b_order_detail", [
                                    'FILTER_ID' => 'DOCUMENTS_LIST',
                                    'GRID_ID' => 'DOCUMENTS_LIST',
                                    'FILTER' => [
                                        [
                                            'id' => 'NUMBER',
                                            'name' =>Loc::getMessage('SPOD_NUMBER'),
                                            'type' => 'string'
                                        ],
                                        [
                                            'id' => 'DOC',
                                            'name' =>Loc::getMessage('SPOD_DOC'),
                                            'type' => 'string'
                                        ],
                                        [
                                            'id' => 'DATE_CREATED_from',
                                            'name' =>Loc::getMessage('SPOD_DATE_CREATED'),
                                            'type' => 'date'
                                        ],
                                        [
                                            'id' => 'DATE_UPDATED',
                                            'name' =>Loc::getMessage('SPOD_DATE_UPDATED'),
                                            'type' => 'date'
                                        ],
                                        [
                                            'id' => 'ORGANIZATION',
                                            'name' =>Loc::getMessage('SPOD_ORGANIZATION'),
                                            'type' => 'string'
                                        ],
                                    ],
                                    'ENABLE_LIVE_SEARCH' => true,
                                    'ENABLE_LABEL' => true
                                ]);*/
                                ?>
                                <?
                                $APPLICATION->IncludeComponent(
                                    'bitrix:main.ui.grid',
                                    '',
                                    array(
                                        'GRID_ID' => 'DOCUMENTS_LIST',
                                        'HEADERS' => array(
                                            array(
                                                "id" => "NUMBER",
                                                "name" =>Loc::getMessage('SPOD_NUMBER'),
                                                "sort" => "NUMBER",
                                                "default" => true
                                            ),
                                            array(
                                                "id" => "DOC",
                                                "name" =>Loc::getMessage('SPOD_DOC'),
                                                "sort" => "DOC",
                                                "default" => true
                                            ),
                                            array(
                                                "id" => "DATE_CREATED",
                                                "name" =>Loc::getMessage('SPOD_DATE_CREATED'),
                                                "sort" => "DATE_CREATED",
                                                "default" => true
                                            ),
                                            array(
                                                "id" => "DATE_UPDATED",
                                                "name" =>Loc::getMessage('SPOD_DATE_UPDATED'),
                                                "sort" => "DATE_UPDATED",
                                                "default" => true
                                            ),
                                            array(
                                                "id" => "ORGANIZATION",
                                                "name" =>Loc::getMessage('SPOD_ORGANIZATION'),
                                                "sort" => "ORGANIZATION",
                                                "default" => true
                                            ),

                                        ),
                                        'ROWS' => $arResult['DOCS_ROWS'],
                                        'FILTER_STATUS_NAME' => '',
                                        'AJAX_MODE' => 'Y',
                                        "AJAX_OPTION_JUMP" => "N",
                                        "AJAX_OPTION_STYLE" => "N",
                                        "AJAX_OPTION_HISTORY" => "N",

                                        "ALLOW_COLUMNS_SORT" => true,
                                        "ALLOW_ROWS_SORT" => array(),
                                        "ALLOW_COLUMNS_RESIZE" => true,
                                        "ALLOW_HORIZONTAL_SCROLL" => true,
                                        "ALLOW_SORT" => false,
                                        "ALLOW_PIN_HEADER" => true,
                                        "ACTION_PANEL" => array(),

                                        "SHOW_CHECK_ALL_CHECKBOXES" => false,
                                        "SHOW_ROW_CHECKBOXES" => false,
                                        "SHOW_ROW_ACTIONS_MENU" => true,
                                        "SHOW_GRID_SETTINGS_MENU" => true,
                                        "SHOW_NAVIGATION_PANEL" => true,
                                        "SHOW_PAGINATION" => true,
                                        "SHOW_SELECTED_COUNTER" => false,
                                        "SHOW_TOTAL_COUNTER" => true,
                                        "SHOW_PAGESIZE" => true,
                                        "SHOW_ACTION_PANEL" => true,

                                        "ENABLE_COLLAPSIBLE_ROWS" => true,
                                        'ALLOW_SAVE_ROWS_STATE' => true,

                                        "SHOW_MORE_BUTTON" => false,
                                        '~NAV_PARAMS' => $arResult['GET_LIST_PARAMS']['NAV_PARAMS'],
                                        'NAV_OBJECT' => $arResult['NAV_OBJECT'],
                                        'NAV_STRING' => $arResult['NAV_STRING'],
                                        "TOTAL_ROWS_COUNT" => count($arResult['DOCS_ROWS']),
                                        "CURRENT_PAGE" => $arResult['CURRENT_PAGE'],
                                        "PAGE_SIZES" => 20,
                                        "DEFAULT_PAGE_SIZE" => 50
                                    ),
                                    $component,
                                    array('HIDE_ICONS' => 'Y')
                                );
                                ?>
                            </div>
                            <!--basic-tab4-->
                            <div class="tab-pane fade" id="basic-tab4">
                                <?
                                /*$APPLICATION->IncludeComponent(
                                    "bitrix:main.ui.filter",
                                    "b2b_order_detail", [
                                    'FILTER_ID' => 'PAY_SYSTEMS_LIST',
                                    'GRID_ID' => 'PAY_SYSTEMS_LIST',
                                    'FILTER' => [
                                        [
                                            'id' => 'NUMBER',
                                            'name' =>Loc::getMessage('SPOD_NUMBER'),
                                            'type' => 'string'
                                        ],
                                        [
                                            'id' => 'DOCUMENT',
                                            'name' =>Loc::getMessage('SPOD_DOC'),
                                            'type' => 'string'
                                        ],
                                        [
                                            'id' => 'DATE_CREATED',
                                            'name' =>Loc::getMessage('SPOD_DATE_CREATED'),
                                            'type' => 'date'
                                        ],
                                        [
                                            'id' => 'DATE_UPDATED',
                                            'name' =>Loc::getMessage('SPOD_DATE_UPDATED'),
                                            'type' => 'date'
                                        ],
                                        [
                                            'id' => 'ORGANIZATION',
                                            'name' =>Loc::getMessage('SPOD_ORGANIZATION'),
                                            'type' => 'string'
                                        ],
                                    ],
                                    'ENABLE_LIVE_SEARCH' => true,
                                    'ENABLE_LABEL' => true
                                ]);*/
                                ?>
                                <?
                                $APPLICATION->IncludeComponent(
                                    'bitrix:main.ui.grid',
                                    '',
                                    array(
                                        'GRID_ID' => 'PAY_SYSTEMS_LIST',
                                        'HEADERS' => array(
                                            array(
                                                "id" => "NUMBER",
                                                "name" =>Loc::getMessage('SPOD_NUMBER'),
                                                "sort" => "NUMBER",
                                                "default" => true
                                            ),
                                            array(
                                                "id" => "NAME",
                                                "name" =>Loc::getMessage('SPOD_PRODUCT_NAME'),
                                                "sort" => "NAME",
                                                "default" => true
                                            ),
                                            array(
                                                "id" => "DATE_CREATED",
                                                "name" =>Loc::getMessage('SPOD_DATE_CREATED'),
                                                "sort" => "DATE_CREATED",
                                                "default" => true
                                            ),
                                            /*array(
                                                "id" => "DATE_UPDATED",
                                                "name" =>Loc::getMessage('SPOD_DATE_UPDATED'),
                                                "sort" => "DATE_UPDATED",
                                                "default" => true
                                            ),*/
                                            array(
                                                "id" => "SUM",
                                                "name" =>Loc::getMessage('SPOL_SUM'),
                                                "sort" => "SUM",
                                                "default" => true
                                            ),
                                            array(
                                                "id" => "IS_PAID",
                                                "name" =>Loc::getMessage('SPOL_PAYMENT_IS_PAID'),
                                                "sort" => "IS_PAID",
                                                "default" => true
                                            ),
                                            array(
                                                "id" => "ORGANIZATION",
                                                "name" =>Loc::getMessage('SPOD_ORGANIZATION'),
                                                "sort" => "ORGANIZATION",
                                                "default" => true
                                            ),

                                        ),
                                        'ROWS' => $arResult['PAY_SYSTEM_ROWS'],
                                        'FILTER_STATUS_NAME' => '',
                                        'AJAX_MODE' => 'Y',
                                        "AJAX_OPTION_JUMP" => "N",
                                        "AJAX_OPTION_STYLE" => "N",
                                        "AJAX_OPTION_HISTORY" => "N",

                                        "ALLOW_COLUMNS_SORT" => true,
                                        "ALLOW_ROWS_SORT" => array(),
                                        "ALLOW_COLUMNS_RESIZE" => true,
                                        "ALLOW_HORIZONTAL_SCROLL" => true,
                                        "ALLOW_SORT" => false,
                                        "ALLOW_PIN_HEADER" => true,
                                        "ACTION_PANEL" => array(),

                                        "SHOW_CHECK_ALL_CHECKBOXES" => false,
                                        "SHOW_ROW_CHECKBOXES" => false,
                                        "SHOW_ROW_ACTIONS_MENU" => true,
                                        "SHOW_GRID_SETTINGS_MENU" => true,
                                        "SHOW_NAVIGATION_PANEL" => true,
                                        "SHOW_PAGINATION" => true,
                                        "SHOW_SELECTED_COUNTER" => false,
                                        "SHOW_TOTAL_COUNTER" => true,
                                        "SHOW_PAGESIZE" => true,
                                        "SHOW_ACTION_PANEL" => true,

                                        "ENABLE_COLLAPSIBLE_ROWS" => true,
                                        'ALLOW_SAVE_ROWS_STATE' => true,

                                        "SHOW_MORE_BUTTON" => false,
                                        '~NAV_PARAMS' => $arResult['GET_LIST_PARAMS']['NAV_PARAMS'],
                                        'NAV_OBJECT' => $arResult['NAV_OBJECT'],
                                        'NAV_STRING' => $arResult['NAV_STRING'],
                                        "TOTAL_ROWS_COUNT" => count($arResult['PAY_SYSTEM_ROWS']),
                                        "CURRENT_PAGE" => $arResult['CURRENT_PAGE'],
                                        "PAGE_SIZES" => 20,
                                        "DEFAULT_PAGE_SIZE" => 50
                                    ),
                                    $component,
                                    array('HIDE_ICONS' => 'Y')
                                );
                                ?>
                            </div>
                            <!--basic-tab5-->
                            <div class="tab-pane fade" id="basic-tab5">
                                <?
                                /*$APPLICATION->IncludeComponent(
                                    "bitrix:main.ui.filter",
                                    "b2b_order_detail", [
                                    'FILTER_ID' => 'SHIPMENT_LIST',
                                    'GRID_ID' => 'SHIPMENT_LIST',
                                    'FILTER' => [
                                        [
                                            "id" => "NUMBER",
                                            "name" =>Loc::getMessage('SPOD_NUMBER'),
                                            'type' => 'string'
                                        ],
                                        [
                                            "id" => "NAME",
                                            "name" =>Loc::getMessage('SPOD_PRODUCT_NAME'),
                                            'type' => 'string'
                                        ],
//                                        [
//                                            "id" => "STATUS",
//                                            "name" =>Loc::getMessage('SPOD_STATUS'),
//                                            'type' => 'date'
//                                        ],
//                                        [
//                                            "id" => "SUMM",
//                                            "name" =>Loc::getMessage('SPOD_ORDER_PRICE_BILL'),
//                                            'type' => 'string'
//                                        ],
                                    ],
                                    'ENABLE_LIVE_SEARCH' => true,
                                    'ENABLE_LABEL' => true
                                ]);*/
                                ?>
                                <?
                                $APPLICATION->IncludeComponent(
                                    'bitrix:main.ui.grid',
                                    '',
                                    array(
                                        'GRID_ID' => 'SHIPMENT_LIST',
                                        'HEADERS' => array(
                                            array(
                                                "id" => "NUMBER",
                                                "name" =>Loc::getMessage('SPOD_NUMBER'),
                                                "sort" => "NUMBER",
                                                "default" => true
                                            ),
                                            array(
                                                "id" => "NAME",
                                                "name" =>Loc::getMessage('SPOD_PRODUCT_NAME'),
                                                "sort" => "NAME",
                                                "default" => true
                                            ),
                                            array(
                                                "id" => "STATUS",
                                                "name" =>Loc::getMessage('SPOD_STATUS'),
                                                "sort" => "STATUS",
                                                "default" => true
                                            ),
                                            array(
                                                "id" => "SUMM",
                                                "name" =>Loc::getMessage('SPOD_ORDER_PRICE_BILL'),
                                                "sort" => "SUMM",
                                                "default" => true
                                            ),
                                        ),
                                        'ROWS' => $arResult['SHIPMENT_ROWS'],
                                        'FILTER_STATUS_NAME' => '',
                                        'AJAX_MODE' => 'Y',
                                        "AJAX_OPTION_JUMP" => "N",
                                        "AJAX_OPTION_STYLE" => "N",
                                        "AJAX_OPTION_HISTORY" => "N",

                                        "ALLOW_COLUMNS_SORT" => true,
                                        "ALLOW_ROWS_SORT" => array(),
                                        "ALLOW_COLUMNS_RESIZE" => true,
                                        "ALLOW_HORIZONTAL_SCROLL" => true,
                                        "ALLOW_SORT" => false,
                                        "ALLOW_PIN_HEADER" => true,
                                        "ACTION_PANEL" => array(),

                                        "SHOW_CHECK_ALL_CHECKBOXES" => false,
                                        "SHOW_ROW_CHECKBOXES" => false,
                                        "SHOW_ROW_ACTIONS_MENU" => true,
                                        "SHOW_GRID_SETTINGS_MENU" => true,
                                        "SHOW_NAVIGATION_PANEL" => true,
                                        "SHOW_PAGINATION" => true,
                                        "SHOW_SELECTED_COUNTER" => false,
                                        "SHOW_TOTAL_COUNTER" => true,
                                        "SHOW_PAGESIZE" => true,
                                        "SHOW_ACTION_PANEL" => true,

                                        "ENABLE_COLLAPSIBLE_ROWS" => true,
                                        'ALLOW_SAVE_ROWS_STATE' => true,

                                        "SHOW_MORE_BUTTON" => false,
                                        '~NAV_PARAMS' => $arResult['GET_LIST_PARAMS']['NAV_PARAMS'],
                                        'NAV_OBJECT' => $arResult['NAV_OBJECT'],
                                        'NAV_STRING' => $arResult['NAV_STRING'],
                                        "TOTAL_ROWS_COUNT" => count($arResult['SHIPMENT_ROWS']),
                                        "CURRENT_PAGE" => $arResult['CURRENT_PAGE'],
                                        "PAGE_SIZES" => 20,
                                        "DEFAULT_PAGE_SIZE" => 50
                                    ),
                                    $component,
                                    array('HIDE_ICONS' => 'Y')
                                );
                                ?>
                            </div>
                            <!--basic-tab6-->
                            <div class="tab-pane fade" id="basic-tab6">
                                <?
                                $APPLICATION->IncludeComponent(
                                    "bitrix:support.ticket.edit",
                                    "b2bcabinet_detail",
                                    array(
                                        "ID" => $arResult["TICKET"]["ID"],
                                        "MESSAGES_PER_PAGE" => "20",
                                        "MESSAGE_MAX_LENGTH" => "70",
                                        "MESSAGE_SORT_ORDER" => "asc",
                                        "SET_PAGE_TITLE" => "N",
                                        "SHOW_COUPON_FIELD" => "N",
                                        "TICKET_EDIT_TEMPLATE" => "#",
                                        "TICKET_LIST_URL" => "ticket_list.php",
                                        "ORDER_ID" => $arResult["ID"],
                                        "COMPONENT_TEMPLATE" => "b2bcabinet_detail"
                                    ),
                                    false
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?
	/*$javascriptParams = array(
		"url" => CUtil::JSEscape($this->__component->GetPath().'/ajax.php'),
		"templateFolder" => CUtil::JSEscape($templateFolder),
		"templateName" => $this->__component->GetTemplateName(),
        'changePayment' => '.sale-order-detail-payment-options-methods-info-change-link',
        'changePaymentWrapper' => '.payment-wrapper',
		"paymentList" => $paymentData,
	);
	$javascriptParams = CUtil::PhpToJSObject($javascriptParams);*/
    ?>


    <script>
        $(function ()
        {
            var b2bOrder = new B2bOrderDetail({
                'ajaxUrl': '<?= CUtil::JSEscape($this->__component->GetPath() . '/ajax.php');?>',
                'changePayment': '.sale-order-detail-payment-options-methods-info-change-link',
                'changePaymentWrapper': '.payment-wrapper',
                "paymentList": <?= CUtil::PhpToJSObject($paymentData);?>,
                "arParams":<?= Json::encode($arResult['PARAMS']); ?>,
                'filter':<?= Json::encode($arResult['FILTER_EXCEL']);?>,
                'qnts':<?= Json::encode($arResult['QNTS']);?>,
                "arResult":<?= CUtil::PhpToJSObject($arResult['BASKET'], false, true); ?>,
                "TemplateFolder": '<?= $templateFolder?>',
                "OrderId": "<?= $arResult["ID"] ?>",
                "Headers":<?= CUtil::PhpToJSObject($Headers, false, true); ?>,
                "HeadersSum":<?= CUtil::PhpToJSObject($HeadersSum, false, true); ?>,
                "TemplateName": 'b2bcabinet',
            });
        })

        $('.b2b_detail_order__second__tab__btn').on('click', function ()
        {
            $('.b2b_detail_order__second__tab__btn__block').toggle();
        });

        $('.b2b_detail_order__nav_ul__block a').click(function (e)
        {
            e.preventDefault();
            $(this).tab('show');
        })
        //BX.Sale.PersonalOrderComponent.PersonalOrderDetail.init(<?//=$javascriptParams?>//);
    </script>
    <?
}
?>