<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Filter;
use Sotbit\B2bCabinet\Helper;
use Bitrix\Main\Localization\Loc;

$prop = array();
$arResult["FULL_QUANTITY"] = 0;
$docIblockId = Helper\Document::getIblocks();

foreach ($arResult['ORDER_PROPS'] as $key => $ORDER_PROP) {
    $arResult['ORDER_PROPS'][$ORDER_PROP['CODE']] = $ORDER_PROP;
    unset($arResult['ORDER_PROPS'][$key]);
}

// ----- PRODUCTS
if(!empty($arResult['BASKET'])) {
    $filterOption = new Filter\Options('PRODUCT_LIST');
    $filterData = array();
    $filterData = $filterOption->getFilter([]);

    $productFilter = [
        'ID',
        'NAME',
        'ARTICLE',
        'QUANTITY',
        'PRICE',
        'SUM',
        'FIND'
    ];
    $filter = [];

    foreach ($arResult['BASKET'] as $key => &$item) {
        $res = CIBlockElement::GetByID($item['PRODUCT_ID'])->GetNextElement();
        if ($res !== false)
            $item["PROPERTIES"] = $res->GetProperties();
        $arResult["FULL_QUANTITY"] += $item['QUANTITY'];

        if ($filterData) {
            foreach ($filterData as $key => $value) {
                if (in_array($key, $productFilter)) {
                    $filter[$key] = $value;
                }
            }
        }
        $needContinue = false;

        foreach ($filter as $key => $value) {
            $sum = $item['QUANTITY'] * $item['PRICE'];
            $sum = (string)$sum;

            if ($key == 'SUM' && $sum != $value) {
                if (strpos($sum, $value) === false)
                    $needContinue = true;
            }
            if ($key == 'PRICE') {
                if (strpos((string)$item['BASE_PRICE'], $value) === false)
                    $needContinue = true;
            } elseif ($key == 'NAME' || $key == 'FIND') {
                if (strpos(strtolower($item['NAME']), strtolower($value)) === false) {
                    $needContinue = true;
                }
            } elseif ($key == 'ARTICLE') {
                if (strpos($item['PROPERTY_CML2_ARTICLE_VALUE'], $value) === false) {
                    $needContinue = true;
                }
            } elseif ($key != 'SUM' && $item[$key] != $value) {
                $needContinue = true;
                break;
            }
        }

        if ($needContinue) {
            continue;
        }

        $productCols = [
            'ID' => $item['ID'],
            'ARTICLE' => $item['PROPERTY_CML2_ARTICLE_VALUE'],
            'NAME' => $item['NAME'],
            'QUANTITY' => $item['QUANTITY'],
            'DISCOUNT' => $item['DISCOUNT_PRICE_PERCENT_FORMATED'],
            'PRICE' => $item['BASE_PRICE_FORMATED'],
            'SUM' => $item['FORMATED_SUM'],
        ];

        $arResult['PRODUCT_ROWS'][] = [
            'data' => [
                'ID' => $item['ID'],
                'ARTICLE' => $item['PROPERTY_CML2_ARTICLE_VALUE'],
                'NAME' => $item['NAME'],
                'QUANTITY' => $item['QUANTITY'],
                'DISCOUNT' => $item['DISCOUNT_PRICE_PERCENT_FORMATED'],
                'PRICE' => $item['BASE_PRICE_FORMATED'],
                'SUM' => $item['FORMATED_SUM'],
            ],
            'actions' => [],
            'COLUMNS' => $productCols,
            'editable' => true,
        ];
    }
}
// ----- PRODUCTS_2
if(!empty($arResult['BASKET']))
{
    $filterOption = new Filter\Options('PRODUCT_LIST_2');
    $filterData = array();
    $filterData = $filterOption->getFilter([]);

    $productFilter = [
        'ID',
        'NAME',
        'ARTICLE',
        'QUANTITY',
        'SUM',
        'FIND'
    ];
    $filter = [];

    foreach ($arResult['BASKET'] as $key => &$item) {

        if ($filterData) {
            foreach ($filterData as $key => $value) {
                if (in_array($key, $productFilter)) {
                    $filter[$key] = $value;
                }
            }
        }
        $needContinue = false;

        foreach ($filter as $key => $value) {
            $sum = $item['QUANTITY'] * $item['PRICE'];
            $sum = (string)$sum;

            if ($key == 'SUM' && $sum != $value) {
                if (strpos($sum, $value) === false)
                    $needContinue = true;
            } elseif ($key == 'NAME' || $key == 'FIND') {
                if (strpos(strtolower($item['NAME']), strtolower($value)) === false) {
                    $needContinue = true;
                }
            } elseif ($key == 'ARTICLE') {
                if (strpos($item['PROPERTY_CML2_ARTICLE_VALUE'], $value) === false) {
                    $needContinue = true;
                }
            } elseif ($key != 'SUM' && $item[$key] != $value) {
                $needContinue = true;
                break;
            }
        }

        if ($needContinue) {
            continue;
        }

        $productCols = [
            'ID' => $item['ID'],
            'ARTICLE' => $item['PROPERTY_CML2_ARTICLE_VALUE'],
            'NAME' => $item['NAME'],
            'QUANTITY' => $item['QUANTITY'],
            'SUM' => $item['QUANTITY'] * $item['PRICE']
        ];

        $arResult['PRODUCT_2_ROWS'][] = [
            'data' => [
                'ID' => $item['ID'],
                'ARTICLE' => $item['PROPERTY_CML2_ARTICLE_VALUE'],
                'NAME' => $item['NAME'],
                'QUANTITY' => $item['QUANTITY'],
                'SUM' => $item['FORMATED_SUM']
            ],
            'actions' => [],
            'COLUMNS' => $productCols,
            'editable' => true,
        ];
    }
}

