<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Kit\B2bCabinet\Helper\Config;

$methodIstall = Config::getMethodInstall(SITE_ID) == 'AS_TEMPLATE' ? SITE_DIR.\KitB2bCabinet::PATH : SITE_DIR;
?>
<div class="company-list__group-button">
    <?if($arResult["�AN_JOIN"] && $arResult["�AN_JOIN"]=="Y"):?>
        <a type="button" href="javascript:void(0)" class="btn index_company-join_organization-button">
            <?=Loc::getMessage('SPOL_ADD_JOIN_PROFILE')?>
        </a>
    <?endif;?>

    <a type="button" href="<?=$methodIstall?>/personal/companies/add.php" class="btn index_company-add_organization-button">
        <?=Loc::getMessage('SPOL_ADD_NEW_PROFILE')?>
    </a>
</div>
<?

if(strlen($arResult["ERROR_MESSAGE"]) > 0)
{
    ShowError($arResult["ERROR_MESSAGE"]);
}

if(count($arResult["ROWS"]))
{
    $APPLICATION->IncludeComponent(
        'bitrix:main.ui.grid',
        '',
        [
            'GRID_ID' => 'PERSONAL_PROFILE_LIST',

            'HEADERS' => $arParams['GRID_HEADER'],
            'ROWS' => $arResult['ROWS'],

            'AJAX_MODE' => 'Y',
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "N",
            "AJAX_OPTION_HISTORY" => "N",

            "ALLOW_COLUMNS_SORT" => true,
            "ALLOW_ROWS_SORT" => $arParams['ALLOW_COLUMNS_SORT'],
            "ALLOW_COLUMNS_RESIZE" => true,
            "ALLOW_HORIZONTAL_SCROLL" => true,
            "ALLOW_SORT" => true,
            "ALLOW_PIN_HEADER" => true,
            "ACTION_PANEL" => $arResult['GROUP_ACTIONS'],

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
            "TOTAL_ROWS_COUNT" => count($arResult['ROWS']),
            "CURRENT_PAGE" => $arResult['CURRENT_PAGE'],
            "PAGE_SIZES" => $arParams['ORDERS_PER_PAGE'],
            "DEFAULT_PAGE_SIZE" => 50
        ],
        $component,
        ['HIDE_ICONS' => 'Y']
    );
}
else
{
    ?>
    <h3><?= Loc::getMessage("STPPL_EMPTY_PROFILE_LIST") ?></h3>
    <?
}
?>


<div class="wrap-popup-window popup-join-company" style="display: none;">
    <div class="modal-popup-bg" onclick="closeModal();">&nbsp;</div>
    <div class="popup-window">
        <div class="popup-close" onclick="closeModal();"></div>
        <div class="popup-content">
            <?$APPLICATION->IncludeComponent(
                "kit:auth.company.join",
                "",
                array(
                ),
                false
            );?>
        </div>
    </div>
</div>
