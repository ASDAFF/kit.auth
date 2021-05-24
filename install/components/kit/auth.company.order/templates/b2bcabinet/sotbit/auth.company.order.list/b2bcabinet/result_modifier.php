<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

foreach($arResult["ORDERS"] as $val)
{
    $arResult["ORDER_BY_STATUS"][$val["ORDER"]["STATUS_ID"]][] = $val;
}

$methodIstall = Option::get('kit.b2bcabinet', 'method_install', '', SITE_ID) == 'AS_TEMPLATE' ? SITE_DIR.'b2bcabinet/' : SITE_DIR;

$filterOption = new Bitrix\Main\UI\Filter\Options('ORDER_LIST');
$filterData = $filterOption->getFilter([]);
$arResult['FILTER_STATUS_NAME'] = (isset($filterData['STATUS'])) ? $arResult['INFO']['STATUS'][$filterData['STATUS']]['NAME']: '';

$buyers = [];
$pt = unserialize(Option::get("kit.b2bcabinet","BUYER_PERSONAL_TYPE","a:0:{}"));
if(!is_array($pt))
{
    $pt = [];
}

$innProps = unserialize(Option::get('kit.b2bcabinet', 'PROFILE_ORG_INN'));
if(!is_array($innProps))
{
    $innProps = [];
}


$orgProps = unserialize(Option::get('kit.b2bcabinet', 'PROFILE_ORG_NAME'));
if(!is_array($orgProps))
{
    $orgProps = [];
}

$arResult['BUYERS'] = [];
$orgs = [];
$idOrders = [];

foreach($arResult['ORDERS'] as $key => $arOrder)
{

    if(isset($filterData['FIND']) && !empty($filterData['FIND']) && $filterData['FIND'] != $arOrder['ORDER']['ID']) {
        unset($arResult['ORDERS'][$key]);
        continue;
    }

    $idOrders[] = $arOrder['ORDER']['ID'];
}

$rs = \Bitrix\Sale\Internals\OrderPropsValueTable::getList([
    'filter' => [
        'ORDER_ID' => $idOrders,
        'ORDER_PROPS_ID' => $innProps
    ]
]);
while($org = $rs->fetch())
{
    $company = \Kit\Auth\Internals\CompanyPropsValueTable::getList([
        'filter' => ['VALUE'=>$org["VALUE"]],
        'select' => ['COMPANY_ID', 'COMPANY_NAME' => 'COMPANY.NAME'],
    ])->fetch();

    if($company["COMPANY_ID"]){
        $name =  $company["COMPANY_NAME"];
        $name .= ( $org["VALUE"])?' ('. $org["VALUE"].')':'';
        $orgs[$org['ORDER_ID']] = '<a href="'. $methodIstall .'personal/companies/profile_detail.php?ID='. $company["COMPANY_ID"] .'">'. $name .'</a>';
    }
}

$dbstatus = CSaleStatus::GetList(
    [], [] , false, false, ["ID", "NAME"]
);
while($resultStatus = $dbstatus->Fetch()){
    $arResult["ORDER_STATUS"][$resultStatus["ID"]] = $resultStatus["NAME"];
}

$dbpaySystem = CSalePaySystem::GetList(
    [], [] , false, false, ["ID", "NAME"]
);
while($resultPaySystem = $dbpaySystem->Fetch()){
    $arResult["PAY_SYSTEM"][$resultPaySystem["ID"]] = $resultPaySystem["NAME"];
}

$dbDelivery = CSaleDelivery::GetList(
    [], [] , false, false, ["ID", "NAME"]
);
while($resultDelivery = $dbDelivery->Fetch()){
    $arResult["DELIVERY"][$resultDelivery["ID"]] = $resultDelivery["NAME"];
}


$defaultFilter =  array(
    'ID',
    'DATE_INSERT_to',
    'DATE_INSERT_from',
    'STATUS_ID',
    'PAYED',
    'PAY_SYSTEM_ID',
    'DELIVERY_ID',
    'FIND',
    'BUYER'
);

