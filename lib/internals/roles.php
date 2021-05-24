<?php

namespace Kit\Auth\Internals;

use Bitrix\Main\ORM\Fields,
    Bitrix\Main\Localization\Loc;

class RolesTable extends \DataManagerEx_Auth
{
    /**
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'kit_auth_roles';
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
            'CODE' => [
                'data_type' => 'string',
                'required' => true,
                'validation' => function() {
                    return array(
                        new Fields\Validators\UniqueValidator(Loc::getMessage("KIT_AUTH_DUPLICATE_FIELD_ROLE")),
                    );
                }
            ],
            'NAME' => [
                'data_type' => 'string',
                'required' => true
            ],
        );
    }
}
?>