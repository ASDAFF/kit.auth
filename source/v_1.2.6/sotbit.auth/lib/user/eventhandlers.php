<?php

namespace Sotbit\Auth\User;

use Bitrix\Main\Config\Option;

/**
 * class for catch events for user
 *
 * @author Sergey Danilkin < s.danilkin@sotbit.ru >
 */
class EventHandlers extends \SotbitAuth
{
	/**
	 * handler of catch event ('main', 'OnBeforeUserAdd')
	 *
	 * @param array $arFields
	 */
	public function OnBeforeUserAddHandler($arFields)
	{
		$return = $arFields;
		if(\Bitrix\Main\Loader::includeModule("sotbit.auth"))
		{
			$SotbitAuth = new \SotbitAuth();
			if($SotbitAuth->getDemo())
			{
				if(!$arFields['GROUPS'])
				{
					$arFields['GROUPS'] = [];
				}
				$User = new User();
				$User->setField('LOGIN', $arFields['LOGIN']);
				$User->setGroups();
				$User->setField('GROUP_ID', $arFields['GROUP_ID']);
				$User->setLoginEqEmail($arFields['EMAIL']);
				$return['LOGIN'] = $User->getField('LOGIN');
				$User->setGroups();
				$return['GROUP_ID'] = $User->getField('GROUP_ID');
				$Site = \Bitrix\Main\SiteTable::getList([
					'select' => [
						'LID'
					],
					'filter' => [
					],
					'limit' => 1
				])->fetch();
				$User->setField('EMAIL', $arFields['EMAIL']);
				$User->setField('PASSWORD', $arFields['PASSWORD']);
				if($User->getInAdminSection())
				{
					$User->sendPassword($Site['LID']);
				}
				unset($User);
			}
			unset($SotbitAuth);
		}

		return $return;
	}

	/**
	 * handler of catch event ('main', 'OnBeforeUserLoginHandler')
	 *
	 * @param array $arFields
	 */
	public function OnBeforeUserLoginHandler($arFields)
	{
		$return = $arFields;
		if(\Bitrix\Main\Loader::includeModule("sotbit.auth"))
		{
			$SotbitAuth = new \SotbitAuth();
			if($SotbitAuth->getDemo())
			{
				$User = new User();
				$User->find([
					'LOGIN'
				], [
					'LOGIN' => $arFields['LOGIN']
				]);
				if(!$User->getField('LOGIN') && Option::get(self::idModule, 'AUTH_BY_EMAIL', 'Y', $User->getSite()) == 'Y')
				{
					$User->find([
						'LOGIN'
					], [
						'EMAIL' => $arFields['LOGIN']
					]);
				}
				if(!$User->getField('LOGIN'))
				{
					$fields = Option::get(self::idModule, 'AUTH_BY_FIELD', 'a:0:{}', $User->getSite());
					$fields = unserialize($fields);
					if($fields && is_array($fields))
					{
						foreach ($fields as $field)
						{
							$User->find([
								'LOGIN'
							], [
								$field => $arFields['LOGIN']
							]);
							if($User->getField('LOGIN'))
							{
								break;
							}
						}
						unset($field);
					}
					unset($fields);
				}
				if($User->getField('LOGIN'))
				{
					$return['LOGIN'] = $User->getField('LOGIN');
				}
			}
			unset($SotbitAuth);
		}

		return $return;
	}

	/**
	 * handler of catch event ('main', 'OnBeforeUserRegisterHandler')
	 *
	 * @param array $return
	 */
	public function OnBeforeUserRegisterHandler($arFields)
	{
		$return = $arFields;
		if(\Bitrix\Main\Loader::includeModule("sotbit.auth"))
		{
			$SotbitAuth = new \SotbitAuth();
			if($SotbitAuth->getDemo())
			{
				$User = new User();
				$User->setField('LOGIN', $arFields['LOGIN']);
				$User->setLoginEqEmail($arFields['EMAIL']);
				$return['LOGIN'] = $User->getField('LOGIN');
                if($arFields['USER_GROUP'] > 0)
                { 
                    array_push($return['GROUP_ID'],$arFields['USER_GROUP']);
                }
			}
		}

		return $return;
	}

