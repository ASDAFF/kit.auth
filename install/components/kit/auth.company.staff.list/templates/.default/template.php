<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div class="staff_wrapper card">

    <div class="staff-list_detail-menu">
        <ul class="nav nav-tabs nav-tabs-highlight .b2b_detail_order__nav_ul__block">
            <li class="nav-item">
                <a href="#basic-tab1" class="nav-link first-item active" data-toggle="tab">
                    <?=GetMessage("KIT_COMPANY_STAFF_LIST_TAB_1_TEXT")?>
                </a>
            </li>
            <?if($arResult['CONFIRM_N']['ROWS']):?>
                <li class="nav-item">
                    <a href="#basic-tab2" class="nav-link" data-toggle="tab">
                        <?=GetMessage("KIT_COMPANY_STAFF_LIST_TAB_2_TEXT")?>
                    </a>
                </li>
            <?endif;?>
        </ul>
    </div>
    <div class="tab-content">
        <?if($arResult["IS_ADMIN"]):?>
            <div class="staff-register-btn">
                <button id="staff-list__add-staff" type="button" class="btn btn-light add_staff btn_b2b" data-toggle="modal">
                    <?=GetMessage("KIT_COMPANY_STAFF_STAFF_REGISTER_BTN")?>
                </button>
            </div>
        <?endif;?>
        <div class="tab-pane show active" id="basic-tab1">

            <div class="main-ui-filter-search-wrapper">
                <?
                $APPLICATION->IncludeComponent(
                    "bitrix:main.ui.filter",
                    "b2bcabinet",
                    array(
                        "FILTER_ID" => "STAFF_LIST",
                        "GRID_ID" => "STAFF_LIST",
                        'FILTER' => [
                            ['id' => 'ID', 'name' => GetMessage("KIT_COMPANY_STAFF_HEADER_ID_TITLE"), 'type' => 'string'],
                            ['id' => 'FULL_NAME', 'name' => GetMessage("KIT_COMPANY_STAFF_HEADER_FULL_NAME_TITLE"), 'type' => 'string'],
                            ['id' => 'COMPANY', 'name' => GetMessage("KIT_COMPANY_STAFF_HEADER_COMPANY_TITLE"), 'type' => 'string'],
                            ['id' => 'WORK_POSITION', 'name' => GetMessage("KIT_COMPANY_STAFF_HEADER_WORK_POSITION_TITLE"), 'type' => 'list', 'params' => ['multiple' => 'Y'], 'items' => $arResult["FILTER_ROLES"]],
                        ],
                        "ENABLE_LIVE_SEARCH" => true,
                        "ENABLE_LABEL" => true,
                        "COMPONENT_TEMPLATE" => ".default"
                    ),
                    false
                );
                ?>


            </div>
            <div class="checkbox_show-all">
                <div class="form-check">
                    <label for="show-all-users" class="form-check-label">
                        <input type="checkbox" name="show-all-users" class="form-input-styled" onclick="showAllUsers();" <?=($_SESSION["SHOW_ALL_USERS"] == "Y") ? 'checked' : ''?>>
                        <?=GetMessage("KIT_COMPANY_STAFF_LABEL_SHOW_ALL")?>
                    </label>
                </div>
            </div>

            <?
            $APPLICATION->IncludeComponent(
                'bitrix:main.ui.grid',
                '',
                array(
                    'GRID_ID' => 'STAFF_LIST',
                    'HEADERS' => array(
                        array("id" => "ID", "name" => GetMessage("KIT_COMPANY_STAFF_HEADER_ID_TITLE"), "sort" => "USER_ID", "default" => false, "editable" => false),
                        array("id" => "FULL_NAME", "name" => GetMessage("KIT_COMPANY_STAFF_HEADER_FULL_NAME_TITLE"), "sort" => "LAST_NAME", "default" => true, "editable" => false),
                        array("id" => "COMPANY", "name" => GetMessage("KIT_COMPANY_STAFF_HEADER_COMPANY_TITLE"),  "sort" => "NAME_COMPANY",  "default" => true,  "editable" => false),
                        array("id" => "WORK_POSITION", "name" => GetMessage("KIT_COMPANY_STAFF_HEADER_WORK_POSITION_TITLE"), "sort" => "ROLE", "default" => true, "editable" => false),
                        array("id" => "USER_SHOW_GROUPS", "name" => GetMessage("KIT_COMPANY_STAFF_HEADER_USER_SHOW_GROUPS_TITLE"), "default" => true, "editable" => false),
                    ),
                    'ROWS' => $arResult['CONFIRM_Y']['ROWS'],
                    'AJAX_MODE' => 'Y',

                    "AJAX_OPTION_JUMP" => "N",
                    "AJAX_OPTION_STYLE" => "N",
                    "AJAX_OPTION_HISTORY" => "N",

                    "ALLOW_COLUMNS_SORT" => true,
                    "ALLOW_ROWS_SORT" => ['ID', 'COMPANY', 'WORK_POSITION'],
                    "ALLOW_COLUMNS_RESIZE" => true,
                    "ALLOW_HORIZONTAL_SCROLL" => true,
                    "ALLOW_SORT" => true,
                    "ALLOW_PIN_HEADER" => true,
                    "ACTION_PANEL" => [],

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
                    'NAV_STRING' => $arResult['NAV_STRING_STAFF_A'],
                    "TOTAL_ROWS_COUNT" => count($arResult['CONFIRM_Y']['ROWS']),
                    "CURRENT_PAGE" => $arResult['CURRENT_PAGE'],
                    "PAGE_SIZES" => $arParams['ORDERS_PER_PAGE'],
                    "DEFAULT_PAGE_SIZE" => 50
                ),
                $component,
                array('HIDE_ICONS' => 'Y')
            );
            ?>
        </div>
        <?if($arResult['CONFIRM_N']['ROWS']):?>
            <div class="tab-pane" id="basic-tab2">
                <div class="main-ui-filter-search-wrapper">
                    <?
                    $APPLICATION->IncludeComponent(
                        "bitrix:main.ui.filter",
                        "b2bcabinet",
                        array(
                            "FILTER_ID" => "STAFF_UNCONFIRMED_LIST",
                            "GRID_ID" => "STAFF_UNCONFIRMED_LIST",
                            'FILTER' => [
                                ['id' => 'ID', 'name' => GetMessage("KIT_COMPANY_STAFF_HEADER_ID_TITLE"), 'type' => 'string'],
                                ['id' => 'FULL_NAME', 'name' => GetMessage("KIT_COMPANY_STAFF_HEADER_FULL_NAME_TITLE"), 'type' => 'string'],
                                ['id' => 'COMPANY', 'name' => GetMessage("KIT_COMPANY_STAFF_HEADER_COMPANY_TITLE"), 'type' => 'string'],
                            ],
                            "ENABLE_LIVE_SEARCH" => true,
                            "ENABLE_LABEL" => true,
                            "COMPONENT_TEMPLATE" => ".default"
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
                        'GRID_ID' => 'STAFF_UNCONFIRMED_LIST',
                        'HEADERS' => array(
                            array("id" => "ID", "name" => GetMessage("KIT_COMPANY_STAFF_HEADER_ID_TITLE"), "sort" => "USER_ID", "default" => false, "editable" => false),
                            array("id" => "FULL_NAME", "name" => GetMessage("KIT_COMPANY_STAFF_HEADER_FULL_NAME_TITLE"), "sort" => "LAST_NAME", "default" => true, "editable" => false),
                            array("id" => "COMPANY", "name" => GetMessage("KIT_COMPANY_STAFF_HEADER_COMPANY_TITLE"), "sort" => "NAME_COMPANY", "default" => true, "editable" => false),
                            array("id" => "USER_SHOW_GROUPS", "name" => GetMessage("KIT_COMPANY_STAFF_HEADER_USER_SHOW_GROUPS_TITLE"), "default" => true, "editable" => false),
                        ),
                        'ROWS' => $arResult['CONFIRM_N']['ROWS'],
                        'AJAX_MODE' => 'Y',

                        "AJAX_OPTION_JUMP" => "N",
                        "AJAX_OPTION_STYLE" => "N",
                        "AJAX_OPTION_HISTORY" => "N",

                        "ALLOW_COLUMNS_SORT" => true,
                        "ALLOW_ROWS_SORT" => ['ID', 'COMPANY', 'WORK_POSITION'],
                        "ALLOW_COLUMNS_RESIZE" => true,
                        "ALLOW_HORIZONTAL_SCROLL" => true,
                        "ALLOW_SORT" => true,
                        "ALLOW_PIN_HEADER" => true,
                        "ACTION_PANEL" => [],

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
                        'NAV_STRING' => $arResult['NAV_STRING_STAFF_M'],
                        "TOTAL_ROWS_COUNT" => count($arResult['CONFIRM_N']['ROWS']),
                        "CURRENT_PAGE" => $arResult['CURRENT_PAGE'],
                        "PAGE_SIZES" => $arParams['ORDERS_PER_PAGE'],
                        "DEFAULT_PAGE_SIZE" => 50
                    ),
                    $component,
                    array('HIDE_ICONS' => 'Y')
                );
                ?>
            </div>
        <?endif;?>
    </div>
