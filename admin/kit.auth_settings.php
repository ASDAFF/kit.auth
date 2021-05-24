<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\GroupTable;
use Bitrix\Sale\Internals\PersonTypeTable;
use Kit\Auth\User\WholeSaler;
use Bitrix\Main\Type;

require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
Loc::loadMessages( __FILE__ );
if( $APPLICATION->GetGroupRight( "main" ) < "R" )
{
	$APPLICATION->AuthForm( Loc::getMessage( "ACCESS_DENIED" ) );
}


function OptionGetValue($key)
{
	$result = COption::GetOptionString(KitAuth::idModule, $key);
	if ($_REQUEST[$key]) {
		$result = $_REQUEST[$key];
	}
	return $result;
}

if( !\Bitrix\Main\Loader::includeModule( "kit.auth" ) )
{
	return;
}

$site_id = $site;

require_once (Application::getDocumentRoot() . '/bitrix/modules/main/include/prolog_admin.php');

if( $REQUEST_METHOD == "POST" && strlen( $RestoreDefaults ) > 0 && check_bitrix_sessid() )
{
	$KitAuth = new KitAuth();
	if( !$KitAuth->getDemo() )
	{
		return false;
	}
	Option::delete( KitAuth::idModule );
	$z = CGroup::GetList( $v1 = "id", $v2 = "asc", array(
			"ACTIVE" => "Y",
			"ADMIN" => "N"
	) );
	while ( $zr = $z->Fetch() )
	{
		$APPLICATION->DelGroupRight( KitAuth::idModule, array(
				$zr["ID"]
		) );
	}
	if( (strlen( $Apply ) > 0) || (strlen( $RestoreDefaults ) > 0) )
	{
		LocalRedirect(
				$APPLICATION->GetCurPage() . "?lang=" . LANGUAGE_ID . "&mid=" . urlencode( $mid ) . "&tabControl_active_tab=" . urlencode( $_REQUEST["tabControl_active_tab"] ) . "&back_url_settings=" . urlencode(
						$_REQUEST["back_url_settings"] ) );
	}
	else
	{
		LocalRedirect( $_REQUEST["back_url_settings"] );
	}
}

// mail events
$events = array();
$rsEvents = \Bitrix\Main\Mail\Internal\EventTypeTable::getList(
		array(
				'select' => array(
						'EVENT_NAME'
				),
				'order' => array(
						'EVENT_NAME' => 'asc'
				),
				'group' => array(
						'EVENT_NAME'
				)
		) );
while ( $event = $rsEvents->fetch() )
{
	if( $event['EVENT_NAME'] != KitAuth::KIT_AUTH_SEND_EVENT )
	{
		$events['REFERENCE_ID'][] = $event['EVENT_NAME'];
		$events['REFERENCE'][] = $event['EVENT_NAME'];
	}
}

// user groups
$groups = array(
		'REFERENCE_ID' => array(0),
		'REFERENCE' => array('-')
);
$rs = GroupTable::getList( array(
		'filter' => array(
				'ACTIVE' => 'Y',
				'!ID' => 1
		),
		'select' => array(
				'ID',
				'NAME'
		)
) );
$groups_auth = array();
while ( $group = $rs->fetch() )
{
	$groups['REFERENCE_ID'][] = $group['ID'];
	$groups['REFERENCE'][] = '[' . $group['ID'] . '] ' . $group['NAME'];
	$groups_auth['REFERENCE_ID'][] = $group['ID'];
	$groups_auth['REFERENCE'][] = '[' . $group['ID'] . '] ' . $group['NAME'];
}
unset( $Group, $rs );
// personal type
$personTypes = array();
$rs = PersonTypeTable::getList(
    array(
        'filter' => array(
            'ACTIVE' => 'Y',
            array(
                'LOGIC' => 'OR',
                array('LID' => $site_id),
                array('PERSON_TYPE_SITE.SITE_ID' => $site_id),
            ),
        ),
        'select' => array(
            'ID',
            'NAME'
        )
    )
);

$personTypes['REFERENCE_ID'] = $personTypes['REFERENCE'] = array();

while ($personType = $rs->fetch(
)) {
    if (!in_array(
        $personType['ID'],
        $personTypes['REFERENCE_ID']
    )) {
        $personTypes['REFERENCE_ID'][] = $personType['ID'];
        $personTypes['REFERENCE'][] = '[' . $personType['ID'] . '] ' . $personType['NAME'];
    }
}
unset( $personType, $rs );

