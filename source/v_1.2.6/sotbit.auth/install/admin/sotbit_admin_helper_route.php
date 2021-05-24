<?
if(!empty($_GET["module"]))
{
	if (!@include_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/". $_GET["module"] ."/admin/route.php")
	{
		if (!@include_once $_SERVER["DOCUMENT_ROOT"] . "/local/modules/". $_GET["module"] ."/admin/route.php")
		{
			include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/404.php';
		}
	}	
}
else
{
	include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/404.php';  
}