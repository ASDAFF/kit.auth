<?php

namespace Kit\Auth\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FileTable extends \DataManagerEx_Auth
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'kit_auth_file';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'BUYER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('FILE_ENTITY_BUYER_ID_FIELD'),
			),
			'FILE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('FILE_ENTITY_FILE_ID_FIELD'),
			),
		);
	}
}

?>