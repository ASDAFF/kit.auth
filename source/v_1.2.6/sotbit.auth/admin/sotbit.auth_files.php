<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Sotbit\Auth\Internals\FileTable;
use Bitrix\Main\Config\Option;

require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$id_module = 'sotbit.auth';
if( !Loader::includeModule( "sotbit.auth" ) || !Loader::includeModule( "sale" ))
{
	return;
}

Loc::loadMessages(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight ( $id_module );
if ($POST_RIGHT == "D")
	$APPLICATION->AuthForm ( Loc::getMessage ( "ACCESS_DENIED" ) );

//$SotbitAuth = new SotbitAuth();
//if( !$SotbitAuth->getDemo() )
//{
//	return false;
//}

$sTableID = "b_sotbit_auth";
$oSort = new CAdminSorting ( $sTableID, "BUYER_ID", "desc" );
$lAdmin = new CAdminList ( $sTableID, $oSort );
function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ( $FilterArr as $f )
		global $$f;
	return count ( $lAdmin->arFilterErrors ) == 0;
}

$FilterArr = Array (
		"find",
		"find_buyer",
);

$lAdmin->InitFilter ( $FilterArr );
$arFilter = array ();

if (CheckFilter ())
{
	$arFilter['BUYER_ID'] = $find_buyer_id;

	if (empty ( $arFilter['BUYER_ID'] ))
		unset ( $arFilter['BUYER_ID'] );
}

if ($arID = $lAdmin->GroupAction ())
{
	foreach ( $arID as $ID )
	{
		if (strlen ( $ID ) <= 0)
			continue;
		$ID = IntVal ( $ID );
		switch ($_REQUEST["action"])
		{
			case "delete" :
				$record = Application::getConnection()->query('DELETE FROM '.FileTable::getTableName().' WHERE BUYER_ID='.$ID);
				break;
		}
	}
}

$show = "all";
$arResult = [];
$idBuyers = [];

$rsData = FileTable::getList ( array (
		'order' => array (
				$by => $order 
		) 
) );

while ( $arRes = $rsData->Fetch () )
{
	$src = \CFile::GetPath($arRes['FILE_ID']);
    $name = \CFile::GetByID($arRes['FILE_ID'])->fetch();
    $name = $name['ORIGINAL_NAME'];

	$file = '<a href="'.$src.'" download="'. $name .'">'. $name .'</a><br>';
	if($arResult[$arRes['BUYER_ID']])
	{
		$arRes['FILE_ID'] = $arResult[$arRes['BUYER_ID']]['FILE_ID'].$file;
	}
	else
	{
		$arRes['FILE_ID'] = $file;
	}
	$arResult[$arRes['BUYER_ID']] = $arRes;
	$idBuyers[$arRes['BUYER_ID']] = $arRes['BUYER_ID'];
}

$buyers = [];

if($idBuyers)
{
	$inns = [];

	$rs = \Bitrix\Main\SiteTable::getList(array(
		'select' => array('LID'),
	));
	while($site = $rs->fetch())
	{
		$pts = unserialize(Option::get('sotbit.auth','WHOLESALERS_PERSON_TYPE','',$site['LID']));
		if(!is_array($pts))
		{
			$pts = [];
		}
		if($pts)
		{
			foreach($pts as $pt)
			{
				$inn = Option::get('sotbit.auth','GROUP_ORDER_INN_FIELD_'.$pt,'',$site['LID']);
				if($inn)
				{
					$pInn = \CSaleOrderProps::GetList([],['CODE' => $inn,'PERSON_TYPE_ID' => $pt])->Fetch();
					if($pInn)
					{
						$inns[$pInn['ID']] = $pInn['ID'];
					}
				}
			}
		}
	}
	$rs = CSaleOrderUserPropsValue::GetList(
		array(),
		array("USER_PROPS_ID" => $idBuyers,'ORDER_PROPS_ID' => $inns),
		false,
		false,
		array("*")
	);
	while($buyer = $rs->Fetch())
	{
		if($arFilter && $arFilter['BUYER_ID'] != $buyer['VALUE'])
		{
			unset($arResult[$buyer['USER_PROPS_ID']]);
		}
		$buyers[$buyer['USER_PROPS_ID']] = $buyer['VALUE'];
	}
}
if($buyers)
{
	if($order == 'asc')
	{
		asort($buyers);
	}
	else
	{
		arsort($buyers);
	}

	$tmp = $arResult;
	$arResult = [];
	foreach ($buyers as $key => $v)
	{
		$arResult[$key] = $tmp[$key];
	}
}
$rsData = new CAdminResult ( $arResult, $sTableID );

