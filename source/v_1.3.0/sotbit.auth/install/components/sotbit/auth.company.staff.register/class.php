<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\Loc;
use Sotbit\Auth\Company\Company;
use Bitrix\Main\Context;
use Sotbit\Auth\Internals\CompanyTable;

Loc::loadMessages(__FILE__);

class CompanyStaffRegister extends CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'sendForm' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ]
        ];
    }


    protected function listKeysSignedParameters()
    {
        return ["REQUIRED_FIELDS", "SHOW_FIELDS", "USE_CAPTCHA", "USER_GROUPS", "ABILITY_TO_SET_ROLE"];
    }


    function executeComponent()
    {

        global $USER_FIELD_MANAGER;
        global $APPLICATION;

// apply default param values
        $arDefaultValues = array(
            "SHOW_FIELDS" => array(),
            "REQUIRED_FIELDS" => array(),
            "AUTH" => "Y",
            "USE_BACKURL" => "Y",
            "SUCCESS_PAGE" => "",
        );

        foreach ($arDefaultValues as $key => $value) {
            if (!is_set($this->arParams, $key)) {
                $this->arParams[$key] = $value;
            }
        }
        if (!is_array($this->arParams["SHOW_FIELDS"])) {
            $this->arParams["SHOW_FIELDS"] = array();
        }
        if (!is_array($this->arParams["REQUIRED_FIELDS"])) {
            $this->arParams["REQUIRED_FIELDS"] = array();
        }

// if user registration blocked - return auth form
        if (COption::GetOptionString("main", "new_user_registration", "N") == "N") {
            $APPLICATION->AuthForm(array());
        }

        $this->arResult["PHONE_REGISTRATION"] = (COption::GetOptionString("main", "new_user_phone_auth", "N") == "Y");
        $this->arResult["PHONE_REQUIRED"] = ($this->arResult["PHONE_REGISTRATION"] && COption::GetOptionString("main",
                "new_user_phone_required", "N") == "Y");
        $this->arResult["EMAIL_REGISTRATION"] = (COption::GetOptionString("main", "new_user_email_auth", "Y") <> "N");
        $this->arResult["EMAIL_REQUIRED"] = ($this->arResult["EMAIL_REGISTRATION"] && COption::GetOptionString("main",
                "new_user_email_required", "Y") <> "N");
        $this->arResult["USE_EMAIL_CONFIRMATION"] = (COption::GetOptionString("main",
            "new_user_registration_email_confirmation", "N") == "Y" && $this->arResult["EMAIL_REQUIRED"] ? "Y" : "N");
        $this->arResult["PHONE_CODE_RESEND_INTERVAL"] = CUser::PHONE_CODE_RESEND_INTERVAL;

// apply core fields to user defined

        if ($this->arResult["EMAIL_REQUIRED"]) {
            $arDefaultFields[] = "EMAIL";
        }
        if ($this->arResult["PHONE_REQUIRED"]) {
            $arDefaultFields[] = "PHONE_NUMBER";
        }
        $arDefaultFields[] = "PASSWORD";
        $arDefaultFields[] = "CONFIRM_PASSWORD";

        if (!$this->arParams["USER_GROUPS"] || $this->arParams["USER_GROUPS"][0] == "HIDDEN") {
            $def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
            if ($def_group <> "") {
                $this->arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(explode(",", $def_group));
            } else {
                $this->arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(array());
            }
        } else {
            $this->arResult["GROUP_POLICY"] = CUser::GetGroupPolicy($this->arParams["USER_GROUPS"]);
        }
        $this->arResult["SHOW_FIELDS"] = array_unique(array_merge($arDefaultFields, $this->arParams["SHOW_FIELDS"]));

        if(isset($this->arParams["ABILITY_TO_SET_ROLE"]) && $this->arParams["ABILITY_TO_SET_ROLE"] == "Y"){
            $this->arResult["SHOW_FIELDS"][] = "STAFF_ROLE";
            $companyObject = new Company(SITE_ID);
            $this->arResult["SELECT_STAFF_ROLES"] = $companyObject->getRoles();
        }

        if (isset($this->arParams["USER_GROUPS"]) && !empty($this->arParams["USER_GROUPS"]) && $this->arParams["USER_GROUPS"][0] != "HIDDEN") {
            $this->arResult["SHOW_FIELDS"][] = "USER_GROUPS";

            $rsGroups = CGroup::GetList($by = "id", $order = "asc", Array());
            while ($resGroups = $rsGroups->Fetch()) {
                if (in_array($resGroups["ID"], $this->arParams["USER_GROUPS"])) {
                    $this->arResult["SELECT_USER_GROUPS"][$resGroups["ID"]] = $resGroups["NAME"];
                }
            }
        }

        $this->arResult["REQUIRED_FIELDS"] = array_unique(array_merge($arDefaultFields,
            $this->arParams["REQUIRED_FIELDS"]));

// use captcha?
        $this->arResult["USE_CAPTCHA"] = $this->arParams["USE_CAPTCHA"];

// start values
        $this->arResult["VALUES"] = array();
        $this->arResult["ERRORS"] = array();
        $this->arResult["SHOW_SMS_FIELD"] = false;
        $register_done = false;
// register user


// if user is registered - redirect him to backurl or to success_page; currently added users too
        if ($register_done) {
            $this->arResult["REGISTER_SUCCESS"] = "Y";
        }

        $this->arResult["VALUES"] = htmlspecialcharsEx($this->arResult["VALUES"]);

// redefine required list - for better use in template
        $this->arResult["REQUIRED_FIELDS_FLAGS"] = array();
        foreach ($this->arResult["REQUIRED_FIELDS"] as $field) {
            $this->arResult["REQUIRED_FIELDS_FLAGS"][$field] = "Y";
        }

// check backurl existance
        $this->arResult["BACKURL"] = htmlspecialcharsbx($_REQUEST["backurl"]);

// get countries list
        if (in_array("PERSONAL_COUNTRY", $this->arResult["SHOW_FIELDS"]) || in_array("WORK_COUNTRY",
                $this->arResult["SHOW_FIELDS"])) {
            $this->arResult["COUNTRIES"] = GetCountryArray();
        }

// get date format
        if (in_array("PERSONAL_BIRTHDAY", $this->arResult["SHOW_FIELDS"])) {
            $this->arResult["DATE_FORMAT"] = CLang::GetDateFormat("SHORT");
        }

// ********************* User properties ***************************************************
        $this->arResult["USER_PROPERTIES"] = array("SHOW" => "N");
        $arUserFields = $USER_FIELD_MANAGER->GetUserFields("USER", 0, LANGUAGE_ID);
        if (is_array($arUserFields) && count($arUserFields) > 0) {
            if (!is_array($this->arParams["USER_PROPERTY"])) {
                $this->arParams["USER_PROPERTY"] = array($this->arParams["USER_PROPERTY"]);
            }

            foreach ($arUserFields as $FIELD_NAME => $arUserField) {
                if (!in_array($FIELD_NAME, $this->arParams["USER_PROPERTY"]) && $arUserField["MANDATORY"] != "Y") {
                    continue;
                }

                $arUserField["EDIT_FORM_LABEL"] = strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
                $arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
                $arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
                $this->arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
            }
        }
        if (!empty($this->arResult["USER_PROPERTIES"]["DATA"])) {
            $this->arResult["USER_PROPERTIES"]["SHOW"] = "Y";
            $this->arResult["bVarsFromForm"] = (count($this->arResult['ERRORS']) <= 0) ? false : true;
        }
// ******************** /User properties ***************************************************

// initialize captcha
        if ($this->arResult["USE_CAPTCHA"] == "Y") {
            $this->arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
        }


//time zones
        $this->arResult["TIME_ZONE_ENABLED"] = CTimeZone::Enabled();
        if ($this->arResult["TIME_ZONE_ENABLED"]) {
            $this->arResult["TIME_ZONE_LIST"] = CTimeZone::GetZones();
        }

        $this->arResult["SECURE_AUTH"] = false;
        if (!CMain::IsHTTPS() && COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y') {
            $sec = new CRsaSecurity();
            if (($arKeys = $sec->LoadKeys())) {
                $sec->SetKeys($arKeys);
                $sec->AddToForm('regform', array('REGISTER[PASSWORD]', 'REGISTER[CONFIRM_PASSWORD]'));
                $this->arResult["SECURE_AUTH"] = true;
            }
        }

// all done
        $this->IncludeComponentTemplate();
    }


    public function sendFormAction()
    {
        global $USER_FIELD_MANAGER;
        global $APPLICATION;
        global $DB;
        $this->executeComponent();
        if ($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["REGISTER"] <> '') {
            if (COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y') {
                //possible encrypted user password
                $sec = new CRsaSecurity();
                if (($arKeys = $sec->LoadKeys())) {
                    $sec->SetKeys($arKeys);
                    $errno = $sec->AcceptFromForm(array('REGISTER'));
                    if ($errno == CRsaSecurity::ERROR_SESS_CHECK) {
                        $this->arResult["ERRORS"][] = GetMessage("main_register_sess_expired");
                    } elseif ($errno < 0) {
                        $this->arResult["ERRORS"][] = GetMessage("main_register_decode_err",
                            array("#ERRCODE#" => $errno));
                    }
                }
            }

            // check emptiness of required fields
            foreach ($this->arResult["SHOW_FIELDS"] as $key) {
                if ($key != "PERSONAL_PHOTO" && $key != "WORK_LOGO") {
                    $this->arResult["VALUES"][$key] = $_REQUEST["REGISTER"][$key];
                    if (in_array($key,
                            $this->arResult["REQUIRED_FIELDS"]) && trim($this->arResult["VALUES"][$key]) == '') {
                        $this->arResult["ERRORS"][$key] = GetMessage("REGISTER_FIELD_REQUIRED");
                    }
                } else {
                    $_FILES["REGISTER_FILES_" . $key]["MODULE_ID"] = "main";
                    $this->arResult["VALUES"][$key] = $_FILES["REGISTER_FILES_" . $key];
                    if (in_array($key,
                            $this->arResult["REQUIRED_FIELDS"]) && !is_uploaded_file($_FILES["REGISTER_FILES_" . $key]["tmp_name"])) {
                        $this->arResult["ERRORS"][$key] = GetMessage("REGISTER_FIELD_REQUIRED");
                    }
                }
            }

            if (isset($_REQUEST["REGISTER"]["TIME_ZONE"])) {
                $this->arResult["VALUES"]["TIME_ZONE"] = $_REQUEST["REGISTER"]["TIME_ZONE"];
            }

            $USER_FIELD_MANAGER->EditFormAddFields("USER", $this->arResult["VALUES"]);

            //this is a part of CheckFields() to show errors about user defined fields
            if (!$USER_FIELD_MANAGER->CheckFields("USER", 0, $this->arResult["VALUES"])) {
                $e = $APPLICATION->GetException();
                $this->arResult["ERRORS"][] = substr($e->GetString(), 0, -4); //cutting "<br>"
                $APPLICATION->ResetException();
            }

            // check captcha
            if ($this->arResult["USE_CAPTCHA"] == "Y") {
                if (!$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"])) {
                    $this->arResult["ERRORS"][] = GetMessage("REGISTER_WRONG_CAPTCHA");
                }
            }

            if (count($this->arResult["ERRORS"]) > 0) {

                $arError = $this->arResult["ERRORS"];
                foreach ($arError as $key => $error) {
                    if (intval($key) == 0 && $key !== 0) {
                        $arError[$key] = str_replace("#FIELD_NAME#", '"' . GetMessage('REGISTER_FIELD_' . $key) . '"',
                            $error);
                    }
                }

                return [
                    'error' => true,
                    'errorMessage' => $arError
                ];
            } else // if there's no any errors - create user
            {
                $this->arResult['VALUES']["GROUP_ID"] = array();
                //группы пользователя
                if (!$this->arParams["USER_GROUPS"] || $this->arParams["USER_GROUPS"][0] == "HIDDEN" || !$_REQUEST["REGISTER"]["USER_GROUPS"]) {
                    $def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
                    if ($def_group != "") {
                        $this->arResult['VALUES']["GROUP_ID"] = explode(",", $def_group);
                    }
                } else {
                    $this->arResult['VALUES']["GROUP_ID"] = $_REQUEST["REGISTER"]["USER_GROUPS"];
                }

                //
                $this->arResult['VALUES']["LOGIN"] = $this->arResult['VALUES']["EMAIL"];
                $this->arResult['VALUES']["CHECKWORD"] = md5(CMain::GetServerUniqID() . uniqid());
                $this->arResult['VALUES']["~CHECKWORD_TIME"] = $DB->CurrentTimeFunction();
                $this->arResult['VALUES']["ACTIVE"] = "Y";
                $this->arResult['VALUES']["CONFIRM_CODE"] = ($bConfirmReq ? randString(8) : "");
                $this->arResult['VALUES']["LID"] = SITE_ID;
                $this->arResult['VALUES']["LANGUAGE_ID"] = LANGUAGE_ID;

                $this->arResult['VALUES']["USER_IP"] = $_SERVER["REMOTE_ADDR"];
                $this->arResult['VALUES']["USER_HOST"] = @gethostbyaddr($_SERVER["REMOTE_ADDR"]);

                if ($this->arResult["VALUES"]["AUTO_TIME_ZONE"] <> "Y" && $this->arResult["VALUES"]["AUTO_TIME_ZONE"] <> "N") {
                    $this->arResult["VALUES"]["AUTO_TIME_ZONE"] = "";
                }

                $bOk = true;
                $ID = 0;
                $user = new CUser();
                global $USER;
                $companyObject = new Company(SITE_ID);

                if ($companyObject->isUserAdmin($USER->GetID(), $_SESSION['AUTH_COMPANY_CURRENT_ID']) && $bOk) {
                    $ID = $user->Add($this->arResult["VALUES"]);
                } else {
                    $this->arResult["ERRORS"][] = GetMessage("main_register_error_permission");
                }

                if (intval($ID) > 0) {

                    if($this->arParams["ABILITY_TO_SET_ROLE"] == "Y" && isset($_REQUEST["REGISTER"]["STAFF_ROLE"])){
                        $staffRoles = $_REQUEST["REGISTER"]["STAFF_ROLE"];
                    }
                    else{
                        $staffRoles = "STAFF";
                    }

                    if (!($resAddStaff = $companyObject->addStaff($_SESSION['AUTH_COMPANY_CURRENT_ID'], $ID, $staffRoles, "Y"))) {
                        $this->arResult["ERRORS"][] = $resAddStaff;
                    }

                    $this->arResult['VALUES']["USER_ID"] = $ID;

                    return [
                        'error' => false,
                        'successMessage' => GetMessage("SOTBIT_AUTH_STAFF_REGISTER_SUCCESS", ["#LOGIN#" =>$this->arResult['VALUES']["LOGIN"]])
                    ];
                } else {
                    $existUser = $this->checkExistUser($this->arResult['VALUES']["EMAIL"]);
                    if($existUser['error'] === true){
                        return $existUser;
                    }
                    else {
                        $this->arResult["ERRORS"][] = $user->LAST_ERROR;
                    }
                }

                if (count($this->arResult["ERRORS"]) <= 0) {
                    if (COption::GetOptionString("main", "event_log_register", "N") === "Y") {
                        CEventLog::Log("SECURITY", "USER_REGISTER", "main", $ID);
                    }
                } else {
                    foreach ($this->arResult["ERRORS"] as $error) {
                        $returnError = explode("<br>", $error);
                    }
                    return [
                        'error' => true,
                        'errorMessage' => $returnError
                    ];
                }

            }
        }

    }

    public function confirmStaffAction()
    {
        $request = Bitrix\Main\Context::getCurrent()->getRequest();
        $registerFields = $request->get('REGISTER');
        if(isset($registerFields) && !empty($registerFields)) {
            $email = $request->get('REGISTER')['EMAIL'];
            $rsUsers = CUser::GetList(($by = "id"), ($order = "asc"), ["=EMAIL" => $email]);
            if ($arrayUser = $rsUsers->fetch()) {
                $staffId = $arrayUser["ID"];
            }
        }

        $staffObject = \Sotbit\Auth\Internals\StaffTable::getList(array(
            'filter' => ['COMPANY_ID' => $_SESSION['AUTH_COMPANY_CURRENT_ID'], 'USER_ID' => $staffId],
            'select' => array('ID')
        ));
        if ($staffArray = $staffObject->fetch()) {
            return [
                'error' => true,
                'errorMessage' => GetMessage("main_register_error_user_isset_on_company")
            ];
        }
        else {
            $company = new Company(SITE_ID);
            $roles = $request->get('REGISTER')['STAFF_ROLE'];
            if(!$roles){
                $roles = "STAFF";
            }
            $result = $company->addStaff($_SESSION['AUTH_COMPANY_CURRENT_ID'], $staffId, $roles, "Y");
            if (is_numeric($result)) {
                return [
                    'error' => false,
                    'successMessage' => GetMessage("SOTBIT_AUTH_STAFF_REGISTER_SUCCESS", ["#LOGIN#" => $arrayUser["LOGIN"]])
                ];
            } else {
                return [
                    'error' => true,
                    'errorMessage' => $result
                ];
            }
        }

    }

    public function sendReferralFormAction()
    {
        $request = Bitrix\Main\Context::getCurrent()->getRequest();
        $registerFields = $request->get('REGISTER');
        if(isset($registerFields) && !empty($registerFields)){
            $email = $request->get('REGISTER')['EMAIL'];

            if(isset($email) && check_email($email)){
                if (!$this->arParams["USER_GROUPS"] || $this->arParams["USER_GROUPS"][0] == "HIDDEN") {
                    $def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
                    if ($def_group <> "") {
                        $this->arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(explode(",", $def_group));
                    } else {
                        $this->arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(array());
                    }
                } else {
                    $this->arResult["GROUP_POLICY"] = CUser::GetGroupPolicy($this->arParams["USER_GROUPS"]);
                }

                $existUser = $this->checkExistUser($email);
                if($existUser['error'] === true){
                    return $existUser;
                }
                else{
                    $password = randString($this->arResult["GROUP_POLICY"]["PASSWORD_LENGTH"] ?: 6);
                    $userFields = [
                        "LOGIN" => $email,
                        "EMAIL" => $email,
                        "PASSWORD" => $password,
                        "GROUP_ID" => $request->get('REGISTER')['USER_GROUPS']
                    ];
                    $user = new CUser;
                    $ID = $user->Add($userFields);
                    if (intval($ID) > 0){
                        $company = new Company(SITE_ID);
                        $roles = $request->get('REGISTER')['STAFF_ROLE'];
                        if(!$roles){
                            $roles = "STAFF";
                        }
                        $resAddStaff = $company->addStaff($_SESSION['AUTH_COMPANY_CURRENT_ID'], $ID, $roles, "Y");
                        return [
                            'error' => false,
                            'successMessage' => GetMessage("SOTBIT_AUTH_STAFF_REGISTER_REFERRAL_SUCCESS")
                        ];
                    }
                    else{
                        return [
                            'error' => true,
                            'errorMessage' => $user->LAST_ERROR
                        ];
                    }
                }
            }
            else{
                return [
                    'error' => true,
                    'errorMessage' => GetMessage("SOTBIT_AUTH_STAFF_REGISTER_ERROR_EMAIL")
                ];
            }
        }
    }

    private function checkExistUser($email){
        $rsUsers = CUser::GetList(($by = "id"), ($order = "asc"), ["=EMAIL" => $email]);
        if ($arrayUser = $rsUsers->fetch()) {
            $userId = $arrayUser["ID"];
            $this->arResult["USER_ISSET"] = "Y";
            $staffObject = \Sotbit\Auth\Internals\StaffTable::getList(array(
                'filter' => ['COMPANY_ID' => $_SESSION['AUTH_COMPANY_CURRENT_ID'], 'USER_ID' => $userId],
                'select' => array('ID')
            ));
            if ($staffArray = $staffObject->fetch()) {
                $errorMessage[] = GetMessage("main_register_error_user_isset_on_company");
                return [
                    'error' => true,
                    'errorMessage' => $errorMessage
                ];
            } else {
                $errorMessage[] = GetMessage("main_register_error_user_isset_on_system");
                return [
                    'error' => true,
                    'userId' => $userId,
                    'errorMessage' => $errorMessage
                ];
            }

        } else {
            return [
                'error' => false
            ];
        }
    }

}