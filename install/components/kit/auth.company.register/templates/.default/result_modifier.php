<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

use Bitrix\Sale\Internals\PersonTypeTable;
use Kit\Auth\User\WholeSaler;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(empty($arResult["AGREEMENT_ORIGINATOR_ID"])) {
    $arResult["AGREEMENT_ORIGINATOR_ID"] = "main/reg";
}

if(empty($arResult["AGREEMENT_ORIGIN_ID"])) {
    $arResult["AGREEMENT_ORIGIN_ID"] = "register";
}

if(empty($arResult["AGREEMENT_INPUT_NAME"])) {
    $arResult["AGREEMENT_INPUT_NAME"] = "USER_AGREEMENT";
}


$arFormFields = array(
    "EMAIL" => 1,
    "TITLE" => 1,
    "NAME" => 1,
    "SECOND_NAME" => 1,
    "LAST_NAME" => 1,
    "AUTO_TIME_ZONE" => 1,
    "PERSONAL_PROFESSION" => 1,
    "PERSONAL_WWW" => 1,
    "PERSONAL_ICQ" => 1,
    "PERSONAL_GENDER" => 1,
    "PERSONAL_BIRTHDAY" => 1,
    "PERSONAL_PHOTO" => 1,
    "PERSONAL_PHONE" => 1,
    "PERSONAL_FAX" => 1,
    "PERSONAL_MOBILE" => 1,
    "PERSONAL_PAGER" => 1,
    "PERSONAL_STREET" => 1,
    "PERSONAL_MAILBOX" => 1,
    "PERSONAL_CITY" => 1,
    "PERSONAL_STATE" => 1,
    "PERSONAL_ZIP" => 1,
    "PERSONAL_COUNTRY" => 1,
    "PERSONAL_NOTES" => 1,
    "WORK_COMPANY" => 1,
    "WORK_DEPARTMENT" => 1,
    "WORK_POSITION" => 1,
    "WORK_WWW" => 1,
    "WORK_PHONE" => 1,
    "WORK_FAX" => 1,
    "WORK_PAGER" => 1,
    "WORK_STREET" => 1,
    "WORK_MAILBOX" => 1,
    "WORK_CITY" => 1,
    "WORK_STATE" => 1,
    "WORK_ZIP" => 1,
    "WORK_COUNTRY" => 1,
    "WORK_PROFILE" => 1,
    "WORK_LOGO" => 1,
    "WORK_NOTES" => 1
);

$orderFields = array();

$rs = \Bitrix\Sale\Internals\OrderPropsTable::getList( array(
    'filter' => array(
        'ACTIVE' => 'Y',
    ),
    'select' => array('ID','CODE','NAME', 'REQUIRED', 'SETTINGS', 'PERSON_TYPE_ID', 'DESCRIPTION', 'TYPE', 'DEFAULT_VALUE', 'MULTIPLE')
) );

while($property = $rs->fetch()) {
    $orderFieldsAll[$property['PERSON_TYPE_ID']][$property['CODE']] = $property;
}

$wholesaler = new WholeSaler();
$groups = $wholesaler->getPersonType();
$arResult['PERSON_GROUPS'] = $groups;

$rs = PersonTypeTable::getList(array(
    'filter' => array(
        'ACTIVE' => 'Y',
        'LID' => SITE_ID
    ),
    'select' => array(
        'ID',
        'NAME',
        'LID',
    )
));

while ($types = $rs->fetch()){
    $personTypes[$types["ID"]] = $types;
}

$types = array();
$registerFieldsRequired = array();
foreach ($personTypes as $typeID=>$personType)
{
    $types[$personType['ID']] = $personType;
    $fields[$personType['ID']] = unserialize(Option::get(KitAuth::idModule, 'GROUP_FIELDS_' . $personType['ID'] , '', SITE_ID));
    $registerFieldsRequired[$personType['ID']] = unserialize(Option::get(KitAuth::idModule, 'GROUP_REQUIRED_FIELDS_' . $personType['ID'] , '', SITE_ID));
    $orderOptFields = unserialize(Option::get(KitAuth::idModule, 'GROUP_ORDER_FIELDS_' . $personType['ID'] , '', SITE_ID));

    if (is_array($orderOptFields)) {
        foreach ($orderOptFields as $key => $code) {
            if($orderFieldsAll[$personType['ID']][$code]["TYPE"] == "ENUM"){
                $dbEnum = CSaleOrderPropsVariant::GetList([], ["ORDER_PROPS_ID"=>$orderFieldsAll[$typeID][$code]], false, false, []);
                while($variant = $dbEnum->fetch()){
                    $orderFieldsAll[$personType['ID']][$code]["VARIANTS"][] = $variant;
                }
            }

            $orderFields[$personType['ID']][] = $orderFieldsAll[$personType['ID']][$code];
        }
    }

    if(Option::get(KitAuth::idModule, 'FILE_DOCS_USE_DEFAULT_' . $personType['ID'] , '', SITE_ID) == 'Y') {
        $orderFields[$personType['ID']][]['CODE']['FILE'] = 'Y';
    }

    $companyNameFieldCode =  Option::get("kit.auth", "COMPANY_PROPS_NAME_FIELD_".$typeID, '', SITE_ID);
    $fieldNameIsSet = false;

    foreach ($orderFields[$personType['ID']] as $fieldId => &$orderField){
        if($orderField["CODE"] == $companyNameFieldCode){
            $orderField["REQUIRED"] = "Y";
            $fieldNameIsSet = true;
        }
    }

    if(!$fieldNameIsSet || !$orderFields[$personType['ID']]){
        $nameField = $orderFieldsAll[$personType['ID']][$companyNameFieldCode];
        $nameField["REQUIRED"] = "Y";
        array_unshift($orderFields[$personType['ID']], $nameField);
    }
}

$wholeSalerPersonTypes = unserialize(Option::get( KitAuth::idModule, "WHOLESALERS_PERSON_TYPE", "",SITE_ID ));
if(!is_array($wholeSalerPersonTypes))
{
    $wholeSalerPersonTypes = [];
}
foreach ($wholeSalerPersonTypes as $key=>$personTypeId) $arResult['PERSON_TYPES'][] = $types[$personTypeId];

$arResult['OPT_FIELDS'] = $fields;
$arResult['OPT_ORDER_FIELDS'] = $orderFields;
$arResult['OPT_FIELDS_REQUIRED'] = $registerFieldsRequired;