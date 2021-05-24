<?php

namespace Kit\Auth\Company;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Application,
    Bitrix\Main\Entity\Base,
    Bitrix\Sale\Internals\OrderPropsTable,
    Kit\Auth\Internals\CompanyTable,
    Kit\Auth\Internals\CompanyConfirmTable,
    Kit\Auth\Internals\CompanyPropsValueTable,
    Kit\Auth\Internals\StaffTable,
    Kit\Auth\Internals\RolesTable;
use function PHPSTORM_META\elementType;


Loc::loadMessages( __FILE__ );
/**
 * class for work with company
 *
 */
class Company extends \KitAuth
{
    const MODULE_ID = 'kit.auth';
    const ADMIN_ROLE = 'ADMIN';

    protected $idSite;
    protected $InAdminSection;


    public function __construct($site = '')
    {
        $context = \Bitrix\Main\Application::getInstance()->getContext();
        $request = $context->getRequest();
        if ($site) {
            $this->idSite = $site;
        } else {
            $this->idSite = $context->getSite();
        }

        $this->InAdminSection = $request->isAdminSection();
        unset($request);
        unset($context);
    }

    public function getSite()
    {
        return $this->idSite;
    }

    public function getPersonType()
    {
        if(isset($_SESSION["AUTH_COMPANY_CURRENT_ID"])){
            $dbCompany = CompanyTable::getList([
                'filter' => ['ID' => $_SESSION["AUTH_COMPANY_CURRENT_ID"]],
                'select' => ['BUYER_TYPE']
            ]);

            if($result = $dbCompany->fetch()){
                return $result['BUYER_TYPE'];
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    /**
     * add company with company property
     *
     * @param array $companyFields
     * @param int $personType
     * @param int $userID
     */
    public function addCompany($companyFields, $personType, $userID = '')
    {

        if(is_array($companyFields) && !empty($companyFields)){

            $companyName = $companyFields[Option::get(\KitAuth::idModule, 'COMPANY_PROPS_NAME_FIELD_' . $personType, '', $this->getSite())];

            $resultAddCompany = CompanyTable::add(array(
                'NAME' => $companyName,
                'BUYER_TYPE' => $personType,
                'HASH' => $this->getHash($companyFields, $personType),
                'STATUS' => $companyFields["STATUS"],
            ));

            if ($resultAddCompany->isSuccess())
            {
                $companyID = $resultAddCompany->getId();
                $resultAddProps = $this->addCompanyPropsValue($companyID, $companyFields, $personType);
                if(!empty($userID)){
                    $resultAddStaff = $this->addStaff ($companyID, $userID, 'ADMIN', 'Y');
                }
                if(!empty($companyFields["FILES"])){
                    $this->addFile($companyID, $companyFields["FILES"]);
                }

                $rsEvents = GetModuleEvents(\KitAuth::idModule, "OnAfterAddCompany");
                while ($arEvent = $rsEvents->Fetch())
                {
                    ExecuteModuleEvent($arEvent, $companyFields, $companyID);
                }

                return $companyID;
            }
            $errors = $resultAddCompany->getErrorMessages();
            return $errors;
        }
        else{
            return false;
        }
    }

    /**
     * add company property
     *
     * @param int $companyID
     * @param array $companyFields
     * @param int $personType
     */
    public function addCompanyPropsValue($companyID, $companyFields, $personType)
    {

        if(Loader::includeModule("sale")){
            $obOrderProps = OrderPropsTable::getList( array(
                'filter' => array(
                    'CODE' => array_keys($companyFields),
                    'PERSON_TYPE_ID' => $personType
                ),
                'select' => array('ID','CODE','NAME')
            ) );
            while($orderProp = $obOrderProps->fetch()){
                if(is_array($value = $companyFields[$orderProp["CODE"]])){
                    $value = implode(",", $value);
                }
                $result = CompanyPropsValueTable::add(array(
                    'COMPANY_ID' => $companyID,
                    'PROPERTY_ID' => $orderProp["ID"],
                    'NAME' => $orderProp["NAME"],
                    'VALUE' => $value,
                ));
            }
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * add staff with reference to the company
     *
     * @param int $companyID
     * @param int $userID
     * @param string|array $userRole
     * @param string $userStatus
     */
    public function addStaff($companyID, $userID, $userRole = 'STAFF', $userStatus = 'N')
    {
        $staffRole = $this->getStaffRole($userRole);

        if($staffRole) {
            $result = StaffTable::add(array(
                'USER_ID' => $userID,
                'COMPANY_ID' => $companyID,
                'ROLE' => $staffRole,
                'STATUS' => $userStatus,
            ));

            if ($result->isSuccess()) {
                $id = $result->getId();
                return $id;
            } else {
                $errors = $result->getErrorMessages();
                return $errors;
            }
        }
        else{
            return false;
        }
    }

    /**
     * get id role
     *
     * @param string|array $role
     */
    public function getStaffRole($role)
    {
        $roleID = [];
        $obRoles = RolesTable::getList( array(
            'filter' => array(
                'CODE' => $role
            ),
            'select' => array('ID')
        ) );
        while($staffRole = $obRoles->fetch()){
            $roleID[] = $staffRole["ID"];
        }

        if(!empty($roleID)){
            return $roleID;
        }
        else{
            return false;
        }
    }

    /**
     * add file with reference to the company
     *
     * @param int $companyID
     * @param int $fileID
     */
    public function addFile ($companyID, $fileID)
    {
        if(is_array($fileID) && !empty($fileID)){
            foreach ($fileID as $file){
                FileTable::add(array(
                    'BUYER_ID' => $companyID,
                    'FILE_ID' => $file,
                ));
            }
        }
        elseif (is_numeric($fileID)){
            FileTable::add(array(
                'BUYER_ID' => $companyID,
                'FILE_ID' => $fileID,
            ));
        }
    }

    public function getHash($companyFields, $personType) {
        $validatedFields = unserialize(Option::get(\KitAuth::idModule, 'GROUP_FIELDS_FOR_CHECK_'.$personType, '', $this->getSite()));

        if(is_array($validatedFields) && !empty($validatedFields)) {
            $hash = "";

            if (is_array($validatedFields) && !empty($validatedFields)) {
                foreach ($companyFields as $code => $field) {
                    if (in_array($code, $validatedFields)) {
                        $hash .= $field;
                    }
                }
                $hash = md5($hash);
            }

            return $hash;
        }
        else{
            return false;
        }
    }

    public function checkExistCompany($companyFields, $personType)
    {
        $hash = $this->getHash($companyFields, $personType);
        if($hash){
            $company = CompanyTable::getList([
                'filter' => ["HASH" => $hash],
                'select' => ["ID"]
            ])->fetch();

            if($company){
                return $company;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }


    public function getRoles($filter = [], $select = null, $order = [])
    {
        $result = RolesTable::getList( array(
            'filter' => $filter,
            'select' => $select??['*'],
            'order' => $order
        ) )->fetchAll();

        return $result;
    }

    public function confirmCompany($id)
    {
        $canModerate = true;

        $confirmCompany = CompanyConfirmTable::getList(
            [
                'filter' => ["ID" => $id],
                'select' => ["*", "HASH"=>"COMPANY.HASH"]
            ]
        )->fetch();

        $rsEvents = GetModuleEvents(\KitAuth::idModule, "OnBeforeCompanyModerate");
        while ($arEvent = $rsEvents->Fetch())
        {
            if (ExecuteModuleEvent($arEvent, $confirmCompany, $id) === false)
            {
                $canModerate = false;
                break;
            }
        }

        if($canModerate){
            $rs = CompanyConfirmTable::update($id, [
                'STATUS' => true
            ]);
            if(!$rs->isSuccess())
            {
                return [
                    'error' => true,
                    'message' => $rs->getErrorMessages()
                ];
            }
            else{
                $orderFields = $confirmCompany["FIELDS"]["ORDER_FIELDS"];
                if(is_array($orderFields) && sizeof($orderFields) > 0) {

                    $dbCompanyProp = CompanyPropsValueTable::getList([
                        'filter' => ["COMPANY_ID" => $confirmCompany["COMPANY_ID"]],
                        'select' => ["ID", "PROPERTY_ID"]
                    ]);

                    while($property = $dbCompanyProp->fetch()){
                       $arCompanyProps[$property["PROPERTY_ID"]] = $property;
                    }

                    $rs = \Bitrix\Sale\Internals\OrderPropsTable::getList([
                        'filter' => [
                            'ACTIVE' => 'Y',
                            'PERSON_TYPE_ID' => $confirmCompany["FIELDS"]["PERSON_TYPE"],
                            'CODE' => array_keys($orderFields)
                        ],
                        'select' => [
                            'ID',
                            'CODE',
                            'PERSON_TYPE_ID',
                            'NAME',
                        ]
                    ]);
                    while ($property = $rs->fetch()) {
                        $arNewCompanyProps[$property['PERSON_TYPE_ID']][$property['ID']] = $orderFields[$property['CODE']];
                        if(is_array($value = $orderFields[$property['CODE']])){
                            $value = implode(",", $value);
                        }
                        if(isset($arCompanyProps[$property['ID']])){
                            CompanyPropsValueTable::update(
                                $arCompanyProps[$property['ID']]["ID"],
                                ["VALUE" => $value]);
                        }
                        else{
                            CompanyPropsValueTable::add(
                                [
                                    'COMPANY_ID'  => $confirmCompany["COMPANY_ID"],
                                    'PROPERTY_ID' => $property['ID'],
                                    'NAME'        => $property['NAME'],
                                    'VALUE'       => $value,
                                ]
                            );
                        }
                    }

                }

                $rs = CompanyTable::update($confirmCompany["COMPANY_ID"], [
                    'HASH' => $confirmCompany["FIELDS"]["HASH"] ?: $confirmCompany["HASH"],
                    'STATUS' => "A",
                    'ACTIVE' => "Y",
                    'DATE_UPDATE' => new \Bitrix\Main\Type\DateTime(),
                ]);

                if(!$rs->isSuccess())
                {
                    return [
                        'error' => true,
                        'message' => $rs->getErrorMessages()
                    ];
                }

                $rsEvents = GetModuleEvents(\KitAuth::idModule, "OnAfterCompanyModerate");
                while ($arEvent = $rsEvents->Fetch())
                {
                    ExecuteModuleEvent($arEvent, $confirmCompany["FIELDS"]["ORDER_FIELDS"], $confirmCompany["COMPANY_ID"]);
                }
            }
        }
    }

    public function unconfirmCompany($id)
    {
        $confirmCompany = CompanyConfirmTable::getList(
            [
                'filter' => ["ID" => $id],
                'select' => ["COMPANY_ID", "ACTIVE"=>"COMPANY.ACTIVE", "FIELDS"]
            ]
        )->fetch();

        if($confirmCompany["ACTIVE"] == "Y"){
            $canRejected = true;
            $rsEvents = GetModuleEvents(\KitAuth::idModule, "OnBeforeCompanyRejection");
            while ($arEvent = $rsEvents->Fetch())
            {
                if (ExecuteModuleEvent($arEvent, $confirmCompany["FIELDS"]["ORDER_FIELDS"], $confirmCompany["COMPANY_ID"]) === false)
                {
                    $canRejected = false;
                    break;
                }
            }

            if($canRejected){
                CompanyTable::update($confirmCompany["COMPANY_ID"], [
                    'STATUS' => "R",
                    'ACTIVE' => "Y",
                    "DATE_UPDATE" => new \Bitrix\Main\Type\DateTime(),
                ]);
            }
        }
        else{
            $canChangeRejected = true;
            $rsEvents = GetModuleEvents(\KitAuth::idModule, "OnBeforeCompanyChangesRejection");
            while ($arEvent = $rsEvents->Fetch())
            {
                if (ExecuteModuleEvent($arEvent, $confirmCompany["FIELDS"]["ORDER_FIELDS"], $confirmCompany["COMPANY_ID"]) === false)
                {
                    $canChangeRejected = false;
                    break;
                }
            }

            if($canChangeRejected) {
                CompanyTable::update($confirmCompany["COMPANY_ID"], [
                    'STATUS' => "A",
                    'ACTIVE' => "Y",
                    "DATE_UPDATE" => new \Bitrix\Main\Type\DateTime(),
                ]);
            }
        }
    }

    public function getCompaniesByUserID ($userID, $order = [])
    {
        $obStaff = StaffTable::getList( [
            'filter' => [
                'USER_ID' => $userID,
                'STATUS' => 'Y',
                'COMPANY_STATUS' => 'A'
            ],
            'select' => ['ID_COMPANY'=>'COMPANY.ID', 'COMPANY_NAME'=>'COMPANY.NAME', 'COMPANY_ACTIVE'=>'COMPANY.ACTIVE', 'COMPANY_STATUS'=>'COMPANY.STATUS', '*'],
            'order' => $order
        ] );

        while($result = $obStaff->fetch()){
            $company[$result["ID_COMPANY"]] = $result;
        }

        if(isset($company) && !empty($company)){
            return $company;
        }
        else{
            return false;
        }
    }


    public function isUserAdmin($userId, $companyId)
    {
        $isAdmin = false;
        $staff = StaffTable::getList( [
            'filter' => [
                'USER_ID' => $userId,
                'COMPANY_ID' => $companyId
            ],
            'select' => ['ROLE'],
        ] )->fetch();

        $dbRoles = RolesTable::getList([
            'filter' => [
                'ID' => $staff['ROLE'],
            ],
            'select' => ['*'],
        ]);

        while($role = $dbRoles->fetch()){
            if($role["CODE"] == self::ADMIN_ROLE){
                $isAdmin = true;
            }
        }

        return $isAdmin;
    }

    public function getCompaniesStaffAdmin($userId)
    {
        $adminRoleId = RolesTable::getList([
            'filter' => ["CODE" => self::ADMIN_ROLE],
            'select' => ["ID"]
        ])->fetch();

        $dbResult = StaffTable::getList([
            'filter' => ["USER_ID" => $userId, "%ROLE" => serialize((string)$adminRoleId["ID"])],
            'select' => ["COMPANY_ID", "ID"]
        ]);

        while($result = $dbResult->fetch()){
            $companies[] = $result["COMPANY_ID"];
        }

        return $companies ?: false;
    }

    public function confirmStaff($userTableId)
    {
        $result = StaffTable::update($userTableId, ["STATUS" => "Y"]);

        if(!$result->isSuccess())
        {
            return $result->getErrorMessages();
        }
        else{
            return true;
        }
    }


    public function unconfirmStaff($userTableId)
    {
        $result = StaffTable::delete($userTableId);

        if(!$result->isSuccess())
        {
            return $result->getErrorMessages();
        }
        else{
            return true;
        }
    }


    public  function getCompaniesAllByUserID ($userID, $order = [])
    {
        $obStaff = StaffTable::getList( [
            'filter' => [
                'USER_ID' => $userID,
                'STATUS' => 'Y',
            ],
            'select' => ['RELATED_COMPANY_'=>'COMPANY'],
            'order' => $order
        ] );

        while($result = $obStaff->fetch()){
            $company[$result["RELATED_COMPANY_ID"]] = $result;
        }

        if(isset($company) && !empty($company)){
            return $company;
        }
        else{
            return false;
        }
    }

    public function getCompanyProps($idCompany, $select = null)
    {
        $result = [];
        $q = CompanyPropsValueTable::getList(
            [
                'select' => $select ?? ['*', 'COMPANY_PROPERTY_'=>'PROPERTY'],
                'filter' => ['COMPANY_ID' => $idCompany],
            ]
        );
        while($company = $q->fetch()) {
            $result[$company['PROPERTY_ID']] = $company;
        }

        return $result;
    }

    public function getCompanyOrders()
    {
        if(!$_SESSION["AUTH_COMPANY_CURRENT_ID"]){
            return false;
        }

        $uniquePropsCode = unserialize(Option::get(\KitAuth::idModule, "GROUP_FIELDS_FOR_CHECK_2", "", $this->getSite()));

        $dbProps = CompanyPropsValueTable::getList(
            [
                'select' => ['VALUE', 'CODE'=>'PROPERTY.CODE', "ID", "PROPERTY_ID"],
                'filter' => ['CODE' => $uniquePropsCode, 'COMPANY_ID' => $_SESSION["AUTH_COMPANY_CURRENT_ID"]],
            ]
        );
        while($arProps = $dbProps->fetch()) {
           $propsValue[]  = $arProps["VALUE"];
        }

        $dbOrderProps = \CSaleOrderPropsValue::GetList(
            [],
            ["CODE"=>$uniquePropsCode, "@VALUE"=>$propsValue],
        false,
            false,
            ['ORDER_ID']
        );

        while ($arOrderProps = $dbOrderProps->Fetch())
        {
           $ordersId[] = $arOrderProps['ORDER_ID'];
        }

        return array_unique($ordersId);
    }

    public function checkOrder($orderId)
    {
        if(in_array($orderId, $this->getCompanyOrders())){
            return true;
        }
        else{
            return false;
        }
    }
}