// ----- DOCS
if(!empty($docIblockId) && !empty($arResult['ID'])) {
    $filterOption = new Filter\Options('DOCUMENTS_LIST');
    $filterData = array();
    $filterData = $filterOption->getFilter([]);

    $productFilter = [
        'NUMBER',
        'DOC',
        'DATE_CREATED_from',
        'DATE_CREATED_to',
        'DATE_UPDATED_from',
        'DATE_UPDATED_to',
        'DATE_UPDATE',
        'ORGANIZATION',
        'FIND'
    ];
    $filter = [];

    $arRes = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $docIblockId, 'PROPERTY_ORDER' => $arResult['ID']],
        false,
        false,
        ['ID', 'NAME', 'DATE_CREATE', 'TIMESTAMP_X', 'DETAIL_TEXT', 'PROPERTY_DOCUMENT']
    );
    while($doc = $arRes->Fetch()) {
        if($filterData)
        {
            foreach ($filterData as $key => $value)
            {
                if(in_array($key, $productFilter))
                {
                    $filter[$key] = $value;
                }
            }
        }
        $needContinue = false;

        foreach ($filter as $key => $value) {
            if ($key == 'NUMBER' && $doc['ID'] != $value) {
                $needContinue = true;
            } elseif($key == 'DOC' || $key == 'FIND') {
                if (strpos($doc['NAME'], $value) === false) {
                    $needContinue = true;
                }
            } elseif(in_array(strtolower($key), ['date_created_from', 'date_created_to'])) {
                $date = strtotime($doc['DATE_CREATE']);

                $start = $filter['DATE_CREATED_from'];
                $end = $filter['DATE_CREATED_to'];
                if ($date < strtotime($start) || $date > strtotime($end)) {
                    $needContinue = true;
                    break;
                }
            } elseif(in_array(strtolower($key), ['date_updated_from', 'date_updated_to'])) {
                $date = strtotime($doc['TIMESTAMP_X']);

                $start = $filter['DATE_UPDATED_from'];
                $end = $filter['DATE_UPDATED_to'];
                if($date < strtotime($start) || $date > strtotime($end))
                {
                    $needContinue = true;
                    break;
                }
            } elseif($item[$key] != $value) {
                $needContinue = true;
                break;
            }
        }

        if($needContinue)
        {
            continue;
        }

        // Url document file
        if(!empty($doc['PROPERTY_DOCUMENT_VALUE']))
            $doc['PROPERTY_DOCUMENT_VALUE'] = Bitrix\Main\FileTable::getById($doc['PROPERTY_DOCUMENT_VALUE'])->fetch();

        $docsCols = [
            'ID' => $doc['ID'],
            'NUMBER' => $doc['ID'],
            'DOC' => $doc['NAME'],
            'DATE_CREATED' => $doc['DATE_CREATE'],
            'DATE_UPDATED' => $doc['TIMESTAMP_X'],
            'ORGANIZATION' => $doc['DETAIL_TEXT']
        ];

        $arResult['DOCS_ROWS'][] = [
            'data' => [
                'ID' => $doc['ID'],
                'NUMBER' => $doc['ID'],
                'DOC' => $doc['NAME'],
                'DATE_CREATED' => $doc['DATE_CREATE'],
                'DATE_UPDATED' => $doc['TIMESTAMP_X'],
                'ORGANIZATION' => $doc['DETAIL_TEXT']
            ],
            'actions' => [
                array(
                    "ICONCLASS" => "download",
                    "TEXT"      => (!empty($doc['PROPERTY_DOCUMENT_VALUE']) ? Loc::getMessage('SPOD_DOWNLOAD_DOC') : Loc::getMessage('SPOD_DOWNLOAD_DOC_NOT_FOUND')),
                    "ONCLICK"   => (!empty($doc['PROPERTY_DOCUMENT_VALUE']) ? "window.location.href='/upload/".$doc['PROPERTY_DOCUMENT_VALUE']['SUBDIR']."/"
                        .$doc['PROPERTY_DOCUMENT_VALUE']['FILE_NAME']."'" : ""),
                    "DEFAULT"   => true,
                ),
            ],
            'COLUMNS' => $docsCols,
            'editable' => true,
        ];

//        $arResult['DOCS'][] = $doc;
    }
}