</div>
<?if($arResult["IS_ADMIN"]):?>
    <div class="wrap-popup-window popup-staff-register" style="display: none;">
        <div class="modal-popup-bg" onclick="closeModal();">&nbsp;</div>
        <div class="popup-window">
            <div class="popup-close" onclick="closeModal();"></div>
            <div class="popup-content">
                <?$APPLICATION->IncludeComponent(
	"kit:auth.company.staff.register",
	".default", 
	array(
		"AUTH" => "N",
		"REQUIRED_FIELDS" => array(
			0 => "EMAIL",
		),
		"SET_TITLE" => "Y",
		"SHOW_FIELDS" => array(
			0 => "EMAIL",
			1 => "NAME",
		),
		"SUCCESS_PAGE" => "",
		"USER_PROPERTY" => array(
		),
		"USER_PROPERTY_NAME" => "",
		"USE_BACKURL" => "Y",
		"COMPONENT_TEMPLATE" => ".default",
		"USE_CAPTCHA" => "N",
		"USER_GROUPS" => array(
			0 => "2",
			1 => "3",
			2 => "4",
			3 => "5",
			4 => "6",
			5 => "7",
			6 => "8",
			7 => "9",
			8 => "10",
		),
		"ABILITY_TO_SET_ROLE" => "Y"
	),
	false
);?>
            </div>
        </div>
    </div>
<?endif;?>