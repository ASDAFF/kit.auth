<?php

namespace Kit\Auth\Internals;

use Bitrix\Main\Entity\Validator;

class CompanyPropsValueTable extends \DataManagerEx_Auth
{
    /**
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'kit_auth_company_props_value';
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
            'COMPANY_ID' => [
                'data_type' => 'integer',
            ],
            'PROPERTY_ID' => [
                'data_type' => 'integer',
            ],
            'NAME' => [
                'required' => true,
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'getNameValidators'),
            ],
            'VALUE' => [
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'getValueValidators'),
            ],
            'COMPANY' => [
                'data_type' => 'Kit\Auth\Internals\CompanyTable',
                'reference' => array('=this.COMPANY_ID' => 'ref.ID'),
                'join_type' => 'LEFT',
            ],
            'PROPERTY' => [
                'data_type' => 'Bitrix\Sale\Internals\OrderPropsTable',
                'reference' => array('=this.PROPERTY_ID' => 'ref.ID'),
                'join_type' => 'LEFT',
            ],
        );
    }

    public static function getNameValidators()
    {
        return array(
            new Validator\Length(1, 255),
        );
    }

    public static function getValueValidators()
    {
        return array(
            new Validator\Length(null, 255),
        );
    }
}
?>