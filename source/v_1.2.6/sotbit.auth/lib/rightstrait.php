<?php

namespace Sotbit\Auth;

use Sotbit\Auth\AccessManager;

/**
 * Trait for check access, usage helpers of sotbit.privateoffice.
 */
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
	 * (for sotbit.privateoffice)
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
