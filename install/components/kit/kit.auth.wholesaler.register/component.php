<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Kit\Auth\Internals\BuyerConfirmTable;
use Kit\Auth\Internals\UserConfirmTable;
use Kit\Auth\User\WholeSaler;

global $USER_FIELD_MANAGER;
Loc::loadMessages(__FILE__);

if(!\Bitrix\Main\Loader::includeModule("kit.auth"))
{
    return;
}

$arDefaultValues = [
    "SHOW_FIELDS" => [],
    "REQUIRED_FIELDS" => [],
    "SHOW_WHOLESALERS_FIELDS" => [],
    "REQUIRED_WHOLESALERS_FIELDS" => [],
    "SHOW_WHOLESALER_ORDER_FIELDS" => [],
    "AUTH" => "Y",
    "USE_BACKURL" => "Y",
    "SUCCESS_PAGE" => ""
];

foreach ($arDefaultValues as $key => $value)
{
    if(!is_set($arParams, $key))
    {
        $arParams[$key] = $value;
    }
}

if(!is_array($arParams["SHOW_FIELDS"]))
{
    $arParams["SHOW_FIELDS"] = [];
}
if(!is_array($arParams["REQUIRED_FIELDS"]))
{
    $arParams["REQUIRED_FIELDS"] = [];
}
if(!is_array($arParams["SHOW_WHOLESALER_FIELDS"]))
{
    $arParams["SHOW_WHOLESALER_FIELDS"] = [];
}
if(!is_array($arParams["REQUIRED_WHOLESALER_FIELDS"]))
{
    $arParams["REQUIRED_WHOLESALER_FIELDS"] = [];
}
if(!is_array($arParams["SHOW_WHOLESALE_ORDER_FIELDS"]))
{
    $arParams["SHOW_WHOLESALE_ORDER_FIELDS"] = [];
}

if(Option::get("main", "new_user_registration", "N") == "N")
{
    $APPLICATION->AuthForm([]);
}

$arParams['LOGIN_EQ_EMAIL'] = Option::get("kit.auth", "LOGIN_EQ_EMAIL", "N", SITE_ID);

$arResult["EMAIL_REQUIRED"] = (Option::get("main", "new_user_email_required", "Y") != "N");

$arResult["USE_EMAIL_CONFIRMATION"] = (Option::get("main", "new_user_registration_email_confirmation", "N") == "Y" && $arResult["EMAIL_REQUIRED"] ? "Y" : "N");

$arDefaultFields = [
    "LOGIN",
    "PASSWORD",
    "CONFIRM_PASSWORD"
];
$arDefaultWholeSalerFields = [];

if($arResult["EMAIL_REQUIRED"])
{
    $arDefaultFields[] = "EMAIL";
}

if($arResult["EMAIL_REQUIRED"])
{
    $arDefaultWholeSalerFields[] = "EMAIL";
}

$def_group = Option::get("main", "new_user_registration_def_group", "");
if($def_group != "")
{
    $arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(explode(",", $def_group));
}
else
{
    $arResult["GROUP_POLICY"] = CUser::GetGroupPolicy([]);
}

$arResult["SHOW_FIELDS"] = array_unique(array_merge($arDefaultFields, $arParams["SHOW_FIELDS"]));
$arResult["REQUIRED_FIELDS"] = array_unique(array_merge($arDefaultFields, $arParams["REQUIRED_FIELDS"]));

$arResult["SHOW_WHOLESALER_FIELDS"] = array_unique(array_merge($arDefaultWholeSalerFields, $arParams["SHOW_WHOLESALER_FIELDS"]));
$arResult["REQUIRED_WHOLESALER_FIELDS"] = array_unique(array_merge($arDefaultWholeSalerFields, $arParams["REQUIRED_WHOLESALER_FIELDS"]));

// use captcha?
$arResult["USE_CAPTCHA"] = Option::get("main", "captcha_registration", "N") == "Y" ? "Y" : "N";

// start values
$arResult["VALUES"] = [
    'FIELDS' => [],
    'WHOLESALER_FIELDS' => [],
    'WHOLESALER_ORDER_FIELDS' => []
];
$arResult["ERRORS"] = [];
$register_done = false;
$arResult['WHOLESALER_ORDER_FIELDS'] = [];

