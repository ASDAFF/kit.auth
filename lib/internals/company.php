<?php

namespace Kit\Auth\Internals;

use Bitrix\Main,
    Bitrix\Main\Entity;
use Kit\Auth\Company\Company;

class CompanyTable extends \DataManagerEx_Auth
{
    const COMPANNY_STATUS_APPROVED = 'A';
    const COMPANNY_STATUS_REJECTED = 'R';
    const COMPANNY_STATUS_MODERATION = 'M';
    /**
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'kit_auth_company';
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
            'NAME' => [
                'data_type' => 'string',
                'required' => true
            ],
            'BUYER_TYPE' => [
                'data_type' => 'integer',
                'required' => true
            ],
            'DATE_CREATE' => [
                'data_type' => 'datetime',
                'default_value' => new Main\Type\DateTime(),
            ],
            'DATE_UPDATE' => [
                'data_type' => 'datetime',
            ],
            'HASH' => [
                'data_type' => 'string',
            ],
            'ACTIVE' => [
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'default_value' => 'Y',
            ],
            'STATUS' => array(
                'data_type' => 'enum',
                'values' => array(
                    self::COMPANNY_STATUS_APPROVED,
                    self::COMPANNY_STATUS_REJECTED,
                    self::COMPANNY_STATUS_MODERATION,
                ),
            ),
        );
    }
}
?>