// ----- PAYMENT_SYSTEMS
if(!empty($arResult['PAYMENT']))
{
    $filterOption = new Filter\Options('PAY_SYSTEMS_LIST');
    $filterData = array();
    $filterData = $filterOption->getFilter([]);

    $productFilter = [
        'NUMBER',
        'DOC',
        'DATE_CREATED_from',
        'DATE_CREATED_to',
        'DATE_UPDATED_from',
        'DATE_UPDATED_to',
        'DATE_UPDATE',
        'ORGANIZATION',
        'FIND'
    ];
    $filter = [];

    if ($filterData) {
        foreach ($filterData as $key => $value) {
            if (in_array($key, $productFilter)) {
                $filter[$key] = $value;
            }
        }
    }
    $needContinue = false;


    foreach ($arResult['PAYMENT'] as $payment) {
        foreach ($filter as $key => $value) {
            if ($key == 'NUMBER' && $payment['ID'] != $value) {
                $needContinue = true;
            } elseif ($key == 'DOC' || $key == 'FIND') {
                if (strpos($payment['PAY_SYSTEM']['NAME'], $value) === false) {
                    $needContinue = true;
                }
            } elseif (in_array(strtolower($key), ['date_created_from', 'date_created_to'])) {
                $date = strtotime($payment['DATE_BILL']);

                $start = $filter['DATE_CREATED_from'];
                $end = $filter['DATE_CREATED_to'];
                if ($date < strtotime($start) || $date > strtotime($end)) {
                    $needContinue = true;
                    break;
                }
            } elseif (in_array(strtolower($key), ['date_updated_from', 'date_updated_to'])) {
                $date = strtotime($payment['DATE_BILL']);

                $start = $filter['DATE_UPDATED_from'];
                $end = $filter['DATE_UPDATED_to'];
                if ($date < strtotime($start) || $date > strtotime($end)) {
                    $needContinue = true;
                    break;
                }
            } elseif ($payment[$key] != $value) {
                $needContinue = true;
                break;
            }
        }

//    if ($needContinue) {
//        continue;
//    }

        $pSystemCols = [
            'ID'           => $payment['ID'],
            'NUMBER'       => $payment['ACCOUNT_NUMBER'],
            'NAME'         => $payment['NAME'],
            'DATE_CREATED' => $arResult['DATE_INSERT_FORMATED'],
            //'DATE_UPDATED' => $payment['DATE_UPDATE_FORMATED'],
            'SUM'         => $payment['PRICE_FORMATED'],
            'IS_PAID'         => ($payment['PAID'] && $payment['PAID'] == "Y" ? GetMessage("SPOL_PAYMENT_IS_PAID_Y")
                : GetMessage("SPOL_PAYMENT_IS_PAID_N")),
            'ORGANIZATION' => (
            isset($arResult['ORDER_PROPS']['FIO']) && !empty($arResult['ORDER_PROPS']['FIO']) ?
                $arResult['ORDER_PROPS']['FIO']['NAME'] : $arResult['ORDER_PROPS']['COMPANY']['NAME']
            )
        ];

        $arResult['PAY_SYSTEM_ROWS'][] = [
            'data'     => [
                'ID'           => $payment['ID'],
                'NUMBER'       => $payment['ACCOUNT_NUMBER'],
                'NAME'     => $payment['PAY_SYSTEM']['NAME'],
                'DATE_CREATED' => $arResult['DATE_INSERT_FORMATED'],
                //'DATE_UPDATED' => $payment['DATE_UPDATE_FORMATED'],
                'SUM'         => $payment['PRICE_FORMATED'],
                'IS_PAID'         => ($payment['PAID'] && $payment['PAID'] == "Y" ? GetMessage("SPOL_PAYMENT_IS_PAID_Y")
                    : GetMessage("SPOL_PAYMENT_IS_PAID_N")),
                'ORGANIZATION' => (
                isset($arResult['ORDER_PROPS']['FIO']) && !empty($arResult['ORDER_PROPS']['FIO']) ?
                    $arResult['ORDER_PROPS']['FIO']['NAME'] : $arResult['ORDER_PROPS']['COMPANY']['NAME']
                )
            ],
            'actions'  => [],
            'COLUMNS'  => $pSystemCols,
            'editable' => true,
        ];
    }
}

