<?

use Bitrix\Main\Config\Option;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Sotbit\Auth\Company;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$request = Main\Application::getInstance()->getContext()->getRequest();

$personTypeID = [];
if(!empty($arResult['PROFILES'])) {
    $personTypeID = array_unique(
        array_map(
            function($v) {
                return (int)$v['PERSON_TYPE_ID'];
            },
            $arResult['PROFILES']
        )
    );
}

$arFilter = [
    'USER_PROPS'     => 'Y',
    'ACTIVE'         => 'Y',
    'UTIL'           => 'N',
    'PERSON_TYPE_ID' => $personTypeID,
];
$orderPropertiesList = CSaleOrderProps::GetList(
    [
        "SORT" => "ASC",
        "NAME" => "ASC",
    ],
    $arFilter,
    false,
    false,
    [
        "ID",
        "PERSON_TYPE_ID",
        "NAME",
        "TYPE",
        "CODE",
        "PROPS_GROUP_ID",
        'USER_PROPS',
        'ACTIVE',
        'CODE',
    ]
);
$arOrderPropertyList = [];

while($orderProperty = $orderPropertiesList->GetNext()) {
    if(is_array($personTypeID) && !in_array($orderProperty['PERSON_TYPE_ID'], $personTypeID)) {
        continue;
    }
    $arOrderPropertyList[$orderProperty['ID']] = $orderProperty;
    if(in_array(
        $orderProperty['CODE'],
        [
            'ID',
            'NAME',
            'DATE_UPDATE',
            'PERSON_TYPE_NAME',
        ]
    )
    ) {
        $arParams['GRID_HEADER'][] = [
            'id'       => $orderProperty['CODE'],
            'name'     => $orderProperty['NAME'],
            'sort'     => $orderProperty['CODE'],
            'default'  => false,
            'editable' => false,
        ];
    }
}
$arParams['GRID_HEADER'][] = [
    'id'       => 'ACTIVE',
    'name'     => Loc::getMessage('P_DATE_ACTIVE'),
    'sort'     => 'ACTIVE',
    'default'  => true,
    'editable' => false,
];

$arParams['GRID_HEADER'][] = [
    'id'       => 'STATUS',
    'name'     =>  Loc::getMessage('P_DATE_STATUS'),
    'sort'     => 'STATUS',
    'default'  => true,
    'editable' => false,
];

/*if(is_array($personTypeID)) {
    foreach($arResult["PROFILES"] as $key => $val) {
        if(!in_array($val['PERSON_TYPE_ID'], $personTypeID)) {
            unset($arResult["PROFILES"][$key]);
        }
    }
}*/
$arResult['ROWS'] = [];

foreach($arResult["PROFILES"] as $val) {
    $aActions = [];

    $aActions[] = [
        "ICONCLASS" => "detail",
        "TEXT"      => GetMessage('SPOL_DETAIL_PROFIL'),
        "ONCLICK"   => "jsUtils.Redirect(arguments, '".$val["URL_TO_DETAIL"]."')",
        "DEFAULT"   => true,
    ];

    if(Company\Company::isUserAdmin((int)($USER->GetID()), $val['ID']) && $val['STATUS'] === 'A') {
        $aActions[] = [
            "ICONCLASS" => "detail",
            "TEXT"      => GetMessage('SPOL_DETAIL_EDIT'),
            "ONCLICK"   => "jsUtils.Redirect(arguments, '".dirname($val["URL_TO_DETAIL"]).'/add.php?EDIT_ID='.$val["ID"]
                ."')",
            "DEFAULT"   => true,
        ];
    }


    if($val['ACTIVE'] == 'N') {
        $val['ACTIVE'] = Loc::getMessage('P_DATE_ACTIVE_N');
    }
    else{
        $val['ACTIVE'] = Loc::getMessage('P_DATE_ACTIVE_Y');
    }

    $val['STATUS'] = Loc::getMessage('COMPANY_LIST_STATUS_' . $val['STATUS']);

    $arResult['ROWS'][] = [
        'data'     => $val,
        'actions'  => $aActions,
        'COLUMNS'  => $val,
        'editable' => true,
    ];
}

if(isset($_GET['by'])
    && in_array(
        $_GET['by'],
        [
            'ID',
            'NAME',
            'DATE_UPDATE',
            'PERSON_TYPE_NAME',
            'STATUS',
            'ACTIVE',
        ]
    )
) {
    $by = $_GET['by'];
    $order = in_array(
        $_GET['order'],
        [
            'asc',
            'ASC',
            'desc',
            'DESC',
        ]
    ) ? strtolower($_GET['order']) : 'asc';

    for($i = 0; $i < count($arResult['ROWS']); $i++) {
        for($j = 0; $j < count($arResult['ROWS']) - 1; $j++) {
            $change = false;
            $t = [];

            if($order == 'desc' && strcmp($arResult['ROWS'][$i]['data'][$by], $arResult['ROWS'][$j]['data'][$by]) > 0) {
                $change = true;
            } elseif($order == 'asc'
                && strcmp($arResult['ROWS'][$i]['data'][$by], $arResult['ROWS'][$j]['data'][$by]) < 0
            ) {
                $change = true;
            }

            if($change) {
                $t = $arResult['ROWS'][$j];
                $arResult['ROWS'][$j] = $arResult['ROWS'][$i];
                $arResult['ROWS'][$i] = $t;
            }
        }
    }
}