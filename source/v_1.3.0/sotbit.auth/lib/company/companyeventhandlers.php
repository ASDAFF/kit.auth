<?php

namespace Sotbit\Auth\Company;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Sotbit\Auth\Internals\CompanyConfirmTable;
use Sotbit\Auth\Internals\CompanyPropsValueTable;
use Sotbit\Auth\Internals\CompanyTable;
use Bitrix\Main\Event;
use Bitrix\Main\Context;
use Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);


class CompanyEventHandlers extends \SotbitAuth
{
    const EVENT_PREFIX = 'MAIL_EVENT_COMPANY_';

    public function setCurrentProfileValue(&$arResult, &$arUserResult, $arParams)
    {
        if (self::getVersionMode() == "N" || !self::isB2BCabinet() || !isset($_SESSION["AUTH_COMPANY_CURRENT_ID"])) {
            return true;
        }

        foreach ($arResult['PERSON_TYPE'] as $key => $type) {
            if ($type['CHECKED'] == 'Y') {
                unset($arResult['PERSON_TYPE'][$key]['CHECKED']);
            }
        }
        $buerType = self::getBuyerTypeCurrentCompany();
        $arResult['PERSON_TYPE'][$buerType]['CHECKED'] = 'Y';
        $arUserResult['PERSON_TYPE_ID'] = $buerType;
    }

    public function setOrderPropertyValues(&$arResult, &$arUserResult, $arParams)
    {

        if (self::getVersionMode() == "N" || !self::isB2BCabinet() || !isset($_SESSION["AUTH_COMPANY_CURRENT_ID"])) {
            return true;
        }

        global $USER;
        $companyProps = self::getCompanyProps();
        $buerType = self::getBuyerTypeCurrentCompany();


        $issetProfile = \CSaleOrderUserProps::GetList(
            array("DATE_UPDATE" => "DESC"),
            array("USER_ID" => $USER->GetID())
        )->fetch();

        $requiredGroups = unserialize(Option::get(\SotbitAuth::idModule, "COMPANY_PROPS_GROUP_ID_" . $buerType, ""));
        $companyPropsName = unserialize(Option::get("sotbit.b2bcabinet", "PROFILE_ORG_NAME", "", SITE_ID));

        foreach ($arResult["ORDER_PROP"]["USER_PROPS_Y"] as $key => &$prop) {
            if (in_array($key, $companyPropsName) && $companyProps[$key]["VALUE"]) {
                $profileName = $companyProps[$key]["VALUE"];
            }

            if (isset($_REQUEST["ORDER_PROP_" . $key])) {
                $currentValue = $_REQUEST["ORDER_PROP_" . $key];
            } elseif(isset($companyProps[$key]["VALUE"])) {
                $currentValue = $companyProps[$key]["VALUE"];
            }
            else{
                $currentValue = '';
            }

            $reedonly = "N";
            if (in_array($prop["PROPS_GROUP_ID"], $requiredGroups)) {
                $reedonly = "Y";
            }
            $prop["REEDONLY"] = $reedonly;

            if ($prop["TYPE"] == "LOCATION") {
                $locationId = $_REQUEST["ORDER_PROP_" . $key] ?: $companyProps[$key]["VALUE"];
                if ($locationId) {
                    $arLocation = \CSaleLocation::GetByID($locationId);
                    $prop["VALUE"] = $locationId;
                    $arResult["ORDER_PROP"]["PRINT"][$key]["VALUE"] = $arLocation["COUNTRY_NAME"] ;
                    $arUserResult["ORDER_PROP"][$key] = $arLocation["CODE"];
                    if(empty($issetProfile)){
                        $prop["OVERRIDE"] = "Y";
                    }
                    continue;
                }
            }

            if($currentValue){
                $prop["VALUE"] = $currentValue;
                $prop["VALUE_FORMATED"] = $currentValue;
                $arResult["ORDER_PROP"]["PRINT"][$key]["VALUE"] = $currentValue;
                $arUserResult["ORDER_PROP"][$key] = $currentValue;
            }
        }


        $profile = \CSaleOrderUserProps::GetList(
            array("DATE_UPDATE" => "DESC"),
            array("USER_ID" => $USER->GetID(), "PERSON_TYPE_ID" => $buerType, "=NAME" => $profileName)
        )->fetch();


        if (!empty($profile)) {
            $profile["CHECKED"] = "Y";
        }

        foreach ($arResult["ORDER_PROP"]["USER_PROFILES"] as $idProfile => &$userProfile){
            unset($userProfile["CHECKED"]);
            if(!empty($profile) && $idProfile == $profile["ID"]){
                $userProfile["CHECKED"] = "Y";
                $arUserResult["PROFILE_ID"] = $idProfile;
            }
        }
    }

    public function isB2BCabinet()
    {
        $arTmpPath = explode("/", SITE_TEMPLATE_PATH);
        return end($arTmpPath) == "b2bcabinet";
    }

    public function getVersionMode()
    {
        return Option::get(\SotbitAuth::idModule, "EXTENDED_VERSION_COMPANIES", "N");
    }

    public function getBuyerTypeCurrentCompany()
    {
        $result = CompanyTable::getList([
            'filter' => ["ID" => $_SESSION["AUTH_COMPANY_CURRENT_ID"]],
            'select' => ["BUYER_TYPE"]
        ])->fetch();

        return $result["BUYER_TYPE"];
    }