if(  isset( $_REQUEST["CONFIRM_REGISTER"] ) && $_REQUEST["CONFIRM_REGISTER"]) {
    $arCurrentValues["CONFIRM_REGISTER"] = $_REQUEST["CONFIRM_REGISTER"];
}
elseif (isset($_REQUEST['update'])){
    $arCurrentValues["CONFIRM_REGISTER"] = "N";
}
else{
    $arCurrentValues["CONFIRM_REGISTER"] = Option::get( KitAuth::idModule, "CONFIRM_REGISTER", "",$site_id );
}
if( isset( $_REQUEST["WHOLESALERS_PERSON_TYPE"] ) && $_REQUEST["WHOLESALERS_PERSON_TYPE"] )
{
	$arCurrentValues["WHOLESALERS_PERSON_TYPE"] = $_REQUEST["WHOLESALERS_PERSON_TYPE"];
}
else
{
	$arCurrentValues["WHOLESALERS_PERSON_TYPE"] = unserialize(Option::get( KitAuth::idModule, "WHOLESALERS_PERSON_TYPE", "",$site_id ));
}
$arWholeSalerFields = array();
if($arCurrentValues["WHOLESALERS_PERSON_TYPE"] > 0)
{
	$rs = \Bitrix\Sale\Internals\OrderPropsTable::getList( array(
			'filter' => array(
					'ACTIVE' => 'Y',
					'PERSON_TYPE_ID' => $arCurrentValues["WHOLESALERS_PERSON_TYPE"]
			),
			'select' => array('ID','CODE','NAME')
	) );
	while($property = $rs->fetch())
	{
		$arWholeSalerFields[$property['CODE']] = "[".$property['CODE']."] " .$property['NAME'];
	}
	unset($rs, $property);
}

$UserFields = array(
		'EMAIL' => '[EMAIL] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_EMAIL'),
		'TITLE' => '[TITLE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_TITLE'),
		'NAME' => '[NAME] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_NAME'),
		'SECOND_NAME' => '[SECOND_NAME] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_SECOND_NAME'),
		'LAST_NAME' => '[LAST_NAME] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_LAST_NAME'),
		'PERSONAL_PROFESSION' => '[PERSONAL_PROFESSION] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_PROFESSION'),
		'PERSONAL_WWW' => '[PERSONAL_WWW] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_WWW'),
		'PERSONAL_ICQ' => '[PERSONAL_ICQ] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_ICQ'),
		'PERSONAL_GENDER' => '[PERSONAL_GENDER] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_GENDER'),
		'PERSONAL_BIRTHDAY' => '[PERSONAL_BIRTHDAY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_BIRTHDAY'),
		'PERSONAL_PHOTO' => '[PERSONAL_PHOTO] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_PHOTO'),
		'PERSONAL_PHONE' => '[PERSONAL_PHONE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_PHONE'),
		'PERSONAL_FAX' => '[PERSONAL_FAX] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_FAX'),
		'PERSONAL_MOBILE' => '[PERSONAL_MOBILE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_MOBILE'),
		'PERSONAL_PAGER' => '[PERSONAL_PAGER] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_PAGER'),
		'PERSONAL_STREET' => '[PERSONAL_STREET] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_STREET'),
		'PERSONAL_MAILBOX' => '[PERSONAL_MAILBOX] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_MAILBOX'),
		'PERSONAL_CITY' => '[PERSONAL_CITY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_CITY'),
		'PERSONAL_STATE' => '[PERSONAL_STATE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_STATE'),
		'PERSONAL_ZIP' => '[PERSONAL_ZIP] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_ZIP'),
		'PERSONAL_COUNTRY' => '[PERSONAL_COUNTRY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_COUNTRY'),
		'PERSONAL_NOTES' => '[PERSONAL_NOTES] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_NOTES'),
		'WORK_COMPANY' => '[WORK_COMPANY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_COMPANY'),
		'WORK_DEPARTMENT' => '[WORK_DEPARTMENT] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_DEPARTMENT'),
		'WORK_POSITION' => '[WORK_POSITION] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_POSITION'),
		'WORK_WWW' => '[WORK_WWW] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_WWW'),
		'WORK_PHONE' => '[WORK_PHONE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_PHONE'),
		'WORK_FAX' => '[WORK_FAX] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_FAX'),
		'WORK_PAGER' => '[WORK_PAGER] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_PAGER'),
		'WORK_STREET' => '[WORK_STREET] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_STREET'),
		'WORK_MAILBOX' => '[WORK_MAILBOX] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_MAILBOX'),
		'WORK_CITY' => '[WORK_CITY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_CITY'),
		'WORK_STATE' => '[WORK_STATE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_STATE'),
		'WORK_ZIP' => '[WORK_ZIP] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_ZIP'),
		'WORK_COUNTRY' => '[WORK_COUNTRY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_COUNTRY'),
		'WORK_PROFILE' => '[WORK_PROFILE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_PROFILE'),
		'WORK_LOGO' => '[WORK_LOGO] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_LOGO'),
		'WORK_NOTES' => '[WORK_NOTES] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_NOTES')
);


if( $_REQUEST['ORDER_FIELD'] && $_REQUEST['USER_FIELD'] )
{
	foreach( $_POST['ORDER_FIELD'] as $i => $orderField )
	{
		$CurrentValueOrderFields[$i]['ORDER_FIELD'] = $orderField;
		$CurrentValueOrderFields[$i]['USER_FIELD'] = $_POST['USER_FIELD'][$i];
	}
}
elseif($_REQUEST['save'])
{
	$CurrentValueOrderFields = array();
}
else
{
	$CurrentValueOrderFields= unserialize( Option::get( KitAuth::idModule, 'WHOLESALERS_ORDER_FIELDS', 'a:0:{}',$site_id ) );
}


