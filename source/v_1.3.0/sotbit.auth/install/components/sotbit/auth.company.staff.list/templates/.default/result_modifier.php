<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arResult["ROLES"]){
    foreach ($arResult["ROLES"] as $role){
        $arResult["FILTER_ROLES"][$role["ID"]] = $role["NAME"];
    }
}