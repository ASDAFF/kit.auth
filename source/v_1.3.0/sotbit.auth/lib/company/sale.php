<?php

namespace Sotbit\Auth\Company;

use  Sotbit\Auth\Internals\CompanyTable;
use  Sotbit\Auth\Internals\StaffTable;
use  Sotbit\Auth\Internals\CompanyPropsValueTable;
use  Sotbit\Auth\Internals\RolesTable;
use  Sotbit\Auth\Internals\FileTable;
use  Bitrix\Main\Application;
use  Bitrix\Main\Entity\Base;
use  Bitrix\Sale\Internals\OrderPropsTable;
use  Bitrix\Main\Localization\Loc;
use  Bitrix\Main\Loader;

Loc::loadMessages( __FILE__ );
/**
 * Class for work with module sale
 *
 */
class Sale extends \SotbitAuth
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sale order props
     *
     * @return array
     */
    public function getOrderProps() {

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

        return $arSaleOrderProps;
    }

    /**
     * Buyer types
     *
     * @return array
     */
    public function getBuyerTypes() {
        $arrBuyerTypes = [];
        $q = \CSalePersonType::GetList(Array("SORT" => "ASC"), Array());
        while ($typeBuyers = $q->Fetch())
        {
            $arrBuyerTypes[$typeBuyers['ID']] = $typeBuyers['NAME'];
        }
        return $arrBuyerTypes;
    }

    /**
     * @param Bitrix\Sale\Order $order
     *
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function getOrderUserEmail($order) {
        global $USER;

        $userEmail = "";

        if($order instanceof \Bitrix\Sale\Order) {
            /** @var \Bitrix\Sale\PropertyValueCollection $propertyCollection */
            if($propertyCollection = $order->getPropertyCollection()) {
                // Looks for an order property that has an IS_EMAIL flag
                if($propUserEmail = $propertyCollection->getUserEmail()) {
                    $userEmail = $propUserEmail->getValue();
                } else {
                    /** @var \Bitrix\Sale\PropertyValue $orderProperty */
                    foreach($propertyCollection as $orderProperty) {
                        if($orderProperty->getField('CODE') === 'NAME') {
                            $userEmail = $orderProperty->getValue();
                            break;
                        }
                    }
                }
            }
        }

        return trim($userEmail);
    }
}