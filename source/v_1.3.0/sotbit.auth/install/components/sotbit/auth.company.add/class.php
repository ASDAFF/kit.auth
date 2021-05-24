<?php
use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;

use Sotbit\Auth\Internals\CompanyTable,
    Sotbit\Auth\Internals\CompanyPropsValueTable,
    Sotbit\Auth\Internals\BuyerConfirmTable,
    Sotbit\Auth\Internals\StaffTable,
    Sotbit\Auth\Internals\CompanyConfirmTable,
    Sotbit\Auth\Company;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class SotbitProfileAdd extends CBitrixComponent implements Controllerable
{
    const E_SALE_MODULE_NOT_INSTALLED = 10000;
    protected $companyId;
    protected $companyData;
    protected $isAjax;
    protected $companyObject;

    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'SotbitProfile' => [
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

    public function executeComponent()
    {
        global $USER, $APPLICATION;

        if (!$USER->IsAuthorized()) {
            $APPLICATION->AuthForm(Loc::getMessage("SOA_SALE_ACCESS_DENIED"), false, false, 'N', false);
            return;
        }

        Loc::loadMessages(__FILE__);

        $this->setFrameMode(false);

        if (!$this->checkRequiredModules()) {
            ShowError(Loc::getMessage("SOA_NECESSARY_MODULE_NOT_INSTALL"));
            return;
        }
        $request = Main\Application::getInstance()->getContext()->getRequest();

        if ($this->arParams["SET_TITLE"] === 'Y') {
            if ($request->get("EDIT_ID")) {
                $APPLICATION->SetTitle(Loc::getMessage("SOA_NEW_SALE_TITLE_EDIT"));
            } else {
                $APPLICATION->SetTitle(Loc::getMessage("SOA_NEW_SALE_TITLE"));
            }
        }


        if (count($this->arParams['PERSONAL_TYPES']) <= 0) {
            $this->arResult['ERRORS'][] = Loc::getMessage("SOA_SALE_ORG_NO_TYPES");
        }

        $this->companyObject = new Company\Company(SITE_ID);
        if ($request->get("EDIT_ID")) {
            $this->companyId = (int)$request->get("EDIT_ID");
            if(($resultPermission = $this->checkPermission()) === true) {
                $userId = (int)($USER->GetID());

                if (!empty($this->companyId)) {
                    $this->companyData = CompanyTable::getById($this->companyId)->fetch();
                } else {
                    $this->arResult['ERRORS_FATAL'][] = Loc::getMessage("SOA_SALE_NO_PROFILE");
                }

                if (empty($this->companyData)) {
                    $this->arResult['ERRORS_FATAL'][] = Loc::getMessage("SOA_SALE_NO_PROFILE");
                } elseif (!$this->companyObject->isUserAdmin($userId, $request->get("EDIT_ID"))) {
                    LocalRedirect(($this->arParams['PATH_TO_LIST']) ? $this->arParams['PATH_TO_LIST'] : $APPLICATION->GetCurPage());
                } else {

                    $idPersonType = $this->companyData['BUYER_TYPE'];

                    $companyProps = Company\Company::getCompanyProps($this->companyId);
                    if ($companyProps) {

                        $this->companyData['PROPS'] = array_map(
                            function ($v) {
                                return $this->removeSuffix($v, 'COMPANY_PROPERTY_');
                            },
                            $companyProps
                        );
                    }
                    if (empty($this->companyData)) {
                        $this->arResult['ERRORS_FATAL'][] = Loc::getMessage("SOA_SALE_NO_PROFILE");
                    }
                }
            }
            else{
                $this->arResult["ERRORS_FATAL"][] = $resultPermission;
            }
        }
        else {
            $idPersonType = reset($this->arParams['PERSONAL_TYPES']);
        }

        if ($request->isPost() && ($request->get("change_person_type")) && check_bitrix_sessid()) {
            if (in_array($request->get('PERSON_TYPE'), $this->arParams['PERSONAL_TYPES'])) {
                $idPersonType = $request->get('PERSON_TYPE');
            }
        }

        if ($request->isPost() && $request->get("cancel") && check_bitrix_sessid()) {
            $href = ($this->arParams['PATH_TO_LIST']) ? $this->arParams['PATH_TO_LIST'] : $APPLICATION->GetCurPage();

            \localRedirect($href);
            return;
        }


        if ($request->isPost() && ($request->get("save") || $request->get("apply")) && check_bitrix_sessid()) {
            if (empty($this->arResult['ERRORS_FATAL'])) {
                $this->isAjax = false;
                $this->addProfileProperties($request);
                if (!empty($request->get("PERSON_TYPE"))) {
                    $idPersonType = $request->get("PERSON_TYPE");
                }
            }
        }

        $this->fillResultArray($idPersonType, $request);
        $this->includeComponentTemplate();
    }

    protected function checkPermission()
    {
        global $USER;
        $userId = (int)($USER->GetID());
        if(!$this->companyObject->isUserAdmin($userId, $this->companyId)){
            return loc::getMessage("ERROR_USER_ACCESS_IS_DENIED");
        }

        $companyConfirm = CompanyConfirmTable::getList([
            'filter' => ["COMPANY_ID" => $this->companyId, 'STATUS' => NULL],
            'select' => ["ID"]
        ])->fetch();

        if($companyConfirm){
            return loc::getMessage("ERROR_ALREADY_ON_MODERATION");
        }


        return true;
    }
    /**
     * Function checks if required modules installed. If not, throws an exception
     */
    protected function checkRequiredModules()
    {
        return !(!Loader::includeModule('sale') || !Loader::includeModule('sotbit.b2bcabinet') || !Loader::includeModule('sotbit.auth'));
    }

    /**
     *
     * @param  Main\HttpRequest $request
     */
    protected function addProfileProperties(\Bitrix\Main\HttpRequest $request)
    {
        $fieldValues = $this->prepareAddProperties($request);

        // add
        if (empty($this->arResult['ERRORS'])) {
            $idProfile = $this->executeAddProperties($request, $fieldValues);
        }
        return $idProfile;


        // update
//		if(empty($this->arResult['ERRORS']) && $idProfile > 0)
//		{
//			if(strlen($request->get("save")) > 0)
//			{
//                \localRedirect($this->arParams["PATH_TO_LIST"]);
//			}
//		}
    }

    /**
     *
     * @param  Main\HttpRequest $request
     * @return array
     */
    protected function prepareAddProperties($request)
    {
//		if(strlen($request->get("NAME")) <= 0) {
//            $this->arResult['ERRORS'][] = Loc::getMessage("SOA_SALE_NO_INN");
//		}
//
//		if(!$request->get('PERSON_TYPE') || !in_array($request->get('PERSON_TYPE'), $this->arParams['PERSONAL_TYPES'])) {
//            $this->arResult['ERRORS'][] = Loc::getMessage("SOA_SALE_NO_PERSON_TYPE");
//		}

        $fieldValues = [];
        $orderPropertiesList = self::getOrderProps([
            'filter' => ["PERSON_TYPE_ID" => $request->get('PERSON_TYPE')]
        ]);

        while ($orderProperty = $orderPropertiesList->fetch()) {
            $currentValue = $request->get("ORDER_PROP_" . $orderProperty["ID"]);

            if ($this->checkProperty($orderProperty, $currentValue)) {
                $fieldValues[$orderProperty["ID"]] = [
                    "USER_PROPS_ID" => $this->idProfile,
                    "ORDER_PROPS_ID" => $orderProperty["ID"],
                    "NAME" => $orderProperty["NAME"],
                    'MULTIPLE' => $orderProperty["MULTIPLE"]
                ];

                if ($orderProperty["TYPE"] === 'FILE') {
                    $fileIdList = [];

                    $currentValue = $request->getFile("ORDER_PROP_" . $orderProperty["ID"]);

                    foreach ($currentValue['name'] as $key => $fileName) {
                        if (strlen($fileName) > 0) {
                            $fileArray = [
                                'name' => $fileName,
                                'type' => $currentValue['type'][$key],
                                'tmp_name' => $currentValue['tmp_name'][$key],
                                'error' => $currentValue['error'][$key],
                                'size' => $currentValue['size'][$key],
                            ];

                            $fileIdList[] = CFile::SaveFile($fileArray, "/sale/profile/");
                        }
                    }

                    $fieldValues[$orderProperty["ID"]]['VALUE'] = $fileIdList;
                } elseif ($orderProperty['TYPE'] == "MULTISELECT") {
                    $fieldValues[$orderProperty["ID"]]['VALUE'] = implode(',', $currentValue);
                } else {
                    $fieldValues[$orderProperty["ID"]]['VALUE'] = $currentValue;
                }
            } else {
                $this->arResult['ERRORS'][] = Loc::getMessage("SOA_SALE_NO_FIELD") . " \"" . $orderProperty["NAME"] . "\"";
            }
        }

        return $fieldValues;
    }

    /**
     * @param Main\HttpRequest $request
     * @param array $fieldValues
     */
    protected function executeAddProperties($request, $fieldValues)
    {
        $idProfile = 0;
        global $USER;
        $idUser = $USER->GetID();

        if (!$idUser) {
            $this->arResult['ERRORS'][] = Loc::getMessage("SOA_SALE_NO_USER");
        }

        $this->arResult['COMPANY_ADD_OK'] = false;
        if (empty($this->arResult['ERRORS'])) {
            if (Loader::includeModule('sotbit.auth')) {
                $fields = [
                    'PERSON_TYPE' => (int)$request->get('PERSON_TYPE'),
                    'ADD_COMPANY_NAME' => $request->get('ADD_COMPANY_NAME'),
                    'ORDER_FIELDS' => []
                ];

                $orderPropertiesList = self::getOrderProps([
                    'filter' => ["PERSON_TYPE_ID" => (int)$request->get('PERSON_TYPE')]
                ]);

                while ($orderProperty = $orderPropertiesList->fetch()) {
                    if(is_array($orderPropValue = $request->get('ORDER_PROP_' . $orderProperty['ID']))){
                        $orderPropValue = implode(',', $orderPropValue);
                    }
                    $fields['ORDER_FIELDS'][$orderProperty['CODE']] = $orderPropValue;
                    $orderPropertyList[$orderProperty['CODE']] = $orderProperty;
                }
                $hashCompany = $this->companyObject->getHash($fields['ORDER_FIELDS'], (int)$request->get('PERSON_TYPE'));
                $issetCompany = $this->companyObject->checkExistCompany($fields['ORDER_FIELDS'], (int)$request->get('PERSON_TYPE'));
                $fields['HASH'] = $hashCompany;
                if ($this->companyId) {
                    $addCompanyId = $this->companyId;
                    $fields['MODE'] = 'UPDATE';
                    if ($issetCompany && !empty($issetCompany) && $issetCompany["ID"] != $this->companyId) {
                        $this->arResult['ERRORS'][] = Loc::getMessage("SOA_SALE_DUBLICATE");
                    }
                    else{
                        if($request->get('apply')=="Y") {
                            $companyConfirm = CompanyConfirmTable::add([
                                'LID' => SITE_ID,
                                'FIELDS' => $fields,
                                'ID_USER' => $idUser,
                                'COMPANY_ID' => $addCompanyId
                            ]);

                            if ($companyConfirm->isSuccess()) {
                                // deactivate company if edit
                                CompanyTable::update($addCompanyId, [
                                    'DATE_UPDATE' => new \Bitrix\Main\Type\DateTime(),
                                    'STATUS' => "M",
                                    'ACTIVE' => "N",
                                ]);
                            }

                            $this->arResult["COMPANY_ADD_MODERATE_OK"] = true;
                        }

                        else{
                            $q = CompanyPropsValueTable::getList(['filter' => ['COMPANY_ID' => $this->companyData["ID"]]])->fetchAll();

                            foreach ($q as $i=> $item){
                                $originalProps[$this->companyData["PROPS"][$item["PROPERTY_ID"]]["CODE"]] = [
                                    'ID' => $item['ID'],
                                    'VALUE' => $item['VALUE'],
                                    //'NAME'=>$orderPropertyList[$this->companyData["PROPS"][$item["PROPERTY_ID"]]["CODE"]]["NAME"]
                                ];
                            }
                            try {
                                foreach($fields["ORDER_FIELDS"] as $key => $val) {
                                    if($originalProps[$key]){
                                        CompanyPropsValueTable::update($originalProps[$key]["ID"], ["VALUE"=>$val]);
                                    }
                                    else{
                                        $result = CompanyPropsValueTable::add(
                                            [
                                                'COMPANY_ID'  => $this->companyData["ID"],
                                                'PROPERTY_ID' => $orderPropertyList[$key]['ID'],
                                                'NAME'        => $orderPropertyList[$key]['NAME'],
                                                'VALUE'       => $val,
                                            ]
                                        );
                                    }
                                }

                                CompanyTable::update($this->companyData["ID"], [
                                    "NAME" => $fields["ADD_COMPANY_NAME"],
                                    "HASH" => $hashCompany,
                                    "DATE_UPDATE" => new Bitrix\Main\Type\DateTime(),
                                ]);

                            }
                            catch(\Bitrix\Main\DB\SqlQueryException $e) {
                                $this->arResult['ERRORS'][] = [$e->getMessage()];
                            }

                            $this->arResult['RESULT_MESSAGE'] = Loc::getMessage("SOA_SALE_SUCCESS_EDIT");
                        }
                    }
                }
                else {
                    // check isset company
                    if ($issetCompany) {
                        $this->arResult['ERRORS'][] = Loc::getMessage("SOA_SALE_DUBLICATE");
                    } else {
                        //company add
                        if(Option::get(SotbitAuth::idModule, 'CONFIRM_BUYER', 'N', SITE_ID) === 'Y'){
                            $fields['ORDER_FIELDS']['STATUS'] = "M";
                        }
                        else{
                            $fields['ORDER_FIELDS']['STATUS'] = "A";
                        }
                        $addCompanyId = $this->companyObject->addCompany(
                            $fields['ORDER_FIELDS'], $fields['PERSON_TYPE'], $idUser
                        );
                        $fields['MODE'] = 'ADD';

                        if(Option::get(SotbitAuth::idModule, 'CONFIRM_BUYER', 'N', SITE_ID) === 'Y'){
                            $companyConfirm = CompanyConfirmTable::add([
                                'LID' => SITE_ID,
                                'FIELDS' => $fields,
                                'ID_USER' => $idUser,
                                'COMPANY_ID' => $addCompanyId
                            ]);
                            if(!$companyConfirm->isSuccess()){
                                $this->arResult['ERRORS'][] = $companyConfirm->getErrorMessages();
                            }
                            else{
                                $this->arResult["COMPANY_ADD_MODERATE_OK"] = true;
                            }
                        }
                        else{
                            $this->arResult['COMPANY_ADD_OK'] = true;
                        }
                    }
                }
            }
        }
    }


    /**
     * Fill $arResult array for output in template
     * @param int $idPersonType
     * @param Main\HttpRequest $request
     */
    protected function fillResultArray($idPersonType, $request)
    {
        $this->arResult["ORDER_PROPS"] = [];
        $this->arResult["ORDER_PROPS_VALUES"] = [];

        if (!empty($this->companyData['NAME'])) {
            $this->arResult['ADD_COMPANY_NAME'] = $this->companyData['NAME'];
        }

        if ($request->get('NAME')) {
            $this->arResult['NAME'] = $request->get('NAME');
        }
        $rsPersonTypes = \Bitrix\Sale\Internals\PersonTypeTable::getList(
            [
                'filter' => [
                    [
                        'LOGIC' => 'OR',
                        ['LID' => SITE_ID],
                        ['PERSON_TYPE_SITE.SITE_ID' => SITE_ID],
                    ],
                    'ID' => $this->arParams['PERSONAL_TYPES']
                ]
            ]
        );
        while ($personType = $rsPersonTypes->fetch()) {
            $this->arResult['PERSON_TYPES'][$personType['ID']] = $personType['NAME'];
        }
        $personType = Sale\PersonType::load(SITE_ID, $idPersonType);
        $this->arResult["PERSON_TYPE"] = $personType[$idPersonType];
        $this->arResult["PERSON_TYPE"]["NAME"] = htmlspecialcharsbx($this->arResult["PERSON_TYPE"]["NAME"]);

        $locationValue = [];

        if ($this->arParams['COMPATIBLE_LOCATION_MODE'] == 'Y') {
            $locationDb = CSaleLocation::GetList(
                [
                    "SORT" => "ASC",
                    "COUNTRY_NAME_LANG" => "ASC",
                    "CITY_NAME_LANG" => "ASC"
                ],
                [],
                LANGUAGE_ID
            );

            while ($location = $locationDb->Fetch()) {
                $locationValue[] = $location;
            }
        }

        $arrayTmp = [];

        $orderPropertiesListGroup = CSaleOrderPropsGroup::GetList(
            [
                "SORT" => "ASC",
                "NAME" => "ASC"
            ],
            ["PERSON_TYPE_ID" => $idPersonType],
            false,
            false,
            [
                "ID",
                "PERSON_TYPE_ID",
                "NAME",
                "SORT"
            ]
        );

        while ($orderPropertyGroup = $orderPropertiesListGroup->GetNext()) {
            //$arrayTmp[$orderPropertyGroup["ID"]] = $orderPropertyGroup;
            $orderPropertiesList = self::getOrderProps(
                [
                    'filter' => [
                        "PERSON_TYPE_ID" => $idPersonType,
                        "PROPS_GROUP_ID" => $orderPropertyGroup["ID"],
                    ]
                ]
            );

            $this->arResult["ORDER_PROPS"][$orderPropertyGroup["ID"]] = $orderPropertyGroup;

            while ($orderProperty = $orderPropertiesList->fetch()) {

                if(in_array($orderProperty["TYPE"], [
                    "SELECT",
                    "MULTISELECT",
                    "RADIO",
                    "ENUM"
                ])) {
                    $dbVars = CSaleOrderPropsVariant::GetList(($by = "SORT"), ($order = "ASC"), ["ORDER_PROPS_ID" => $orderProperty["ID"]]);
                    while ($vars = $dbVars->GetNext())
                        $orderProperty["VALUES"][] = $vars;
                }


                if ($this->companyId) {
                    $companyProp = $this->companyData['PROPS'][$orderProperty['ID']];
                    if($orderProperty["VALUES"]){
                        $companyProp["VALUES"] = $orderProperty["VALUES"];
                    }
                    if (empty($companyProp)) {
                        $this->arResult["ORDER_PROPS"][$orderPropertyGroup["ID"]]["PROPS"][] = $orderProperty;
                        $this->arResult["ORDER_PROPS_VALUES"]['ORDER_PROP_' . $orderProperty['ID']] = '';
                    } else {
                        $this->arResult["ORDER_PROPS"][$orderPropertyGroup["ID"]]["PROPS"][] = $companyProp;
                        $this->arResult["ORDER_PROPS_VALUES"]['ORDER_PROP_' . $orderProperty['ID']] = $companyProp['VALUE'];
                    }

                } else {
                    $this->arResult["ORDER_PROPS"][$orderPropertyGroup["ID"]]["PROPS"][] = $orderProperty;
                }

                if ($request->get('ORDER_PROP_' . $orderProperty['ID'])) {
                    $this->arResult["ORDER_PROPS_VALUES"]['ORDER_PROP_' . $orderProperty['ID']] = $request->get('ORDER_PROP_' . $orderProperty['ID']);
                }

                if ($request->get('ADD_COMPANY_NAME')) {
                    $this->arResult["ADD_COMPANY_NAME"] = $request->get('ADD_COMPANY_NAME');
                }


            }
        }


    }

    protected function getOrderProps(array $params = array())
    {
        if (!is_array($params['filter'])) {
            $params['filter'] = [];
        }

        if (!is_array($params['order'])) {
            $params['order'] = [];
        }

        Loader::includeModule("sale");

        return \Bitrix\Sale\Internals\OrderPropsTable::getList(
            [
                'filter' => array_merge([
                    "USER_PROPS" => "Y",
                    "ACTIVE" => "Y",
                    "UTIL" => "N"
                ], $params['filter']),
                'select' => (isset($params['select'])) ? $params['select'] : [
                    "ID",
                    "PERSON_TYPE_ID",
                    "NAME",
                    "TYPE",
                    "REQUIRED",
                    "DEFAULT_VALUE",
                    "SORT",
                    "USER_PROPS",
                    "IS_LOCATION",
                    "PROPS_GROUP_ID",
                    "DESCRIPTION",
                    "IS_EMAIL",
                    "IS_PROFILE_NAME",
                    "IS_PAYER",
                    "IS_LOCATION4TAX",
                    "CODE",
                    "SORT",
                    "MULTIPLE",
                    "SETTINGS"
                ],
                'order' => array_merge([
                    "SORT" => "ASC",
                    "NAME" => "ASC"
                ], $params['order'])
            ]
        );
    }

    /**
     * Check value required params of property
     * @param $property
     * @param $currentValue
     * @return bool
     */
    protected function checkProperty($property, $currentValue)
    {
        if ($property["REQUIRED"] == "Y") {
            if ($property["TYPE"] == "LOCATION") {
                if ((int)($currentValue) <= 0) {
                    return false;
                }
            } elseif ($property["TYPE"] == "MULTISELECT") {
                if (!is_array($currentValue) || count(array_filter($currentValue)) <= 0) {
                    return false;
                }
            }
            if ($property["IS_EMAIL"] == "Y") {
                if (strlen(trim($currentValue)) <= 0 || !check_email(trim($currentValue))) {
                    return false;
                }
            } elseif ($property["IS_PROFILE_NAME"] == "Y") {
                if (strlen(trim($currentValue)) <= 0) {
                    return false;
                }
            } elseif ($property["IS_PAYER"] == "Y") {
                if (strlen(trim($currentValue)) <= 0) {
                    return false;
                }
            } else {
                if (strlen($currentValue) <= 0) {
                    return false;
                }
            }
        }

        if($property["SETTINGS"]){

        }

        return true;
    }

    protected function removeSuffix($arr, $suffix)
    {
        foreach ($arr as $k => $v) {
            if (stripos($k, $suffix) !== false) {
                $newKey = str_replace($suffix, '', $k);
                $arr[$newKey] = $v;
                unset($arr[$k]);
            }
        }

        return $arr;
    }

    public function checkFieldsAction()
    {
        $changeConfirm = 'N';
        if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST)) {
            $request = Main\Application::getInstance()->getContext()->getRequest();

            $checkPropsGroup = unserialize(Option::get(SotbitAuth::idModule, "COMPANY_PROPS_GROUP_ID_" . $request->get("PERSON_TYPE"), "",
                SITE_ID));

            if (Loader::includeModule('sotbit.auth') && Option::get(SotbitAuth::idModule, 'CONFIRM_BUYER', 'N',
                    SITE_ID) === 'Y' && $checkPropsGroup) {
                $fields = [
                    'PERSON_TYPE' => (int)$request->get('PERSON_TYPE'),
                    'ADD_COMPANY_NAME' => $request->get('ADD_COMPANY_NAME'),
                    'ORDER_FIELDS' => []
                ];

                $orderPropertiesList = self::getOrderProps([
                    'filter' => ["PERSON_TYPE_ID" => (int)$request->get('PERSON_TYPE')]
                ]);

                while ($orderProperty = $orderPropertiesList->fetch()) {
                    $fields['ORDER_FIELDS'][$orderProperty['CODE']] = $request->get('ORDER_PROP_' . $orderProperty['ID']);
                }

                $companyProps = Company\Company::getCompanyProps($request->get('EDIT_ID'));
                if ($companyProps) {

                    $companyListProps = array_map(
                        function ($v) {
                            return $this->removeSuffix($v, 'COMPANY_PROPERTY_');
                        },
                        $companyProps
                    );
                }

                foreach ($companyListProps as $prop) {
                    if (in_array($prop["PROPS_GROUP_ID"], $checkPropsGroup)) {
                        if ($prop["VALUE"] != $fields["ORDER_FIELDS"][$prop["CODE"]]) {
                            $changeConfirm = "Y";
                        }
                    }
                }
            }
        }

        return $changeConfirm;
    }
}