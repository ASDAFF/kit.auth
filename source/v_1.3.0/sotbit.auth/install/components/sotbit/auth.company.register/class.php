<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option,
    Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Engine\Contract\Controllerable,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Engine\ActionFilter\Authentication,
    Sotbit\Auth\Internals\BuyerConfirmTable,
    Sotbit\Auth\Internals\CompanyConfirmTable,
    Sotbit\Auth\Internals\UserConfirmTable,
    Sotbit\Auth\User\WholeSaler;

Loc::loadMessages(__FILE__);

class CompanyRegister extends CBitrixComponent implements Controllerable
{
    protected $error;
    protected $resultFields;
    protected $whosalerId;
    protected $registerValues = [];
    protected $eventResult = true;
    protected $issetCompany = false;
    protected $return;


    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'registerCompany' => [
                '-prefilters' => [
                    Authentication::class,
                ],
            ]
        ];
    }

    public function onPrepareComponentParams($params)
    {
        return $params;
    }

    protected function listKeysSignedParameters()
    {
        return [
            "SHOW_WHOLESALER_ORDER_FIELDS",
            "SHOW_FIELDS",
            "REQUIRED_FIELDS",
            "SHOW_WHOLESALER_FIELDS",
            "REQUIRED_WHOLESALER_FIELDS",
            "SHOW_WHOLESALE_ORDER_FIELDS",
            "USE_CAPTCHA",
            "USER_GROUPS",
        ];
    }

    private function setDefaultParams()
    {
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
            if(!is_set($this->arParams, $key))
            {
                $this->arParams[$key] = $value;
            }
        }

        if(!is_array($this->arParams["SHOW_FIELDS"]))
        {
            $this->arParams["SHOW_FIELDS"] = [];
        }
        if(!is_array($this->arParams["REQUIRED_FIELDS"]))
        {
            $this->arParams["REQUIRED_FIELDS"] = [];
        }
        if(!is_array($this->arParams["SHOW_WHOLESALER_FIELDS"]))
        {
            $this->arParams["SHOW_WHOLESALER_FIELDS"] = [];
        }
        if(!is_array($this->arParams["REQUIRED_WHOLESALER_FIELDS"]))
        {
            $this->arParams["REQUIRED_WHOLESALER_FIELDS"] = [];
        }
        if(!is_array($this->arParams["SHOW_WHOLESALE_ORDER_FIELDS"]))
        {
            $this->arParams["SHOW_WHOLESALE_ORDER_FIELDS"] = [];
        }

        $this->arParams['LOGIN_EQ_EMAIL'] = Option::get("sotbit.auth", "LOGIN_EQ_EMAIL", "N", SITE_ID);
        $this->arResult["EMAIL_REQUIRED"] = (Option::get("main", "new_user_email_required", "Y") != "N");
        $this->arParams["USE_EMAIL_CONFIRMATION"] = (Option::get("main", "new_user_registration_email_confirmation", "N") == "Y" && $this->arResult["EMAIL_REQUIRED"] ? "Y" : "N");

        $arDefaultFields = [
            "LOGIN",
            "PASSWORD",
            "CONFIRM_PASSWORD"
        ];
        $arDefaultWholeSalerFields = [];

        if($this->arResult["EMAIL_REQUIRED"])
        {
            $arDefaultFields[] = "EMAIL";
        }

        if($this->arResult["EMAIL_REQUIRED"])
        {
            $arDefaultWholeSalerFields[] = "EMAIL";
        }

        $def_group = Option::get("main", "new_user_registration_def_group", "");
        if($def_group != "")
        {
            $this->arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(explode(",", $def_group));
        }
        else
        {
            $this->arResult["GROUP_POLICY"] = CUser::GetGroupPolicy([]);
        }

        $this->arResult["SHOW_FIELDS"] = array_unique(array_merge($arDefaultFields, $this->arParams["SHOW_FIELDS"]));
        $this->arResult["REQUIRED_FIELDS"] = array_unique(array_merge($arDefaultFields, $this->arParams["REQUIRED_FIELDS"]));

        $this->arResult["SHOW_WHOLESALER_FIELDS"] = array_unique(array_merge($arDefaultWholeSalerFields, $this->arParams["SHOW_WHOLESALER_FIELDS"]));
        $this->arResult["REQUIRED_WHOLESALER_FIELDS"] = array_unique(array_merge($arDefaultWholeSalerFields, $this->arParams["REQUIRED_WHOLESALER_FIELDS"]));

