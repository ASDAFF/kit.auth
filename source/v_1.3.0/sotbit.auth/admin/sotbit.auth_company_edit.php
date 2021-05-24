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


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

define('MODULE_ID', 'sotbit.auth');
define ( 'B_ADMIN_AJAX_LIST_STAFF', 1 );
define ( 'B_ADMIN_AJAX_LIST_STAFF_LIST', false );
define('PROP_INN', 'INN');
define('PROP_KPP', 'KPP');


if(!Loader::includeModule('iblock') || !Loader::includeModule('sale') || !Loader::includeModule(MODULE_ID)) {
    die();
}

$ID = (int)$_REQUEST["ID"];


$POST_RIGHT = $APPLICATION->GetGroupRight(MODULE_ID);
if($POST_RIGHT === "D") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

Loc::loadMessages(__FILE__);




$aTabs = [
    [
        "DIV"   => "edit1",
        "TAB"   => Loc::getMessage(SotbitAuth::idModule."_EDIT_TAB_ORGANIZATION"),
        "ICON"  => "main_user_edit",
        "TITLE" => Loc::getMessage(SotbitAuth::idModule."_EDIT_TAB_ORGANIZATION_TITLE"),
    ],
    [
        "DIV"   => "edit2",
        "TAB"   => Loc::getMessage(SotbitAuth::idModule."_EDIT_TAB_STAFF"),
        "ICON"  => "main_user_edit",
        "TITLE" => Loc::getMessage(SotbitAuth::idModule."_EDIT_TAB_STAFF_TITLE"),
    ],
];

$tabControl = new CAdminForm("sotbit_auth_add_company", $aTabs);

/****************
 * Data
 ****************/
$ID = (int) $ID;


if($ID) {
    // All company
    $companyList = [];
    $q = Internals\CompanyTable::getList([
        'select' => ['ID', 'NAME']
    ]);
    while ($qc = $q->Fetch()) {
        $companyList[$qc['ID']] = $qc;
    }

    // All staff
    $staffList = [];
    $q = Internals\StaffTable::getList([
         'select' => ['ID', 'USER_ID', 'COMPANY_ID']
     ]);
    while ($qc = $q->Fetch()) {
        $staffList[$qc['USER_ID']][] = $qc;
    }
}

// Buyer types
$arrBuyerTypes = [];
$q = \CSalePersonType::GetList(Array("SORT" => "ASC"), Array());
while ($typeBuyers = $q->Fetch())
{
    $arrBuyerTypes[$typeBuyers['ID']] = $typeBuyers['NAME'];
}


// Sale order props
$arSaleOrderProps = [];
$arSaleOrderPropsId = [];
$q = OrderPropsTable::getList(
    [
        'select' => ['*'],
        'filter' => ['ACTIVE' => 'Y']
    ]
);
while($orderProp = $q->Fetch())
{
    $arSaleOrderProps[$orderProp['PERSON_TYPE_ID']][$orderProp['CODE']] = $orderProp;
    $arSaleOrderPropsId[$orderProp['PERSON_TYPE_ID']][$orderProp['ID']] = $orderProp;
}



