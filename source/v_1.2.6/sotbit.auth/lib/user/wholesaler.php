<?php

namespace Sotbit\Auth\User;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type;
use Sotbit\Auth\Internals\FileTable;

/**
 * class for work with worksalers
 *
 * @author Sergey Danilkin < s.danilkin@sotbit.ru >
 *
 */
\CModule::IncludeModule('sale');


class WholeSaler extends User
{
	private $_user;
	private $_group;
	private $_personType;
	private $_personCurrentType;
	private $_emailNotification;
	private $_linkOrderFields;
	const SendWholeSalerNotice = 'SOTBIT_AUTH_WHOLESALER_NOTICE';

	public function __construct($site = '')
	{
		$this->_user = new User($site);
		$this->_group = Option::get(\SotbitAuth::idModule, 'WHOLESALERS_GROUP', '', $this->_user->getSite());
		$this->_personType = unserialize(Option::get(\SotbitAuth::idModule, 'WHOLESALERS_PERSON_TYPE', '', $this->_user->getSite()));
		$this->_emailNotification = Option::get(\SotbitAuth::idModule, 'WHOLESALERS_EMAIL_NOTIFICATION', '', $this->_user->getSite());
		$this->_linkOrderFields = unserialize(Option::get(\SotbitAuth::idModule, 'WHOLESALERS_ORDER_FIELDS', '', $this->_user->getSite()));
		if(!is_array($this->_personType))
		{
			$this->_personType = [];
		}
		if(!is_array($this->_linkOrderFields))
		{
			$this->_linkOrderFields = [];
		}
	}

	private static function getActiveSite()
	{
		$result = \Bitrix\Main\SiteTable::getList([
			'select' => ['LID'],
			'filter' => ['ACTIVE' => 'Y']
		]);

		return $result->fetch();
	}

	public function setPersonCurrentType($type)
	{
		$this->_personCurrentType = $type;

		return $this->_personCurrentType;
	}

	public function getPersonType()
	{
		return $this->_personType;
	}

	public function getGroup()
	{
		return $this->_group;
	}


	public function getInns()
	{
		$inns = [];
		$res = Profile::GetList([], []);

		while ($profile = $res->Fetch())
		{

			$profileId = $profile['ID'];
			$Site = self::getActiveSite();
			$f_PROPS = Profile::GetProfileProps($profileId, $profile['PERSON_TYPE_ID']);
			$code = Option::get(\SotbitAuth::idModule, 'GROUP_ORDER_INN_FIELD_' . $profile['PERSON_TYPE_ID'], '', $Site['LID']);

			$inns = array_merge($inns, $this->getInnByCode($f_PROPS, $code));
			global $DB;
			$prop = $DB->Query("SELECT * FROM `b_sale_bizval` WHERE (`CODE_KEY`='BUYER_PERSON_INN' OR `CODE_KEY`='BUYER_COMPANY_INN') AND `PERSON_TYPE_ID`='" . $profile[PERSON_TYPE_ID] . "'");

			while ($res2 = $prop->fetch())
			{
				$inns[] = $res2['VALUE'];
			}

		}


		return $inns;
	}

	private function getInnByCode(
		$props,
		$code
	)
	{
		$inns[] = [];
		foreach ($props as $prop)
		{
			if($prop['CODE'] == $code) $inns[] = $prop['VALUE'];
		}

		return $inns;
	}

	public function save()
	{
		$this->sendNotice();
		$this->addBuyer();
	}

	public function addBuyer()
	{
		if(\Bitrix\Main\Loader::includeModule("sale") && $this->_personType && $this->getField('USER_ID') > 0)
		{
			$orderFields = $this->getField('ORDER_FIELDS');

			$fields = $this->getFields();
			foreach ($this->_linkOrderFields as $link)
			{
				if($link['ORDER_FIELD'] && $link['USER_FIELD'] && isset($fields[$link['USER_FIELD']]))
				{
					$orderFields[$link['ORDER_FIELD']] = $fields[$link['USER_FIELD']];
				}
			}
			unset($link, $fields);

			if(is_array($orderFields) && sizeof($orderFields) > 0)
			{
				$name = date('Y-m-d');

				$rs = \Bitrix\Sale\Internals\OrderPropsTable::getList([
					'filter' => [
						'ACTIVE' => 'Y',
						'PERSON_TYPE_ID' => $this->_personType,
						'CODE' => array_keys($orderFields)
					],
					'select' => [
						'ID',
						'CODE',
						'PERSON_TYPE_ID',
						'IS_PROFILE_NAME'
					]
				]);
				while ($property = $rs->fetch())
				{
					if($property['IS_PROFILE_NAME'] == 'Y')
					{
						$name = $orderFields[$property['CODE']];
					}
					$arWholeSaler[$property['PERSON_TYPE_ID']][$property['ID']] = $orderFields[$property['CODE']];
				}

				$personType = $this->_personCurrentType;

				$idUserProps = \CSaleOrderUserProps::add(
					[
						'NAME' => $name,
						'USER_ID' => $this->getField('USER_ID'),
						'PERSON_TYPE_ID' => $personType
					]);
				if($idUserProps)
				{
					\CSaleOrderUserProps::DoSaveUserProfile($this->getField('USER_ID'), $idUserProps, $name,
						$personType, $arWholeSaler[$personType],$arErrors);


					$files = $this->getField('FILES');
					if($files)
					{
						foreach($files as $file)
						{
							FileTable::add(['BUYER_ID' => $idUserProps, 'FILE_ID' => $file]);
						}
					}
				}
				unset($idUserProps);

				unset($name, $rs, $property, $arWholeSaler, $personType);
			}
			unset($orderFields);
		}
	}

	public function sendNotice()
	{
		if($this->_emailNotification == 'Y')
		{
			$this->_user->find([
				'ID',
				'NAME',
				'LAST_NAME',
				'EMAIL'
			], [
				'ID' => $this->getField('USER_ID')
			]);
			\Bitrix\Main\Mail\Event::send(
				[
					"EVENT_NAME" => self::SendWholeSalerNotice,
					"LID" => $this->getField('LID'),
					"C_FIELDS" => [
						'EMAIL_TO' => $this->_user->getField('EMAIL'),
						'NAME' => $this->_user->getField('NAME'),
						'LAST_NAME' => $this->_user->getField('LAST_NAME'),
					]
				]);
		}
	}

	public function setEmailNotification($val)
	{
		$this->_emailNotification = $val;
	}
}

?>