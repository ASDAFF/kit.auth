<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (CModule::IncludeModule('sale'))
{
	$dbStat = CSaleStatus::GetList(array('sort' => 'asc'), array('LID' => LANGUAGE_ID), false, false, array('ID', 'NAME'));
	$statList = array();
	while ($item = $dbStat->Fetch())
		$statList[$item['ID']] = $item['NAME'];

	$statList['PSEUDO_CANCELLED'] = 1;	

	$availColors = array(
		'green' => GetMessage("SPO_STATUS_COLOR_GREEN"),
		'yellow' => GetMessage("SPO_STATUS_COLOR_YELLOW"),
		'red' => GetMessage("SPO_STATUS_COLOR_RED"),
		'gray' => GetMessage("SPO_STATUS_COLOR_GRAY"),
	);

	$colorDefaults = array(
		'N' => 'green', // new
		'P' => 'yellow', // payed
		'F' => 'gray', // finished
		'PSEUDO_CANCELLED' => 'red' // cancelled
	);

	foreach ($statList as $id => $name)
		$arTemplateParameters["STATUS_COLOR_".$id] = array(
			"NAME" => $id == 'PSEUDO_CANCELLED' ? GetMessage("SPO_PSEUDO_CANCELLED_COLOR") : GetMessage("SPO_STATUS_COLOR").' "'.$name.'"',
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => $availColors,
			"DEFAULT" => empty($colorDefaults[$id]) ? 'gray' : $colorDefaults[$id],
		);
}

if (!\Bitrix\Main\Loader::includeModule('iblock'))
	return;
$boolCatalog = \Bitrix\Main\Loader::includeModule('catalog');

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$rsIBlock = CIBlock::GetList(array("sort" => "asc"), array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
global $MESS;
//printr($MESS);

$arTemplateParameters['IBLOCK_TYPE'] = array(
			'PARENT' => 'MISS_SHOP',
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
);

$arTemplateParameters['IBLOCK_ID'] = array(
			"PARENT" => "MISS_SHOP",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
);

$arSKU = false;
$boolSKU = false;
if ($boolCatalog && (isset($arCurrentValues['IBLOCK_ID']) && 0 < intval($arCurrentValues['IBLOCK_ID'])))
{
	$arSKU = CCatalogSKU::GetInfoByProductIBlock($arCurrentValues['IBLOCK_ID']);
	$boolSKU = !empty($arSKU) && is_array($arSKU);
}


if (isset($arCurrentValues['IBLOCK_ID']) && 0 < intval($arCurrentValues['IBLOCK_ID']))
{
	$arAllPropList = array();
	$arFilePropList = array(
		'-' => GetMessage('CP_BC_TPL_PROP_EMPTY')
	);
	$arListPropList = array(
		'-' => GetMessage('CP_BC_TPL_PROP_EMPTY')
	);
	$arHighloadPropList = array(
		'-' => GetMessage('CP_BC_TPL_PROP_EMPTY')
	);
    $arElementPropList = array(
		'-' => GetMessage('CP_BC_TPL_PROP_EMPTY')
	);
	$rsProps = CIBlockProperty::GetList(
		array('SORT' => 'ASC', 'ID' => 'ASC'),
		array('IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'], 'ACTIVE' => 'Y')
	);
	while ($arProp = $rsProps->Fetch())
	{
		$strPropName = '['.$arProp['ID'].']'.('' != $arProp['CODE'] ? '['.$arProp['CODE'].']' : '').' '.$arProp['NAME'];
		if ('' == $arProp['CODE'])
			$arProp['CODE'] = $arProp['ID'];
		$arAllPropList[$arProp['CODE']] = $strPropName;
		if ('F' == $arProp['PROPERTY_TYPE'])
			$arFilePropList[$arProp['CODE']] = $strPropName;
		if ('L' == $arProp['PROPERTY_TYPE'])
			$arListPropList[$arProp['CODE']] = $strPropName;
        if ('E' == $arProp['PROPERTY_TYPE'])
			$arElementPropList[$arProp['CODE']] = $strPropName;
        if('S' == $arProp['PROPERTY_TYPE'] && 'directory' != $arProp['USER_TYPE'])
            $arStringPropList[$arProp['CODE']] = $strPropName;
		if ('S' == $arProp['PROPERTY_TYPE'] && 'directory' == $arProp['USER_TYPE'] && CIBlockPriceTools::checkPropDirectory($arProp))
			$arHighloadPropList[$arProp['CODE']] = $strPropName;
	}
}