// Staff
$arStaff = [];
if(!empty($ID)) {
    $rsData = Internals\StaffTable::getList(
        [
            'select' => ['*', 'EMAIL' => 'USER.EMAIL', 'NAME' => 'USER.NAME', 'LAST_NAME' => 'USER.LAST_NAME'],
            'filter' => [
                'COMPANY_ID' => $ID,
                'STATUS' => 'Y'
            ],
            'order'  => ['ID' => 'DESC'],
        ]
    );
    while($arRes = $rsData->Fetch()) {
        $arStaff[$arRes['COMPANY_ID']][] = $arRes;
    }
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

if(isset($_REQUEST["ACTIVE"])) {
    $company['ACTIVE'] = ($_REQUEST["ACTIVE"] !== "Y" ? "N" : "Y");
}
if(isset($_REQUEST["STATUS"])) {
    $company['STATUS'] = $_REQUEST["STATUS"];
}



$companyProps = [];
if(!empty($arSaleOrderProps[$company['BUYER_TYPE']])) {
    foreach($arSaleOrderProps[$company['BUYER_TYPE']] as $propName => $propField) {

        if(isset($_REQUEST[$propName]) && $propField['TYPE'] !== 'FILE') {
            $company[$propName] = $companyProps[$propName] = $_REQUEST[$propName];
        }
    }
}

/****************
 * Action
 ****************/
// Delete
if($_REQUEST['action'] === 'delete' && $ID > 0) {
    // company props
    $props = Internals\CompanyPropsValueTable::getList(['filter' => ['COMPANY_ID' => $ID]]);
    while($prop = $props->fetch()) {
        Internals\CompanyPropsValueTable::delete($prop['ID']);
    }

    // company staff
    $props = Internals\StaffTable::getList(['filter' => ['COMPANY_ID' => $ID]]);
    while($prop = $props->fetch()) {
        Internals\StaffTable::delete($prop['ID']);
    }

    // company
    $result = Internals\CompanyTable::delete($ID);

    if($result->isSuccess()) {
        LocalRedirect('/bitrix/admin/sotbit.auth_company_list.php?lang='.LANGUAGE_ID);
    }
}

// Add staff action
if(!empty($_REQUEST['ADD_STAFF'])) {
    $userId = (int) $_REQUEST['ADD_STAFF'];

    if($userId) {
        $userId = \Bitrix\Main\UserTable::getList(
            [
                'select' => ['ID'],
                'filter' => ['ID' => $userId],
                'limit' => 1,
            ]
        )->fetch()['ID'];

        $issetUsers = [];
        if(!empty($arStaff[$ID])) {
            $issetUsers = array_map(function($v){return $v['ID'];}, $arStaff[$ID]);
        }

        if(!in_array($userId, $issetUsers)) {
            $companyObject = new Company();
            $companyObject->addStaff($ID, $userId, 'STAFF', 'Y');
        }

    }

    $href = $APPLICATION->GetCurPageParam("", ["ADD_STAFF", "DEL_STAFF"])."&".$tabControl->ActiveTabParam();
    LocalRedirect($href);
}

// Del staff action
if(!empty($_REQUEST['DEL_STAFF'])) {
    $selectId = (int) $_REQUEST['DEL_STAFF'];

    if($selectId) {
        $selectId = Internals\StaffTable::getList(
            [
                'select' => ['ID'],
                'filter' => ['ID' => $selectId],
                'limit' => 1,
            ]
        )->fetch();

        if($selectId) {
            Internals\StaffTable::delete($selectId);
        }

    }

    $href = $APPLICATION->GetCurPageParam("", ["ADD_STAFF", "DEL_STAFF"])."&".$tabControl->ActiveTabParam();;
    LocalRedirect($href);
}

// POST
if($REQUEST_METHOD === "POST" && ($save != "" || $apply != "") && $POST_RIGHT === "W" && check_bitrix_sessid()) {
    $bVarsFromForm = true;

    // Company table
    $hash = '';
    $companyObject = new Company();
    $hash = $companyObject->getHash($company, $company['BUYER_TYPE']);

    $arFieldsCompany = [
        "NAME"            => $company['NAME'],
        "BUYER_TYPE"      => $company['BUYER_TYPE'],
        "DATE_UPDATE"     => new Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s'),
        "ACTIVE"          => ($_POST['ACTIVE'] !== "Y" ? "N" : "Y"),
        "STATUS"          => $company['STATUS'],
        "HASH"            => $hash,
    ];


    $arFieldsCompanyProps = $companyProps;

    $arrInfoOrderProp = $arSaleOrderPropsId[$company['BUYER_TYPE']];
    $arrInfoOrderPropCode = $arSaleOrderProps[$company['BUYER_TYPE']];

    if(empty($errors)) {
        // update company
        if($ID > 0) {
            try {
                $result = Internals\CompanyTable::update($ID, $arFieldsCompany);
                if(!$result->isSuccess()) {
                    $errors = $result->getErrorMessages();
                    $res = false;
                } else {
                    $res = true;
                }
            } catch(\Bitrix\Main\DB\SqlQueryException $e) {
                $errors = [$e->getMessage()];
            }
            // insert company
        } else {
            try {
                $result = Internals\CompanyTable::add($arFieldsCompany);
                if($result->isSuccess()) {
                    $ID = $result->getId();
                    $res = true;
                } else {
                    $errors = $result->getErrorMessages();
                    $res = false;
                }
            } catch(\Bitrix\Main\DB\SqlQueryException $e) {
                $errors = [$e->getMessage()];
            }
        }



        // Company property
        if($ID) {
            // original props
            $q = Internals\CompanyPropsValueTable::getList(['filter' => ['COMPANY_ID' => $ID]])->fetchAll();

            foreach($q as $prop) {
                $originalProps[$arrInfoOrderProp[$prop['PROPERTY_ID']]['CODE']] = ['ID' => $prop['ID'], 'VALUE' => $prop['VALUE']];
            }

            try {
                foreach($arFieldsCompanyProps as $key => $val) {
                    if(!empty($originalProps[$key])) {
                        $result = Internals\CompanyPropsValueTable::update($originalProps[$key]['ID'], ['VALUE' => $val]);
                    } else {
                        $result = Internals\CompanyPropsValueTable::add(
                            [
                                'COMPANY_ID'  => $ID,
                                'PROPERTY_ID' => $arrInfoOrderPropCode[$key]['ID'],
                                'NAME'        => $arrInfoOrderPropCode[$key]["NAME"],
                                'VALUE'       => $val,
                            ]
                        );
                    }

                    if(!$result->isSuccess()) {
                        $errors = $result->getErrorMessages();
                        $res = false;
                    } else {
                        $res = true;
                    }
                }
            } catch(\Bitrix\Main\DB\SqlQueryException $e) {
                $errors = [$e->getMessage()];
            }
        }

    } else {
        $res = false;
    }

    if($res) {
        if($apply != "") {
            LocalRedirect(
                "/bitrix/admin/sotbit.auth_company_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&"
                .$tabControl->ActiveTabParam()
            );
        } else {
            LocalRedirect("/bitrix/admin/sotbit.auth_company_list.php?lang=".LANG);
        }
    }
}


$APPLICATION->SetTitle(
    ($ID > 0
        ? Loc::getMessage(SotbitAuth::idModule."_EDIT_EDIT").$ID
        : Loc::getMessage(
            SotbitAuth::idModule."_EDIT_ADD"
        ))
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(SotbitAuth::returnDemo() == 2) {
    ?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <div class="adm-info-message">
            <div class="adm-info-message-title"><?= Loc::getMessage(SotbitAuth::idModule."_DEMO") ?></div>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
    <?
} elseif(SotbitAuth::returnDemo() == 3 || SotbitAuth::returnDemo() == 0) {
    ?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <div class="adm-info-message">
            <div class="adm-info-message-title"><?= Loc::getMessage(SotbitAuth::idModule."_DEMO_END") ?></div>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
    <?
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
    exit;
}

/****************
 * Header menu
 ****************/
// back
$aMenu[] = [
    "TEXT"  => Loc::getMessage(SotbitAuth::idModule."_EDIT_BACK"),
    "TITLE" => Loc::getMessage(SotbitAuth::idModule."_EDIT_BACK_TITLE"),
    "ICON"  => "btn_list",
    "LINK"  => "sotbit.auth_company_list.php?lang=".LANG,
];
if($ID > 0) {
    $aMenu[] = [
        "SEPARATOR" => "Y",
    ];
    // del
    $aMenu[] = [
        "TEXT"  => Loc::getMessage(SotbitAuth::idModule."_EDIT_DEL"),
        "TITLE" => Loc::getMessage(SotbitAuth::idModule."_EDIT_DEL_TITLE"),
        "LINK"  => "javascript:if(confirm('".Loc::getMessage(SotbitAuth::idModule."_EDIT_DEL_CONF")
            ."'))window.location='sotbit.auth_company_edit.php?ID=".$ID."&action=delete&lang=".LANG."&"
            .bitrix_sessid_get()."';",
        "ICON"  => "btn_delete",
    ];
}
$context = new CAdminContextMenu($aMenu);
$context->Show();




/****************
 * Errors
 ****************/


if(empty($arrBuyerTypes)) {
    CAdminMessage::ShowMessage(["MESSAGE" => Loc::getMessage(SotbitAuth::idModule."_EMPTY_BUYER_TYPE"),]
    );
}
if(empty($arSaleOrderProps)) {
    CAdminMessage::ShowMessage(["MESSAGE" => Loc::getMessage(SotbitAuth::idModule."_EMPTY_SALE_ORDER_PROPS"),]
    );
}


if(isset($errors) && is_array($errors) && count($errors) > 0) {
    CAdminMessage::ShowMessage(
        [
            "MESSAGE" => $errors[0],
        ]
    );
}
if($_REQUEST["mess"] === "ok" && $ID > 0) {
    CAdminMessage::ShowMessage(
        [
            "MESSAGE" => Loc::getMessage(SotbitAuth::idModule."_EDIT_SAVED"),
            "TYPE"    => "OK",
        ]
    );
}



/****************
 * Tabs
 ****************/
$tabControl->BeginEpilogContent();
?>

<?= bitrix_sessid_post() ?>
    <!--<input type="hidden" name="ID"
           value="<?/*= htmlspecialcharsbx(!empty($row) ? $row['ID'] : '') */?>">-->
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
<?php $tabControl->EndEpilogContent();


$tabControl->Begin(["FORM_ACTION" => $APPLICATION->GetCurUri()]);
$tabControl->BeginNextFormTab();

// Tab1
if($ID) {
    $tabControl->AddViewField('ID', Loc::getMessage(SotbitAuth::idModule."_EDIT_ID"), $ID, false);
    $tabControl->AddViewField(
        'DATE_CREATE',
        Loc::getMessage(SotbitAuth::idModule."_EDIT_DATE_CREATE"),
        $company['DATE_CREATE'],
        false
    );

    $tabControl->AddViewField(
        'DATE_UPDATE',
        Loc::getMessage(SotbitAuth::idModule."_EDIT_DATE_CHANGE"),
        $company['DATE_UPDATE'],
        false
    );
}
$tabControl->AddCheckBoxField(
    "ACTIVE",
    Loc::getMessage(SotbitAuth::idModule."_EDIT_ACT"),
    false,
    "Y",
    ($company['ACTIVE'] === "Y" || !isset($company['ACTIVE']))
);

$tabControl->AddEditField(
    "NAME",
    Loc::getMessage(SotbitAuth::idModule."_EDIT_NAME"),
    true,
    ["size" => 30, "maxlength" => 255],
    $company['NAME']
);

// Buyer types
$tabControl->BeginCustomField("BUYER_TYPE", Loc::getMessage(SotbitAuth::idModule."_EDIT_BUYER_TYPE"), true); ?>
    <tr id="tr_BUYER_TYPE">
        <td><?=$tabControl->GetCustomLabelHTML()?></td>
        <td>
            <?
            if(!empty($arrBuyerTypes)) {
                $arr = [
                    "reference" => array_values($arrBuyerTypes),
                    "reference_id" => array_keys($arrBuyerTypes),
                ];
                $href = CUtil::JSEscape($APPLICATION->GetCurPageParam("", ["BUYER_TYPE"]));
                $href .= (stripos($href, '?') !== false ? '&' : '?').'BUYER_TYPE=';
                echo SelectBoxFromArray(
                    "BUYER_TYPE",
                    $arr,
                    $company['BUYER_TYPE'],
                    "",
                    'OnChange="'.htmlspecialcharsbx('window.location=\''.$href.'\' + this.value').'"'
                );
            } ?>
        </td>
    </tr>
<?$tabControl->EndCustomField("BUYER_TYPE");


//status
$tabControl->BeginCustomField("STATUS", Loc::getMessage(SotbitAuth::idModule."_EDIT_STATUS"), true); ?>
    <tr id="tr_STATUS">
        <td><?=$tabControl->GetCustomLabelHTML()?></td>
        <td>
            <?
                $arr = [
                    "reference" => [
                        Loc::getMessage(SotbitAuth::idModule."_EDIT_STATUS_A"),
                        Loc::getMessage(SotbitAuth::idModule."_EDIT_STATUS_R"),
                        Loc::getMessage(SotbitAuth::idModule."_EDIT_STATUS_M"),


                    ],
                    "reference_id" => [
                            "A",
                            "R",
                            "M",
                    ],
                ];
                echo SelectBoxFromArray(
                    "STATUS",
                    $arr,
                    $company['STATUS'] ? : "A"
                );
                ?>
        </td>
    </tr>
<?$tabControl->EndCustomField("STATUS");
// Properties
if(!empty($arSaleOrderProps[$company['BUYER_TYPE']])) {
    foreach($arSaleOrderProps[$company['BUYER_TYPE']] as $propName => $propField) {
        $value = $arCompanyProps[$propField['ID']]['VALUE'];
        switch($propField['TYPE']) {
            /*case 'FILE':
                $tabControl->AddFileField(
                    $propField['ID'],
                    $propField['NAME'],
                    $value
                );
                break;
            case 'DATE':
                $tabControl->AddCalendarField(
                    $propField['ID'],
                    $propField['NAME'],
                    $value
                );
                break;*/
            case 'NUMBER':
                $tabControl->AddEditField(
                    $propName,
                    $propField['NAME'],
                    false,
                    ["size" => 30, "maxlength" => 255],
                    ($value === 0 || $value) ? (int)$value : null
                );
                break;
            case 'STRING':
                $tabControl->AddEditField(
                    $propName,
                    $propField['NAME'],
                    false,
                    ["size" => 30, "maxlength" => 255],
                    htmlspecialcharsbx($value)
                );
                break;
            case 'LOCATION':
                $tabControl->BeginCustomField($propName, $propField['NAME']);
                $value = $arCompanyProps[$propField['ID']]['VALUE'];?>

                <tr>
                    <td width="40%"><?=$propField['NAME']?></td>
                    <td width="60%">
                        <div style="max-width: 500px">
                            <?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.steps", "", array(
                                "ID" => (int)$value,
                                "CODE" => "",
                                "INPUT_NAME" => $propName,
                                "PROVIDE_LINK_BY" => "id",
                                "SHOW_ADMIN_CONTROLS" => 'Y',
                                "SELECT_WHEN_SINGLE" => 'N',
                                "FILTER_BY_SITE" => 'N',
                                "SHOW_DEFAULT_LOCATIONS" => 'N',
                                "SEARCH_BY_PRIMARY" => 'Y',
                            ), false
                            );?>
                        </div>
                    </td>
                </tr>
                <?$tabControl->EndCustomField($code, '');

                break;
            default:
        }

    }
}
$tabControl->BeginNextFormTab();


