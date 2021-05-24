<?php

namespace Sotbit\Auth\Internals;

use Bitrix\Main\Entity;
use Sotbit\Auth\Company\Company;

class StaffTable extends \DataManagerEx_Auth
{
    /**
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'sotbit_auth_staff';
    }
    /**
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => [
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true
            ],
            'USER_ID' => [
                'data_type' => 'string',
                'required' => true
            ],
            'COMPANY_ID' => [
                'data_type' => 'integer',
                'required' => true
            ],
            'ROLE' => [
                'data_type' => 'string',
                'serialized' => true
            ],
            'STATUS' => [
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'default_value' => 'N',
            ],
            'COMPANY' => [
                'data_type' => 'Sotbit\Auth\Internals\CompanyTable',
                'reference' => array('=this.COMPANY_ID' => 'ref.ID'),
                'join_type' => 'LEFT',
            ],
            'USER' => [
                'data_type' => 'Bitrix\Main\UserTable',
                'reference' => array('=this.USER_ID' => 'ref.ID'),
                'join_type' => 'LEFT',
            ],
        );
    }

    public static function OnBeforeUpdate(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $fields = $event->getParameter('fields');
        $id = $event->getParameter('id');

        if($fields['STATUS'] && $fields['STATUS'] == "Y")
        {
            $userFields = self::getList([
                'filter' => ['ID' => $id["ID"]],
                'select' => ['EMAIL' => 'USER.EMAIL', 'SECOND_NAME' => 'COMPANY.NAME']
            ])->fetch();

            $rsEvents = GetModuleEvents(\SotbitAuth::idModule, "OnBeforeStaffUpdate");
            while ($arEvent = $rsEvents->Fetch())
            {
                ExecuteModuleEvent($arEvent, $userFields);
            }
        }

    }

    public static function OnBeforeDelete(Entity\Event $event)
    {
        $id = $event->getParameter('id');
        $fields = self::getList([
            'filter' => ['ID' => $id["ID"]],
            'select' => ['EMAIL' => 'USER.EMAIL', 'SECOND_NAME' => 'COMPANY.NAME', 'STATUS']
        ])->fetch();

        $messageId = $fields["STATUS"] == "Y" ? "OnBeforeStaffRemoved" : "OnBeforeStaffRejected";
        $rsEvents = GetModuleEvents(\SotbitAuth::idModule, $messageId);
        while ($arEvent = $rsEvents->Fetch())
        {
            ExecuteModuleEvent($arEvent, $fields);
        }
    }

    public static function OnBeforeAdd(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $arParams = $event->getParameters();

        $adminRoleId = RolesTable::getList([
            'filter' => ["CODE" => Company::ADMIN_ROLE],
            'select' => ["ID"]
        ])->fetch();

        $fields = self::getList([
            'filter' => ['COMPANY_ID' => $arParams["fields"]["COMPANY_ID"], "%ROLE" => serialize((string)$adminRoleId["ID"])],
            'select' => ['ID', 'SECOND_NAME' => 'COMPANY.NAME', 'EMAIL' => 'USER.EMAIL']
        ])->fetch();

        if(!$fields)
            return $result;


        if($fields && $arParams["fields"]["STATUS"] == "Y"){
            $userFields = \CUser::GetByID($arParams["fields"]["USER_ID"])->fetch();
            $companyFields = CompanyTable::getList([
                'filter' => ["ID" => $arParams["fields"]["COMPANY_ID"]],
                'select' => ["COMPANY" => "NAME"]
            ])->fetch();
            $rsEvents = GetModuleEvents(\SotbitAuth::idModule, "OnBeforeStaffInvite");
            while ($arEvent = $rsEvents->Fetch())
            {
                ExecuteModuleEvent($arEvent, array_merge($userFields,$companyFields));
            }
        }
        elseif ($fields && $arParams["fields"]["STATUS"] == "N"){
            $rsEvents = GetModuleEvents(\SotbitAuth::idModule, "OnBeforeJoinRequest");
            while ($arEvent = $rsEvents->Fetch())
            {
                ExecuteModuleEvent($arEvent, $fields);
            }
        }
        return $result;
    }
}
?>