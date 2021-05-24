<?php

namespace Kit\Auth\Mail;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages( __FILE__ );
/**
 * class for catch events for user
 *
 */
class EventHandlers extends \KitAuth
{
	/**
	 * handler of catch event ('main', 'OnBeforeUserSendPasswordHandler')
	 * 
	 * @param array $arFields        	
	 */
	public function OnBeforeUserSendPasswordHandler($arFields)
	{
		$return = true;
		if( \Bitrix\Main\Loader::includeModule( "kit.auth" ) )
		{
			$KitAuth = new \KitAuth();
			if( $KitAuth->getDemo() )
			{
				$Mail = new Mail();
				if( $Mail->validateEmail( $arFields['EMAIL'] ) )
				{
					
					$Mail->User()->find( array(
							'ID',
							'LOGIN',
							'EMAIL' 
					), array(
							'EMAIL' => $arFields['EMAIL'] 
					) );
					if( $Mail->User()->getField( 'ID' ) )
					{
						$userGroups = \Bitrix\Main\UserTable::getUserGroupIds( $Mail->User()->getField( 'ID' ) );
						$Mail->User()->generatePass( $userGroups );
						$Mail->User()->save();
						if( !$Mail->User()->getError() )
						{
							\CEventLog::Add( 
									array(
											'SEVERITY' => 'WARNING',
											'AUDIT_TYPE_ID' => 'SUCCESS_CHANGE_PASSWORD',
											'MODULE_ID' => $KitAuth::idModule,
											'DESCRIPTION' => Loc::getMessage( "KIT_AUTH_SUCCESS_CHANGE_PASSWORD",
													array(
															'#USER_ID#' => $Mail->User()->getField( 'ID' ),
															'#USER_LOGIN#' => $Mail->User()->getField( 'LOGIN' ) 
													) ),
											'ITEM_ID' => $KitAuth::idModule
									) );
									$result = \Bitrix\Main\Mail\Event::send( 
											array(
													'EVENT_NAME' => $KitAuth::SEND_FORGOT_NEW_PASSWORD_EVENT,
													'LID' => $Mail->User()->getSite(),
													'C_FIELDS' => array(
															"USER_ID" => $Mail->User()->getField( 'ID' ),
															"LOGIN" => $Mail->User()->getField( 'LOGIN' ),
															"EMAIL" => $Mail->User()->getField( 'EMAIL' ),
															"USER_NEW_PASSWORD" => $Mail->User()->getField( 'PASSWORD' ) 
													) 
											) );
							if( $result->isSuccess() )
							{
								\CEventLog::Add( 
										array(
												'SEVERITY' => 'WARNING',
												'AUDIT_TYPE_ID' => 'SUCCESS_SEND_PASSWORD',
												'MODULE_ID' => $KitAuth::idModule,
												'DESCRIPTION' => Loc::getMessage( "KIT_AUTH_SUCCESS_SEND_PASSWORD",
														array(
																'#USER_ID#' => $Mail->User()->getField( 'ID' ),
																'#USER_LOGIN#' => $Mail->User()->getField( 'LOGIN' ),
																'#ID_EVENT#' => $result->getId() 
														) ),
												'ITEM_ID' => $KitAuth::idModule
										) );
							}
							else
							{
								\CEventLog::Add( 
										array(
												'SEVERITY' => 'WARNING',
												'AUDIT_TYPE_ID' => 'ERROR_SEND_PASSWORD',
												'MODULE_ID' => $KitAuth::idModule,
												'DESCRIPTION' => Loc::getMessage( "KIT_AUTH_ERROR_SEND_PASSWORD",
														array(
																'#USER_ID#' => $Mail->User()->getField( 'ID' ),
																'#USER_LOGIN#' => $Mail->User()->getField( 'LOGIN' ) 
														) ),
												'ITEM_ID' => $KitAuth::idModule
										) );
								$Mail->User()->setError( Loc::getMessage( "KIT_AUTH_ERROR" ) );
							}
						}
					}
					else
					{
						$Mail->User()->setError( Loc::getMessage( "KIT_AUTH_USER_NOT_FOUND" ) );
					}
				}
				else
				{
					$Mail->User()->setError( Loc::getMessage( "KIT_AUTH_ERROR_WRONG_EMAIL" ) );
				}
				if( $Mail->User()->getError() )
				{
					$GLOBALS["APPLICATION"]->ThrowException( $Mail->User()->getError() );
					return false;
				}
				else
				{
					return false;
				}
			}
		}
		return $return;
	}
	/**
	 * handler of catch event ('main', 'OnBeforeUserSendPasswordHandler')
	 * 
	 * @param array $arFields        	
	 */
	public function OnBeforeEventSendHandler($Fields, $Template)
	{
		$return = false;
		if( \Bitrix\Main\Loader::includeModule( "kit.auth" ) )
		{
			$Events = Option::get("kit.auth", 'AUTH_FROM_EMAIL_EVENTS','a:0:{}',$Template['LID']);
			$Events = unserialize($Events);
			if( (Option::get( self::idModule, 'AUTH_FROM_EMAIL', 'Y', $Template['LID'] ) == 'Y' /*|| Option::get( self::idModule, 'AUTH_STATISTICS', 'Y', $Template['LID'] ) == 'Y'*/) && Option::get( self::idModule, 'AUTH_FROM_EMAIL_DOMAIN', '', $Template['LID'] ) && is_array($Events) && in_array($Template['EVENT_NAME'],$Events))
			{
				$Mail = new Mail();
				$Mail->setField( 'SITE_URL', Option::get( self::idModule, 'AUTH_FROM_EMAIL_DOMAIN', '', $Template['LID'] ));
				$Mail->setField( 'LID', $Template['LID'] );
				$Mail->setField( 'EVENT_NAME', $Template['EVENT_NAME'] );
				$Mail->setField( 'MESSAGE', $Template['MESSAGE'] );
				$Mail->setField( 'EMAIL_FROM', $Template['EMAIL_FROM'] );
				$Mail->setField( 'EMAIL_TO', $Template['EMAIL_TO'] );
				$Mail->setField( 'SUBJECT', $Template['SUBJECT'] );
				$Mail->setField( 'FIELDS', $Fields );
				$Mail->setField( 'ID_TEMPLATE', $Template['ID'] );
				$Mail->compileMessage();

				$Mail->replaceLinks();


				
				$files = array();
				foreach($Mail->Message()->getMailAttachment() as $file)
				{
					if($file['ID'] > 0)
					{
						$files[] = $file['ID'];
					}
				}
				unset($file);
				$Mail->setField('FILES',$files);
				//$Mail->addStatistics();
				
				$event = new \Bitrix\Main\Event('kit.auth', 'OnBeforeSendMail', array(
						'MAIL' => $Mail
				));
				$event->send();

				if($Mail->getField('NEW_TEXT'))
				{
					$mailHeaders = $Mail->Message()->getMailHeaders();
					$result = \Bitrix\Main\Mail\Event::send(array(
						"EVENT_NAME" => self::KIT_AUTH_SEND_EVENT,
						"LID" => $Mail->getField('LID'),
						"C_FIELDS" => array(
								'EMAIL_FROM' => $mailHeaders['From'],
								'EMAIL_TO' => $Mail->Message()->getMailTo(),
								'MESSAGE' => $Mail->getField('NEW_TEXT'),
								'SUBJECT' => $Mail->Message()->getMailSubject(),
								'BCC' => $mailHeaders['BCC']
						),
						'FILE' => $Mail->getField('FILES'),
					));
					if (!$result->isSuccess())
					{
						$Mail->setError($result->getErrorMessages());
					}
					unset($mailHeaders, $files);
				}

				if( !$Mail->getError() )
				{
					$return = true;
				}
			}
			unset($Events);
		}
		return $return;
	}
}
?>