<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;

global  $APPLICATION,
        $USER,
        $USER_FIELD_MANAGER;

$hideSection = array(
    "HIDE" => Loc::getMessage('HIDE_SECTION')
);

if($userID = intval($USER->GetID())) {
    $arrRes = CUser::GetByID($userID)->fetch();

    if($arrRes) {

        $generalFields = array(
            'TITLE',
            'NAME',
            'LAST_NAME',
            'SECOND_NAME',
            'EMAIL',
            'LOGIN'
        );

        $arrData['GENERAL'] = $hideSection;

        foreach ($generalFields as $key => $generalField) {
            if(array_key_exists($generalField, $arrRes)) {
                $arrData['GENERAL'][$generalField] = Loc::getMessage($generalField);
            }
        }

        $personalFields = array(
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
            'PERSONAL_COUNTRY',
            'PERSONAL_STATE',
            'PERSONAL_CITY',
            'PERSONAL_ZIP',
            'PERSONAL_STREET',
            'PERSONAL_MAILBOX',
            'PERSONAL_NOTES',
        );

        $arrData['PERSONAL'] = $hideSection;
        foreach ($personalFields as $key => $personalField) {
            if(array_key_exists($personalField, $arrRes)) {
                $arrData['PERSONAL'][$personalField] = Loc::getMessage($personalField);
            }
        }

        $workFields = array(
            'WORK_COMPANY',
            'WORK_WWW',
            'WORK_DEPARTMENT',
            'WORK_POSITION',
            'WORK_PROFILE',
            'WORK_LOGO',
            'WORK_PHONE',
            'WORK_FAX',
            'WORK_PAGER',
            'WORK_COUNTRY',
            'WORK_STATE',
            'WORK_CITY',
            'WORK_ZIP',
            'WORK_STREET',
            'WORK_MAILBOX',
            'WORK_NOTES',
        );

        $arrData['WORK'] = $hideSection;
        foreach ($workFields as $key => $workField) {
            if(array_key_exists($workField, $arrRes)) {
                $arrData['WORK'][$workField] = Loc::getMessage($workField);
            }
        }
    }



    $arrData['USER_FIELDS'] = $USER_FIELD_MANAGER->GetUserFields('USER', $userID, LANGUAGE_ID);
};

$groups = $hideSection;
$rsGroups = CGroup::GetList ($by = "c_sort", $order = "asc", Array ());
while($resGroups = $rsGroups->Fetch()){
    $groups[$resGroups["ID"]] = $resGroups["NAME"];
}

$arComponentParameters = array_merge($arComponentParameters,
    array(
        "COUNT_STAFF_PAGE"=> array(
            "NAME" => GetMessage("COUNT_STAFF_PAGE"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => 20,
            "PARENT" => "ADDITIONAL_SETTINGS",
        )
    )
);

if(!empty($arrData['GENERAL']) && count($arrData['GENERAL']) > 1) {
    $arComponentParameters = array_merge($arComponentParameters,
        array(
            "USER_PROPERTY_GENERAL_DATA"=> array(
                "NAME" => GetMessage("USER_PROPERTY_GENERAL_DATA"),
                "TYPE" => "LIST",
                "MULTIPLE" => "Y",
                "VALUES" => $arrData['GENERAL']
            )
        )
    );
}

if(!empty($arrData['PERSONAL']) && count($arrData['PERSONAL']) > 1) {
    $arComponentParameters = array_merge($arComponentParameters,
        array(
            "USER_PROPERTY_PERSONAL_DATA" => array(
                "NAME" => GetMessage("USER_PROPERTY_PERSONAL_DATA"),
                "TYPE" => "LIST",
                "MULTIPLE" => "Y",
                "VALUES" => $arrData['PERSONAL']
            )
        )
    );
}

if(!empty($arrData['WORK']) && count($arrData['WORK']) > 1) {
    $arComponentParameters = array_merge($arComponentParameters,
        array(
            "USER_PROPERTY_WORK_INFORMATION_DATA" => array(
                "NAME" => GetMessage("USER_PROPERTY_WORK_INFORMATION_DATA"),
                "TYPE" => "LIST",
                "MULTIPLE" => "Y",
                "VALUES" => $arrData['WORK']
            )
        )
    );
}

$arComponentParameters = array_merge(
    $arComponentParameters ,
    array(
        "USER_PROPERTY_ADMIN_NOTE_DATA" => array(
            "NAME" => GetMessage("USER_PROPERTY_ADMIN_NOTE_DATA"),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $hideSection
        )
    )
);

$arComponentParameters = array_merge($arComponentParameters,
    array(
        "USER_SHOW_GROUPS" => array(
            "NAME" => GetMessage("USER_SHOW_GROUPS"),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $groups
        )
    )
);

$arComponentParameters = array_merge($arComponentParameters,
    array(
        "CACHE_TIME"  =>  array("DEFAULT"=>36000000),
    )
);

$arComponentParameters = array(
    "PARAMETERS" => $arComponentParameters);
?>