$SyncOrderField = '<div id="SyncOrderField">';
if( count( $CurrentValueOrderFields ) > 0 && is_array( $CurrentValueOrderFields) )
{
	foreach( $CurrentValueOrderFields as $i => $vals )
	{
		$SyncOrderField .= '<div><select name="ORDER_FIELD[]">';
		foreach( $arWholeSalerFields as $SalerFieldId => $SalerField)
		{
			if( $SalerFieldId == $vals['ORDER_FIELD'] )
			{
				$Select = 'selected';
			}
			else
			{
				$Select = '';
			}
			$SyncOrderField .= '<option value="' . $SalerFieldId. '" ' . $Select . '>' . $SalerField. '</option>';
		}
		$SyncOrderField .= '</select>';
		$SyncOrderField .= '<select name="USER_FIELD[]">';
		foreach( $UserFields as $IdUserField => $UserField)
		{
			if( $IdUserField== $vals['USER_FIELD'] )
			{
				$Select = 'selected';
			}
			else
			{
				$Select = '';
			}
			$SyncOrderField .= '<option value="' . $IdUserField. '" ' . $Select . '>' . $UserField. '</option>';
		}
		$SyncOrderField .= '</select>';
		$SyncOrderField .= '</div>';
	}
}
else
{
	$SyncOrderField .= '<div>';
	$SyncOrderField .= '</div>';
}

$SyncOrderField .= '
	</div>
	<input type="button" value="+" onclick="new_order_field()">
	<input type="button" value="-" onclick="delete_order_field()">
	<script type="text/javascript">
		function new_order_field()
		{
			var div = document.createElement("div");
			div.innerHTML = \'<select name="ORDER_FIELD[]">';
foreach( $arWholeSalerFields as $SalerFieldId => $SalerField)
{
	$SyncOrderField .= '<option value="' . $SalerFieldId. '">' . $SalerField. '</option>';
}
$SyncOrderField .= '</select><select name="USER_FIELD[]">';
foreach( $UserFields as $IdUserField => $UserField)
{
	$SyncOrderField .= '<option value="' . $IdUserField. '">' . $UserField. '</option>';
}
$SyncOrderField .= '</select>\';
			document.getElementById("SyncOrderField").appendChild(div);
		}
		function delete_order_field()
		{
			var ElCnt=document.getElementById("SyncOrderField").getElementsByTagName("div").length;
			if(ElCnt>0)
			{
				var children = document.getElementById("SyncOrderField").childNodes;
				document.getElementById("SyncOrderField").removeChild(children[children.length-1]);
			}
		}
	</script>';


$userFieldsRegister = array(
    'REFERENCE_ID' => array(
        'EMAIL',
        'TITLE',
        'NAME',
        'SECOND_NAME',
        'LAST_NAME',
        'PERSONAL_PROFESSION',
        'PERSONAL_WWW',
        'PERSONAL_ICQ',
        'PERSONAL_GENDER',
        'PERSONAL_BIRTHDAY',
        'PERSONAL_PHOTO',
        'PERSONAL_PHONE',
        'PERSONAL_FAX',
        'PERSONAL_MOBILE',
        'PERSONAL_PAGER',
        'PERSONAL_STREET',
        'PERSONAL_MAILBOX',
        'PERSONAL_CITY',
        'PERSONAL_STATE',
        'PERSONAL_ZIP',
        'PERSONAL_COUNTRY',
        'PERSONAL_NOTES',
        'WORK_COMPANY',
        'WORK_DEPARTMENT',
        'WORK_POSITION',
        'WORK_WWW',
        'WORK_PHONE',
        'WORK_FAX',
        'WORK_PAGER',
        'WORK_STREET',
        'WORK_MAILBOX',
        'WORK_CITY',
        'WORK_STATE',
        'WORK_ZIP',
        'WORK_COUNTRY',
        'WORK_PROFILE',
        'WORK_LOGO',
        'WORK_NOTES',
    ),
    'REFERENCE' => array(
        '[EMAIL] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_EMAIL'),
        '[TITLE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_TITLE'),
        '[NAME] '.Loc::getMessage(KitAuth::idModule . '_USER_FIELD_NAME'),
        '[SECOND_NAME] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_SECOND_NAME'),
        '[LAST_NAME] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_LAST_NAME'),
        '[PERSONAL_PROFESSION] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_PROFESSION'),
        '[PERSONAL_WWW] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_WWW'),
        '[PERSONAL_ICQ] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_ICQ'),
        '[PERSONAL_GENDER] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_GENDER'),
        '[PERSONAL_BIRTHDAY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_BIRTHDAY'),
        '[PERSONAL_PHOTO] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_PHOTO'),
        '[PERSONAL_PHONE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_PHONE'),
        '[PERSONAL_FAX] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_FAX'),
        '[PERSONAL_MOBILE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_MOBILE'),
        '[PERSONAL_PAGER] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_PAGER'),
        '[PERSONAL_STREET] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_STREET'),
        '[PERSONAL_MAILBOX] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_MAILBOX'),
        '[PERSONAL_CITY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_CITY'),
        '[PERSONAL_STATE] '.Loc::getMessage( KitAuth::idModule. '_USER_FIELD_PERSONAL_STATE'),
        '[PERSONAL_ZIP] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_ZIP'),
        '[PERSONAL_COUNTRY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_COUNTRY'),
        '[PERSONAL_NOTES] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_PERSONAL_NOTES'),
        '[WORK_COMPANY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_COMPANY'),
        '[WORK_DEPARTMENT] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_DEPARTMENT'),
        '[WORK_POSITION] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_POSITION'),
        '[WORK_WWW] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_WWW'),
        '[WORK_PHONE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_PHONE'),
        '[WORK_FAX] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_FAX'),
        '[WORK_PAGER] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_PAGER'),
        '[WORK_STREET] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_STREET'),
        '[WORK_MAILBOX] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_MAILBOX'),
        '[WORK_CITY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_CITY'),
        '[WORK_STATE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_STATE'),
        '[WORK_ZIP] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_ZIP'),
        '[WORK_COUNTRY] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_COUNTRY'),
        '[WORK_PROFILE] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_PROFILE'),
        '[WORK_LOGO] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_LOGO'),
        '[WORK_NOTES] '.Loc::getMessage( KitAuth::idModule . '_USER_FIELD_WORK_NOTES')
    )
);

