<?php

namespace Kit\Auth;

class AccessManager
{
	/** @const string Доступ запрещен ко всему модулю */
	const RIGHT_DENIED_MODULE = 'D';
	/** @const string Чтение всех данных модуля*/
	const RIGHT_READ = 'R';
	/** @const string Полный доступ ко всему модулю */
	const RIGHT_FULL_MODULE = 'W';

	
	public static function hasRights()
	{
		/**
		 * Разрешаем доступ если он явно не запрещен
		 */
		if (static::getModuleRight() != static::RIGHT_DENIED_MODULE)
		{
			return true;
		}

		/**
		 * Без полных прав доступ к модулю запрещен
		 */

		return false;
	}

	/**
	 * Есть ли права на чтение сущности
	 * @return bool
	 *
	 */
	public static function hasReadRights()
	{
		return (static::getModuleRight() >= static::RIGHT_READ);
	}

	/**
	 * Есть ли права на изменение сущности
	 * @return bool
	 *
	 */
	public static function hasWriteRights()
	{
		//return false;
		return (static::getModuleRight() > static::RIGHT_READ);
		
	}

	/**
	 * Есть ли права на удаление сущности
	 * @return bool
	 *
	 */
	public static function hasDeleteRights()
	{
		return false;
		//return static::hasWriteRights();
	}
	
	/**
	* Есть ли права на добавление и редактирование сущности
	* 
	*/
	public static function hasAddRights()
	{
		return static::hasDeleteRights();
	}
	
	/**
	 * Получение буквы уровня доступа
	 * 
	 * @return string Буква доступа
	 */
	public static function getModuleRight()
	{
		global $APPLICATION;

		return $APPLICATION->GetUserRight('kit.privateoffice');
	}
	
	/**
	 * Имеет полные права
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
