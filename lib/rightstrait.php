<?php

namespace Kit\Auth;

use Kit\Auth\AccessManager;

trait RightsTrait
{
	/**
	 * check access
	 * 
	 * @return bool @api
	 */
	protected function hasRights()
	{
		return AccessManager::hasRights();
	}
	
	/**
	 * check read access
	 * 
	 * @return bool @api
	 */
	protected function hasReadRights()
	{
		return AccessManager::hasReadRights();
	}
	
	/**
	 * check edit access
	 * 
	 * @return bool @api
	 */
	protected function hasWriteRights()
	{
		return AccessManager::hasWriteRights();
	}
	
	/**
	 * check add access
	 * (for kit.privateoffice)
	 */
	public static function hasAddRights()
	{
		return AccessManager::hasAddRights();
	}
	
	/**
	 * Check delete access
	 * 
	 * @return bool @api
	 */
	protected function hasDeleteRights()
	{
		return AccessManager::hasDeleteRights();
	}
}
