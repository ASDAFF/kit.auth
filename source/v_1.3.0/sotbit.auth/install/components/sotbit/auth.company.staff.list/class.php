<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Sotbit\Auth\Internals\StaffTable;
use Sotbit\Auth\Company\Company;
use Sotbit\Auth\Internals\RolesTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CompanyStaffList extends CBitrixComponent implements Controllerable
{

    protected $isAdmin;
    protected $userGroups;
    protected $companyObject;
    protected $companiesWhenUserAdmin;
    protected $select;
    protected $order;

    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'staffList' => [
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


    public function onPrepareComponentParams($params)
    {
        $params["COUNT_STAFF_PAGE"] = ((int)($params["COUNT_STAFF_PAGE"]) <= 0 ? 20 : (int)($params["COUNT_STAFF_PAGE"]));

        return $params;
    }

    private function getUsersGroup ($groupsId){
        $arrayGroup = [];
        foreach ($groupsId as $groupID) {
            $rsGroups = CGroup::GetList($by = "c_sort", $order = "asc", ["ID" => $groupID]);
            if ($resGroups = $rsGroups->Fetch()) {
                $arrayGroup[$groupID] = $resGroups["NAME"];
            }
        }
        return $arrayGroup;
    }

    private function getUsers($filter, $select = [], $order = []){
        $obStaff = StaffTable::getList( [
            'filter' => $filter,
            'select' => $select,
            'order' => $order,
        ] );

        while ($arStaff = $obStaff->fetch()){
            $staffArray[$arStaff["USER_ID"]] = $arStaff;
        }
        return $staffArray;
    }

    private function setSelect()
    {
        $userFields = array_merge($this->arParams["USER_PROPERTY_GENERAL_DATA"],
            $this->arParams["USER_PROPERTY_PERSONAL_DATA"],
            $this->arParams["USER_PROPERTY_WORK_INFORMATION_DATA"],
            $this->arParams["USER_PROPERTY_ADMIN_NOTE_DATA"]);

        foreach ($userFields as $fieldCod) {
            $this->select[$fieldCod] = 'USER.' . $fieldCod;
        }

        $this->select += ["NAME_COMPANY"=>"COMPANY.NAME", "USER_ID", "ROLE", "STATUS", "ID", "COMPANY_ID"];
    }

    private function setOrder()
    {
        if($this->arParams["SORT_BY1"] != 'COMPANY') {
            $this->order = [$this->arParams["SORT_BY1"] => $this->arParams["SORT_ORDER1"]];
        }
        else{
            $this->order = ['NAME_COMPANY' => $this->arParams["SORT_ORDER1"]];
        }
    }

    private function setFilter($filter = array())
    {
        global $USER;
        global ${$this->arParams['FILTER_NAME']};

        if(isset($filter) && !empty($filter)){
            $filterStaff = $filter;
        }
        else{
            if(!$_SESSION["SHOW_ALL_USERS"] || $_SESSION["SHOW_ALL_USERS"] == "N"){
                $filterStaff = ['COMPANY_ID' => $_SESSION['AUTH_COMPANY_CURRENT_ID'], '!USER_ID'=>$USER->getID(), 'STATUS'=>'Y'];
            }
            else{

                $obCompanyByStaff = StaffTable::getList( [
                    'filter' => [
                        'USER_ID' => $USER->getID(),
                        'STATUS' => 'Y'
                    ],
                    'select' => ['COMPANY_ID', 'NAME_COMPANY'=>'COMPANY.NAME'],
                ] );

                while($result = $obCompanyByStaff->fetch()){
                    $company[] = $result["COMPANY_ID"];
                }

                $filterStaff = ['COMPANY_ID' => $company, '!USER_ID'=>$USER->getID(), 'STATUS'=>'Y'];
            }
        }
        if (${$this->arParams['FILTER_NAME']}) {
            $filterStaff = array_merge($filterStaff, ${$this->arParams['FILTER_NAME']});
        }

        return $filterStaff;
    }

    private function setFieldsUser($staff){

            if ($staff["PERSONAL_PHOTO"]) {
                $staff["PERSONAL_PHOTO"] = CFile::GetFileArray($staff["PERSONAL_PHOTO"]);
            }
            $staff["FULL_NAME"] = '';
            if ($staff["NAME"]) {
                $staff["FULL_NAME"] .= $staff["NAME"];
            }
            if ($staff["LAST_NAME"]) {
                $staff["FULL_NAME"] .= ' ' . $staff["LAST_NAME"];
            }
            if ($staff["SECOND_NAME"]) {
                $staff["FULL_NAME"] .= ' ' . $staff["SECOND_NAME"];
            }
            if (!$staff["FULL_NAME"] && $staff["EMAIL"]) {
                $staff["FULL_NAME"] = $staff["EMAIL"];
            }

            if($staff["STATUS"] == "Y"){
                $dbUser = StaffTable::getList( [
                   'filter' => ['USER_ID' => $staff["USER_ID"], 'STATUS' => "Y"],
                    'select' => ["NAME_COMPANY" => "COMPANY.NAME", "ROLE"],
                    'order' => ['NAME_COMPANY' => 'asc']
                ]);
                $staff["COMPANY"] = '<div class="staff-list__companies-list">';
                $staff["WORK_POSITION"] = '<div class="staff-list__roles-list">';
                while($resultUserCompany = $dbUser->fetch()){
                    $staff["COMPANY"] .= '<span>'.$resultUserCompany["NAME_COMPANY"].'</span>';
                    $mainRole = RolesTable::getList([
                        'filter' => ['ID' => $resultUserCompany["ROLE"]],
                        'select' => ["NAME"],
                    ])->fetch();
                    $staff["WORK_POSITION"] .= '<span>'.$mainRole["NAME"].'</span>';
                    $roles[] = $mainRole;

                }
                $staff["COMPANY"] .= '</div>';
                $staff["WORK_POSITION"] .= '</div>';
            }
            else{
                $staff["COMPANY"] .= '<span>' . $staff["NAME_COMPANY"] . '</span><br>';
            }

            if (isset($this->userGroups) && !empty($this->userGroups)) {
                foreach ($this->userGroups as $idgroup => $group) {
                    if (in_array($idgroup, CUser::GetUserGroup($staff["USER_ID"]))) {
                        $staff["USER_SHOW_GROUPS"] .= '<span>' . $group . '</span><br>';
                    }
                }
            }

            if($staff["STATUS"] == "Y" && $this->isAdmin && (!$_SESSION["SHOW_ALL_USERS"] || $_SESSION["SHOW_ALL_USERS"] == "N")){
                $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['actions'][0]["TEXT"] = GetMessage("SOTBIT_AUTH_COMPANY_STAFF_LIST_ACTION_REMOVE");
                $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['actions'][0]["ONCLICK"] = "removeUserCompany(".$staff["ID"].", ".$staff["COMPANY_ID"].")";
                $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['actions'][0]["DEFAULT"] = "1";
            }

            elseif($staff["STATUS"] == "N"){
                $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['actions'][0]["TEXT"] = GetMessage("SOTBIT_AUTH_COMPANY_STAFF_LIST_ACCEPT");
                $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['actions'][0]["ONCLICK"] = "confirmUser(".$staff["ID"].", ".$staff["COMPANY_ID"].")";
                $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['actions'][0]["DEFAULT"] = "1";
                $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['actions'][1]["TEXT"] = GetMessage("SOTBIT_AUTH_COMPANY_STAFF_LIST_UNACCEPT");
                $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['actions'][1]["ONCLICK"] = "unconfirmUser(".$staff["ID"].", ".$staff["COMPANY_ID"].")";
                $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['actions'][1]["DEFAULT"] = "1";
            }

            $personalPhoto = $staff["PERSONAL_PHOTO"]["SRC"] ? $staff["PERSONAL_PHOTO"]["SRC"] : $this->GetPath().'/img/nopic_30x30.gif';
            $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['data']["ID"] = $staff["USER_ID"];
            $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['data']["FULL_NAME"] = '<img class="staff-personal-photo" src="'.$personalPhoto.'" width="38" height="38" border="0"><span>'.$staff["FULL_NAME"].'</span>';
            $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['data']["COMPANY"] = $staff["COMPANY"];
            $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['data']["WORK_POSITION"] = $staff["WORK_POSITION"];
            $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['data']["USER_SHOW_GROUPS"] = $staff["USER_SHOW_GROUPS"];
            $this->arResult["CONFIRM_".$staff["STATUS"]]['ROWS'][$staff["ID"]]['editable'] = true;

    }

    private function setRoleUser()
    {
        global $USER;
        $this->isAdmin = $this->companyObject->isUserAdmin($USER->getID(), $_SESSION['AUTH_COMPANY_CURRENT_ID']);
    }

    private function getCompaniesStaffAdmin()
    {
        global $USER;
        $this->companiesWhenUserAdmin =  $this->companyObject->getCompaniesStaffAdmin($USER->getID());
    }

    private function showStaffList($filter = [], $status)
    {
        $filterStaff = $this->setFilter($filter);
        $rs = new CDBResult;
        $rs->InitFromArray($this->getUsers($filterStaff, $this->select, $this->order));
        $rs->NavStart($this->arParams["COUNT_STAFF_PAGE"]);
        $this->arResult["NAV_STRING_STAFF_".$status] = $rs->GetPageNavString(GetMessage("SOTBIT_AUTH_COMPANY_STAFF_LIST_PAGE_NAV_STRING"));

        while($arStaffProps = $rs->GetNext())
        {
            $this->setFieldsUser($arStaffProps);
        }
    }


    function executeComponent()
    {
        $this->companyObject = new Company(SITE_ID);
        $this->setRoleUser();
        $this->getCompaniesStaffAdmin();
        if (isset($this->arParams["USER_SHOW_GROUPS"]) && $this->arParams["USER_SHOW_GROUPS"][0] != "HIDE") {
            $this->userGroups = $this->getUsersGroup($this->arParams["USER_SHOW_GROUPS"]);
        }
        $this->setSelect();
        $this->setOrder();

        //staff approve list
        $this->showStaffList([], 'A');

        //staff moderate list
        if($this->companiesWhenUserAdmin){
            $this->showStaffList(["COMPANY_ID" => $this->companiesWhenUserAdmin, "STATUS"=>"N", "USER.ACTIVE" => "Y"], 'M');
        }

        $this->arResult["IS_ADMIN"] = $this->isAdmin;
        $this->arResult["ROLES"] = $this->companyObject->getRoles();

        $this->includeComponentTemplate();
    }



    public function removeUserCompanyAction($userTableId, $companyId)
    {
        global $USER;

        if($userTableId && $companyId){
            $company = new Company(SITE_ID);
            if($company->isUserAdmin($USER->getID(), $companyId)){
                if($result = $company->unconfirmStaff($userTableId)){
                    return [
                        'error' => false,
                    ];
                }
                else{
                    return [
                        'error' => true,
                        'errorMessage' => $result
                    ];
                }
            }
            else{
                return [
                    'error' => true,
                    'errorMessage' => GetMessage("SOTBIT_AUTH_COMPANY_CHOOSE_ERROR_MESSAGE_PERMISSION_DENIED")
                ];
            }
        }
        else{
            return [
                'error' => true,
                'errorMessage' => GetMessage("SOTBIT_AUTH_COMPANY_CHOOSE_ERROR_MESSAGE_NO_RELATED_COMPANIES")
            ];
        }
    }

    public function confirmUserAction($userTableId, $companyId)
    {
        global $USER;

        if($userTableId && $companyId){
            $company = new Company(SITE_ID);
            if($company->isUserAdmin($USER->getID(), $companyId)){
                if(!($result = $company->confirmStaff($userTableId))){
                    return [
                        'error' => true,
                        'errorMessage' => $result
                    ];
                }
                else{
                    return [
                        'error' => false,
                    ];
                }
            }
            else{
                return [
                    'error' => true,
                    'errorMessage' => GetMessage("SOTBIT_AUTH_COMPANY_CHOOSE_ERROR_MESSAGE_PERMISSION_DENIED")
                ];
            }
        }
        else{
            return [
                'error' => true,
                'errorMessage' => GetMessage("SOTBIT_AUTH_COMPANY_CHOOSE_ERROR_MESSAGE_NO_RELATED_COMPANIES")
            ];
        }
    }

    public function unconfirmUserAction($userTableId, $companyId)
    {
        global $USER;

        if($userTableId && $companyId){
            $company = new Company(SITE_ID);
            if($company->isUserAdmin($USER->getID(), $companyId)){
                if($result = $company->unconfirmStaff($userTableId)){
                    return [
                        'error' => false,
                    ];
                }
                else{
                    return [
                        'error' => true,
                        'errorMessage' => $result
                    ];
                }
            }
            else{
                return [
                    'error' => true,
                    'errorMessage' => GetMessage("SOTBIT_AUTH_COMPANY_CHOOSE_ERROR_MESSAGE_PERMISSION_DENIED")
                ];
            }
        }
        else{
            return [
                'error' => true,
                'errorMessage' => GetMessage("SOTBIT_AUTH_COMPANY_CHOOSE_ERROR_MESSAGE_NO_RELATED_COMPANIES")
            ];
        }
    }

    public function showAllUsersAction()
    {
        if(!$_SESSION["SHOW_ALL_USERS"] || $_SESSION["SHOW_ALL_USERS"] == "Y"){
            $_SESSION["SHOW_ALL_USERS"] = "N";
        }
        else{
            $_SESSION["SHOW_ALL_USERS"] = "Y";
        }
        return;
    }

}