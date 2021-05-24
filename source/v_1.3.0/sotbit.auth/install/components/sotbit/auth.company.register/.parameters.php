<?
if( !defined( "B_PROLOG_INCLUDED" ) || B_PROLOG_INCLUDED !== true )
	die();
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages( __FILE__ );
if( !\Bitrix\Main\Loader::includeModule( "sotbit.auth" ) || !\Bitrix\Main\Loader::includeModule( "sale" ))
{
	return;
}

$arFormFields = array(
		"EMAIL" => 1,
		"TITLE" => 1,
		"NAME" => 1,
		"SECOND_NAME" => 1,
		"LAST_NAME" => 1,
		"AUTO_TIME_ZONE" => 1,
		"PERSONAL_PROFESSION" => 1,
		"PERSONAL_WWW" => 1,
		"PERSONAL_ICQ" => 1,
		"PERSONAL_GENDER" => 1,
		"PERSONAL_BIRTHDAY" => 1,
		"PERSONAL_PHOTO" => 1,
		"PERSONAL_PHONE" => 1,
		"PERSONAL_FAX" => 1,
		"PERSONAL_MOBILE" => 1,
		"PERSONAL_PAGER" => 1,
		"PERSONAL_STREET" => 1,
		"PERSONAL_MAILBOX" => 1,
		"PERSONAL_CITY" => 1,
		"PERSONAL_STATE" => 1,
		"PERSONAL_ZIP" => 1,
		"PERSONAL_COUNTRY" => 1,
		"PERSONAL_NOTES" => 1,
		"WORK_COMPANY" => 1,
		"WORK_DEPARTMENT" => 1,
		"WORK_POSITION" => 1,
		"WORK_WWW" => 1,
		"WORK_PHONE" => 1,
		"WORK_FAX" => 1,
		"WORK_PAGER" => 1,
		"WORK_STREET" => 1,
		"WORK_MAILBOX" => 1,
		"WORK_CITY" => 1,
		"WORK_STATE" => 1,
		"WORK_ZIP" => 1,
		"WORK_COUNTRY" => 1,
		"WORK_PROFILE" => 1,
		"WORK_LOGO" => 1,
		"WORK_NOTES" => 1 
);

if( !CTimeZone::Enabled() )
{
	unset( $arFormFields["AUTO_TIME_ZONE"] );
}

$arUserFields = array();
foreach( $arFormFields as $value => $dummy )
{
	$arUserFields[$value] = "[" . $value . "] " . Loc::getMessage( "REGISTER_FIELD_" . $value );
}

$arWholeSalerFields = array();

$rs = \Bitrix\Sale\Internals\OrderPropsTable::getList( array(
		'filter' => array(
				'ACTIVE' => 'Y' 
		),
		'select' => array('ID','CODE','NAME')
) );
while($property = $rs->fetch())
{
	$arWholeSalerFields[$property['CODE']] = "[".$property['CODE']."] " .$property['NAME'];
}
unset($rs, $property);


$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields( "USER", 0, LANGUAGE_ID );
$userProp = array();
if( !empty( $arRes ) )
{
	foreach( $arRes as $key => $val )
	{
		$userProp[$val["FIELD_NAME"]] = (strLen( $val["EDIT_FORM_LABEL"] ) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
	}
}

$arComponentParameters = array(
		"PARAMETERS" => array(
				"SHOW_FIELDS" => array(
						"NAME" => Loc::getMessage( "REGISTER_SHOW_FIELDS" ),
						"TYPE" => "LIST",
						"MULTIPLE" => "Y",
						"ADDITIONAL_VALUES" => "N",
						"VALUES" => $arUserFields,
						"PARENT" => "BASE" 
				),
				"REQUIRED_FIELDS" => array(
						"NAME" => Loc::getMessage( "REGISTER_REQUIRED_FIELDS" ),
						"TYPE" => "LIST",
						"MULTIPLE" => "Y",
						"ADDITIONAL_VALUES" => "N",
						"VALUES" => $arUserFields,
						"PARENT" => "BASE" 
				),
				/*"SHOW_WHOLESALER_FIELDS" => array(
						"NAME" => Loc::getMessage( "REGISTER_SHOW_WHOLESALER_FIELDS" ),
						"TYPE" => "LIST",
						"MULTIPLE" => "Y",
						"ADDITIONAL_VALUES" => "N",
						"VALUES" => $arUserFields,
						"PARENT" => "BASE" 
				),*/
				"REQUIRED_WHOLESALER_FIELDS" => array(
						"NAME" => Loc::getMessage( "REGISTER_REQUIRED_WHOLESALER_FIELDS" ),
						"TYPE" => "LIST",
						"MULTIPLE" => "Y",
						"ADDITIONAL_VALUES" => "N",
						"VALUES" => $arUserFields,
						"PARENT" => "BASE" 
				),
				/*"SHOW_WHOLESALER_ORDER_FIELDS" => array(
						"NAME" => Loc::getMessage( "REGISTER_REQUIRED_WHOLESALER_ORDER_FIELDS" ),
						"TYPE" => "LIST",
						"MULTIPLE" => "Y",
						"ADDITIONAL_VALUES" => "N",
						"VALUES" => $arWholeSalerFields,
						"PARENT" => "BASE"
				),*/
				"AUTH" => array(
						"NAME" => Loc::getMessage( "REGISTER_AUTOMATED_AUTH" ),
						"TYPE" => "CHECKBOX",
						"DEFAULT" => "Y",
						"PARENT" => "ADDITIONAL_SETTINGS" 
				),
				"USE_BACKURL" => array(
						"NAME" => Loc::getMessage( "REGISTER_USE_BACKURL" ),
						"TYPE" => "CHECKBOX",
						"DEFAULT" => "Y",
						"PARENT" => "ADDITIONAL_SETTINGS" 
				),
				"SUCCESS_PAGE" => array(
						"NAME" => Loc::getMessage( "REGISTER_SUCCESS_PAGE" ),
						"TYPE" => "STRING",
						"DEFAULT" => "",
						"PARENT" => "ADDITIONAL_SETTINGS" 
				),
				"SET_TITLE" => array(),
				"USER_PROPERTY" => array(
						"PARENT" => "ADDITIONAL_SETTINGS",
						"NAME" => Loc::getMessage( "USER_PROPERTY" ),
						"TYPE" => "LIST",
						"VALUES" => $userProp,
						"MULTIPLE" => "Y",
						"DEFAULT" => array() 
				) 
		) 

);
?>