$arTabs = array(
		array(
				'DIV' => 'edit1',
				'TAB' => Loc::getMessage( KitAuth::idModule . '_edit1' ),
				'ICON' => '',
				'SITE' => $site_id,
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_edit1' ),
				'SORT' => '10'
		),
		array(
				'DIV' => 'edit2',
				'TAB' => Loc::getMessage( KitAuth::idModule . '_edit2' ),
				'ICON' => '',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_edit2' ),
				'SORT' => '20'
		),
        array(
                'DIV' => 'mail_events',
                'TAB' => Loc::getMessage( KitAuth::idModule . '_mail_events' ),
                'ICON' => '',
                'TITLE' => Loc::getMessage( KitAuth::idModule . '_mail_events' ),
                'SORT' => '30'
        )
);
$arGroups = array(
		'GROUP_SETTINGS' => array(
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_GROUP_SETTINGS' ),
				'TAB' => 0
		),
		'GROUP_SETTINGS_WHOLESALERS' => array(
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_GROUP_SETTINGS_WHOLESALERS' ),
				'TAB' => 1
		),
        'GROUP_SETTINGS_MAIL_EVENTS' => array(
                'TITLE' => Loc::getMessage( KitAuth::idModule . '_GROUP_SETTINGS_MAIL_EVENTS' ),
                'TAB' => 2
        ),

);

