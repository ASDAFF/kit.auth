<?php

use Sotbit\Auth\Internals;
use Sotbit\Auth\Company\Company;
use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Main\Entity\Query;
define('MODULE_ID', 'sotbit.auth');
define('PROP_INN', 'INN');

if(!Loader::includeModule('iblock') || !Loader::includeModule('sale') || !Loader::includeModule(MODULE_ID)) {
    die();
}
Loc::loadMessages(__DIR__.'/sotbit.auth_company_edit.php');

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
global $APPLICATION, $DB;

$ID = (int)$_REQUEST["ID"];

if((false == defined('B_ADMIN_AJAX_LIST_STAFF')) || (1 != B_ADMIN_AJAX_LIST_STAFF)) {
    return '';
}
if(false == defined('B_ADMIN_AJAX_LIST_STAFF_LIST')) {
    return '';
}

$POST_RIGHT = $APPLICATION->GetGroupRight(SotbitAuth::idModule);
if($POST_RIGHT === "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if($_REQUEST['mode'] === 'frame') {
    CFile::DisableJSFunction(true);
}


require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/classes/general/subelement.php');
$sTableID = Internals\StaffTable::getTableName();
$arHideFields = ['ID'];

$strSubElementAjaxPath = '/bitrix/admin/sotbit.auth_company_list_staff_ajax.php?ID='.$ID.'&lang='.LANGUAGE_ID;
$strSubElementAjaxPath = trim($strSubElementAjaxPath);


/**
 * Data
 */
// Staff
$arStaff = [];
if(!empty($ID)) {
    $rsData = Internals\StaffTable::getList(
        [
            'select' => ['*', 'EMAIL' => 'USER.EMAIL', 'NAME' => 'USER.NAME', 'LAST_NAME' => 'USER.LAST_NAME'],
            'filter' => [
                'COMPANY_ID' => $ID,
            ],
            'order'  => ['ID' => 'DESC'],
        ]
    );
    while($arRes = $rsData->Fetch()) {
        $arStaff[$arRes['COMPANY_ID']][] = $arRes;
    }
}

// Roles
$arRoles = [];
if(!empty($ID)) {
    $rsData = Internals\RolesTable::getList();
    while($arRes = $rsData->Fetch()) {
        $arRoles[$arRes['ID']] = $arRes['CODE'];
        $arRolesLang[$arRes['ID']] = $arRes['NAME'];
    }
}

// User groups
$arUserGroups = [];
$result = \Bitrix\Main\GroupTable::getList(
    [
        'select' => ['NAME', 'ID', 'STRING_ID', 'C_SORT'],
    ]
);
while($arGroup = $result->fetch()) {
    $arUserGroups[$arGroup['ID']] = $arGroup;
}

// Company property
$arCompanyAllProps = [];
$arCompanyProps = [];
if($ID) {
    $rsData = Internals\CompanyPropsValueTable::getList();
    while($arRes = $rsData->Fetch()) {
        $arCompanyAllProps[$arRes['COMPANY_ID']][$arRes['PROPERTY_ID']] = $arRes;
        if($arRes['COMPANY_ID'] == $ID) {
            $arCompanyProps[$arRes['PROPERTY_ID']] = $arRes;
        }
    }
}

// Sale order props
$arSaleOrderProps = [];
$arSaleOrderPropsId = [];
$q = OrderPropsTable::getList(
    [
        'select' => ['*'],
        'filter' => ['ACTIVE' => 'Y'],
    ]
);
while($orderProp = $q->Fetch()) {
    $arSaleOrderProps[$orderProp['PERSON_TYPE_ID']][$orderProp['CODE']] = $orderProp;
    $arSaleOrderPropsId[$orderProp['PERSON_TYPE_ID']][$orderProp['ID']] = $orderProp;
}

if($ID) {
    // All company
    $companyList = [];
    $q = Internals\CompanyTable::getList(
        [
            'select' => ['ID', 'NAME'],
        ]
    );
    while($qc = $q->Fetch()) {
        $companyList[$qc['ID']] = $qc;
    }

    // All staff
    $staffList = [];
    $q = Internals\StaffTable::getList(
        [
            'select' => ['ID', 'USER_ID', 'COMPANY_ID'],
        ]
    );
    while($qc = $q->Fetch()) {
        $staffList[$qc['USER_ID']][] = $qc;
    }
}

/****************
 * Check data
 ****************/
if($ID > 0) {
    $companyRes = Internals\CompanyTable::getById($ID);
    $company = $companyRes->fetch();
    if(empty($company)) {
        LocalRedirect("/bitrix/admin/sotbit.auth_company_list.php?lang=".LANG);
    }
}

