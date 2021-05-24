<?php
namespace Sotbit\Auth;

use Sotbit\Auth\Helper\Menu;
/**
 * class for catch events
 *
 * @author Sergey Danilkin < s.danilkin@sotbit.ru >
 */
class EventHandlers
{
	/**
	 * catch event ('main', 'OnBeforeUserAdd')
	 *
	 * @param array $arFields
	 */
	public function OnBeforeUserAddHandler(&$arFields)
	{
		$arFields = User\EventHandlers::OnBeforeUserAddHandler( $arFields );
	}
	/**
	 * catch event ('main', 'OnBeforeUserLoginHandler')
	 *
	 * @param array $arFields
	 */
	public function OnBeforeUserLoginHandler(&$arFields)
	{
		$arFields = User\EventHandlers::OnBeforeUserLoginHandler( $arFields );
	}
	/**
	 * catch event ('main', 'OnBeforeUserRegisterHandler')
	 *
	 * @param array $arFields
	 */
	public function OnBeforeUserRegisterHandler(&$arFields)
	{
		$arFields = User\EventHandlers::OnBeforeUserRegisterHandler( $arFields );
	}
	/**
	 * catch event ('main', 'OnBeforeUserUpdateHandler')
	 *
	 * @param array $arFields
	 */
	public function OnBeforeUserUpdateHandler(&$arFields)
	{
		$arFields = User\EventHandlers::OnBeforeUserUpdateHandler( $arFields );
	}
	/**
	 * catch event ('main', 'OnBeforeUserSendPassword')
	 *
	 * @param array $arFields
	 */
	public function OnBeforeUserSendPasswordHandler(&$arFields)
	{
		return Mail\EventHandlers::OnBeforeUserSendPasswordHandler( $arFields );
	}
	/**
	 * catch event ('main', 'OnBeforeEventSend')
	 * @param array $arFields
	 * @param array $arTemplate
	 */
	public function OnBeforeEventSendHandler(&$arFields, &$arTemplate)
	{
		if( \Bitrix\Main\Loader::includeModule( "sotbit.auth" ) && $arTemplate['EVENT_NAME'] != \SotbitAuth::SOTBIT_AUTH_SEND_EVENT)
		{
			$sended = \Sotbit\Auth\Mail\EventHandlers::OnBeforeEventSendHandler( $arFields, $arTemplate);
			if($sended)
			{
				$arFields = array();
				return false;
			}
		}
	}
	/**
	 * catch event ('main', 'OnProlog')
	 */
	public function OnPrologHandler()
	{
		User\EventHandlers::OnPrologHandler();
	}

    public function onBuildGlobalMenuHandler(&$arGlobalMenu, &$arModuleMenu){
        Menu::getAdminMenu($arGlobalMenu, $arModuleMenu);
    }
}
?>