<?php

namespace Sotbit\Auth\Internals;

use Bitrix\Main;
use Bitrix\Main\Type;

/**
 *
 * @author Sergey Danilkin < s.danilkin@sotbit.ru >
 */
class MessageTable extends \DataManagerEx_Auth
{
	/**
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'sotbit_auth_message';
	}
	/**
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
				'ID' => array(
						'data_type' => 'integer',
						'primary' => true,
						'autocomplete' => true 
				),
				'EVENT_NAME' => array(
						'data_type' => 'string',
						'required' => true 
				),
				'ID_MESSAGE' => array(
						'data_type' => 'integer',
						'required' => true 
				),
				'LID' => array(
						'data_type' => 'string',
						'required' => true,
						'validation' => array(
								__CLASS__,
								'validateLid' 
						) 
				),
				'DATE_CREATE' => array(
						'data_type' => 'datetime',
						'required' => true,
						'default_value' => new Type\DateTime() 
				),
				'EMAIL_TO' => array(
						'data_type' => 'string',
						'required' => true 
				),
				'ID_USER' => array(
						'data_type' => 'integer',
						'required' => true 
				),
				'HASH' => array(
						'data_type' => 'string',
				),
				'DATE_ENTRANCE' => array(
						'data_type' => 'datetime',
				),
		);
	}
	/**
	 * Returns validators for LID field.
	 * 
	 * @return array
	 */
	public static function validateLid()
	{
		return array(
				new Main\Entity\Validator\Length( null, 2 ) 
		);
	}
	/**
	 * Returns validators for SEND field.
	 * 
	 * @return array
	 */
	public static function validateSend()
	{
		return array(
				new Main\Entity\Validator\Range( 0, 1 ) 
		);
	}

}
?>