if(!empty($_REQUEST["NAME"])) {
    $company["NAME"] = $_REQUEST["NAME"];
}
if(!empty($_REQUEST["BUYER_TYPE"])) {
    $company["BUYER_TYPE"] = $_REQUEST["BUYER_TYPE"];
}
if(empty($company["BUYER_TYPE"]) && !empty($arSaleOrderProps)) {
    $company["BUYER_TYPE"] = array_keys($arSaleOrderProps)[0];
}


if(!empty($_REQUEST["STATUS"])) {
    $company["STATUS"] = $_REQUEST["STATUS"];
}

$companyProps = [];
if(!empty($arSaleOrderProps[$company['BUYER_TYPE']])) {
    foreach($arSaleOrderProps[$company['BUYER_TYPE']] as $propName => $propField) {
        if(isset($_REQUEST[$propName]) && $propField['TYPE'] === 'STRING') {
            $company[$propName] = $companyProps[$propName] = $_REQUEST[$propName];
        }
    }
}


$lAdmin = new CAdminSubList($sTableID, false, $strSubElementAjaxPath, false);

if($lAdmin->EditAction()) {
    $request = Main\Application::getInstance()->getContext()->getRequest();
    $postList = $request->getPostList()->toArray();
    foreach($postList['FIELDS'] as $fieldID => $arFields) {

        if($arFields['ROLE']) {
            $arFields['ROLE'] = [$arFields['ROLE']];
        }
        $fieldID = (int)$fieldID;

        if ($fieldID <= 0 || !$lAdmin->IsUpdated($fieldID))
            continue;

        $result = Internals\StaffTable::update($fieldID, $arFields);
        if(!$result->isSuccess()) {
            if ($ex = $APPLICATION->GetException())
                $lAdmin->AddUpdateError($ex->GetString(), $fieldID);
            else
                $lAdmin->AddUpdateError(str_replace("#ID#", $fieldID, GetMessage("SEO_META_SAVE_ERROR")), $fieldID);
        }

    }

}

if($arID = $lAdmin->GroupAction()) {
    //\Bitrix\Main\Diag\Debug::dump($_REQUEST);
    ////exit;
}


$lAdmin->AddHeaders(
    [
        [
            "id"      => "ID",
            "content" => "ID",
            "align"   => "right",
            "default" => true,
        ],
        [
            "id"      => "NAME",
            "content" => Loc::getMessage(SotbitAuth::idModule."_EDIT_STAFF_NAME"),
            "default" => true,
        ],
        [
            "id"      => "EMAIL",
            "content" => Loc::getMessage(SotbitAuth::idModule."_EDIT_STAFF_EMAIL"),
            "default" => true,
        ],
        [
            "id"      => "ROLE",
            "content" => Loc::getMessage(SotbitAuth::idModule."_EDIT_STAFF_ROLE"),
            "default" => true,
        ],
        [
            "id"      => "GROUPS",
            "content" => Loc::getMessage(SotbitAuth::idModule."_EDIT_STAFF_GROUPS"),
            "default" => true,
        ],
        [
            "id"      => "COMPANY",
            "content" => Loc::getMessage(SotbitAuth::idModule."_EDIT_STAFF_COMPANY"),
            "default" => true,
        ],
        [
            "id"      => "STATUS",
            "content" => Loc::getMessage(SotbitAuth::idModule."_EDIT_STAFF_STATUS"),
            "default" => true,
        ],
    ]
);




$totalCount = 0;
$countQuery = new Query(Internals\StaffTable::getEntity());
$countQuery->addSelect(new ExpressionField('CNT', 'COUNT(1)'));
$countQuery->setFilter(['COMPANY_ID' => $ID]);
$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
unset($countQuery);
$totalCount = (int)$totalCount['CNT'];


$rsData = new CAdminSubResult($arStaff[$ID], $sTableID, $lAdmin->GetListUrl(true));
$rsData->NavPageCount = 1;
$rsData->NavPageNomer = 1;
$rsData->NavRecordCount = $totalCount;
$rsData->NavStart();