if($boolSKU)
{
    $arAllOfferPropList = array();
	$arFileOfferPropList = array(
			'-' => GetMessage('CP_BC_TPL_PROP_EMPTY')
	);
	$arTreeOfferPropList = array(
			'-' => GetMessage('CP_BC_TPL_PROP_EMPTY')
	);
	$rsProps = CIBlockProperty::GetList(
			array('SORT' => 'ASC', 'ID' => 'ASC'),
			array('IBLOCK_ID' => $arSKU['IBLOCK_ID'], 'ACTIVE' => 'Y')
	);
	while ($arProp = $rsProps->Fetch())
	{
	    if ($arProp['ID'] == $arSKU['SKU_PROPERTY_ID'])
				continue;
		$arProp['USER_TYPE'] = (string)$arProp['USER_TYPE'];
		$strPropName = '['.$arProp['ID'].']'.('' != $arProp['CODE'] ? '['.$arProp['CODE'].']' : '').' '.$arProp['NAME'];
		if ('' == $arProp['CODE'])
		    $arProp['CODE'] = $arProp['ID'];
		$arAllOfferPropList[$arProp['CODE']] = $strPropName;
		if ('F' == $arProp['PROPERTY_TYPE'])
		    $arFileOfferPropList[$arProp['CODE']] = $strPropName;
		if ('N' != $arProp['MULTIPLE'])
		    continue;
		if (
				//'L' == $arProp['PROPERTY_TYPE']
				//|| 'E' == $arProp['PROPERTY_TYPE']
				/*||*/ ('S' == $arProp['PROPERTY_TYPE'] && 'directory' == $arProp['USER_TYPE'] && CIBlockPriceTools::checkPropDirectory($arProp))
		)
		$arTreeOfferPropList[$arProp['CODE']] = $strPropName;


	}
}

$arTemplateParameters['OFFER_TREE_PROPS'] = array(
			'PARENT' => 'MISS_SHOP',
			'NAME' => GetMessage('CP_BC_TPL_OFFER_TREE_PROPS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => $arTreeOfferPropList
);

$arTemplateParameters['OFFER_COLOR_PROP'] = array(
			'PARENT' => 'MISS_SHOP',
			'NAME' => GetMessage('MS_TPL_OFFER_COLOR_PROP'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => $arTreeOfferPropList
);
$arTemplateParameters['MANUFACTURER_ELEMENT_PROPS'] = array(
			'PARENT' => 'MISS_SHOP',
			'NAME' => GetMessage('MS_TPL_OFFER_MANUFACTURER_ELEMENT_PROPS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => $arElementPropList
);
$arTemplateParameters['MANUFACTURER_LIST_PROPS'] = array(
			'PARENT' => 'MISS_SHOP',
			'NAME' => GetMessage('MS_TPL_OFFER_MANUFACTURER_LIST_PROPS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => array_merge($arListPropList, $arStringPropList)
);
$arTemplateParameters["PICTURE_FROM_OFFER"] = array(
		"PARENT" => "MISS_SHOP",
		"NAME" => GetMessage('MS_TPL_MORE_PHOTO_PICTURE_FROM_OFFER'),
		"TYPE" => "CHECKBOX",
        'REFRESH' => 'Y',
		"DEFAULT" => "N"
);
$arTemplateParameters['MORE_PHOTO_PRODUCT_PROPS'] = array(
			'PARENT' => 'MISS_SHOP',
			'NAME' => GetMessage('MS_TPL_MORE_PHOTO_PRODUCT_PROPS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => $arFilePropList
);

if (isset($arCurrentValues['PICTURE_FROM_OFFER']) && $arCurrentValues['PICTURE_FROM_OFFER']=="Y")
{
    $arTemplateParameters['MORE_PHOTO_OFFER_PROPS'] = array(
			'PARENT' => 'MISS_SHOP',
			'NAME' => GetMessage('MS_TPL_MORE_PHOTO_OFFER_PROPS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => $arFileOfferPropList
    );
}

$arTemplateParameters['IMG_WIDTH'] = array(
			'PARENT' => 'MISS_SHOP',
			'NAME' => GetMessage('MS_TPL_IMG_WIDTH'),
			'TYPE' => 'STRING',
			'DEFAULT' => '80'
);
$arTemplateParameters['IMG_HEIGHT'] = array(
			'PARENT' => 'MISS_SHOP',
			'NAME' => GetMessage('MS_TPL_IMG_HEIGHT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '120'
);


?>