	/**
	 * handler of catch event ('main', 'OnBeforeUserUpdateHandler')
	 *
	 * @param array $return
	 */
	public function OnBeforeUserUpdateHandler(&$arFields)
	{
		$return = $arFields;
		if(\Bitrix\Main\Loader::includeModule("sotbit.auth"))
		{
			$SotbitAuth = new \SotbitAuth();
			if($SotbitAuth->getDemo())
			{
				$User = new User();
				$User->setField('LOGIN', $arFields['LOGIN']);
				$User->setLoginEqEmail($arFields['EMAIL']);
				$return['LOGIN'] = $User->getField('LOGIN');
			}
		}

		return $return;
	}

	/**
	 * handler of catch event ('main', 'OnPrologHandler')
	 */
	public function OnPrologHandler()
	{
		global $USER;
		if(\Bitrix\Main\Loader::includeModule("sotbit.auth"))
		{
			$SotbitAuth = new \SotbitAuth();
			if($SotbitAuth->getDemo())
			{
				$context = \Bitrix\Main\Application::getInstance()->getContext();
				$request = $context->getRequest();
				$hash = $request->get("SOTBIT_AUTH_HASH");
				$idMessage = $request->get("SOTBIT_AUTH_MESSAGE");
				if($hash && $idMessage > 0)
				{
					$Message = \Sotbit\Auth\Internals\MessageTable::getList(
						[
							'select' => [
								'ID',
								'ID_USER'
							],
							'filter' => [
								'HASH' => $hash,
								'ID' => $idMessage
							],
							'limit' => 1
						])->fetch();
					$auth = false;
					$groups = unserialize(Option::get(self::idModule, 'AUTH_WHOLESALERS_GROUP', 'Y', SITE_ID));
					$user_groups = \CUser::GetUserGroup($Message['ID_USER']);
					if(count($groups) > 0 && !in_array(1,$user_groups) && count(array_intersect($groups, $user_groups)) > 0)
					{
						$auth = true;
					}
					if(count($groups) == 0)
					{
						$auth = true;
					}
					if($Message['ID'] > 0 && $Message['ID_USER'] > 0 && $auth)
					{
						$data = [];
						$mes = \Sotbit\Auth\Internals\StatisticsTable::getList(['filter' => ['ID_MESSAGE' => $Message['ID']]])->fetch();
						$ar = json_decode($mes['MESSAGE_TRANSITION']);

						$ar[] = $_SERVER['SCRIPT_URI'];
						$data['MESSAGE_TRANSITION'] = json_encode($ar);
						if($mes['IP'] == '') $data['IP'] = $_SERVER['REMOTE_ADDR'];
						if($mes['LOCATION'] == '')
						{
							if(\CModule::includeModule('statistic'))
							{
								$cityObj = new \CCity();
								$arThisCity = $cityObj->GetFullInfo();
								$data['LOCATION'] = $arThisCity['CITY_NAME']['VALUE'] == '' ? $arThisCity['REGION_NAME']['VALUE'] == "" ? $arThisCity['COUNTRY_NAME']['VALUE'] : $arThisCity['REGION_NAME']['VALUE'] : $arThisCity['CITY_NAME']['VALUE'];
							}
							else
							{
								if(isset($_SERVER['GEOIP_COUNTRY_NAME']))
								{
									$data['LOCATION'] = $_SERVER['GEOIP_COUNTRY_NAME'];
								}
							}
						}
						if($mes['DEVICE'] == '')
						{
							$data['DEVICE'] = $_SERVER['HTTP_USER_AGENT'];
						}
						\Sotbit\Auth\Internals\StatisticsTable::update($mes['ID'], $data);
						if(!$USER->IsAuthorized())
						{
							$USER->Authorize($Message['ID_USER']);
							\Sotbit\Auth\Internals\MessageTable::update(
								$Message['ID'],
								[
									'DATE_ENTRANCE' => new \Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s')
								]);
						}
					}
				}
			}
		}
	}
}

?>