$filter = [];
$filterOption = new Bitrix\Main\UI\Filter\Options( 'ORDER_LIST' );
$filterData = $filterOption->getFilter( [] );
foreach( $filterData as $key => $value )
{
    if( in_array($key, $defaultFilter) && !empty($value))
        $filter[$key] = $value;
}


if( $filterData['BUYER'] )
{
    $orders = [];
    $rs = \Bitrix\Sale\Internals\OrderTable::getList( [
        'filter' => [
            'USER_ID' => $USER->GetID()
        ]
    ] );
    while ( $order = $rs->fetch() )
    {
        $orders[] = $order['ID'];
    }
    if( $orders )
    {
        $innV = [];
        $innProps = unserialize( Bitrix\Main\Config\Option::get( 'kit.b2bcabinet', 'PROFILE_ORG_INN' ) );
        if( !is_array( $innProps ) )
        {
            $innProps = [];
        }
        $rs = \Bitrix\Sale\Internals\UserPropsValueTable::getList( array(
            'filter' => array(
                "USER_PROPS_ID" => $filterData['BUYER'],
                'ORDER_PROPS_ID' => $innProps
            ),
            "select" => array(
                "ORDER_PROPS_ID",
                'USER_PROPS_ID',
                'VALUE'
            )
        ) );
        while ( $buyer = $rs->fetch() )
        {
            $innV[] = $buyer['VALUE'];
        }

        $rOrders = [];
        $rs = \Bitrix\Sale\Internals\OrderPropsValueTable::getList( [
            'filter' => [
                'ORDER_ID' => $orders,
                'ORDER_PROPS_ID' => $innProps,
                'VALUE' => $innV
            ]
        ] );
        while ( $v = $rs->fetch() )
        {
            $rOrders[] = $v['ORDER_ID'];
        }

    }
}


