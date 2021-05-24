<?php
use Sotbit\Auth\Internals;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\OrderPropsTable;

define('PROP_INN', 'INN');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule(SotbitAuth::idModule);
if(!Loader::includeModule("sale")) {
    return false;
}
Loc::loadMessages(__FILE__);

$postRight = $APPLICATION->GetGroupRight("sotbit.auth");
if($postRight == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


$tableName = Internals\CompanyTable::getTableName();
$oSort = new CAdminSorting($tableName, "ID", "desc");
$lAdmin = new CAdminList($tableName, $oSort);

function CheckFilter()
{
    global $filterArr, $lAdmin;
    foreach($filterArr as $f) {
        global $$f;
    }

    return count($lAdmin->arFilterErrors) == 0;
}

$filterArr = [
    "find_id",
    "find_name",
    "find_status",
    "find_inn",
    "find_staff",
    "find_buyer_type",
    "find_active",
];

$arrHeaders = [
    [
        "id"      => "ID",
        "content" => Loc::getMessage(SotbitAuth::idModule."_ID"),
        "sort"    => "ID",
        "align"   => "right",
        "default" => true,
    ],
    [
        "id"      => "NAME",
        "content" => Loc::getMessage(SotbitAuth::idModule."_TITLE"),
        "sort"    => "NAME",
        "default" => true,
    ],
    [
        "id"      => "INN",
        "content" => Loc::getMessage(SotbitAuth::idModule."_INN"),
        "sort"    => "INN",
        "default" => true,
    ],
    [
        "id"      => "BUYER_TYPE",
        "content" => Loc::getMessage(SotbitAuth::idModule."_BUYER_TYPE"),
        "sort"    => "BUYER_TYPE",
        "default" => false,
    ],
    [
        "id"      => "STAFF",
        "content" => Loc::getMessage(SotbitAuth::idModule."_STAFF"),
        "sort"    => "ACTIVE",
        "align"   => "left",
        "default" => true,
    ],
    [
        "id"      => "DATE_CREATE",
        "content" => Loc::getMessage(SotbitAuth::idModule."_DATE_CREATE"),
        "sort"    => "DATE_CREATE",
        "default" => true,
    ],
    [
        "id"      => "DATE_UPDATE",
        "content" => Loc::getMessage(SotbitAuth::idModule."_DATE_CHANGE"),
        "sort"    => "DATE_UPDATE",
        "default" => true,
    ],
    [
        "id"      => "ACTIVE",
        "content" => Loc::getMessage(SotbitAuth::idModule."_ACTIVE"),
        "sort"    => "ACTIVE",
        "default" => true,
    ],
    [
        "id"      => "STATUS",
        "content" => Loc::getMessage(SotbitAuth::idModule."_STATUS"),
        "sort"    => "STATUS",
        "default" => true,
    ],
];

$lAdmin->InitFilter($filterArr);
$arFilter = [];

if(CheckFilter()) {

    if($find_id != '') {
        $arFilter['ID'] = $find_id;
    }
    $arFilter['NAME'] = $find_name;
    $arFilter['STATUS'] = $find_status;
    $arFilter['INN'] = (int)$find_inn;
    $arFilter['STAFF'] = $find_staff;
    $arFilter['BUYER_TYPE'] = (int)$find_buyer_type;
    $arFilter['ACTIVE'] = $find_active;

    if(empty($arFilter['ID'])) {
        unset($arFilter['ID']);
    }
    if(empty($arFilter['NAME'])) {
        unset($arFilter['NAME']);
    }
    if(empty($arFilter['STATUS'])) {
        unset($arFilter['STATUS']);
    }
    if(empty($arFilter['INN'])) {
        unset($arFilter['INN']);
    }
    if(empty($arFilter['STAFF'])) {
        unset($arFilter['STAFF']);
    }
    if(empty($arFilter['BUYER_TYPE'])) {
        unset($arFilter['BUYER_TYPE']);
    }
    if(empty($arFilter['ACTIVE'])) {
        unset($arFilter['ACTIVE']);
    }
}

// Filter company
$filterCompany = ['ID', 'NAME', 'BUYER_TYPE', 'STATUS', 'ACTIVE'];
$filterCompany = array_intersect_key($arFilter, array_flip($filterCompany));
/*
if($lAdmin->EditAction()) {
    foreach($FIELDS as $ID => $field) {

        if(!$lAdmin->IsUpdated($ID)) {
            continue;
        }

        $ID = (int)$ID;
        if($ID > 0) {
                foreach($field as $key => $value) {
                    $arData[$key] = $value;
                }

                $arData['DATE_CHANGE'] = new Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');

                try {
                    $result = Internals\CompanyTable::update($ID, $arData);

                    if(!$result->isSuccess()) {
                        $lAdmin->AddGroupError(
                            Loc::getMessage(SotbitAuth::idModule."_SAVE_ERROR")." ".Loc::getMessage(
                                SotbitAuth::idModule."_NO_ZAPIS"
                            ),
                            $ID
                        );
                    }
                } catch(Exception $e) {
                    $lAdmin->AddGroupError(
                        Loc::getMessage(SotbitAuth::idModule."_SAVE_ERROR")." ".$e->getMessage(),
                        $ID
                    );
                }
        } else {
            $lAdmin->AddGroupError(
                Loc::getMessage(SotbitAuth::idModule."_SAVE_ERROR")." ".Loc::getMessage(
                    SotbitAuth::idModule."_NO_ZAPIS"
                ),
                $ID
            );
        }
    }
}

if($arID = $lAdmin->GroupAction()) {
    if($_REQUEST['action_target'] === 'selected') {
        $rsData = Internals\CompanyTable::getList(
            [
                'select' => ['*'],
                'filter' => $arFilter,
                'order'  => [$by => $order],
            ]
        );
        while($arRes = $rsData->Fetch()) {
            $arID[] = $arRes['ID'];
        }

        if(!isset($filter)) {
            $filter = [];
        }
    }

    foreach($arID as $ID) {
        $type = substr($ID, 0, 1);
        $ID = (int)substr($ID, 1);

        if(strlen($ID) <= 0) {
            continue;
        }
        $ID = IntVal($ID);

        switch($_REQUEST['action']) {
            case "delete":
                $result = Internals\CompanyTable::delete($ID);
                if(!$result->isSuccess()) {
                    $lAdmin->AddGroupError(
                        Loc::getMessage(SotbitAuth::idModule."_DEL_ERROR")." ".Loc::getMessage(
                            SotbitAuth::idModule."_NO_ZAPIS"
                        ),
                        $ID
                    );
                }
                break;
            case "activate":
            case "deactivate":
                $field["STATUS"] = ($_REQUEST['action'] == "activate" ? "Y" : "N");
                \Bitrix\Main\Diag\Debug::dump($_REQUEST);exit();
                $result = Internals\CompanyTable::update(
                    $ID,
                    [
                        'ACTIVE' => $field["ACTIVE"],
                    ]
                );
                if(!$result->isSuccess()) {
                    $lAdmin->AddGroupError(
                        Loc::getMessage(SotbitAuth::idModule."_SAVE_ERROR")." ".Loc::getMessage(
                            SotbitAuth::idModule."_NO_ZAPIS"
                        ),
                        $ID
                    );
                }
                break;
        }
    }
}*/

$filter = $arFilter;

// Company table
$arrFilter = [
    'select' => ['*'],
    'filter' => $filterCompany,
];

if(array_key_exists($by, Internals\CompanyTable::getMap())) {
    $arrFilter['order'] = [$by => $order];
}

$rsData = Internals\CompanyTable::getList($arrFilter);
while($arRes = $rsData->Fetch()) {
    $arResult[] = $arRes;
}

// Selected company
$arCompanyIdSelect = [];
if(!empty($arResult)) {
    $arCompanyIdSelect = array_unique(
        array_map(
            function($v) {
                return $v['ID'];
            },
            $arResult
        )
    );
}

// Company property
$arCompanyProps = [];
if($arCompanyIdSelect) {

    $arrFilter = [
        'select' => ['*'],
        'filter' => [
            'COMPANY_ID' => $arCompanyIdSelect,
        ],
    ];
    if(array_key_exists($by, Internals\CompanyPropsValueTable::getMap())) {
        $arrFilter['order'] = [$by => $order];
    }

    $rsData = Internals\CompanyPropsValueTable::getList($arrFilter);
    while($arRes = $rsData->Fetch()) {
        $arCompanyProps[$arRes['COMPANY_ID']][$arRes['PROPERTY_ID']] = $arRes;
    }
}


// Staff
$arStaff = [];
if(!empty($arResult)) {

    $arrFilter = [
        'select' => ['*', 'EMAIL' => 'USER.EMAIL', 'NAME' => 'USER.NAME', 'LAST_NAME' => 'USER.LAST_NAME'],
        'filter' => [
            'COMPANY_ID' => $arCompanyIdSelect,
        ],
    ];
    if(array_key_exists($by, Internals\StaffTable::getMap())) {
        $arrFilter['order'] = [$by => $order];
    }
    $rsData = Internals\StaffTable::getList($arrFilter);
    while($arRes = $rsData->Fetch()) {
        $arStaff[$arRes['COMPANY_ID']][] = $arRes;
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
$q = OrderPropsTable::getList(
    [
        'select' => ['ID', 'PERSON_TYPE_ID', 'CODE', 'NAME'],
    ]
);
while($orderProp = $q->Fetch())
{
    $arSaleOrderProps[$orderProp['PERSON_TYPE_ID']][$orderProp['CODE']] = $orderProp;
}

/**
 * Create table
 */
$rs = new CDBResult;
$rs->InitFromArray($arResult);
$rsData = new CAdminResult($rs, $tableName);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage(SotbitAuth::idModule."_NAV")));
$lAdmin->AddHeaders($arrHeaders);

while($arRes = $rsData->NavNext(true, "f_")):
    // INN
    $innPropId = $arSaleOrderProps[$arRes['BUYER_TYPE']][PROP_INN]['ID'];
    if($innPropId && $arCompanyProps[$arRes['ID']][$innPropId]) {
        $arRes['INN'] = $arCompanyProps[$arRes['ID']][$innPropId]['VALUE'];
    }

    // Buyer type
    if(!empty($arRes['BUYER_TYPE']) && !empty($arrBuyerTypes[$arRes['BUYER_TYPE']])) {
        $arRes['BUYER_TYPE'] = $arrBuyerTypes[$arRes['BUYER_TYPE']];
    }

    // Staff
    $staffs = '';
    if(!empty($arStaff[$arRes['ID']])) {
        foreach($arStaff[$arRes['ID']] as $staff) {
            $staffs .=
                '[<a href="/bitrix/admin/user_edit.php?lang='.LANG.'&ID='.$staff['USER_ID'].'">'.Loc::getMessage(SotbitAuth::idModule.'_USER').'</a>] '.
                '('.$staff['EMAIL'].') '.$staff['NAME'].' '.$staff['LAST_NAME'].'<br>';
        }
    }

    $status = '';
    if(!empty($arRes['STATUS'])) {
        $status = Loc::getMessage(SotbitAuth::idModule."_STATUS_".$arRes['STATUS']);
    }

    $active = '';
    if(!empty($arRes['ACTIVE'])) {
        $active = Loc::getMessage(SotbitAuth::idModule."_ACTIVE_".$arRes['ACTIVE']);
    }



    $row =& $lAdmin->AddRow($f_ID, $arRes);

    // in edit mode
    $row->AddViewField("NAME", '<a href="sotbit.auth_company_edit.php?ID='.$f_ID.'&lang='.LANG.'">'.$f_NAME.'</a>');
    $row->AddViewField("STAFF", $staffs);
    $row->AddViewField("STATUS", $status);
    $row->AddViewField("ACTIVE", $active);


    // Actions
    $arActions = [];
    /*$arActions[] = [
        "ICON"    => "edit",
        "DEFAULT" => true,
        "TEXT"    => Loc::getMessage(SotbitAuth::idModule."_EDIT"),
        "ACTION"  => $lAdmin->ActionRedirect("sotbit.auth_company_edit.php?ID=".$f_ID),
    ];*/
    if($postRight >= "W") {
        $arActions[] = [
            "ICON"   => "delete",
            "TEXT"   => Loc::getMessage(SotbitAuth::idModule."_DEL"),
            "ACTION" => "if(confirm('".GetMessage('SEO_SEARCH_DEL_CONF')."')) ".$lAdmin->ActionDoGroup(
                    $f_ID,
                    "delete"
                ),
        ];
    }
    $arActions[] = ["SEPARATOR" => true];
    if(is_set($arActions[count($arActions) - 1], "SEPARATOR")) {
        unset($arActions[count($arActions) - 1]);
    }
    //$row->AddActions($arActions);

endwhile;

$lAdmin->AddFooter(
    [
        ["title" => Loc::getMessage(SotbitAuth::idModule."_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()],
        ["counter" => true, "title" => Loc::getMessage(SotbitAuth::idModule."_LIST_CHECKED"), "value" => "0"],
    ]
);

/*$lAdmin->AddGroupActionTable(
    [
        "delete"     => Loc::getMessage(SotbitAuth::idModule."_LIST_DELETE"),
        "activate"   => Loc::getMessage(SotbitAuth::idModule."_LIST_ACTIVATE"),
        "deactivate" => Loc::getMessage(SotbitAuth::idModule."_LIST_DEACTIVATE"),
    ]
);*/


$aContext = [
    [
        "TEXT"  => Loc::getMessage(SotbitAuth::idModule."_POST_ADD_TEXT"),
        "LINK"  => "sotbit.auth_company_edit.php?lang=".LANG,
        "TITLE" => Loc::getMessage(SotbitAuth::idModule."_POST_ADD_TITLE"),
        "ICON"  => "btn_new",
    ],
];

$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage(SotbitAuth::idModule."_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
    $tableName."_filter",
    [
        Loc::getMessage(SotbitAuth::idModule."_ID"),
        Loc::getMessage(SotbitAuth::idModule."_NAME"),
        Loc::getMessage(SotbitAuth::idModule."_INN"),
        Loc::getMessage(SotbitAuth::idModule."_STAFF"),
        Loc::getMessage(SotbitAuth::idModule."_BUYER_TYPE"),
        Loc::getMessage(SotbitAuth::idModule."_STATUS"),
        Loc::getMessage(SotbitAuth::idModule."_ACTIVE"),
    ]
);

if(SotbitAuth::returnDemo() == 3 || SotbitAuth::returnDemo() == 0) {
    ?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <div class="adm-info-message">
            <div class="adm-info-message-title"><?= getMessage(SotbitAuth::idModule."_DEMO_END") ?></div>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
    <?
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

    return '';
}
?>

<?php
/**
 * FILTER FORM
 */
?>
    <form name="find_form" method="get" action="<? echo $APPLICATION->GetCurPage(); ?>">
        <? $oFilter->Begin(); ?>
        <!--<tr>
            <td><b><?/*= Loc::getMessage(SotbitAuth::idModule."_FIND") */?>:</b></td>
            <td>
                <input type="text" size="25" name="find" value="<?/* echo htmlspecialchars($find) */?>"
                       title="<?/*= Loc::getMessage(SotbitAuth::idModule."_FIND_TITLE") */?>">
                <?/*
                $arr = [
                    "reference"    => [
                        "ID",
                    ],
                    "reference_id" => [
                        "id",
                    ],
                ];
                echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
                */?>
            </td>
        </tr>-->
        <tr>
            <td><?= Loc::getMessage(SotbitAuth::idModule."_ID") ?>:</td>
            <td>
                <input type="text" name="find_id" size="47" value="<? echo htmlspecialchars($find_id) ?>">
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage(SotbitAuth::idModule."_NAME") ?>:</td>
            <td>
                <input type="text" name="find_name" size="47" value="<? echo htmlspecialchars($find_name) ?>">
            </td>
        </tr>

        <tr>
            <td><?= Loc::getMessage(SotbitAuth::idModule."_INN") ?>:</td>
            <td>
                <input type="text" name="find_inn" size="47" value="<? echo htmlspecialchars($find_inn) ?>">
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage(SotbitAuth::idModule."_SEARCH_STAFF") ?>:</td>
            <td>
                <input type="text" name="find_staff" size="47" value="<? echo htmlspecialchars($find_staff) ?>">
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage(SotbitAuth::idModule."_BUYER_TYPE") ?>:</td>
            <td>
                <?
                $arr = [
                    "reference"    => array_values($arrBuyerTypes),
                    "reference_id" => array_keys($arrBuyerTypes),
                ];
                echo SelectBoxFromArray("find_buyer_type", $arr, $find_buyer_type, "", "");
                ?>            </td>
        </tr>

        <tr>
            <td><?= Loc::getMessage(SotbitAuth::idModule."_STATUS") ?>:</td>
            <td>
                <?
                $arr = [
                    "reference"    => [
                        Loc::getMessage(SotbitAuth::idModule."_STATUS_M"),
                        Loc::getMessage(SotbitAuth::idModule."_STATUS_R"),
                        Loc::getMessage(SotbitAuth::idModule."_STATUS_A"),
                    ],
                    "reference_id" => [
                        "M",
                        "R",
                        "A",
                    ],
                ];
                echo SelectBoxFromArray("find_status", $arr, $find_active, "", "");
                ?>
            </td>
        </tr>

        <tr>
            <td><?= Loc::getMessage(SotbitAuth::idModule."_ACTIVE") ?>:</td>
            <td>
                <?
                $arr = [
                    "reference"    => [
                        Loc::getMessage(SotbitAuth::idModule."_ACTIVE_Y"),
                        Loc::getMessage(SotbitAuth::idModule."_ACTIVE_N"),
                    ],
                    "reference_id" => [
                        "Y",
                        "N"
                    ],
                ];
                echo SelectBoxFromArray("find_active", $arr, $find_active, "", "");
                ?>            </td>
        </tr>
        <?
        $oFilter->Buttons(["table_id" => $tableName, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"]);
        $oFilter->End();
        ?>
    </form>

<?

if(SotbitAuth::returnDemo() == 2) {
    ?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <div class="adm-info-message">
            <div class="adm-info-message-title"><?= getMessage(SotbitAuth::idModule."_DEMO") ?></div>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
    <?
}

$lAdmin->DisplayList();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");