    public function getCompanyProps()
    {
        $dbCompanyProps = CompanyPropsValueTable::getList([
            'filter' => ["COMPANY_ID" => $_SESSION["AUTH_COMPANY_CURRENT_ID"]],
            'select' => ["PROPERTY_ID", "VALUE", "NAME", "ID"],
            'order' => ["PROPERTY_ID" => "asc"]
        ]);

        while ($property = $dbCompanyProps->fetch()) {
            $result[$property["PROPERTY_ID"]] = $property;
        }

        return $result;
    }

    public function getMailEvents($eventName, $siteId = SITE_ID)
    {
        return unserialize(Option::get(\SotbitAuth::idModule, $eventName, "", $siteId));
    }

    public function sendMail($eventName, $fields, $siteId = SITE_ID)
    {
        if(!($mailEvents = self::getMailEvents($eventName, $siteId)))
            return;
        if($fields) {
            if (isset($fields["SECOND_NAME"])) {
                $fields["COMPANY"] = $fields["SECOND_NAME"];
            }
        }

        foreach ($mailEvents as $event){
            $result = \Bitrix\Main\Mail\Event::send(
                array(
                    'EVENT_NAME' => $event,
                    'LID' => $siteId,
                    'C_FIELDS' => $fields
                )
            );
        }

    }

    public function onAfterAddCompany($companyFields, $companyID)
    {
        if(empty($companyFields) || empty($companyID))
            return;

        switch ($companyFields["STATUS"]){
            case "M":
                $eventName = self::EVENT_PREFIX . "SENT_MODERATION";
                break;
            case "A":
                $eventName = self::EVENT_PREFIX . "REGISTER";
                break;
        }

        self::sendMail($eventName, $companyFields);
    }

    public function onAfterCompanyModerate($companyFields, $companyID)
    {
        if(empty($companyFields) || empty($companyID))
            return;

        $lid = CompanyConfirmTable::getList([
            'filter' => ['COMPANY_ID' => $companyID],
            'select' => ['LID'],
            'order' => ['ID' => 'desc']
        ])->fetch();

        $eventName = self::EVENT_PREFIX . "CONFIRM";

        self::sendMail($eventName, $companyFields, $lid["LID"]);
    }

    public function onBeforeCompanyRejection($companyFields, $companyID)
    {
        if(empty($companyFields) || empty($companyID))
            return true;

        $lid = CompanyConfirmTable::getList([
            'filter' => ['COMPANY_ID' => $companyID],
            'select' => ['LID'],
            'order' => ['ID' => 'desc']
        ])->fetch();

        $eventName = self::EVENT_PREFIX . "REJECTED";
        self::sendMail($eventName, $companyFields, $lid["LID"]);
        return true;
    }

    public function onBeforeCompanyChangesRejection($companyFields, $companyID)
    {
        if(empty($companyFields) || empty($companyID))
            return true;

        $lid = CompanyConfirmTable::getList([
            'filter' => ['COMPANY_ID' => $companyID],
            'select' => ['LID'],
            'order' => ['ID' => 'desc']
        ])->fetch();
        $eventName = self::EVENT_PREFIX . "CHANGES_REJECTED";
        self::sendMail($eventName, $companyFields, $lid["LID"]);
        return true;
    }

    public function onBeforeAdminModerate($fields)
    {
        if(empty($fields))
            return true;

        $orderFields = $fields["ORDER_FIELDS"];
        unset($fields["ORDER_FIELDS"]);
        $fields = array_merge($orderFields, $fields);
        $eventName = self::EVENT_PREFIX . "ADMIN_CONFIRM";
        self::sendMail($eventName, $fields, $fields["LID"]);
        return true;
    }

    public function onBeforeStaffRemoved($fields)
    {
        if(empty($fields))
            return true;

        $eventName = self::EVENT_PREFIX . "STAFF_REMOVED";
        self::sendMail($eventName, $fields);
        return true;
    }

    public function onBeforeStaffRejected($fields)
    {
        if(empty($fields))
            return true;

        $eventName = self::EVENT_PREFIX . "JOIN_REQUEST_REJECTED";
        self::sendMail($eventName, $fields);
        return true;
    }

    public function onBeforeStaffInvite($fields)
    {
        if(empty($fields))
            return true;

        $eventName = self::EVENT_PREFIX . "STAFF_INVITE";
        self::sendMail($eventName, $fields);
        return true;
    }

    public function onBeforeJoinRequest($fields)
    {
        if(empty($fields))
            return true;

        $eventName = self::EVENT_PREFIX . "JOIN_REQUEST";
        self::sendMail($eventName, $fields);
        return true;
    }

    public function OnBeforeAdminRejected($fields)
    {
        if(empty($fields))
            return true;

        $eventName = self::EVENT_PREFIX . "ADMIN_REJECTED";
        self::sendMail($eventName, $fields, $fields["LID"]);
        return true;
    }

    public function onBeforeStaffUpdate($fields)
    {
        if(empty($fields))
            return true;

        $eventName = self::EVENT_PREFIX . "JOIN_REQUEST_CONFIRM";
        self::sendMail($eventName, $fields);
        return true;
    }
}