if($arParams['SHOW_WHOLESALER_ORDER_FIELDS'])
{
    $rs = \Bitrix\Sale\Internals\OrderPropsTable::getList(
        [
            'filter' => [
                'ACTIVE' => 'Y',
                'CODE' => $arParams['SHOW_WHOLESALER_ORDER_FIELDS']
            ],
            'select' => [
                'CODE',
                'NAME',
                'REQUIRED'
            ]
        ]);
    while ($property = $rs->fetch())
    {
        $arResult['WHOLESALER_ORDER_FIELDS'][$property['CODE']] = [
            'CODE' => $property['CODE'],
            'NAME' => $property['NAME'],
            'REQUIRED' => $property['REQUIRED']
        ];
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["kit_auth_register"]) && !$USER->IsAuthorized())
{
    if(Option::get('main', 'use_encrypted_auth', 'N') == 'Y')
    {
        $sec = new CRsaSecurity();
        if(($arKeys = $sec->LoadKeys()))
        {
            $sec->SetKeys($arKeys);
            $errno = $sec->AcceptFromForm([
                'REGISTER'
            ]);
            if($errno == CRsaSecurity::ERROR_SESS_CHECK)
            {
                $arResult["ERRORS"][] = Loc::getMessage("main_register_sess_expired");
            }
            elseif($errno < 0)
            {
                $arResult["ERRORS"][] = Loc::getMessage("main_register_decode_err", [
                    "#ERRCODE#" => $errno
                ]);
            }
        }
    }
    $arResult["VALUES"]['FIELDS']['TAB'] = $_REQUEST["TAB"];
    /*--------------------------------------------------------------ОПТОВЫЙ ПОКУПАТЕЛЬ------------------------------*/

    if((isset($_REQUEST["REGISTER_WHOLESALER"]) && !empty($_REQUEST["REGISTER_WHOLESALER"])))
    {
        $whosalerId = $_REQUEST['REGISTER_WHOLESALER']['TYPE'];
        $arResult["VALUES"]['WHOLESALER_FIELDS'][$whosalerId] = [];

        /*----------- ПРОВЕРКА ПО ИНН ------------------*/

        $inn = $_REQUEST['REGISTER_WHOLESALER_OPT'][$whosalerId]['INN'];

        if(!$inn){
            /* $code = Option::get('GROUP_ORDER_INN_FIELD_'.$whosalerId,SITE_ID,'INN');
             $name = $arResult['WHOLESALER_ORDER_FIELDS'][$code]['NAME'];
             if($name)
             {
                 $arResult["ERRORS"]['EMPTY_INN'] = Loc::getMessage("REGISTER_ORDER_FIELD_REQUIRED", ['#FIELD_NAME#' => '"' . $name . '"']);
             }
             else{
                 $arResult["ERRORS"]['EMPTY_INN'] = Loc::getMessage("REGISTER_INN_EMPTY");
             }*/
        }
        else {
            $Wholesaler = new WholeSaler();
            $inns = $Wholesaler->getInns();
            if(in_array($inn, $inns)) $arResult["ERRORS"]['EXIST_INN'] = Loc::getMessage("REGISTER_INN_EXIST");
            unset($Wholesaler);
            unset($inns);
        }
        $fields = $_REQUEST['REGISTER_WHOLESALER_USER'][$whosalerId];

        $requredFields = unserialize(Option::get(KitAuth::idModule, 'GROUP_REQUIRED_FIELDS_' . $whosalerId, '', SITE_ID));

        if(!is_array($requredFields))
        {
            $requredFields = [];
        }

        foreach ($fields as $key => $value)
        {
            if($key != "PERSONAL_PHOTO" && $key != "WORK_LOGO")
            {
                $arResult["VALUES"]['WHOLESALER_FIELDS'][$whosalerId][$key] = $value;

                if(in_array($key, $requredFields) && trim($value) == '') $arResult["ERRORS"][$key] = Loc::getMessage("REGISTER_ORDER_FIELD_REQUIRED");

            }
            else
            {
                $_FILES["REGISTER_WHOLESALER_FILES_" . $key]["MODULE_ID"] = "main";
                $arResult["VALUES"]['WHOLESALER_FIELDS'][$whosalerId][$key] = $_FILES["REGISTER_FILES_" . $key];
                if(in_array($key, $requredFields) && !is_uploaded_file($_FILES["REGISTER_WHOLESALER_FILES_" . $key]["tmp_name"])) $arResult["ERRORS"][$key] = Loc::getMessage("REGISTER_FIELD_REQUIRED");
            }
        }

        /*--- wholesaler ---*/
        $orderOptFields = unserialize(Option::get(KitAuth::idModule, 'GROUP_ORDER_FIELDS_' . $whosalerId, '', SITE_ID));
        $orderFieldsAll = $arResult['WHOLESALER_ORDER_FIELDS'];

        if(is_array($orderOptFields))
            foreach ($orderOptFields as $key => $code) $orderFields[$whosalerId][] = $orderFieldsAll[$code];

        $optFields = $_REQUEST['REGISTER_WHOLESALER_OPT'][$whosalerId];

        foreach ($optFields as $key => $value)
        {
            $arResult["VALUES"]['WHOLESALER_ORDER_FIELDS'][$whosalerId][$key] = $value;

            if($orderFieldsAll[$key]['REQUIRED'] == 'Y' && trim($value) == '')
            {
                $arResult["ERRORS"][$key] = Loc::getMessage("REGISTER_ORDER_FIELD_REQUIRED", ['#FIELD_NAME#' => '"' . $orderFieldsAll[$key]['NAME'] . '"']);
            }

        }

        if(trim($_REQUEST["REGISTER"]['PASSWORD'] == '')) $arResult["ERRORS"]['PASSWORD'] = Loc::getMessage("REGISTER_FIELD_REQUIRED");

        if(trim($_REQUEST["REGISTER"]['CONFIRM_PASSWORD'] == '')) $arResult["ERRORS"]['CONFIRM_PASSWORD'] = Loc::getMessage("REGISTER_FIELD_REQUIRED");

        if($_REQUEST["REGISTER"]['CONFIRM_PASSWORD'] !== $_REQUEST["REGISTER"]['PASSWORD']) $arResult["ERRORS"]['CONFIRM_PASSWORD'] = Loc::getMessage("REGISTER_FIELD_PASSWORD_AND_CONFPASSWORD_NOT_SAME");

        if(isset($_REQUEST["REGISTER_WHOLESALER"]["TIME_ZONE"])) $arResult["VALUES"]['WHOLESALER_FIELDS'][$whosalerId]["TIME_ZONE"] = $_REQUEST["REGISTER_WHOLESALER"]["TIME_ZONE"];

    }/*--------------------------------------------------------------------------------------------*/
    elseif($_REQUEST["TAB"] == 'USER')
    {
        foreach ($arResult["SHOW_FIELDS"] as $key)
        {
            if($key != "PERSONAL_PHOTO" && $key != "WORK_LOGO")
            {
                $arResult["VALUES"]['FIELDS'][$key] = $_REQUEST["REGISTER"][$key];
                if(in_array($key, $arResult["REQUIRED_FIELDS"]) && trim($arResult["VALUES"]['FIELDS'][$key]) == '')
                {
                    $arResult["ERRORS"][$key] = Loc::getMessage("REGISTER_FIELD_REQUIRED");
                }
            }
            else
            {
                $_FILES["REGISTER_FILES_" . $key]["MODULE_ID"] = "main";
                $arResult["VALUES"]['FIELDS'][$key] = $_FILES["REGISTER_FILES_" . $key];
                if(in_array($key, $arResult["REQUIRED_FIELDS"]) && !is_uploaded_file($_FILES["REGISTER_FILES_" . $key]["tmp_name"]))
                {
                    $arResult["ERRORS"][$key] = Loc::getMessage("REGISTER_FIELD_REQUIRED");
                }
            }
        }
        if(isset($_REQUEST["REGISTER"]["TIME_ZONE"]))
        {
            $arResult["VALUES"]['FIELDS']["TIME_ZONE"] = $_REQUEST["REGISTER"]["TIME_ZONE"];
        }
    }
    if($arParams['LOGIN_EQ_EMAIL'] == 'Y')
        $arResult["VALUES"]['FIELDS']['LOGIN'] = !empty($_REQUEST["REGISTER"]['EMAIL']) ? $_REQUEST["REGISTER"]['EMAIL'] : $arResult["VALUES"]['WHOLESALER_FIELDS'][$whosalerId]['EMAIL'];
    else
        $arResult["VALUES"]['FIELDS']['LOGIN'] = !empty($_REQUEST["REGISTER"]['LOGIN']) ? $_REQUEST["REGISTER"]['LOGIN'] : $arResult["VALUES"]['WHOLESALER_FIELDS'][$whosalerId]['LOGIN'];


    $arResult["VALUES"]['FIELDS']['EMAIL'] = !empty($_REQUEST["REGISTER"]['EMAIL']) ? $_REQUEST["REGISTER"]['EMAIL'] : $arResult["VALUES"]['WHOLESALER_FIELDS'][$whosalerId]['EMAIL'];
    $arResult["VALUES"]['FIELDS']['PASSWORD'] = $_REQUEST["REGISTER"]['PASSWORD'];
    $arResult["VALUES"]['FIELDS']['USER_GROUP'] = $_REQUEST["REGISTER"]['USER_GROUP'];
    $arResult["VALUES"]['FIELDS']['CONFIRM_PASSWORD'] = $_REQUEST["REGISTER"]['CONFIRM_PASSWORD'];

    if(CUser::getList($by, $order,['EMAIL' =>$arResult["VALUES"]['FIELDS']['EMAIL']])->fetch()) {
        $arResult["ERRORS"]['EMAIL_EXIST'] = Loc::getMessage("REGISTER_USER_WITH_EMAIL_EXIST", array('#EMAIL#' => $arResult["VALUES"]['FIELDS']['EMAIL']));
    }


    $USER_FIELD_MANAGER->EditFormAddFields("USER", $arResult["VALUES"]['FIELDS']);
    if(!$USER_FIELD_MANAGER->CheckFields("USER", 0, $arResult["VALUES"]['FIELDS']))
    {
        $e = $APPLICATION->GetException();
        $arResult["ERRORS"][] = substr($e->GetString(), 0, -4); // cutting "<br>"
        $APPLICATION->ResetException();
    }

    $Values = [];
    if($arResult["VALUES"]['FIELDS'] && $arResult["VALUES"]['WHOLESALER_FIELDS'])
    {
        $Values = array_merge($arResult["VALUES"]['FIELDS'], $arResult["VALUES"]['WHOLESALER_FIELDS'][$whosalerId]);
    }
    elseif($arResult["VALUES"]['FIELDS'])
    {
        $Values = $arResult["VALUES"]['FIELDS'];
    }
    unset($Values['TAB']);

    $Values['FILES'] = $_REQUEST['FILES'];


    if($arResult["USE_CAPTCHA"] == "Y")
    {
        if(!$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]))
        {
            $arResult["ERRORS"][] = Loc::getMessage("REGISTER_WRONG_CAPTCHA");
        }
    }
    if(count($arResult["ERRORS"]) > 0)
    {
        if(Option::get("main", "event_log_register_fail", "N") === "Y")
        {
            $arError = $arResult["ERRORS"];
            foreach ($arError as $key => $error)
            {
                if(intval($key) == 0 && $key !== 0)
                {
                    $arError[$key] = str_replace("#FIELD_NAME#", '"' . $key . '"', $error);
                }
            }
            CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", false, implode("<br>", $arError));
        }
    }
    else
    {
        $bConfirmReq = (Option::get("main", "new_user_registration_email_confirmation", "N") == "Y" && $arResult["EMAIL_REQUIRED"]);

        $Values["CHECKWORD"] = md5(CMain::GetServerUniqID() . uniqid());
        $Values["~CHECKWORD_TIME"] = $DB->CurrentTimeFunction();
        $Values["ACTIVE"] = $bConfirmReq ? "N" : "Y";
        $Values["CONFIRM_CODE"] = $bConfirmReq ? randString(8) : "";
        $Values["LID"] = SITE_ID;
        $Values["LANGUAGE_ID"] = LANGUAGE_ID;

        $Values["USER_IP"] = $_SERVER["REMOTE_ADDR"];
        $Values["USER_HOST"] = @gethostbyaddr($_SERVER["REMOTE_ADDR"]);

        if($Values["AUTO_TIME_ZONE"] != "Y" && $Values["AUTO_TIME_ZONE"] != "N")
        {
            $Values["AUTO_TIME_ZONE"] = "";
        }


        $def_group = Option::get("main", "new_user_registration_def_group", "");

        if($def_group != "")
        {
            $Values["GROUP_ID"] = explode(",", $def_group);
        }

        if($_REQUEST["REGISTER_WHOLESALER"])
        {
            $Wholesaler = new WholeSaler();

            $Wholesaler->setPersonCurrentType($whosalerId);
            $Values['PERSON_TYPE'] = $whosalerId;
            if($Wholesaler->getGroup())
            {
                $Values["GROUP_ID"][] = $Wholesaler->getGroup();
            }

        }

        $bOk = true;

        if($_REQUEST["REGISTER_WHOLESALER"])
        {
            $Values['ORDER_FIELDS'] = $arResult["VALUES"]['WHOLESALER_ORDER_FIELDS'][$whosalerId];
        }

        $events = GetModuleEvents("main", "OnBeforeUserRegister", true);
        foreach ($events as $arEvent)
        {
            if(ExecuteModuleEventEx($arEvent, [
                    &$Values
                ]) === false)
            {
                if($err = $APPLICATION->GetException())
                {
                    $arResult['ERRORS'][] = $err->GetString();
                }

                $bOk = false;
                break;
            }
        }

        $user = new CUser();
        if(!$user->CheckFields($Values)){
            $bOk = false;
        }

        if($bOk)
        {
            $arResult["CONFIRM_REGISTRATION"] = false;
            if($_REQUEST["REGISTER_WHOLESALER"])
            {

                $confirmRegister = Option::get('kit.auth', 'CONFIRM_REGISTER', 'N');
                if($confirmRegister == 'Y')
                {
                    $rs = UserConfirmTable::add([
                        'LID' => SITE_ID,
                        'FIELDS' => $Values,
                        'EMAIL' => $Values['EMAIL']
                    ]);
                    if($rs->isSuccess())
                    {
                        $arResult["CONFIRM_REGISTRATION"] = true;
                    }
                }
            }
            if(!$arResult["CONFIRM_REGISTRATION"])
            {
                $ID = $user->Add($Values);
            }
        }

        if(intval($ID) > 0)
        {
            $register_done = true;

            // authorize user
            if($arParams["AUTH"] == "Y" && $Values["ACTIVE"] == "Y")
            {
                if(!$arAuthResult = $USER->Login($Values["LOGIN"], $Values["PASSWORD"]))
                {
                    $arResult["ERRORS"][] = $arAuthResult;
                }
            }

            $Values["USER_ID"] = $ID;

            $arEventFields = $Values;
            unset($arEventFields["PASSWORD"]);
            unset($arEventFields["CONFIRM_PASSWORD"]);

            $confirmBuyer = Option::get('kit.auth', 'CONFIRM_BUYER', 'N');
            if($confirmBuyer == 'N')
            {
                $Wholesaler->setFields($Values);
                $Wholesaler->save();
            }
            else
            {
                BuyerConfirmTable::add([
                    'LID' => SITE_ID,
                    'FIELDS' => $Values,
                    'EMAIL' => $Values['EMAIL'],
                    'ID_USER' => $ID,
                    'INN' => $Values['ORDER_FIELDS']['INN']
                ]);
            }

            $event = new CEvent();
            $event->SendImmediate("NEW_USER", SITE_ID, $arEventFields);
            if($bConfirmReq)
            {
                $event->SendImmediate("NEW_USER_CONFIRM", SITE_ID, $arEventFields);
            }
        }
        else
        {
            $arResult["ERRORS"][] = $user->LAST_ERROR;
        }

        if(count($arResult["ERRORS"]) <= 0)
        {
            if(Option::get("main", "event_log_register", "N") === "Y")
            {
                CEventLog::Log("SECURITY", "USER_REGISTER", "main", $ID);
            }
        }
        else
        {
            if(Option::get("main", "event_log_register_fail", "N") === "Y")
            {
                CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", $ID, implode("<br>", $arResult["ERRORS"]));
            }
        }

        $events = GetModuleEvents("main", "OnAfterUserRegister", true);
        foreach ($events as $arEvent)
        {
            ExecuteModuleEventEx($arEvent, [
                &$Values
            ]);
        }
    }
}
if($register_done)
{
    if($arParams["USE_BACKURL"] == "Y" && $_REQUEST["backurl"] != '')
    {
        LocalRedirect($_REQUEST["backurl"]);
    }
    elseif($arParams["SUCCESS_PAGE"] != '')
    {
        LocalRedirect($arParams["SUCCESS_PAGE"]);
    }
}

