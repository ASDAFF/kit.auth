<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
$arComponentDescription = array(
	"NAME" => Loc::getMessage("KIT_AUTH_REGISTER_TITLE"),
	"DESCRIPTION" => Loc::getMessage("KIT_AUTH_REGISTER_DESCR"),
	"ICON" => "/images/user_register.gif",
	"PATH" => array(
			"ID" => "utility",
			"CHILD" => array(
				"ID" => "user",
				"NAME" => Loc::getMessage("KIT_AUTH_USER_GROUP_NAME")
			),
		),
);
?>