// ----- SHIPMENT
if(!empty($arResult['SHIPMENT']))
{
    $filterOption = new Filter\Options('SHIPMENT_LIST');
    $filterData = array();
    $filterData = $filterOption->getFilter([]);

    $productFilter = [
        'NUMBER',
        'NAME',
        'STATUS',
        'SUMM',
        'FIND'
    ];
    $filter = [];

    foreach ($arResult['SHIPMENT'] as $item)
    {

        if ($filterData) {
            foreach ($filterData as $key => $value) {
                if (in_array($key, $productFilter)) {
                    $filter[$key] = $value;
                }
            }
        }
        $needContinue = false;

        foreach ($filter as $key => $value) {
            if ($key == 'NUMBER' && $item['ID'] != $value) {
                $needContinue = true;
            } elseif ($key == 'NAME' || $key == 'FIND') {
                if (strpos($item['DELIVERY_NAME'], $value) === false) {
                    $needContinue = true;
                }
            }
//            elseif ($key == 'STATUS') {
//                if (strpos($item['STATUS'], $value) === false) {
//                    $needContinue = true;
//                }
//            }
            elseif ($item[$key] != $value) {
                $needContinue = true;
                break;
            }
        }

        if ($needContinue) {
            continue;
        }

        $shipmentCols = [
            'ID' => $item['ID'],
            'NUMBER' => $item['ID'],
            'NAME' => $item['DELIVERY_NAME'],
            'STATUS' => $item['STATUS_NAME'],
            'SUMM' => $item['PRICE_DELIVERY_FORMATED']
        ];

        $arResult['SHIPMENT_ROWS'][] = [
            'data' => [
                'ID' => $item['ID'],
                'NUMBER' => $item['ID'],
                'NAME' => $item['DELIVERY_NAME'],
                'STATUS' => $item['STATUS_NAME'],
                'SUMM' => $item['PRICE_DELIVERY_FORMATED']
            ],
            'actions' => [],
            'COLUMNS' => $shipmentCols,
            'editable' => true,
        ];
    }
}

if(Loader::includeModule('support'))
{
    $tickets = CTicket::GetList(
        $by = "ID",
        $order = "asc",
        array('UF_ORDER' => $arResult['ID'], 'CREATED_BY' => $USER->GetID()),
        $isFiltered,
        "Y",
        "Y",
        "Y",
        SITE_ID,
        array()
    );

    if(!empty($tickets))
    {
        $arResult['TICKET'] = $tickets->fetch();
    }
}
?>