$arOptions = array(
		'LOGIN_EQ_EMAIL' => array(
				'GROUP' => 'GROUP_SETTINGS',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_LOGIN_EQ_EMAIL' ),
				'TYPE' => 'CHECKBOX',
				'SITE' => $site_id,
				'REFRESH' => 'N',
				'SORT' => '5',
				'DEFAULT' => 'N',
				'NOTES' => Loc::getMessage( KitAuth::idModule . '_LOGIN_EQ_EMAIL_NOTE' )
		),
		'AUTH_BY_EMAIL' => array(
				'GROUP' => 'GROUP_SETTINGS',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_AUTH_BY_EMAIL' ),
				'TYPE' => 'CHECKBOX',
				'SITE' => $site_id,
				'REFRESH' => 'N',
				'SORT' => '30',
				'DEFAULT' => 'Y',
				'NOTES' => Loc::getMessage( KitAuth::idModule . '_AUTH_BY_EMAIL_NOTE' )
		),
		'AUTH_BY_FIELD' => array(
				'GROUP' => 'GROUP_SETTINGS',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_AUTH_BY_FIELD' ),
				'TYPE' => 'MSELECT',
				'VALUES' => array(
						'REFERENCE_ID' => array(
								'PERSONAL_WWW',
								'PERSONAL_ICQ',
								'PERSONAL_PHONE',
								'PERSONAL_FAX',
								'PERSONAL_MOBILE',
								'PERSONAL_PAGER',
								'WORK_COMPANY',
								'WORK_WWW',
								'WORK_PHONE',
								'WORK_FAX',
								'WORK_PAGER'
						),
						'REFERENCE' => array(
								Loc::getMessage( KitAuth::idModule . '_PERSONAL_WWW' ),
								Loc::getMessage( KitAuth::idModule . '_PERSONAL_ICQ' ),
								Loc::getMessage( KitAuth::idModule . '_PERSONAL_PHONE' ),
								Loc::getMessage( KitAuth::idModule . '_PERSONAL_FAX' ),
								Loc::getMessage( KitAuth::idModule . '_PERSONAL_MOBILE' ),
								Loc::getMessage( KitAuth::idModule . '_PERSONAL_PAGER' ),
								Loc::getMessage( KitAuth::idModule . '_WORK_COMPANY' ),
								Loc::getMessage( KitAuth::idModule . '_WORK_WWW' ),
								Loc::getMessage( KitAuth::idModule . '_WORK_PHONE' ),
								Loc::getMessage( KitAuth::idModule . '_WORK_FAX' ),
								Loc::getMessage( KitAuth::idModule . '_WORK_PAGER' )
						)
				),
				'SITE' => $site_id,
				'REFRESH' => 'N',
				'SORT' => '45',
				'DEFAULT' => 'N'
		),
		'AUTH_FROM_EMAIL' => array(
				'GROUP' => 'GROUP_SETTINGS',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_AUTH_FROM_EMAIL' ),
				'TYPE' => 'CHECKBOX',
				'SITE' => $site_id,
				'REFRESH' => 'N',
				'SORT' => '60',
				'DEFAULT' => 'Y',
				'NOTES' => Loc::getMessage( KitAuth::idModule . '_AUTH_FROM_EMAIL_NOTE' )
		),
		'AUTH_FROM_EMAIL_EVENTS' => array(
				'GROUP' => 'GROUP_SETTINGS',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_AUTH_FROM_EMAIL_EVENTS' ),
				'TYPE' => 'MSELECT',
				'SITE' => $site_id,
				'REFRESH' => 'N',
				'SORT' => '75',
				'SIZE' => 20,
				'VALUES' => $events,
				'NOTES' => Loc::getMessage( KitAuth::idModule . '_AUTH_FROM_EMAIL_EVENTS_NOTE' )
		),
		'AUTH_WHOLESALERS_GROUP' => array(
			'GROUP' => 'GROUP_SETTINGS',
			'TITLE' => Loc::getMessage( KitAuth::idModule . '_AUTH_WHOLESALERS_GROUP' ),
			'TYPE' => 'MSELECT',
			'REFRESH' => 'N',
			'SORT' => '85',
			'VALUES' => $groups_auth,
			'SITE' => $site_id
		),
		'AUTH_FROM_EMAIL_DOMAIN' => array(
				'GROUP' => 'GROUP_SETTINGS',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_AUTH_FROM_EMAIL_DOMAIN' ),
				'TYPE' => 'STRING',
				'SITE' => $site_id,
				'REFRESH' => 'N',
				'SORT' => '90',
				'NOTES' => Loc::getMessage( KitAuth::idModule . '_AUTH_FROM_EMAIL_DOMAIN_NOTE' )
		),
		'CONFIRM_REGISTER' => array(
			'GROUP' => 'GROUP_SETTINGS_WHOLESALERS',
			'TITLE' => Loc::getMessage( KitAuth::idModule . '_CONFIRM_REGISTER' ),
			'TYPE' => 'CHECKBOX',
			'REFRESH' => 'Y',
			'SORT' => '220',
			'SITE' => $site_id,
			'DEFAULT' => 'N',
			'NOTES' => Loc::getMessage( KitAuth::idModule . '_CONFIRM_REGISTER_NOTES' )
		),
		'CONFIRM_BUYER' => array(
			'GROUP' => 'GROUP_SETTINGS_WHOLESALERS',
			'TITLE' => Loc::getMessage( KitAuth::idModule . '_CONFIRM_BUYER' ),
			'TYPE' => 'CHECKBOX',
			'REFRESH' => 'N',
			'SORT' => '230',
			'SITE' => $site_id,
			'DEFAULT' => 'N',
			'NOTES' => Loc::getMessage( KitAuth::idModule . '_CONFIRM_BUYER_NOTES' )
		),
		'WHOLESALERS_GROUP' => array(
				'GROUP' => 'GROUP_SETTINGS_WHOLESALERS',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_WHOLESALERS_GROUP' ),
				'TYPE' => 'SELECT',
				'REFRESH' => 'N',
				'SORT' => '100',
				'VALUES' => $groups,
				'SITE' => $site_id,
				'NOTES' => Loc::getMessage( KitAuth::idModule . '_WHOLESALERS_GROUP_NOTES' )
		),
		'USER_GROUPS' => array(
				'GROUP' => 'GROUP_SETTINGS',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_USER_GROUPS' ),
				'TYPE' => 'MSELECT',
				'REFRESH' => 'N',
				'SORT' => '115',
				'SITE' => $site_id,
				'VALUES' => $groups_auth,
				'NOTES' => Loc::getMessage( KitAuth::idModule . '_USER_GROUPS_NOTES' )
		),
		'WHOLESALERS_PERSON_TYPE' => array(
				'GROUP' => 'GROUP_SETTINGS_WHOLESALERS',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_WHOLESALERS_PERSON_TYPE' ),
				'TYPE' => 'MSELECT',
				'REFRESH' => 'Y',
				'SORT' => '120',
				'VALUES' => $personTypes,
				'SITE' => $site_id,
		),
		'WHOLESALERS_ORDER_FIELDS' => array(
				'GROUP' => 'GROUP_SETTINGS_WHOLESALERS',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_WHOLESALERS_ORDER_FIELDS' ),
				'TYPE' => 'CUSTOM',
				'REFRESH' => 'Y',
				'SORT' => '130',
				'VALUE' => $SyncOrderField,
				'SITE' => $site_id,
				'NOTES' => Loc::getMessage( KitAuth::idModule . '_WHOLESALERS_ORDER_FIELDS_NOTES' )
		),
		'WHOLESALERS_EMAIL_NOTIFICATION' => array(
				'GROUP' => 'GROUP_SETTINGS_WHOLESALERS',
				'TITLE' => Loc::getMessage( KitAuth::idModule . '_WHOLESALERS_EMAIL_NOTIFICATION' ),
				'TYPE' => 'CHECKBOX',
				'REFRESH' => 'N',
				'SORT' => '140',
				'SITE' => $site_id,
				'DEFAULT' => 'Y',
		),
        'MAIL_EVENT_COMPANY_REGISTER' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_REGISTER' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '1',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
        'MAIL_EVENT_COMPANY_SENT_MODERATION' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_SENT_MODERATION' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '1',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
        'MAIL_EVENT_COMPANY_CONFIRM' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_CONFIRM' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '5',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
        'MAIL_EVENT_COMPANY_REJECTED' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_REJECTED' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '10',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
        'MAIL_EVENT_COMPANY_ADMIN_CONFIRM' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_ADMIN_CONFIRM' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '15',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
        'MAIL_EVENT_COMPANY_ADMIN_REJECTED' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_ADMIN_REJECTED' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '20',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
        'MAIL_EVENT_COMPANY_CHANGES_REJECTED' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_CHANGES_REJECTED' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '25',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
        'MAIL_EVENT_COMPANY_STAFF_INVITE' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_STAFF_INVITE' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '30',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
        'MAIL_EVENT_COMPANY_JOIN_REQUEST' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_JOIN_REQUEST' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '35',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
        'MAIL_EVENT_COMPANY_JOIN_REQUEST_CONFIRM' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_JOIN_REQUEST_CONFIRM' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '40',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
        'MAIL_EVENT_COMPANY_JOIN_REQUEST_REJECTED' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_JOIN_REQUEST_REJECTED' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '45',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
        'MAIL_EVENT_COMPANY_STAFF_REMOVED' => array(
            'GROUP' => 'GROUP_SETTINGS_MAIL_EVENTS',
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_MAIL_EVENT_COMPANY_STAFF_REMOVED' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '50',
            'VALUES' => $events,
            'SITE' => $site_id
        ),
);

