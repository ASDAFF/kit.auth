<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("COMP_SOTBIT_STAFF_REGISTER_TITLE"),
	"DESCRIPTION" => GetMessage("COMP_SOTBIT_STAFF_REGISTER_REGISTER_DESCR"),
	"ICON" => "/images/user_register.gif",
	"PATH" => array(
			"ID" => "utility",
			"CHILD" => array(
				"ID" => "user",
				"NAME" => GetMessage("MAIN_USER_GROUP_NAME")
			),
		),
);
?>