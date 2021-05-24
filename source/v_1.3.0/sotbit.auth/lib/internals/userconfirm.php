<?php

namespace Sotbit\Auth\Internals;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
//use Bitrix\Seo\Engine\Bitrix;
use Sotbit\Auth\User\WholeSaler;
use Bitrix\Main\Config\Option;
use Sotbit\Auth\Company\Company;


class UserConfirmTable extends \DataManagerEx_Auth
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'sotbit_auth_user_confirm';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            'ID' => [
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('WHOLESALER_ENTITY_ID_FIELD'),
            ],
            'ID_USER' => [
                'data_type' => 'integer',
                'title' => Loc::getMessage('WHOLESALER_ENTITY_ID_USER_FIELD'),
            ],
            'LID' => [
                'data_type' => 'string',
                'required' => true,
            ],
            'EMAIL' => [
                'data_type' => 'string',
                'required' => true,
            ],
            'FIELDS' => [
                'data_type' => 'text',
                'required' => true,
                'serialized' => true
            ],
            'DATE_CREATE' => [
                'data_type' => 'datetime',
                'default_value' => new Main\Type\DateTime(),
            ],
            'DATE_UPDATE' => [
                'data_type' => 'datetime',
            ],
            'STATUS' => [
                'data_type' => 'boolean',
            ],
        ];
    }

    public static function OnAfterAdd(Entity\Event $event)
    {
        $fields = $event->getParameter('fields');
        Main\Mail\Event::send(
            [
                'EVENT_NAME' => 'SOTBIT_AUTH_CONFIRM_REGISTRATION',
                'LID' => $fields['LID'],
                'C_FIELDS' => array(
                    'EMAIL_TO' => Main\Config\Option::get('main','email_from'),
                    'EMAIL' => $fields['EMAIL'],
                    'ID' => $event->getParameter('id'),
                )
            ]
        );
    }

    public static function OnBeforeUpdate(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $fields = $event->getParameter('fields');
        $id = $event->getParameter('id');

        if($fields['STATUS'])
        {
            $row = self::getById($id['ID'])->fetch();
            if(Option::get( \SotbitAuth::idModule, "EXTENDED_VERSION_COMPANIES", "N") == "Y"){
                $user = new \CUser;
                if(!($userUpdate = $user->Update($row["ID_USER"], ["ACTIVE" => "Y"]))){
                    $error = new Entity\EntityError($userUpdate->LAST_ERROR);
                    $result->addError($error);
                }
                else{
                    $rsEvents = GetModuleEvents(\SotbitAuth::idModule, "OnBeforeAdminModerate");
                    while ($arEvent = $rsEvents->Fetch())
                    {
                        ExecuteModuleEvent($arEvent, $row["FIELDS"]);
                    }
                }
                return $result;
            }

            $Wholesaler = new WholeSaler($row['FIELDS']['LID']);
            if($row['FIELDS']['ORDER_FIELDS'])
            {
                if( $Wholesaler->getGroup())
                {
                    $row['FIELDS']["GROUP_ID"][] = $Wholesaler->getGroup();
                }
            }

            $oUser = new \CUser();
            $idUser = $oUser->Add($row['FIELDS']);
            if($idUser > 0)
            {
                $fields['LID'] = $row['FIELDS']['LID'];
                $fields['PERSON_TYPE'] = $row['FIELDS']['PERSON_TYPE'];
                $fields['ORDER_FIELDS'] = $row['FIELDS']['ORDER_FIELDS'];
                $fields['EMAIL'] = $row['EMAIL'];
                $fields['FILES'] = $row['FIELDS']['FILES'];

                $result->modifyFields(
                    [
                        'DATE_UPDATE' => new Main\Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s'),
                        'ID_USER' => $idUser,
                        'FIELDS' => serialize($fields)
                    ]
                );

                $confirmBuyer = Option::get('sotbit.auth', 'CONFIRM_BUYER', 'N', $fields['LID']);
                if($confirmBuyer == 'N') {
                    if($row['FIELDS']['ORDER_FIELDS'])
                    {
                        $Wholesaler->setPersonCurrentType($row['FIELDS']['PERSON_TYPE']);
                        $Wholesaler->setFields($row['FIELDS']);
                        $Wholesaler->setField('USER_ID', $idUser);
                        $Wholesaler->save();
                    }
                } else {
                    BuyerConfirmTable::add([
                        'LID' => $fields['LID'],
                        'FIELDS' => $fields,
                        'EMAIL' => $fields['EMAIL'],
                        'ID_USER' => $idUser,
                        'INN' => $fields['ORDER_FIELDS']['INN']
                    ]);
                }
            }
            else
            {
                $error = new Entity\EntityError($oUser->LAST_ERROR);
                $result->addError($error);
            }
        }
        elseif (!$fields['STATUS']){
            if(Option::get( \SotbitAuth::idModule, "EXTENDED_VERSION_COMPANIES", "N") == "Y"){
                $row = self::getById($id['ID'])->fetch();

                $staffFields = StaffTable::getList([
                    'filter' => ['USER_ID' => $row["ID_USER"]],
                    'select' => ['COMPANY_ID', 'ID']
                ])->fetch();

                $rsEvents = GetModuleEvents(\SotbitAuth::idModule, "OnBeforeAdminRejected");
                while ($arEvent = $rsEvents->Fetch())
                {
                    ExecuteModuleEvent($arEvent, $row["FIELDS"]);
                }

                if($staffFields["ID"]){
                    $company = new Company(SITE_ID);
                    if(!$company->isUserAdmin($row["ID_USER"], $staffFields["COMPANY_ID"])){
                        \CUser::Delete($row["ID_USER"]);
                        StaffTable::delete($staffFields["ID"]);
                    }
                }
            }
        }
        return $result;
    }
}

?>