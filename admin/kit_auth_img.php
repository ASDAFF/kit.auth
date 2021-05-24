<?define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("kit.auth");

if(isset($_REQUEST['OPEN_MESSAGE']) && $_REQUEST['OPEN_MESSAGE'] > 0){
    $message_id = (int) $_REQUEST['OPEN_MESSAGE'];
    Kit\Auth\Internals\StatisticsTable::update($message_id,
        array('OPEN_MESSAGE'=>'Y', 'DATE_OPEN'=>new Bitrix\Main\Type\DateTime()));
}
?>