if($arCurrentValues["CONFIRM_REGISTER"] == "Y" && Option::get( KitAuth::idModule, "EXTENDED_VERSION_COMPANIES", "N")=="Y"){

    $dbRoles = Kit\Auth\Company\Company::getRoles();

    foreach ($dbRoles as $role){
        $staffRoles["REFERENCE"][]  = "[".$role["CODE"]."] ".$role["NAME"];
        $staffRoles["REFERENCE_ID"][]  = $role["CODE"];
    }

    $arOptions["COMPANY_STAFF_ROLE"] = [
        'GROUP' => 'GROUP_SETTINGS_WHOLESALERS',
        'TITLE' => Loc::getMessage( KitAuth::idModule . '_COMPANY_STAFF_ROLE' ),
        'TYPE' => 'MSELECT',
        'REFRESH' => 'N',
        'SORT' => '225',
        'SITE' => $site_id,
        'VALUES' => $staffRoles,
        'NOTES' => Loc::getMessage( KitAuth::idModule . '_COMPANY_STAFF_ROLE_NOTES' )
    ];
}

if(!$_REQUEST['site']) {
    $arOptions['LOGIN_EQ_EMAIL_IN_ADMIN'] = array(
        'GROUP' => 'GROUP_SETTINGS',
        'TITLE' => Loc::getMessage( KitAuth::idModule . '_NOT_LOGIN_EQ_EMAIL_IN_ADMIN' ),
        'TYPE' => 'CHECKBOX',
        'REFRESH' => 'N',
        'SORT' => '15',
        'DEFAULT' => 'N'
    );
    $arOptions['EXTENDED_VERSION_COMPANIES'] = array(
        'GROUP' => 'GROUP_SETTINGS',
        'TITLE' => Loc::getMessage( KitAuth::idModule . '_EXTENDED_VERSION_COMPANIES' ),
        'TYPE' => 'CHECKBOX',
        'REFRESH' => 'Y',
        'SORT' => '10',
        'DEFAULT' => 'N',
        'NOTES' => Loc::getMessage( KitAuth::idModule . '_EXTENDED_VERSION_COMPANIES_NOTES' )
    );
}

$rs = PersonTypeTable::getList(
    array(
        'filter' => array(
            'ACTIVE' => 'Y',
            array(
                'LOGIC' => 'OR',
                array('LID' => $site_id),
                array('PERSON_TYPE_SITE.SITE_ID' => $site_id),
            ),
        ),
        'select' => array(
            'ID',
            'NAME'
        )
    )
);

$personTypesActive = array();

while ( $res = $rs->fetch() )
{
    if(!in_array($res['ID'], $personTypesActive)) {
        $personTypesActive[] = $res;
    }
}