foreach($arResult['ORDERS'] as $arOrder)
{
    if($filter){
        $continue = false;

        if($filter["BUYER"] && empty($rOrders)){
            $continue = true;
        }
        elseif($filter["BUYER"] && !empty($rOrders)){
            if(!in_array($arOrder["ORDER"]["ID"], $rOrders)){
                $continue = true;
            }
        }

        if($filter["DATE_INSERT_to"] && $filter["DATE_INSERT_from"]){
            if($arOrder["ORDER"]["DATE_INSERT"]->toString()>=$filter["DATE_INSERT_from"] && $arOrder["ORDER"]["DATE_INSERT"]->toString()<=$filter["DATE_INSERT_to"]){
                $continue = false;
            }
            else{
                $continue = true;
            }
        }

        foreach ($filter as $code => $value){
            if($code == "ID" && $arOrder["ORDER"]["ID"] != $value) {
                $continue = true;
                break;
            }
            if($code == "STATUS_ID" && $arOrder["ORDER"]["STATUS_ID"] != $value) {
                $continue = true;
                break;
            }
            if($code == "PAYED" && $arOrder["ORDER"]["PAYED"] != $value) {
                $continue = true;
                break;
            }
            if($code == "PAY_SYSTEM_ID" && $arOrder["ORDER"]["PAY_SYSTEM_ID"] != $value) {
                $continue = true;
                break;
            }
            if($code == "DELIVERY_ID" && $arOrder["ORDER"]["DELIVERY_ID"] != $value) {
                $continue = true;
                break;
            }
            if($code == "FIND" && $arOrder["ORDER"]["ID"] != $value) {
                $continue = true;
                break;
            }
        }
    }

    if($continue){
        continue;
    }

    $aActions = Array(
        array("ICONCLASS"=>"detail", "TEXT"=>GetMessage('SPOL_MORE_ABOUT_ORDER'), "ONCLICK"=>"jsUtils.Redirect(arguments, '".$arOrder['ORDER']["URL_TO_DETAIL"]."')", "DEFAULT"=>true),
//		array("ICONCLASS"=>"copy", "TEXT"=>GetMessage('SPOL_REPEAT_ORDER'), "ONCLICK"=>"jsUtils.Redirect(arguments, '".$arOrder['ORDER']["URL_TO_COPY"]."')", "DEFAULT"=>true),
//		array("SEPARATOR"=>true),
//		array("ICONCLASS"=>"cancel", "TEXT"=>GetMessage('SPOL_CANCEL_ORDER'), "ONCLICK"=>"if(confirm('".GetMessage('SPOL_CONFIRM_DEL_ORDER')."')) window.location='".$arOrder['ORDER']["URL_TO_CANCEL"]."';"),
    );

    if(is_array($allowActions))
        foreach($allowActions as $licence)
            array_push($aActions, GetAction($licence, $arOrder));

    $payment = current($arOrder['PAYMENT']);
    $shipment = current($arOrder['SHIPMENT']);

    $aCols = array(
        "ID" => $arOrder['ORDER']["ID"],
        "DATE_INSERT" => $arOrder['ORDER']['DATE_INSERT']->toString(),
        'ACCOUNT_NUMBER' => $arOrder['ORDER']['ACCOUNT_NUMBER'],
        "DATE_UPDATE" => $arOrder['ORDER']['DATE_UPDATE']->toString(),
        'STATUS' => ($arOrder['ORDER']['CANCELED'] == 'Y' ? Loc::GetMessage('SPOL_PSEUDO_CANCELLED') : $arResult['INFO']['STATUS'][$arOrder['ORDER']['STATUS_ID']]['NAME']),
        'PAYED' => $arOrder["ORDER"]["PAYED"],
        'PAY_SYSTEM_ID' => $arOrder["ORDER"]["PAY_SYSTEM_ID"],
        'BUYER' => $orgs[$arOrder['ORDER']['ID']]
    );

    $items = array();
    $index = 1;
    foreach ($arOrder['BASKET_ITEMS'] as $item)
    {
        array_push($items, $index++.". $item[NAME] - ($item[QUANTITY] $item[MEASURE_TEXT])");
    }

    $arResult['ROWS'][] = array(
        'data' =>array_merge($arOrder['ORDER'], array(
            "SHIPMENT_METHOD" => $arResult["INFO"]["DELIVERY"][$arOrder["ORDER"]["DELIVERY_ID"]]["NAME"],
            "PAYMENT_METHOD" => $arResult["INFO"]["PAY_SYSTEM"][$arOrder["ORDER"]["PAY_SYSTEM_ID"]]["NAME"],
            'ITEMS' => implode('<br>', $items),
            'STATUS' => ($arOrder['ORDER']['CANCELED'] == 'Y' ? Loc::GetMessage('SPOL_PSEUDO_CANCELLED') : $arResult['INFO']['STATUS'][$arOrder['ORDER']['STATUS_ID']]['NAME']),
            'PAYED' => GetMessage('SPOL_'.($arOrder["ORDER"]["PAYED"] == "Y" ? 'YES' : 'NO')),
            'PAY_SYSTEM_ID' => $arOrder["ORDER"]["PAY_SYSTEM_ID"],
            'DELIVERY_ID' => $arOrder["ORDER"]["DELIVERY_ID"],
            'BUYER' => $orgs[$arOrder['ORDER']['ID']]
        ) ),
        'actions' => $aActions,
        'COLUMNS' => $aCols,
        'editable' => true,
    );
}

function GetAction($key, $arOrder)
{
    $arAction = array(
        'repeat' => array("ICONCLASS"=>"copy", "TEXT"=>GetMessage('SPOL_REPEAT_ORDER'), "ONCLICK"=>"jsUtils.Redirect(arguments, '".$arOrder['ORDER']["URL_TO_COPY"]."')", "DEFAULT"=>true),
        'cancel' => array("ICONCLASS"=>"cancel", "TEXT"=>GetMessage('SPOL_CANCEL_ORDER'), "ONCLICK"=>"if(confirm('".GetMessage('SPOL_CONFIRM_DEL_ORDER')."')) window.location='".$arOrder['ORDER']["URL_TO_CANCEL"]."';"),
    );

    return $arAction[$key];
}