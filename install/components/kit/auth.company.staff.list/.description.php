<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
$arComponentDescription = array(
	"NAME" => Loc::getMessage("KIT_COMPANY_STAFF_LIST_NAME"),
	"DESCRIPTION" => Loc::getMessage("KIT_COMPANY_STAFF_LIST_DESCRIPTION"),
	"PATH" => array(
        "ID" => "kit",
        "NAME" => GetMessage("KIT_COMPONENTS_TITLE"),
    ),
);
?>