while($arRes = $rsData->NavNext(true, "")) {

    if(!empty($_REQUEST["FIELDS"][$arRes["ID"]]["STATUS"])){
        $arRes["STATUS"] = $_REQUEST["FIELDS"][$arRes["ID"]]["STATUS"];
    }

    $row =& $lAdmin->AddRow($arRes['ID'], $arRes);

    if(!empty($arRolesLang)) {
        //$row->AddSelectField("ROLE", $arRolesLang);
        $viewRoles = '<select name="FIELDS['.$arRes['ID'].'][ROLE]">';
        foreach($arRolesLang as $idRole => $valRole) {
            $viewRoles .= '<option value="'.$idRole.'"';

            if($idRole == $arRes['ROLE'][0])
            {
                $viewRoles .= ' selected';
            }
            $viewRoles .= '>'.$valRole.'</option>';
        }
        $viewRoles .= '</select><br>';

        $row->AddEditField(
            "ROLE", $viewRoles
        );
    }



    $arActions = [];

    $row->AddViewField(
        "NAME",
        '['.$arRes['USER_ID'].'] <a href="/bitrix/admin/user_edit.php?ID='.$arRes['USER_ID'].'&lang='.LANG.'">'
        .$arRes['NAME'].' '.$arRes['LAST_NAME']
        .'</a>'
    );

    $row->AddViewField(
        "EMAIL",
        $arRes['EMAIL']
    );


    $viewUserRoles = '';
    if(!empty($_REQUEST["FIELDS"][$arRes["ID"]]["ROLE"])){
        $viewUserRoles  .= $arRolesLang[$_REQUEST["FIELDS"][$arRes["ID"]]["ROLE"]];
    }
    elseif(!empty($arRes['ROLE'])) {
        foreach($arRes['ROLE'] as $role) {
            $viewUserRoles .= $arRolesLang[$role];
        }
    }

    $row->AddViewField(
        "ROLE",
        $viewUserRoles
    );


    $viewUserGroup = '';
    $getUserGroup = \CUser::GetUserGroup($arRes['USER_ID']);
    if($getUserGroup) {
        foreach($getUserGroup as $groupId) {
            if(!empty($arUserGroups) && !empty($arUserGroups[$groupId])) {
                $viewUserGroup .= '- '.$arUserGroups[$groupId]['NAME'].'<br>';
            }
        }
    }

    $row->AddViewField(
        "GROUPS",
        $viewUserGroup
    );


    $viewUserCompany = '';
    $innPropId = $arSaleOrderProps[$company['BUYER_TYPE']][PROP_INN]['ID'];
    $getUserCompany = $staffList[$arRes['USER_ID']];

    foreach($getUserCompany as $currentCompany) {
        $companyId = $currentCompany['COMPANY_ID'];
        if(!empty($currentCompany) && !empty($companyList[$companyId])) {
            $inn = $arCompanyAllProps[$companyId][$innPropId];
            $viewUserCompany .= '<a href="sotbit.auth_company_edit.php?ID='.$companyId.'&lang='.LANGUAGE_ID.'">'
                .$companyList[$companyId]['NAME'].'</a> 
                ['.$inn['NAME'].': '.$inn['VALUE'].']
                <br>';
        }
    }

    $row->AddViewField(
        "COMPANY",
        $viewUserCompany
    );


    $row->AddCheckField("STATUS");

    // action menu
    /*$arActions[] = [
        "ICON"    => "edit",
        "DEFAULT" => true,
        "TEXT"    => Loc::getMessage(SotbitAuth::idModule."_EDIT"),
        "ACTION"  => $lAdmin->ActionRedirect(
            ''
        ),
    ];*/
    $arActions[] = [
        "ICON"    => "delete",
        "DEFAULT" => true,
        "TEXT"    => Loc::getMessage(SotbitAuth::idModule."_EDIT_DEL"),
        "ACTION"  => $lAdmin->ActionRedirect(
            $APPLICATION->GetCurPageParam("DEL_STAFF=".$arRes['ID'], ["ADD_STAFF", "DEL_STAFF"])
        ),
    ];


    $arActions[] = ["SEPARATOR" => true];
    if(is_set($arActions[count($arActions) - 1], "SEPARATOR")) {
        unset($arActions[count($arActions) - 1]);
    }

    $row->AddActions($arActions);
}

if(isset($row)) {
    unset($row);
}

$lAdmin->AddFooter(
    [
        ["title" => Loc::getMessage(SotbitAuth::idModule."_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()],
        ["counter" => true, "title" => Loc::getMessage(SotbitAuth::idModule."_LIST_CHECKED"), "value" => "0"],
    ]
);
/*$lAdmin->AddGroupActionTable(
    [
        "delete"     => Loc::getMessage(SotbitAuth::idModule."_LIST_DELETE"),
        //"copy"=>Loc::getMessage(SotbitAuth::idModule."_LIST_COPY"),
        "activate"   => Loc::getMessage(SotbitAuth::idModule."_LIST_ACTIVATE"),
        "deactivate" => Loc::getMessage(SotbitAuth::idModule."_LIST_DEACTIVATE"),
    ]
);*/

$aContext = [
    [
        "TEXT"    => Loc::getMessage(SotbitAuth::idModule."_ADD_STAFF"),
        "LINK"    => "",
        "TITLE"   => Loc::getMessage(SotbitAuth::idModule."_ADD_STAFF"),
        "ICON"    => "btn_new",
        "ONCLICK" => "window.open('/bitrix/admin/user_search.php?lang=ru&FN=sotbit_auth_add_company_form&FC=STAFF', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));",
    ],
];

$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();
$lAdmin->DisplayList();