// use captcha?
        $this->arResult["USE_CAPTCHA"] = Option::get("main", "captcha_registration", "N") == "Y" ? "Y" : "N";

// start values
        $this->arResult["VALUES"] = [
            'FIELDS' => [],
            'WHOLESALER_FIELDS' => [],
            'WHOLESALER_ORDER_FIELDS' => []
        ];
        $this->arResult["ERRORS"] = [];
        $register_done = false;
        $this->arResult['WHOLESALER_ORDER_FIELDS'] = [];
    }

    function getOrderProps($propertyCode)
    {
        if(Loader::includeModule("sale")){
            $rs = \Bitrix\Sale\Internals\OrderPropsTable::getList(
                [
                    'filter' => [
                        'ACTIVE' => 'Y',
                        'CODE' => $propertyCode,
                        'PERSON_TYPE_ID' => $this->whosalerId
                    ],
                    'select' => [
                        'CODE',
                        'NAME',
                        'REQUIRED',
                        'SETTINGS'
                    ]
                ]);
            while ($property = $rs->fetch())
            {
                $this->arResult['WHOLESALER_ORDER_FIELDS'][$property['CODE']] = [
                    'CODE' => $property['CODE'],
                    'NAME' => $property['NAME'],
                    'REQUIRED' => $property['REQUIRED'],
                    'SETTINGS' => $property['SETTINGS'],
                ];
            }
        }
    }

    function executeComponent()
    {
        global $USER_FIELD_MANAGER, $APPLICATION;

        if(!\Bitrix\Main\Loader::includeModule("sotbit.auth"))
        {
            return;
        }

        if(Option::get("main", "new_user_registration", "N") == "N")
        {
            $APPLICATION->AuthForm([]);
        }

        $this->setDefaultParams();

        if($this->arParams['SHOW_WHOLESALER_ORDER_FIELDS'])
        {
            $this->getOrderProps($this->arParams['SHOW_WHOLESALER_ORDER_FIELDS']);
        }


        $this->arResult["VALUES"] = htmlspecialcharsEx($this->arResult["VALUES"]);

        $this->arResult["REQUIRED_FIELDS_FLAGS"] = [];
        foreach ($this->arResult["REQUIRED_FIELDS"] as $field)
        {
            $this->arResult["REQUIRED_FIELDS_FLAGS"][$field] = "Y";
        }

        $this->arResult["BACKURL"] = htmlspecialcharsbx($_REQUEST["backurl"]);

        if(in_array("PERSONAL_COUNTRY", $this->arResult["SHOW_FIELDS"]) || in_array("WORK_COUNTRY", $this->arResult["SHOW_FIELDS"]))
        {
            $this->arResult["COUNTRIES"] = GetCountryArray();
        }

        if(in_array("PERSONAL_BIRTHDAY", $this->arResult["SHOW_FIELDS"]))
        {
            $this->arResult["DATE_FORMAT"] = CLang::GetDateFormat("SHORT");
        }

        $this->arResult["USER_PROPERTIES"] = [
            "SHOW" => "N"
        ];
        $arUserFields = $USER_FIELD_MANAGER->GetUserFields("USER", 0, LANGUAGE_ID);
        if(is_array($arUserFields) && count($arUserFields) > 0)
        {
            if(!is_array($this->arParams["USER_PROPERTY"]))
            {
                $this->arParams["USER_PROPERTY"] = [
                    $this->arParams["USER_PROPERTY"]
                ];
            }

            foreach ($arUserFields as $FIELD_NAME => $arUserField)
            {
                if(!in_array($FIELD_NAME, $this->arParams["USER_PROPERTY"]) && $arUserField["MANDATORY"] != "Y")
                {
                    continue;
                }

                $arUserField["EDIT_FORM_LABEL"] = strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
                $arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
                $arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
                $this->arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
            }
        }
        if(!empty($this->arResult["USER_PROPERTIES"]["DATA"]))
        {
            $this->arResult["USER_PROPERTIES"]["SHOW"] = "Y";
            $this->arResult["bVarsFromForm"] = (count($this->arResult['ERRORS']) <= 0) ? false : true;
        }
// ******************** /User properties ***************************************************

// initialize captcha
        if($this->arResult["USE_CAPTCHA"] == "Y")
        {
            $this->arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
        }

// set title
        if($this->arParams["SET_TITLE"] == "Y")
        {
            $title = Loc::getMessage("REGISTER_DEFAULT_TITLE");
            if(mb_detect_encoding($title, 'UTF-8, CP1251') == 'UTF-8') {
                $title = mb_convert_encoding($title, 'CP1251', 'UTF-8');
            }
            $APPLICATION->SetTitle($title);
        }

// time zones
        $this->arResult["TIME_ZONE_ENABLED"] = CTimeZone::Enabled();
        if($this->arResult["TIME_ZONE_ENABLED"])
        {
            $this->arResult["TIME_ZONE_LIST"] = CTimeZone::GetZones();
        }

        $this->arResult["SECURE_AUTH"] = false;
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
                $this->arResult["SECURE_AUTH"] = true;
            }
        }

        $this->arResult['USER_GROUPS'] = array();
        $userGroups = unserialize(Option::get('sotbit.auth','USER_GROUPS','a:0:{}',SITE_ID));
        if(!is_array($userGroups))
        {
            $userGroups = array();
        }
        if(count($this->arResult['USER_GROUPS'] > 0))
        {
            $rs = \Bitrix\Main\GroupTable::getList(
                array(
                    'filter' => array('ID' => $userGroups,'ACTIVE' => 'Y'),
                    'select' => array('ID','NAME')
                )
            );
            while($group = $rs->fetch())
            {
                $this->arResult['USER_GROUPS'][$group['ID']] = $group['NAME'];
            }
        }


        $this->includeComponentTemplate();
    }

    private function getEncrypted(){
        $sec = new CRsaSecurity();
        if(($arKeys = $sec->LoadKeys()))
        {
            $sec->SetKeys($arKeys);
            $errno = $sec->AcceptFromForm([
                'REGISTER'
            ]);
            if($errno == CRsaSecurity::ERROR_SESS_CHECK)
            {
                $this->error[] = Loc::getMessage("main_register_sess_expired");
            }
            elseif($errno < 0)
            {
                $this->error[] = Loc::getMessage("main_register_decode_err", [
                    "#ERRCODE#" => $errno
                ]);
            }
        }
    }

    private function checkRequiredFields($fields)
    {
        $requiredFields = unserialize(Option::get(SotbitAuth::idModule, 'GROUP_REQUIRED_FIELDS_' . $this->whosalerId, '', SITE_ID));

        if(!is_array($requiredFields))
        {
            $requiredFields = [];
        }

        foreach ($fields as $key => $value)
        {
            if($key != "PERSONAL_PHOTO" && $key != "WORK_LOGO")
            {
                $this->resultFields["VALUES"]['WHOLESALER_FIELDS'][$this->whosalerId][$key] = $value;

                if(in_array($key, $requiredFields) && trim($value) == '') $this->error[$key] = Loc::getMessage("REGISTER_ORDER_FIELD_REQUIRED");

            }
            else
            {
                $_FILES["REGISTER_WHOLESALER_FILES_" . $key]["MODULE_ID"] = "main";
                $this->resultFields["VALUES"]['WHOLESALER_FIELDS'][$this->whosalerId][$key] = $_FILES["REGISTER_FILES_" . $key];
                if(in_array($key, $requiredFields) && !is_uploaded_file($_FILES["REGISTER_WHOLESALER_FILES_" . $key]["tmp_name"])) $this->error[$key] = Loc::getMessage("REGISTER_FIELD_REQUIRED");
            }
        }
    }

    private function checkSettingsFields($request)
    {
        if($this->arResult['WHOLESALER_ORDER_FIELDS']){
            $companyFields = $request->get('REGISTER_WHOLESALER_OPT')[$this->whosalerId];
            foreach ($this->arResult['WHOLESALER_ORDER_FIELDS'] as $code => $field){
                if($field["SETTINGS"]){
                    if($field["SETTINGS"]["PATTERN"] && !empty($field["SETTINGS"]["PATTERN"]) && !preg_match("/".$field["SETTINGS"]["PATTERN"]."/", $companyFields[$code])){
                        $this->error[] = Loc::getMessage("REGISTER_ORDER_FIELD_PATTERN", ["#FIELD_NAME#"=>$field["NAME"]]);
                    }
                    if($field["SETTINGS"]["MINLENGTH"] && !empty($field["SETTINGS"]["MINLENGTH"]) && strlen($companyFields[$code])<$field["SETTINGS"]["MINLENGTH"]){
                        $this->error[] = Loc::getMessage("REGISTER_ORDER_FIELD_MINLENGTH", ["#FIELD_NAME#"=>$field["NAME"], "#MINLENGTH#"=> $field["SETTINGS"]["MINLENGTH"]]);
                    }
                    if($field["SETTINGS"]["MAXLENGTH"] && !empty($field["SETTINGS"]["MAXLENGTH"]) && strlen($companyFields[$code])>$field["SETTINGS"]["MAXLENGTH"]){
                        $this->error[] = Loc::getMessage("REGISTER_ORDER_FIELD_MAXLENGTH", ["#FIELD_NAME#"=>$field["NAME"], "#MAXLENGTH#"=> $field["SETTINGS"]["MINLENGTH"]]);
                    }
                }
            }
        }
    }

    private function setWholesalerFields($request)
    {
        $orderOptFields = unserialize(Option::get(SotbitAuth::idModule, 'GROUP_ORDER_FIELDS_' . $this->whosalerId, '', SITE_ID));
        $this->getOrderProps(array_keys($request->get('REGISTER_WHOLESALER_OPT')[$this->whosalerId]));
        $orderFieldsAll = $this->arResult['WHOLESALER_ORDER_FIELDS'];

        if(is_array($orderOptFields))
            foreach ($orderOptFields as $key => $code) $orderFields[$this->whosalerId][] = $orderFieldsAll[$code];

        $optFields = $request->get('REGISTER_WHOLESALER_OPT')[$this->whosalerId];

        foreach ($optFields as $key => $value)
        {
            $this->resultFields["VALUES"]['WHOLESALER_ORDER_FIELDS'][$this->whosalerId][$key] = $value;

            if($orderFieldsAll[$key]['REQUIRED'] == 'Y' && trim($value) == '')
            {
                $this->error[$key] = Loc::getMessage("REGISTER_ORDER_FIELD_REQUIRED", ['#FIELD_NAME#' => '"' . $orderFieldsAll[$key]['NAME'] . '"']);
            }

        }

        if(trim($request->get("REGISTER")['PASSWORD'] == '')) $this->error['PASSWORD'] = Loc::getMessage("REGISTER_FIELD_REQUIRED");

        if(trim($request->get("REGISTER")['CONFIRM_PASSWORD'] == '')) $this->error['CONFIRM_PASSWORD'] = Loc::getMessage("REGISTER_FIELD_REQUIRED");

        if($request->get("REGISTER")['CONFIRM_PASSWORD'] !== $request->get("REGISTER")['PASSWORD']) $this->error['CONFIRM_PASSWORD'] = Loc::getMessage("REGISTER_FIELD_PASSWORD_AND_CONFPASSWORD_NOT_SAME");

        if(isset($request->get("REGISTER_WHOLESALER")["TIME_ZONE"])) $this->resultFields['WHOLESALER_FIELDS'][$this->whosalerId]["TIME_ZONE"] = $request->get("REGISTER_WHOLESALER")["TIME_ZONE"];
    }

    private function setUserFields()
    {
        foreach ($this->arResult["SHOW_FIELDS"] as $key)
        {
            if($key != "PERSONAL_PHOTO" && $key != "WORK_LOGO")
            {
                $this->resultFields["VALUES"]['FIELDS'][$key] = $_REQUEST["REGISTER"][$key];
                if(in_array($key, $this->arResult["REQUIRED_FIELDS"]) && trim($this->resultFields["VALUES"]['FIELDS'][$key]) == '')
                {
                    $this->error[$key] = Loc::getMessage("REGISTER_FIELD_REQUIRED");
                }
            }
            else
            {
                $_FILES["REGISTER_FILES_" . $key]["MODULE_ID"] = "main";
                $this->resultFields["VALUES"]['FIELDS'][$key] = $_FILES["REGISTER_FILES_" . $key];
                if(in_array($key, $this->arResult["REQUIRED_FIELDS"]) && !is_uploaded_file($_FILES["REGISTER_FILES_" . $key]["tmp_name"]))
                {
                    $this->error[$key] = Loc::getMessage("REGISTER_FIELD_REQUIRED");
                }
            }
        }
        if(isset($_REQUEST["REGISTER"]["TIME_ZONE"]))
        {
            $this->resultFields["VALUES"]['FIELDS']["TIME_ZONE"] = $_REQUEST["REGISTER"]["TIME_ZONE"];
        }
    }

    private function checkCaptcha($request)
    {
        global $APPLICATION;

        if(!$APPLICATION->CaptchaCheckCode($request->get("captcha_word"), $request->get("captcha_sid")))
        {
            $this->error[] = Loc::getMessage("REGISTER_WRONG_CAPTCHA");
        }
    }

    private function addErrorLog()
    {
        if(Option::get("main", "event_log_register_fail", "N") === "Y")
        {
            $arError = $this->error;
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

    private function checkExistUser($request)
    {
        if($this->arParams['LOGIN_EQ_EMAIL'] == 'Y')
            $this->resultFields["VALUES"]['FIELDS']['LOGIN'] = !empty($request->get("REGISTER")['EMAIL']) ? $request->get("REGISTER")['EMAIL'] : $this->resultFields["VALUES"]['WHOLESALER_FIELDS'][$this->whosalerId]['EMAIL'];
        else
            $this->resultFields["VALUES"]['FIELDS']['LOGIN'] = !empty($request->get("REGISTER")['LOGIN']) ? $request->get("REGISTER")['LOGIN'] : $this->resultFields["VALUES"]['WHOLESALER_FIELDS'][$this->whosalerId]['LOGIN'];


        $this->resultFields["VALUES"]['FIELDS']['EMAIL'] = !empty($request->get("REGISTER")['EMAIL']) ? $request->get("REGISTER")['EMAIL'] : $this->resultFields["VALUES"]['WHOLESALER_FIELDS'][$this->whosalerId]['EMAIL'];
        $this->resultFields["VALUES"]['FIELDS']['PASSWORD'] = $request->get("REGISTER")['PASSWORD'];
        $this->resultFields["VALUES"]['FIELDS']['USER_GROUP'] = $request->get("REGISTER")['USER_GROUP'];
        $this->resultFields["VALUES"]['FIELDS']['CONFIRM_PASSWORD'] = $request->get("REGISTER")['CONFIRM_PASSWORD'];

        if(CUser::getList($by, $order,['EMAIL' =>$this->resultFields["VALUES"]['FIELDS']['EMAIL']])->fetch()) {
            $this->error['EMAIL_EXIST'] = Loc::getMessage("REGISTER_USER_WITH_EMAIL_EXIST", array('#EMAIL#' => $this->resultFields["VALUES"]['FIELDS']['EMAIL']));
        }
    }

    private function registerCompany($request)
    {
        global $APPLICATION, $DB, $USER;
        $bConfirmReq = (Option::get("main", "new_user_registration_email_confirmation", "N") == "Y" && $this->arResult["EMAIL_REQUIRED"]);

        $this->registerValues["CHECKWORD"] = md5(CMain::GetServerUniqID() . uniqid());
        $this->registerValues["~CHECKWORD_TIME"] = $DB->CurrentTimeFunction();
        $this->registerValues["ACTIVE"] = $bConfirmReq ? "N" : "Y";
        $this->registerValues["CONFIRM_CODE"] = $bConfirmReq ? randString(8) : "";
        $this->registerValues["LID"] = SITE_ID;
        $this->registerValues["LANGUAGE_ID"] = LANGUAGE_ID;

        $this->registerValues["USER_IP"] = $_SERVER["REMOTE_ADDR"];
        $this->registerValues["USER_HOST"] = @gethostbyaddr($_SERVER["REMOTE_ADDR"]);

        if($this->registerValues["AUTO_TIME_ZONE"] != "Y" && $this->registerValues["AUTO_TIME_ZONE"] != "N")
        {
            $this->registerValues["AUTO_TIME_ZONE"] = "";
        }


        $def_group = Option::get("main", "new_user_registration_def_group", "");

        if($def_group != "")
        {
            $this->registerValues["GROUP_ID"] = explode(",", $def_group);
        }


        if($request->get("REGISTER_WHOLESALER"))
        {

            $this->registerValues['ORDER_FIELDS'] = $this->resultFields["VALUES"]['WHOLESALER_ORDER_FIELDS'][$this->whosalerId];
            $this->registerValues['PERSON_TYPE'] = $this->whosalerId;
        }

        //Проверка на наличие компании

        $events = GetModuleEvents("main", "OnBeforeUserRegister", true);
        foreach ($events as $arEvent)
        {
            if(ExecuteModuleEventEx($arEvent, [
                    &$this->registerValues
                ]) === false)
            {
                if($err = $APPLICATION->GetException())
                {
                    $this->error[] = $err->GetString();
                }

                $this->eventResult = false;
                break;
            }
        }

        if($this->eventResult) {

            if ($request->get("REGISTER_WHOLESALER")) {

                //проверка на модерацию пользователя
                $confirmRegister = Option::get('sotbit.auth', 'CONFIRM_REGISTER', 'N');

                if ($confirmRegister == 'Y') {
                    $needConfirmUser = true;
                    if($this->issetCompany && is_numeric($this->issetCompany["ID"]) &&  $request->get('CONFIRM_JOIN')=="Y"){
                        $confirmRoles = unserialize(Option::get('sotbit.auth', 'COMPANY_STAFF_ROLE', 'ADMIN'));

                        if($confirmRoles && !in_array("STAFF", $confirmRoles)){
                            $needConfirmUser = false;
                        }
                    }
                    else{
                        $confirmRoles = unserialize(Option::get('sotbit.auth', 'COMPANY_STAFF_ROLE', 'ADMIN'));

                        if($confirmRoles && !in_array("ADMIN", $confirmRoles)){
                            $needConfirmUser = false;
                        }
                    }

                    if($needConfirmUser){
                        $this->registerValues["ACTIVE"] = "N";
                    }

                }
            }

            $user = new CUser();
            $ID = $user->Add($this->registerValues);

            if (intval($ID) > 0) {
                if($needConfirmUser){
                    $rs = UserConfirmTable::add([
                        'LID' => SITE_ID,
                        'FIELDS' => $this->registerValues,
                        'EMAIL' => $this->registerValues['EMAIL'],
                        'ID_USER' => $ID
                    ]);
                }
                // authorize user
                if ($this->arParams["AUTH"] == "Y" && $this->registerValues["ACTIVE"] == "Y") {
                    if (!$arAuthResult = $USER->Login($this->registerValues["LOGIN"],
                        $this->registerValues["PASSWORD"])) {
                        $this->error[] = $arAuthResult;
                    }
                }

                $this->registerValues["USER_ID"] = $ID;

                $arEventFields = $this->registerValues;
                unset($arEventFields["PASSWORD"]);
                unset($arEventFields["CONFIRM_PASSWORD"]);
                $this->registerValues["ORDER_FIELDS"]["STATUS"] = "A";

                if (Option::get('sotbit.auth', 'CONFIRM_BUYER', 'N') == 'Y' && !$this->issetCompany["ID"]) {
                    $moderationBuyer = true;
                    $this->registerValues["ORDER_FIELDS"]["STATUS"] = "M";

                }

                $company = new \Sotbit\Auth\Company\Company(SITE_ID);
                if(!$this->issetCompany["ID"]){
                    $resultAddCompany = $company->addCompany($this->registerValues["ORDER_FIELDS"], $this->whosalerId, $ID);
                    if (!is_numeric($resultAddCompany)) {
                        $this->error[] = $resultAddCompany;
                        return $this->error;
                    }
                }
                else{
                    $addStaffResult = $company->addStaff($this->issetCompany["ID"], $ID);
                    if(!is_numeric($addStaffResult)){
                        $this->error[] = $addStaffResult;
                    }
                }

                if($moderationBuyer){
                    CompanyConfirmTable::add([
                        'LID' => SITE_ID,
                        'FIELDS' => $this->registerValues,
                        'ID_USER' => $ID,
                        'COMPANY_ID' => $resultAddCompany,
                    ]);
                }

                $event = new CEvent();
                $event->SendImmediate("NEW_USER", SITE_ID, $arEventFields);
                if ($bConfirmReq) {
                    $event->SendImmediate("NEW_USER_CONFIRM", SITE_ID, $arEventFields);
                }

                if($this->issetCompany["ID"]){
                    if($this->registerValues["ACTIVE"] == "Y"){
                        $this->return["message"] = Loc::getMessage("result_application_accepted");
                        $this->return["authorize"] = "Y";
                    }
                    else{
                        $this->return["message"] = Loc::getMessage("result_application_accepted_need_confirmation");
                    }
                }
                else{
                    if($this->registerValues["ACTIVE"] == "Y" && $moderationBuyer){
                        $this->return["message"] = Loc::getMessage("result_registered_nees_confirmation_company");
                        $this->return["authorize"] = "Y";
                    }
                    elseif($this->registerValues["ACTIVE"] == "Y"){
                        $this->return["message"] = Loc::getMessage("result_registered");
                        $this->return["authorize"] = "Y";
                    }
                    else{
                        $this->return["message"] = Loc::getMessage("result_registered_nees_confirmation_profile");
                    }
                }

            } else {
                $this->error[] = $user->LAST_ERROR;
            }
        }
    }

    public function registerCompanyAction()
    {
        global $USER, $APPLICATION, $USER_FIELD_MANAGER;
        if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["sotbit_auth_register"]) && !$USER->IsAuthorized())
        {
            $request = Bitrix\Main\Context::getCurrent()->getRequest();

            if(Option::get('main', 'use_encrypted_auth', 'N') == 'Y') {
                $this->getEncrypted();
            }

            $this->setDefaultParams();
            $registerWholesaler = $request->get("REGISTER_WHOLESALER");
            if(isset($registerWholesaler) && !empty($registerWholesaler)) {
                $this->whosalerId = $registerWholesaler['TYPE'];
                $this->resultFields["VALUES"]['WHOLESALER_FIELDS'][$this->whosalerId] = [];
                $this->checkRequiredFields($request->get('REGISTER_WHOLESALER_USER')[$this->whosalerId]);
                $this->setWholesalerFields($request);
                $this->checkSettingsFields($request);
            }
            elseif ($request->get("TAB") == 'USER'){
                $this->setUserFields();
            }

            $this->checkExistUser($request);

            $USER_FIELD_MANAGER->EditFormAddFields("USER", $this->resultFields["VALUES"]['FIELDS']);
            if(!$USER_FIELD_MANAGER->CheckFields("USER", 0, $this->resultFields["VALUES"]['FIELDS']))
            {
                $e = $APPLICATION->GetException();
                $this->error[] = substr($e->GetString(), 0, -4); // cutting "<br>"
                $APPLICATION->ResetException();
            }

            if($this->resultFields["VALUES"]['FIELDS'] && $this->resultFields["VALUES"]['WHOLESALER_FIELDS'])
            {
                $this->registerValues = array_merge($this->resultFields["VALUES"]['FIELDS'], $this->resultFields["VALUES"]['WHOLESALER_FIELDS'][$this->whosalerId]);
            }
            elseif($this->resultFields["VALUES"]['FIELDS'])
            {
                $this->registerValues = $this->resultFields["VALUES"]['FIELDS'];
            }
            unset($this->registerValues['TAB']);

            $this->registerValues['FILES'] = $_REQUEST['FILES'];

            if($this->arResult["USE_CAPTCHA"] == "Y")
            {
                $this->checkCaptcha($request);
            }

            if(count($this->error) > 0)
            {
                $this->addErrorLog();
                return ['errors' => $this->generationErrorMessage()];
            }
            else
            {
                $company = new \Sotbit\Auth\Company\Company(SITE_ID);
                $this->issetCompany = $company->checkExistCompany($this->resultFields["VALUES"]['WHOLESALER_ORDER_FIELDS'][$this->whosalerId], $this->whosalerId);

                if($this->issetCompany && is_numeric($this->issetCompany["ID"]) &&  $request->get('CONFIRM_JOIN')!="Y"){
                    return "COMPANY_ISSET";
                }

                $this->registerCompany($request);
            }

            if(count($this->error) > 0)
            {
                return ['errors' => $this->generationErrorMessage()];
            }
            else{
                return  $this->return;
            }

        }
        return false;
    }

    private function generationErrorMessage()
    {
        $message = "";
        foreach ($this->error as $errorMessage){
            if (mb_detect_encoding($errorMessage, 'UTF-8, CP1251') == 'UTF-8') {
                $errorMessage = mb_convert_encoding($errorMessage, 'CP1251', 'UTF-8');
            }
            $message .= "<div><label class='validation-invalid-label errortext'>".$errorMessage."<label></div>";
        }

        return $message;
    }

}