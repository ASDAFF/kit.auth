<?php

namespace Kit\Auth\User;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages( __FILE__ );
/**
 * class for work with user
 *
 */
class User extends \KitAuth
{
	/**
	 *
	 * @var string
	 */
	protected $idSite;
	/**
	 *
	 * @var boolean
	 */
	protected $InAdminSection;
	public function __construct($site = '')
	{
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$request = $context->getRequest();
		if($site)
		{
			$this->idSite = $site;
		}
		else
		{
			$this->idSite = $context->getSite();
		}

		$this->InAdminSection = $request->isAdminSection();
		unset( $request );
		unset( $context );
	}
	/**
	 * set login equal email
	 * 
	 * @param string $email        	
	 *
	 */
	public function setLoginEqEmail($email = '')
	{
		if( $this->InAdminSection )
		{
			$this->setLoginEqEmailInAdmin( $email );
		}
		else
		{
			$this->setLoginEqEmailInSite( $email );
		}
		unset( $email );
	}
	/**
	 * set login equal email in admin
	 * 
	 * @param string $email        	
	 */
	private function setLoginEqEmailInAdmin($email = '')
	{
		if( $email && Option::get( self::idModule, 'LOGIN_EQ_EMAIL_IN_ADMIN', '' ) == 'Y' )
		{
			$this->setField( 'LOGIN', $email );
		}
		unset( $email );
	}
	/**
	 * set login equal email in site
	 * 
	 * @param string $email        	
	 */
	private function setLoginEqEmailInSite($email = '')
	{
		if( $email && Option::get( self::idModule, 'LOGIN_EQ_EMAIL', '', $this->idSite ) == 'Y' )
		{
			$this->setField( 'LOGIN', $email );
		}
		unset( $email );
	}
	/**
	 * send password to new user
	 * 
	 * @param string $Site        	
	 */
	public function sendPassword($Site = '')
	{
		$rsSend = \Bitrix\Main\Mail\Event::send( 
				array(
						'EVENT_NAME' => self::SEND_NEW_USER_PASSWORD_EVENT,
						'LID' => $Site,
						'C_FIELDS' => array(
								"LOGIN" => $this->getField( 'LOGIN' ),
								"EMAIL" => $this->getField( 'EMAIL' ),
								"USER_NEW_PASSWORD" => $this->getField( 'PASSWORD' ) 
						) 
				) );
		$idEvent = 0;
		if( $rsSend->isSuccess() )
		{
			$idEvent = $rsSend->getId();
		}
		unset( $Site );
		unset( $rsSend );
		if( $idEvent )
		{
			\CEventLog::Add( 
					array(
							'SEVERITY' => 'WARNING',
							'AUDIT_TYPE_ID' => 'KIT_AUTH_SUCCESS_SEND_PASSWORD_TO_NEW_USER_IN_ADMIN',
							'MODULE_ID' => self::idModule,
							'DESCRIPTION' => Loc::getMessage( 'KIT_AUTH_SUCCESS_SEND_PASSWORD_TO_NEW_USER_IN_ADMIN',
									array(
											'#USER_LOGIN#' => $this->getField( 'LOGIN' ),
											'#ID_EVENT#' => $idEvent 
									) ),
							'ITEM_ID' => self::idModule 
					) );
		}
		else
		{
			\CEventLog::Add( 
					array(
							'SEVERITY' => 'WARNING',
							'AUDIT_TYPE_ID' => 'ERROR_SEND_PASSWORD_TO_NEW_USER_IN_ADMIN',
							'MODULE_ID' => self::idModule,
							'DESCRIPTION' => Loc::getMessage( 'KIT_AUTH_ERROR_SEND_PASSWORD_TO_NEW_USER_IN_ADMIN', array(
									'#USER_LOGIN#' => $this->getField( 'LOGIN' ) 
							) ),
							'ITEM_ID' => self::idModule 
					) );
		}
	}
	/**
	 * find user
	 * 
	 * @param array $select        	
	 * @param array $filter        	
	 */
	public function find($select = array(), $filter = array())
	{
		$User = \Bitrix\Main\UserTable::getList( array(
				'select' => $select,
				'filter' => $filter,
				'limit' => 1 
		) )->fetch();
		unset( $field );
		unset( $search );
		if( $User )
		{
			$this->setFields( $User );
		}
	}
	/**
	 * generate new password
	 * 
	 * @param array $groups        	
	 */
	public function generatePass($groups = array())
	{
		if( in_array( '1', $groups ) )
		{
			$Pass = \randString( '8' ) . '!' . rand( 0, 9 );
		}
		else
		{
			$Pass = \randString( '6' );
		}
		$this->setField( 'PASSWORD', $Pass );
		$this->setField( 'CONFIRM_PASSWORD', $Pass );
	}
	/**
	 * save user from fields
	 */
	public function save()
	{
		$user = new \CUser();
		$user->Update( $this->getField( 'ID' ), $this->getFields() );
		if( !empty( $user->LAST_ERROR ) )
		{
			$this->setError( $user->LAST_ERROR );
		}
	}
	public function getSite()
	{
		return $this->idSite;
	}
	public function getInAdminSection()
	{
		return $this->InAdminSection;
	}
	public function setGroups()
	{
		if(!$this->getField('GROUP_ID'))
		{
			$groups = Option::get('main', 'new_user_registration_def_group','');
			$groups = explode(',',$groups);
			$this->setField('GROUP_ID',$groups); 
		} 
	}
}
?>