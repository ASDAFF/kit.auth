<?php
use Sotbit\Seometa\SeometaStatisticsTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CJSCore::Init(array("jquery"));
$APPLICATION->AddHeadScript("/bitrix/js/main/amcharts/3.13/amcharts.js");
$APPLICATION->AddHeadScript("/bitrix/js/main/amcharts/3.13/serial.js");
$APPLICATION->AddHeadScript("/bitrix/js/main/amcharts/3.13/pie.js");
$APPLICATION->AddHeadScript("/bitrix/js/main/amcharts/3.13/themes/light.js");
$APPLICATION->AddHeadScript("/bitrix/js/sotbit.seometa/script.js");
$APPLICATION->AddHeadScript("https://www.gstatic.com/charts/loader.js");

$id_module='sotbit.auth';
Loader::includeModule($id_module);
IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sotbit.auth");
if($POST_RIGHT=="D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));


function CheckFilter()
{
    global $FilterArr, $lAdmin;
    foreach ($FilterArr as $f) global $$f;
    if (isset($_REQUEST['del_filter'])&&$_REQUEST['del_filter']!='')
        return false;
    return count($lAdmin->arFilterErrors)==0;
}

$FilterArr = Array(
    "find",
    "find_id",
    "find_from",
    'find_templ',
    'find_event',
);

$arFilter=array();

if (CheckFilter())
{
    if($find_time1!="" && $DB->IsDate($find_time1)) {
        $arFilter['>=DATE_CREATE']= new \Bitrix\Main\Type\DateTime($find_time1.' 00:00:00');
    }
    if ($find_time2!="" && $DB->IsDate($find_time2)){
        $arFilter['<=DATE_CREATE']= new \Bitrix\Main\Type\DateTime($find_time2.' 23:59:59');
    }
    if($find_event != ''){
        $arFilter['EVENT_NAME']= $find_event;
    }
    if($find_templ != ''){
        $arFilter['EVENT_TEMPLATE'] = $find_templ;
    }

    if(empty($arFilter['<=DATE_CREATE'])) unset($arFilter['<=DATE_CREATE']);
    if(empty($arFilter['>=DATE_CREATE'])) unset($arFilter['>=DATE_CREATE']);
    if(empty($arFilter['EVENT_TEMPLATE'])) unset($arFilter['EVENT_TEMPLATE']);
    if(empty($arFilter['EVENT_NAME'])) unset($arFilter['EVENT_NAME']);
}


$APPLICATION->SetTitle(GetMessage("SOTBIT_AUTH_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

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

$oFilter = new CAdminFilter(
    $sTableID."_filter",
    array(

    )
);
?>
    <form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
        <?$oFilter->Begin();?>

        <tr>
            <td>
                <?=GetMessage("SOTBIT_AUTH_EVENT_NAME")?>:
            </td>
            <td>
                <?php
                $sources = unserialize(Option::get("sotbit.auth", 'AUTH_FROM_EMAIL_EVENTS','a:0:{}','s1'));
                $so = array(
                    'reference'=>array('-'),
                    'reference_id'=>array(''),
                );
                foreach($sources as $s){
                    $so['reference'][] = str_replace(array(chr(13),chr(9),' '),'',$s);
                    $so['reference_id'][] = str_replace(array(chr(13),chr(9),' '),'',$s);
                }
                echo SelectBoxFromArray("find_event", $so, $find_event, "", "");
                ?>
            </td>
        </tr>
        <tr>
            <td>template</td>
            <td>
                <?
                $Filt = Array(
                    "TYPE_ID"       => $sources
                );

                $rsMess = \CEventMessage::GetList($by="site_id", $order="desc", $Filt);
                $templ['reference'][] = '-';
                $templ['reference_id'][] = '';
                while($arMess = $rsMess->GetNext())
                {
                    if(!in_array($arMess['SITE_TEMPLATE_ID'], $templ['reference'])){
                        $templ['reference'][] = str_replace(array(chr(13),chr(9),' '),'',$arMess['SITE_TEMPLATE_ID']);
                        $templ['reference_id'][] = str_replace(array(chr(13),chr(9),' '),'',$arMess['SITE_TEMPLATE_ID']);
                    }

                }
                echo SelectBoxFromArray("find_templ", $templ, $find_templ, "", "");
                ?>
            </td>
        </tr>

        <tr>
            <td><?echo GetMessage("SOTBIT_AUTH_TIME")." (".FORMAT_DATE."):"?></td>
            <td><?echo CalendarPeriod("find_time1", $find_time1, "find_time2", $find_time2, "find_form","Y")?></td>
        </tr>
        <?php
        $oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
        $oFilter->End();
        ?>
    </form>
    <style>
        .charts {
            width    : 100%;
            height    : 500px;
        }
    </style>
<?
$open_mails = Sotbit\Auth\Internals\StatisticsTable::getOpenMails($arFilter);
$return_mails = Sotbit\Auth\Internals\StatisticsTable::getReturnMails($arFilter);
?>
    <script type="text/javascript">
        $(document).ready(function() {
            var chart0, chartOrder;
            var legendOrder;
            var selectedOrder;
            var chartDataOpen =[{"percent":<?=$open_mails['Y']?>,"type":"<?=GetMessage('SOTBIT_AUTH_OPEN_MAIL')?>","color":"#f3a882"},
                {"percent":<?=$open_mails['N']?>,"type":"<?=GetMessage('SOTBIT_AUTH_NO_OPEN_MAIL')?>","color":"#f2cb78"}
                ];
            var chartDataReturn =[{"percent":<?=$return_mails['Y']?>,"type":"<?=GetMessage('SOTBIT_AUTH_RETURN_MAIL')?>","color":"#00ff00"},
                {"percent":<?=$return_mails['N']?>,"type":"<?=GetMessage('SOTBIT_AUTH_NO_RETURN_MAIL')?>","color":"#ff0000"}
                ];
// create chart
            AmCharts.ready(function() {
                //CHARTS BY ORDERS
                AmCharts.makeChart("chartdivorder", {
                    "type": "pie",
                    "theme": "light",
                    "dataProvider": chartDataOpen,
                    "labelText": "[[title]]: [[value]]%",
                    "balloonText": "[[title]]: [[value]]%",
                    "titleField": "type",
                    "valueField": "percent",
                    "outlineColor": "#FFFFFF",
                    "outlineAlpha": 0.8,
                    "outlineThickness": 2,
                    "colorField": "color",
                    "pulledField": "pulled",
                    "listeners": [{
                        "event": "clickSlice",
                        "method": function(event) {
                            var chart = event.chart;
                            if (event.dataItem.dataContext.id != undefined) {
                                selectedOrder = event.dataItem.dataContext.id;
                            } else {
                                selectedOrder = undefined;
                            }
                            chart.dataProvider = generateChartPieData();
                            chart.validateData();
                        }
                    }],
                    "export": {
                        "enabled": false
                    }
                });

            });
            AmCharts.ready(function() {
                //CHARTS BY ORDERS
                AmCharts.makeChart("chartdivorder2", {
                    "type": "pie",
                    "theme": "light",
                    "dataProvider": chartDataReturn,
                    "labelText": "[[title]]: [[value]]%",
                    "balloonText": "[[title]]: [[value]]%",
                    "titleField": "type",
                    "valueField": "percent",
                    "outlineColor": "#FFFFFF",
                    "outlineAlpha": 0.8,
                    "outlineThickness": 2,
                    "colorField": "color",
                    "pulledField": "pulled",
                    "listeners": [{
                        "event": "clickSlice",
                        "method": function(event) {
                            var chart = event.chart;
                            if (event.dataItem.dataContext.id != undefined) {
                                selectedOrder = event.dataItem.dataContext.id;
                            } else {
                                selectedOrder = undefined;
                            }
                            chart.dataProvider = generateChartPieData();
                            chart.validateData();
                        }
                    }],
                    "export": {
                        "enabled": false
                    }
                });

            });
        });
    </script>

    <h3><?=GetMessage('SOTBIT_AUTH_OPEN')?></h3>
    <div id="chartdivorder" class="charts"></div>
    <h3><?=GetMessage('SOTBIT_AUTH_RETURN')?></h3>
    <div id="chartdivorder2" class="charts"></div>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");