$typesActive = array();
$personTypesCurrent = array();

foreach ($personTypesActive as $key=>$personTypeActive) $typesActive[$personTypeActive['ID']] = $personTypeActive;

$wholeSalerPersonTypes = unserialize(Option::get( KitAuth::idModule, "WHOLESALERS_PERSON_TYPE", "",$site_id ));
$personTypeCurrent = array();
if (is_array($wholeSalerPersonTypes))
    foreach ($wholeSalerPersonTypes as $key=>$personTypeId)
        $personTypeCurrent[] = $typesActive[$personTypeId];

$countTabs = count($arTabs);
$countGroups = count($arGroups);

foreach ($personTypeCurrent as $key=>$personTypeA)
{
    $countTabs++;
    $countGroups++;

    $groupFields = unserialize(Option::get(KitAuth::idModule, 'GROUP_FIELDS_' . $personTypeA['ID'] , '', $site_id));
    if(!is_array($groupFields))
    {
		$groupFields = [];
    }

    $activeFields['REFERENCE_ID'] = $activeFields['REFERENCE'] = $groupFields;

    $orderFields = array();
    $orderFieldsIds = array();
    $nameFieldCode = '';

    $orderPropsGroup = [];
    $dbPropsGroup = CSaleOrderPropsGroup::GetList([],["PERSON_TYPE_ID"=>$personTypeA["ID"]],false,false,["ID", "NAME"]);

    while ($propsGroup = $dbPropsGroup->Fetch())
    {
        $orderPropsGroup["REFERENCE_ID"][] = $propsGroup["ID"];
        $orderPropsGroup["REFERENCE"][] = $propsGroup["NAME"];
        if($propsGroup["NAME"] == Loc::getMessage( KitAuth::idModule . '_GROUP_COMPANY_IP_DATA' ) ||
            $propsGroup["NAME"] == Loc::getMessage( KitAuth::idModule . '_GROUP_COMPANY_UR_DATA' )
        ) {
            $defaultGroupCompany = serialize([$propsGroup["ID"]]);
        }
    }

    $rs = \Bitrix\Sale\Internals\OrderPropsTable::getList( array(
        'filter' => array(
            'ACTIVE' => 'Y',
            'PERSON_TYPE_ID'=>$personTypeA['ID']
        ),
        'select' => array('ID','CODE','NAME', 'IS_PROFILE_NAME')
    ) );

    while($property = $rs->fetch())
    {
        if(!$nameFieldCode && $property["IS_PROFILE_NAME"] == "Y"){
            $nameFieldCode = $property["CODE"];
        }
        $orderFields['REFERENCE_ID'][$property['CODE']] = $property['CODE'];
        $orderFields['REFERENCE'][$property['CODE']] = "[".$property['CODE']."] " .$property['NAME'];

        $orderFieldsIds['REFERENCE_ID'][$property['ID']] = $property['ID'];
        $orderFieldsIds['REFERENCE'][$property['ID']] = "[".$property['ID']."][".$property['CODE']."] " .$property['NAME'];
    }



    if (OptionGetValue('GROUP_INN_USE_DEFAULT_'.$personTypeA['ID']) != 'Y' && in_array('INN', $orderFields['REFERENCE_ID'])){
				$arOptions['GROUP_ORDER_INN_FIELD_'.$personTypeA['ID']] = array(
					'GROUP' => 'GROUP_SETTINGS_' . $countGroups,
					'TITLE' => Loc::getMessage( KitAuth::idModule . '_OPT_INN_FIELD' ),
					'TYPE' => 'SELECT',
					'REFRESH' => 'N',
					'SORT' => '10',
					'SIZE' => '10',
					'SITE' => $site_id,
					'VALUES' => $orderFields
				);
		}

    $sort = 10 * $countTabs;
    $arTabs[] = array(
        'DIV' => 'edit' . $countTabs,
        'TAB' => $personTypeA['NAME'],
        'ICON' => '',
        'TITLE' => $personTypeA['NAME'],
        'SORT' => $sort
    );
    $arGroups['GROUP_SETTINGS_' . $countGroups] = array(
        'TITLE' => Loc::getMessage( KitAuth::idModule . '_GROUP_SETTINGS_WHOLESALERS' ),
        'TAB' => $countTabs
    );
    $arOptions['GROUP_FIELDS_'.$personTypeA['ID']] = array(
        'GROUP' => 'GROUP_SETTINGS_' . $countGroups,
        'TITLE' => Loc::getMessage( KitAuth::idModule . '_OPT_REGISTER_FIELDS' ),
        'TYPE' => 'MSELECT',
        'REFRESH' => 'N',
        'SORT' => '20',
        'SIZE' => '10',
        'SITE' => $site_id,
        'VALUES' => $userFieldsRegister
    );

    $arOptions['GROUP_ORDER_FIELDS_'.$personTypeA['ID']] = array(
        'GROUP' => 'GROUP_SETTINGS_' . $countGroups,
        'TITLE' => Loc::getMessage( KitAuth::idModule . '_OPT_ORDER_REGISTER_FIELDS' ),
        'TYPE' => 'MSELECT',
        'REFRESH' => 'N',
        'SORT' => '40',
        'SIZE' => '10',
        'SITE' => $site_id,
        'VALUES' => $orderFields
    );

    $arOptions['GROUP_REQUIRED_FIELDS_'.$personTypeA['ID']] = array(
        'GROUP' => 'GROUP_SETTINGS_' . $countGroups,
        'TITLE' => Loc::getMessage( KitAuth::idModule . '_OPT_REQUIRED_REGISTER_FIELDS' ),
        'TYPE' => 'MSELECT',
        'REFRESH' => 'N',
        'SORT' => '30',
        'SIZE' => '10',
        'SITE' => $site_id,
        'VALUES' => $activeFields
    );



	if(in_array('INN', $orderFields['REFERENCE_ID'])){
		$arOptions['GROUP_INN_USE_DEFAULT_'.$personTypeA['ID']] = array(
			'GROUP' => 'GROUP_SETTINGS_' . $countGroups,
			'TITLE' => Loc::getMessage( KitAuth::idModule . '_GROUP_INN_USE_DEFAULT' ),
			'TYPE' => 'CHECKBOX',
			'REFRESH' => 'Y',
			'SORT' => '0',
			'SIZE' => '10',
			'SITE' => $site_id,
		);
        $arOptions['FILE_DOCS_USE_DEFAULT_'.$personTypeA['ID']] = array(
            'GROUP' => 'GROUP_SETTINGS_' . $countGroups,
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_FILE_DOCS_USE_DEFAULT' ),
            'TYPE' => 'CHECKBOX',
            'REFRESH' => 'N',
            'SORT' => '10',
            'SIZE' => '10',
            'SITE' => $site_id,
        );
	}

    if(Option::get(KitAuth::idModule, "EXTENDED_VERSION_COMPANIES", 'N') == "Y"){
        $arOptions['GROUP_FIELDS_FOR_CHECK_'.$personTypeA['ID']] = array(
            'GROUP' => 'GROUP_SETTINGS_' . $countGroups,
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_GROUP_FIELDS_FOR_CHECK' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '60',
            'SIZE' => '10',
            'SITE' => $site_id,
            'VALUES' => $orderFields,
            'NOTES' => Loc::getMessage( KitAuth::idModule . '_GROUP_FIELDS_FOR_CHECK_NOTES' )
        );

        $arOptions['COMPANY_PROPS_GROUP_ID_'.$personTypeA['ID']] = array(
            'GROUP' => 'GROUP_SETTINGS_' . $countGroups,
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_COMPANY_PROPS_GROUP_ID' ),
            'TYPE' => 'MSELECT',
            'REFRESH' => 'N',
            'SORT' => '70',
            'SIZE' => '10',
            'SITE' => $site_id,
            'VALUES' => $orderPropsGroup,
            'DEFAULT' => $defaultGroupCompany ?: "",
        );

        $arOptions['COMPANY_PROPS_NAME_FIELD_'.$personTypeA['ID']] = array(
            'GROUP' => 'GROUP_SETTINGS_' . $countGroups,
            'TITLE' => Loc::getMessage( KitAuth::idModule . '_COMPANY_PROPS_NAME_FIELD' ),
            'TYPE' => 'SELECT',
            'REFRESH' => 'N',
            'SORT' => '70',
            'SIZE' => '10',
            'SITE' => $site_id,
            'VALUES' => $orderFields,
            'DEFAULT' => $nameFieldCode ?: "",
        );
    }
}