$rsData->NavStart ();

$lAdmin->NavText ( $rsData->GetNavPrint ( Loc::getMessage ( $id_module."_NAV" ) ) );

$lAdmin->AddHeaders ( array (
		array (
				"id" => "BUYER_ID",
				"content" => Loc::getMessage ( $id_module."_BUYER_ID" ),
				"sort" => "BUYER_ID",
				"align" => "right",
				"default" => true 
		),
		array (
				"id" => "FILE_ID",
				"content" => Loc::getMessage ( $id_module."_FILES" ),
				"sort" => "",
				"default" => true
		),
) );
while ( $arRes = $rsData->NavNext ( true, "f_" ) )
{
	$row = & $lAdmin->AddRow ( $f_T . $arRes['BUYER_ID'], $arRes );
	$row->AddViewField ( "BUYER_ID", $buyers[$arRes['BUYER_ID']] );
	$row->AddViewField ( "FILE_ID", $arRes['FILE_ID'] );
	$arActions = Array ();
	$row->AddActions ( $arActions );
}


$lAdmin->AddFooter ( array (
		array (
				"title" => Loc::getMessage ( $id_module."_LIST_SELECTED" ),
				"value" => $rsData->SelectedRowsCount () 
		),
		array (
				"counter" => true,
				"title" => Loc::getMessage ( $id_module."_LIST_CHECKED" ),
				"value" => "0" 
		) 
) );

$lAdmin->AddGroupActionTable ( Array (
		"delete" => Loc::getMessage ( $id_module."_LIST_DELETE" ),
) );

$aContext = [];

$lAdmin->AddAdminContextMenu ( $aContext );

$lAdmin->CheckListMode ();

$APPLICATION->SetTitle ( Loc::getMessage ( $id_module."_TITLE" ) );

require (Application::getDocumentRoot() . "/bitrix/modules/main/include/prolog_admin_after.php");

$SotbitAuth = new SotbitAuth();
if( $SotbitAuth->ReturnDemo() == 2 )
{
    ?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <div class="adm-info-message">
            <div class="adm-info-message-title"><?=Loc::getMessage(SotbitAuth::idModule."_DEMO")?></div>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
    <?
}
if( $SotbitAuth->ReturnDemo() == 3 )
{
    ?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <div class="adm-info-message">
            <div class="adm-info-message-title"><?=Loc::getMessage(SotbitAuth::idModule."_DEMO_END")?></div>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
    <?
    require (Application::getDocumentRoot() . "/bitrix/modules/main/include/epilog_admin.php");
    return;
}

$oFilter = new CAdminFilter ( $sTableID . "_filter", array (
		Loc::getMessage ( $id_module."_BUYER_ID" )
) );
?>
<form name="find_form" method="get"
	action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
	<tr>
		<td><?=Loc::getMessage($id_module."_BUYER_ID")?>:</td>
		<td><input type="text" name="find_buyer_id" size="47"
			value="<?echo htmlspecialchars($find_buyer_id)?>"></td>
	</tr>
<?
$oFilter->Buttons ( array (
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage (),
		"form" => "find_form" 
) );
$oFilter->End ();
?>
</form>

<?

$lAdmin->DisplayList ();

require (Application::getDocumentRoot() . "/bitrix/modules/main/include/epilog_admin.php");
?>