// Tab2
$tabControl->BeginCustomField("STAFF", Loc::getMessage(SotbitAuth::idModule."_EDIT_STAFF"), false);
if($ID) {

    $hrefStaffAdd = CUtil::JSEscape($APPLICATION->GetCurPageParam("", ["ADD_STAFF"]));
    $hrefStaffAdd .= (stripos($hrefStaffAdd, '?') !== false ? '&' : '?').'ADD_STAFF=';

    CAdminMessage::ShowNote(Loc::getMessage(SotbitAuth::idModule."_ADD_STAFF_SAVE_INFO"));


    ?>
    <tr id="tr_STAFF">
        <td colspan="2">
            <?php
            require ($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/sotbit.auth/admin/sotbit.auth_company_list_staff.php');
            ?>
        </td>
        </tr>
    <?php
    

    ?>
    <input type="hidden" name="STAFF" id="STAFF" value="" size="4" maxlength="" class="typeinput">
    <script>
        var tvSTAFF='';
        function ChSTAFF()
        {
            if (tvSTAFF!=document.sotbit_auth_add_company_form['STAFF'].value)
            {
                tvSTAFF=document.sotbit_auth_add_company_form['STAFF'].value;
                if (tvSTAFF)
                {
                    window.location='<?=$hrefStaffAdd?>'+tvSTAFF;
                }
            }
            setTimeout(function(){ChSTAFF()},1000);
        }
        BX.ready(function(){
            //js error during admin filter initialization, IE9, http://msdn.microsoft.com/en-us/library/gg622929%28v=VS.85%29.aspx?ppud=4, mantis: 33208
            if(BX.browser.IsIE)
            {
                setTimeout(function(){ChSTAFF()},3000);
            }
            else
                ChSTAFF();
        });
    </script><?php
} else {
    CAdminMessage::ShowMessage(
        ["MESSAGE" => Loc::getMessage(SotbitAuth::idModule."_ADD_STAFF_ERROR_ID"),]
    );
}


$tabControl->EndCustomField("STAFF");


/****************
 * Footer buttons
 ****************/

$tabControl->Buttons([
     'disabled' => false,
     'back_url' => 'sotbit.auth_company_list.php?lang='.LANGUAGE_ID,
 ]);

$tabControl->Show();







require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");