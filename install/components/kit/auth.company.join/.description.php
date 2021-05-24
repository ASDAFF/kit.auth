<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
$arComponentDescription = array(
	"NAME" => Loc::getMessage("KIT_AUTH_COMPANY_JOIN_TITLE"),
	"DESCRIPTION" => Loc::getMessage("KIT_AUTH_COMPANY_JOIN_DESCR"),
	"PATH" => array(
			"ID" => "utility",
			"CHILD" => array(
				"ID" => "user",
				"NAME" => Loc::getMessage("KIT_AUTH_COMPANY_JOIN_GROUP_NAME")
			),
		),
);
?>