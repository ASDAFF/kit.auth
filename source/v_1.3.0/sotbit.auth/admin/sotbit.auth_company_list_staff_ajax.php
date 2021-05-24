<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

CUtil::JSPostUnescape();
/*
 * this page only for actions and get info
 *
 */
define('B_ADMIN_AJAX_LIST_STAFF',1);
define('B_ADMIN_AJAX_LIST_STAFF_LIST',true);


global $APPLICATION;
global $USER;

CModule::IncludeModule("sotbit.auth");

$strSubElementAjaxPath = '/bitrix/admin/sotbit.auth_company_list_staff.php?lang='.LANGUAGE_ID.'&ID='.(int)$_REQUEST['ID'];
require($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sotbit.auth/admin/sotbit.auth_company_list_staff.php');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
?>