$RIGHT = $APPLICATION->GetGroupRight( KitAuth::idModule );
if( $RIGHT != "D" )
{
	$KitAuth = new KitAuth();
	if( $KitAuth->ReturnDemo() == 2 )
	{
		?>
        <div class="adm-info-message-wrap adm-info-message-red">
            <div class="adm-info-message">
                <div class="adm-info-message-title"><?=Loc::getMessage(KitAuth::idModule."_DEMO")?></div>
                <div class="adm-info-message-icon"></div>
            </div>
        </div>
        <?
	}
	if( $KitAuth->ReturnDemo() == 3 )
	{
		?>
        <div class="adm-info-message-wrap adm-info-message-red">
            <div class="adm-info-message">
                <div class="adm-info-message-title"><?=Loc::getMessage(KitAuth::idModule."_DEMO_END")?></div>
                <div class="adm-info-message-icon"></div>
            </div>
        </div>
        <?
        $APPLICATION->SetTitle( Loc::getMessage( KitAuth::idModule . '_TITLE' ) );
        require (Application::getDocumentRoot() . "/bitrix/modules/main/include/epilog_admin.php");
        return;
	}
	$showRightsTab = false;
	$opt = new \Kit\Auth\Option( KitAuth::idModule, $site_id, $arTabs, $arGroups, $arOptions, $showRightsTab );
	$opt->ShowHTML();
}
$APPLICATION->SetTitle( Loc::getMessage( KitAuth::idModule . '_TITLE' ) );
require (Application::getDocumentRoot() . "/bitrix/modules/main/include/epilog_admin.php");
?>