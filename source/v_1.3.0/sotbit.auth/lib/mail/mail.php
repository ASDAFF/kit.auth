<?php

namespace Sotbit\Auth\Mail;

use \Bitrix\Main\Mail\EventMessageCompiler;
use \Bitrix\Main\Mail\Internal\EventMessageTable;
use \Bitrix\Main\Mail\Internal\EventMessageAttachmentTable;
use \Bitrix\Main\Config\Option;

/**
 * class for work with mails
 * 
 * @author Sergey Danilkin < s.danilkin@sotbit.ru >
 */
class Mail extends \SotbitAuth
{
	/**
	 *
	 * @var object
	 */
	private $message;
	private $user;
	public function __construct()
	{
		$this->user = new \Sotbit\Auth\User\User();
	}
	public function compileMessage()
	{
		$filter = array();
		$charset = false;
		$serverName = null;
		$arSites = explode( ",", $this->getField( 'LID' ) );
		$siteDb = \Bitrix\Main\SiteTable::getList( 
				array(
						'select' => array(
								'SERVER_NAME',
								'CULTURE_CHARSET' => 'CULTURE.CHARSET' 
						),
						'filter' => array(
								'LID' => $arSites 
						) 
				) );
		if( $arSiteDb = $siteDb->fetch() )
		{
			$charset = $arSiteDb['CULTURE_CHARSET'];
			$serverName = $arSiteDb['SERVER_NAME'];
		}
		unset( $siteDb );
		unset( $arSiteDb );
		if( $this->getField( 'ID_TEMPLATE' ) > 0 )
		{
			$eventMessageDb = \Bitrix\Main\Mail\Internal\EventMessageTable::getById( $this->getField( 'ID_TEMPLATE' ) );
			if( $eventMessageDb->fetch() )
			{
				$filter['ID'] = $this->getField( 'ID_TEMPLATE' );
				$filter['ACTIVE'] = 'Y';
			}
		}
		if( !$filter )
		{
			$filter = array(
					'ACTIVE' => 'Y',
					'EVENT_NAME' => $this->getField( 'EVENT_NAME' ),
					'EVENT_MESSAGE_SITE.SITE_ID' => $arSites 
			);
		}
		
		$messageDb = EventMessageTable::getList( array(
				'select' => array(
						'ID' 
				),
				'filter' => $filter,
				'group' => array(
						'ID' 
				) 
		) );
		if( $arMessage = $messageDb->fetch() )
		{
			$eventMessage = EventMessageTable::getRowById( $arMessage['ID'] );
			$eventMessage['FILES'] = array();
			$attachmentDb = EventMessageAttachmentTable::getList( 
					array(
							'select' => array(
									'FILE_ID' 
							),
							'filter' => array(
									'EVENT_MESSAGE_ID' => $arMessage['ID'] 
							) 
					) );
			while ( $arAttachmentDb = $attachmentDb->fetch() )
			{
				$eventMessage['FILE'][] = $arAttachmentDb['FILE_ID'];
			}
			unset( $arAttachmentDb );
			unset( $attachmentDb );
			$time = new \Bitrix\Main\Type\DateTime();
			$message = EventMessageCompiler::createInstance( 
					array(
							'EVENT' => array(
									"EVENT_NAME" => $this->getField( 'EVENT_NAME' ),
									"C_FIELDS" => $this->getField( 'FIELDS' ),
									"LID" => $this->getField( 'LID' ),
									"DUPLICATE" => 'N',
									"MESSAGE_ID" => $this->getField( 'ID_TEMPLATE' ),
									"DATE_INSERT" => $time->format( "d.m.Y H:i:s" ),
									"FILE" => $this->getField( 'FILES' ),
									"LANGUAGE_ID" => LANGUAGE_ID,
									"ID" => "0" 
							),
							'FIELDS' => $this->getField( 'FIELDS' ),
							'MESSAGE' => $eventMessage,
							'SITE' => $arSites,
							'CHARSET' => $charset 
					) );
			$message->compile();
			$this->message = $message;
		}
		unset( $message );
		unset( $charset );
		unset( $arSites );
		unset( $eventMessage );
		unset( $time );
		unset( $idMessage );
		unset( $arMessage );
		unset( $messageDb );
		unset( $filter );
	}
	public function replaceLinks()
	{
		$this->User()->find( array(
					'ID'),
					array(
							'EMAIL' => $this->Message()->getMailTo() 
					) 
			);

		if( Option::get( self::idModule, 'AUTH_FROM_EMAIL', '', $this->getField('LID') ) == 'Y' )
		{
			$this->setField( 'HASH', \randString( 15 ) );
		}
		else 
		{
			$this->setField( 'HASH', NULL );
		}

		$rs = \Sotbit\Auth\Internals\MessageTable::add( 
				array(
						'EVENT_NAME' => $this->getField( 'EVENT_NAME' ),
						'ID_MESSAGE' => $this->getField( 'ID_TEMPLATE' ),
						'LID' => $this->getField( 'LID' ),
						'EMAIL_TO' => $this->Message()->getMailTo(),
						'ID_USER' => $this->User()->getField( 'ID' ),
						'HASH' => $this->getField( 'HASH' ) 
				) );
		if( $rs->isSuccess() )
		{
			$this->setField( 'ID', $rs->getId() );
			$reg = '#href=([\'"]?)((?(?<![\'"])[^>\s]+|.+?(?=\1)))\1#si';
			$message = $this->Message()->getMailBody();
			if( preg_match_all( $reg, $message, $find ) )
			{
				foreach( $find[2] as $i => $link )
				{
					$addstr = '';
					if( strpos( $link, $this->getField( 'SITE_URL' ) ) !== false )
					{
						if( strpos( $link, '?' ) !== false )
						{
							if($this->getField( 'HASH' ))
							{
								$addstr .= '&SOTBIT_AUTH_MESSAGE=' . $this->getField( 'ID' ).'&SOTBIT_AUTH_HASH=' . $this->getField( 'HASH' );
							}
						}
						else 
						{
							if($this->getField( 'HASH' ))
							{
								$addstr .= '?SOTBIT_AUTH_MESSAGE=' . $this->getField( 'ID' ).'&SOTBIT_AUTH_HASH=' . $this->getField( 'HASH' );
							}
						}
					}
					$newLink = str_replace($link, $link . $addstr, $find[0][$i]);
					$message = str_replace( $find[0][$i], $newLink, $message );
					unset($addstr, $newLink);
				}
			}
			$this->setField( 'NEW_TEXT', $message );
      $this->addStatistics();
			unset( $message, $reg, $find, $link, $i);
		}
		else
		{
			$this->setError( $rs->getErrorMessages() );
		}
		unset( $rs );
	}
	public function addStatistics()
	{
		if($this->getField('NEW_TEXT') && Option::get( self::idModule, 'AUTH_FROM_EMAIL_DOMAIN', '', $this->getField('LID')))
		{
        $arFilter = Array(
            "TYPE_ID"       => $this->getField('EVENT_NAME')
        );

        $rsMess = \CEventMessage::GetList($by="site_id", $order="desc", $arFilter);
        $messageTempalte = '';
        while($arMess = $rsMess->GetNext())
        {
           $messageTempalte = $arMess['SITE_TEMPLATE_ID'];
        }
        $insert = array(
            'OPEN_MESSAGE'=>'N',
            'ID_MESSAGE'=>$this->getField( 'ID' ),
            'ID_USER' => $this->User()->getField( 'ID' ),
            'EVENT_NAME'=>$this->getField('EVENT_NAME'),
            'EVENT_TEMPLATE'=>$messageTempalte ?: SITE_ID
        );
        $mes = \Sotbit\Auth\Internals\StatisticsTable::add($insert);
        if( $mes->isSuccess() )
        {
            $message = $this->getField('NEW_TEXT');
            $message .= '<img src="'.$this->getField( 'SITE_URL' ).'/bitrix/admin/sotbit_auth_img.php?OPEN_MESSAGE='.$mes->getId().'" width="1px" height="1px"  />';
            $this->setField('NEW_TEXT', $message);
        }
		}
	}
	public function User()
	{
		return $this->user;
	}
	public function Message()
	{
		return $this->message;
	}
}
?>