$arResult["VALUES"] = htmlspecialcharsEx($arResult["VALUES"]);

$arResult["REQUIRED_FIELDS_FLAGS"] = [];
foreach ($arResult["REQUIRED_FIELDS"] as $field)
{
    $arResult["REQUIRED_FIELDS_FLAGS"][$field] = "Y";
}

$arResult["BACKURL"] = htmlspecialcharsbx($_REQUEST["backurl"]);

if(in_array("PERSONAL_COUNTRY", $arResult["SHOW_FIELDS"]) || in_array("WORK_COUNTRY", $arResult["SHOW_FIELDS"]))
{
    $arResult["COUNTRIES"] = GetCountryArray();
}

if(in_array("PERSONAL_BIRTHDAY", $arResult["SHOW_FIELDS"]))
{
    $arResult["DATE_FORMAT"] = CLang::GetDateFormat("SHORT");
}

// ********************* User properties ***************************************************
$arResult["USER_PROPERTIES"] = [
    "SHOW" => "N"
];
$arUserFields = $USER_FIELD_MANAGER->GetUserFields("USER", 0, LANGUAGE_ID);
if(is_array($arUserFields) && count($arUserFields) > 0)
{
    if(!is_array($arParams["USER_PROPERTY"]))
    {
        $arParams["USER_PROPERTY"] = [
            $arParams["USER_PROPERTY"]
        ];
    }

    foreach ($arUserFields as $FIELD_NAME => $arUserField)
    {
        if(!in_array($FIELD_NAME, $arParams["USER_PROPERTY"]) && $arUserField["MANDATORY"] != "Y")
        {
            continue;
        }

        $arUserField["EDIT_FORM_LABEL"] = strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
        $arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
        $arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
        $arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
    }
}
if(!empty($arResult["USER_PROPERTIES"]["DATA"]))
{
    $arResult["USER_PROPERTIES"]["SHOW"] = "Y";
    $arResult["bVarsFromForm"] = (count($arResult['ERRORS']) <= 0) ? false : true;
}
// ******************** /User properties ***************************************************

