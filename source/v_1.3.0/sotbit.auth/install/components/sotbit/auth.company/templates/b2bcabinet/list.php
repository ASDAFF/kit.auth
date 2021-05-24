<?
use Bitrix\Main\Localization\Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/utils.js');

$APPLICATION->IncludeComponent(
	"sotbit:auth.company.list",
	"b2bcabinet",
	array(
		"PATH_TO_DETAIL" => $arResult["PATH_TO_DETAIL"],
		"PER_PAGE" => $arParams["PER_PAGE"],
		"SET_TITLE" =>$arParams["SET_TITLE"],
		"GRID_HEADER" => array(
			array("id"=>"ID", "name"=>Loc::getMessage('SOTBIT_B2BCABINET_ORGANIZATIONS_ID'), "sort"=>"ID", "default"=>true, "editable"=>false),
			array("id"=>"NAME", "name"=>Loc::getMessage('SOTBIT_B2BCABINET_ORGANIZATIONS_NAME'), "sort"=>"NAME", "default"=>true, "editable"=>false),
			array("id"=>"DATE_UPDATE", "name"=>Loc::getMessage('SOTBIT_B2BCABINET_ORGANIZATIONS_DATE_UPDATE'), "sort"=>"DATE_UPDATE", "default"=>true, "editable"=>false),
			array("id"=>"PERSON_TYPE_NAME", "name"=>Loc::getMessage('SOTBIT_B2BCABINET_ORGANIZATIONS_PERSON_TYPE_NAME'), "sort"=>"PERSON_TYPE_ID", "default"=>true, "editable"=>true),
		),
		"BUYER_PERSONAL_TYPE" => $arParams['BUYER_PERSONAL_TYPE'],
	),
	$component
);
?>
