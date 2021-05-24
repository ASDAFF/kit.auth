<?php

namespace Sotbit\Auth;

class AccessManager
{
	/** @const string ������ �������� �� ����� ������ */
	const RIGHT_DENIED_MODULE = 'D';
	/** @const string ������ ���� ������ ������*/
	const RIGHT_READ = 'R';
	/** @const string ������ ������ �� ����� ������ */
	const RIGHT_FULL_MODULE = 'W';

	
	public static function hasRights()
	{
		/**
		 * ��������� ������ ���� �� ���� �� ��������
		 */
		if (static::getModuleRight() != static::RIGHT_DENIED_MODULE)
		{
			return true;
		}

		/**
		 * ��� ������ ���� ������ � ������ ��������
		 */

		return false;
	}

	/**
	 * ���� �� ����� �� ������ ��������
	 * @return bool
	 *
	 */
	public static function hasReadRights()
	{
		return (static::getModuleRight() >= static::RIGHT_READ);
	}

	/**
	 * ���� �� ����� �� ��������� ��������
	 * @return bool
	 *
	 */
	public static function hasWriteRights()
	{
		//return false;
		return (static::getModuleRight() > static::RIGHT_READ);
		
	}

	/**
	 * ���� �� ����� �� �������� ��������
	 * @return bool
	 *
	 */
	public static function hasDeleteRights()
	{
		return false;
		//return static::hasWriteRights();
	}
	
	/**
	* ���� �� ����� �� ���������� � �������������� ��������
	* 
	*/
	public static function hasAddRights()
	{
		return static::hasDeleteRights();
	}
	
	/**
	 * ��������� ����� ������ �������
	 * 
	 * @return string ����� �������
	 */
	public static function getModuleRight()
	{
		global $APPLICATION;

		return $APPLICATION->GetUserRight('sotbit.privateoffice');
	}
	
	/**
	 * ����� ������ �����
	 * @return bool
	 */
	public static function hasFullRights()
	{
		global $USER;

		if(!is_object($USER))
		{
			$USER = new \CUser();
		}

		return $USER->IsAdmin() || (static::getModuleRight() >= static::RIGHT_FULL_MODULE);
	}
}