// initialize captcha
if($arResult["USE_CAPTCHA"] == "Y")
{
    $arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
}

// set title
if($arParams["SET_TITLE"] == "Y")
{
    $title = Loc::getMessage("REGISTER_DEFAULT_TITLE");
    if(mb_detect_encoding($title, 'UTF-8, CP1251') == 'UTF-8') {
        $title = mb_convert_encoding($title, 'CP1251', 'UTF-8');
    }
    $APPLICATION->SetTitle($title);
}

// time zones
$arResult["TIME_ZONE_ENABLED"] = CTimeZone::Enabled();
if($arResult["TIME_ZONE_ENABLED"])
{
    $arResult["TIME_ZONE_LIST"] = CTimeZone::GetZones();
}

$arResult["SECURE_AUTH"] = false;
if(!CMain::IsHTTPS() && Option::get('main', 'use_encrypted_auth', 'N') == 'Y')
{
    $sec = new CRsaSecurity();
    if(($arKeys = $sec->LoadKeys()))
    {
        $sec->SetKeys($arKeys);
        $sec->AddToForm('regform', [
            'REGISTER[PASSWORD]',
            'REGISTER[CONFIRM_PASSWORD]'
        ]);
        $arResult["SECURE_AUTH"] = true;
    }
}

$arResult['USER_GROUPS'] = array();
$userGroups = unserialize(Option::get('kit.auth','USER_GROUPS','a:0:{}',SITE_ID));
if(!is_array($userGroups))
{
    $userGroups = array();
}
if(count($arResult['USER_GROUPS'] > 0))
{
    $rs = \Bitrix\Main\GroupTable::getList(
        array(
            'filter' => array('ID' => $userGroups,'ACTIVE' => 'Y'),
            'select' => array('ID','NAME')
        )
    );
    while($group = $rs->fetch())
    {
        $arResult['USER_GROUPS'][$group['ID']] = $group['NAME'];
    }
}

$this->IncludeComponentTemplate();