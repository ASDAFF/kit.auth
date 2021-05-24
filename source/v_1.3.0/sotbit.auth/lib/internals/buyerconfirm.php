<?php

namespace Sotbit\Auth\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Sotbit\Auth\User\WholeSaler;

class BuyerConfirmTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'sotbit_auth_buyer_confirm';
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
			'INN' => [
				'data_type' => 'string',
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

	public static function OnAfterAdd(Main\Entity\Event $event)
	{
		$fields = $event->getParameter('fields');
		Main\Mail\Event::send(
			[
				'EVENT_NAME' => 'SOTBIT_AUTH_CONFIRM_BUYER',
				'LID' => $fields['LID'],
				'C_FIELDS' => array(
					'EMAIL_TO' => Main\Config\Option::get('main','email_from'),
					'EMAIL' => $fields['EMAIL'],
					'ID' => $event->getParameter('id'),
				)
			]
		);
	}

	/**
	 * @param Main\Entity\Event $event
	 * @return Main\Entity\EventResult|void
	 * @throws Main\ObjectException
	 */
	public static function OnBeforeUpdate(Main\Entity\Event $event)
	{
		$result = new Main\Entity\EventResult;
		$fields = $event->getParameter('fields');
		$id = $event->getParameter('id');
		if($fields['STATUS'])
		{
			$row = self::getById($id['ID'])->fetch();

			if($row['FIELDS']['ORDER_FIELDS'])
			{
				$Wholesaler = new WholeSaler($row['LID']);
				$Wholesaler->setPersonCurrentType($row['FIELDS']['PERSON_TYPE']);
				$Wholesaler->setFields($row['FIELDS']);
				$Wholesaler->setField('USER_ID', $row['ID_USER']);
				$Wholesaler->save();
			}
			$result->modifyFields(['DATE_UPDATE' => new Main\Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s')]);
		}
		return $result;
	}
}

?>