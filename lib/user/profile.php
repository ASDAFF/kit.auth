<?php
namespace Kit\Auth\User;

class Profile
{
    static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array()){
        global $DB;

        if (count($arSelectFields) <= 0)
            $arSelectFields = array("ID", "NAME", "USER_ID", "PERSON_TYPE_ID", "DATE_UPDATE");

        // FIELDS -->
        $arFields = array(
            "ID" => array("FIELD" => "P.ID", "TYPE" => "int"),
            "NAME" => array("FIELD" => "P.NAME", "TYPE" => "string"),
            "USER_ID" => array("FIELD" => "P.USER_ID", "TYPE" => "int"),
            "PERSON_TYPE_ID" => array("FIELD" => "P.PERSON_TYPE_ID", "TYPE" => "int"),
            "DATE_UPDATE" => array("FIELD" => "P.DATE_UPDATE", "TYPE" => "datetime"),
            "FORMAT_DATE_UPDATE" => array("FIELD" => "P.DATE_UPDATE", "TYPE" => "datetime"),
            "DATE_UPDATE_FORMAT" => array("FIELD" => "P.DATE_UPDATE", "TYPE" => "datetime"),
            "PROPERTY_ID" => array("FIELD" => "SP.ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_user_props_value SP ON (P.ID = SP.USER_PROPS_ID)"),
            "PROPERTY_USER_PROPS_ID" => array("FIELD" => "SP.USER_PROPS_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sale_user_props_value SP ON (P.ID = SP.USER_PROPS_ID)"),
            "PROPERTY_NAME" => array("FIELD" => "SP.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_user_props_value SP ON (P.ID = SP.USER_PROPS_ID)"),
            "PROPERTY_VALUE" => array("FIELD" => "SP.VALUE", "TYPE" => "string", "FROM" => "INNER JOIN b_sale_user_props_value SP ON (P.ID = SP.USER_PROPS_ID)"),
        );
        // <-- FIELDS


        $arSqls = \CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);


        $arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

        $strSql =
            "SELECT ".$arSqls["SELECT"]." ".
            "FROM b_sale_user_props P ".
            "	".$arSqls["FROM"]." ";
        if (strlen($arSqls["WHERE"]) > 0)
            $strSql .= "WHERE ".$arSqls["WHERE"]." ";
        if (strlen($arSqls["GROUPBY"]) > 0)
            $strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
        if (strlen($arSqls["ORDERBY"]) > 0)
            $strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

        $dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);


        return $dbRes;
    }

    static function GetProfileProps($profileID = null, $personID = null) {

        if (!empty($profileID)) {
            $props = array();
            $res = \CSaleOrderUserPropsValue::GetList(array(), array('USER_PROPS_ID'=>$profileID), false, false, array('ID', 'ORDER_PROPS_ID', 'VALUE'));
            while ($arRes = $res->Fetch()) {
                $props[$arRes['ORDER_PROPS_ID']] = array('VALUE' => $arRes['VALUE']);
            }
        }

        if (empty($personID)) {
            $res = \CSalePersonType::GetList(array(), array(), false, array('nTopCount' => 1), array());
            if ($arRes = $res->Fetch()) {
                $personID = $arRes["ID"];
            }
        }

        $arProps = array();
        $res = \CSaleOrderProps::GetList(array("SORT"=>"ASC"), array("PERSON_TYPE_ID" => $personID, "USER_PROPS" => "Y"), false, false, array());
        while ($arRes = $res->Fetch()) {
            if (in_array($arRes["TYPE"], array("SELECT", "MULTISELECT", "RADIO"))) {
                $rs = \CSaleOrderPropsVariant::GetList(array(), array("ORDER_PROPS_ID" => $arRes["ID"]));
                while ($arRs = $rs->Fetch()) {
                    $arRes["variants"][] = $arRs;
                }
            }
            if (!empty($props[$arRes['ID']])) {
                $arProps[$arRes['ID']] = array_merge($props[$arRes['ID']], $arRes);
            } else {
                $arProps[$arRes['ID']] = $arRes;
            }
        }
        return $arProps;
    }

}
?>