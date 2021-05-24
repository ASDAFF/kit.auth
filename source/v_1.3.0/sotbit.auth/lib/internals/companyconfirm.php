<?php

namespace Sotbit\Auth\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Sotbit\Auth\User\WholeSaler;

class CompanyConfirmTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'sotbit_auth_company_confirm';
	}

	/**
	 * @return array
	 * @throws Main\ObjectException
	 */
	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'ID_USER' => [
				'data_type' => 'integer',
			],
			'LID' => [
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
			'STATUS' => [
				'data_type' => 'boolean',
			],
            'COMPANY_ID' => [
                'data_type' => 'integer',
            ],
            'COMPANY' => [
                'data_type' => 'Sotbit\Auth\Internals\CompanyTable',
                'reference' => array('=this.COMPANY_ID' => 'ref.ID'),
                'join_type' => 'LEFT',
            ],
		];
	}
}

?>