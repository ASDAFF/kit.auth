<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SOA_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SOA_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_profile_detail.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_personal_add",
			"NAME" => GetMessage("SOA_NAME")
		)
	),
);
?>