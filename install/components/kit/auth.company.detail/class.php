<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2016 Bitrix
 */

use Bitrix\Main,
	Bitrix\Main\Config,
	Bitrix\Main\Localization,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Sale,
	Bitrix\Iblock,
	Bitrix\Main\Data,
	Bitrix\Sale\Location,
    Bitrix\Sale\Internals\OrderPropsTable;

use Kit\Auth\Internals\CompanyTable,
    Kit\Auth\Internals\StaffTable,
    Kit\Auth\Company;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class PersonalProfileDetail extends CBitrixComponent
{
	const E_SALE_MODULE_NOT_INSTALLED = 10000;
	const E_NOT_AUTHORIZED = 10001;

	/** @var  Main\ErrorCollection $errorCollection*/
	protected $errorCollection;

	protected $idCompany;
	protected $companyData;

	/**
	 * Function checks and prepares all the parameters passed. Everything about $arParam modification is here.
	 * @param $params		Parameters of component.
	 * @return array		Checked and valid parameters.
	 */
	public function onPrepareComponentParams($params)
	{
		global $APPLICATION;

		$this->errorCollection = new Main\ErrorCollection();

		$this->idCompany = 0;

		if (isset($params['ID']) && $params['ID'] > 0)
		{
			$this->idCompany = (int)$params['ID'];
		}
		else
		{
			$request = Main\Application::getInstance()->getContext()->getRequest();
			$this->idCompany = (int)($request->get('ID'));
		}

		if (isset($params['PATH_TO_LIST']))
		{
			$params['PATH_TO_LIST'] = trim($params['PATH_TO_LIST']);
		}
		elseif ($this->idCompany)
		{
			$params['PATH_TO_LIST'] = htmlspecialcharsbx($APPLICATION->GetCurPage());
		}
		else
		{
			return false;
		}

		if ($params["PATH_TO_DETAIL"] !== '')
		{
			$params["PATH_TO_DETAIL"] = trim($params["PATH_TO_DETAIL"]);
		}
		else
		{
			$params["PATH_TO_DETAIL"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?ID=#ID#");
		}

		if (!isset($params['COMPATIBLE_LOCATION_MODE']) && $this->initComponentTemplate())
		{
			$template = $this->getTemplate();
			if ($template instanceof CBitrixComponentTemplate
				&& $template->GetSiteTemplate() == ''
				&& $template->GetName() == '.default'
			)
				$params['COMPATIBLE_LOCATION_MODE'] = 'N';
			else
				$params['COMPATIBLE_LOCATION_MODE'] = 'Y';
		}
		else
		{
			$arParams['COMPATIBLE_LOCATION_MODE'] = $params['COMPATIBLE_LOCATION_MODE'] == 'Y' ? 'Y' : 'N';
		}

		return $params;
	}

	public function executeComponent()
	{
		global $USER, $APPLICATION;

		Loc::loadMessages(__FILE__);

		$this->setFrameMode(false);

		$this->checkRequiredModules();

		if (!$USER->IsAuthorized())
		{
			if(!$this->arParams['AUTH_FORM_IN_TEMPLATE'])
			{
				$APPLICATION->AuthForm(Loc::getMessage("SALE_ACCESS_DENIED"), false, false, 'N', false);
			}
			else
			{
				$this->errorCollection->setError(new Main\Error(Loc::getMessage("SALE_ACCESS_DENIED"), self::E_NOT_AUTHORIZED));
			}
		}

		$request = Main\Application::getInstance()->getContext()->getRequest();

		/*if ($this->arParams['AJAX_CALL'] === 'Y')
		{
			return $this->responseAjax();
		}*/

		if ($this->arParams["SET_TITLE"] === 'Y')
		{
			$APPLICATION->SetTitle(Loc::getMessage("SPPD_TITLE").$this->idCompany);
		}

		if ($this->idCompany <= 0 || $request->get('reset'))
		{
			if (!empty($this->arParams["PATH_TO_LIST"]))
			{
				LocalRedirect($this->arParams["PATH_TO_LIST"]);
			}
			else
			{
				$this->errorCollection->setError(new Main\Error(Loc::getMessage("AUTH_NO_COMPANY")));
			}
		}


            $company = StaffTable::getList( [
                'filter' => [
                    'USER_ID' => (int)($USER->GetID()),
                    'RELATED_COMPANY_ID' => $this->idCompany,
                    'STATUS' => 'Y',
                ],
                'select' => ['RELATED_COMPANY_'=>'COMPANY'],
                'limit' => 1
            ] )->fetch();

            if(empty($company)) {
                $this->errorCollection->setError(new Main\Error(Loc::getMessage("AUTH_NO_COMPANY")));
            }



        if($this->errorCollection->isEmpty())
		{
		    $companyProps = Company\Company::getCompanyProps($this->idCompany);

			if ($companyProps)
			{
                $this->companyData = $company;
                $this->companyData['ID'] = $company['RELATED_COMPANY_ID'];
                $this->fillResultArray($companyProps);
			}
			else
			{
				$this->errorCollection->setError(new Main\Error(Loc::getMessage("AUTH_NO_COMPANY")));
			}
		}

		$this->formatResultErrors();

		$this->includeComponentTemplate();
	}

	/**
	 * Function checks if required modules installed. If not, throws an exception
	 * @throws Main\SystemException
	 * @return void
	 */
	protected function checkRequiredModules()
	{
        if (!Loader::includeModule('sale'))
        {
            throw new Main\SystemException(Loc::getMessage("SALE_MODULE_NOT_INSTALL"), self::E_SALE_MODULE_NOT_INSTALLED);
        }
        if (!Loader::includeModule('kit.auth'))
        {
            throw new Main\SystemException(Loc::getMessage("KIT_AUTH_MODULE_NOT_INSTALL"), self::E_SALE_MODULE_NOT_INSTALLED);
        }
	}

	/**
	 * Load html for multiply location input
	 *
	 * @param $name
	 * @param $key
	 * @param $locationTemplate
	 *
	 * @return string
	 */
	protected function getLocationHtml($name, $key, $locationTemplate)
	{
		$name = $name <> '' ? $name : "" ;
		$key = (int)$key >= 0 ? (int)$this->arParams['LOCATION_KEY'] : 0;
		$locationTemplate = $locationTemplate <> '' ? $locationTemplate : '';
		$locationClassName = 'location-block-wrapper';
		if (empty($locationTemplate))
		{
			$locationClassName .= ' location-block-wrapper-delimeter';
		}
		ob_start();
		CSaleLocation::proxySaleAjaxLocationsComponent(
			array(
				"ID" => "propertyLocation".$name."[$key]",
				"AJAX_CALL" => "N",
				'CITY_OUT_LOCATION' => 'Y',
				'COUNTRY_INPUT_NAME' => $name.'_COUNTRY',
				'CITY_INPUT_NAME' => $name."[$key]",
				'LOCATION_VALUE' => ''
			),
			array(
			),
			$locationTemplate,
			true,
			$locationClassName
		);
		$resultHtml = ob_get_contents();
		ob_end_clean();
		return $resultHtml;
	}


	/**
	 * Fill $arResult array for output in template 
	 * @param $property
	 * @throws Main\ArgumentException
	 * @return void
	 */
	protected function fillResultArray($property)
	{
        $arBuyerTypes = Company\Sale::getBuyerTypes();
		$this->arResult["ORDER_PROPS"] = array();
		$this->arResult['PROP'] = $property;

		$this->arResult["TITLE"] = Loc::getMessage("SPPD_PROFILE_NO", array("#ID#" => $this->companyData['RELATED_COMPANY_ID']));
        $this->arResult['ID'] = $this->companyData['ID'];
        $this->arResult['NAME'] = $this->companyData['RELATED_COMPANY_NAME'];

		$personType = $this->companyData['RELATED_COMPANY_BUYER_TYPE'];

		$this->arResult["PERSON_TYPE_ID"] = $personType;
		$this->arResult["PERSON_TYPE_NAME"] = $arBuyerTypes[$personType];

		$locationValue = array();

		if ($this->arParams['COMPATIBLE_LOCATION_MODE'] == 'Y')
		{
			$locationDb = CSaleLocation::GetList(
				array("SORT" => "ASC", "COUNTRY_NAME_LANG" => "ASC", "CITY_NAME_LANG" => "ASC"),
				array(),
				LANGUAGE_ID
			);
			while ($location = $locationDb->Fetch())
			{
				$locationValue[] = $location;
			}
		}

		$arrayTmp = array();
		$propertyIds = array();
		$orderPropertiesListGroup = CSaleOrderPropsGroup::GetList(
			array("SORT" => "ASC", "NAME" => "ASC"),
			array("PERSON_TYPE_ID" => $this->arResult["PERSON_TYPE_ID"]),
			false,
			false,
			array("ID", "PERSON_TYPE_ID", "NAME", "SORT")
		);

		while ($orderPropertyGroup = $orderPropertiesListGroup->GetNext())
		{

			$arrayTmp[$orderPropertyGroup["ID"]] = $orderPropertyGroup;
            $orderPropertiesList = CSaleOrderProps::GetList(
                array("SORT" => "ASC", "NAME" => "ASC"),
                array(
                    "PERSON_TYPE_ID" => $this->arResult["PERSON_TYPE_ID"],
                    "PROPS_GROUP_ID" => $orderPropertyGroup["ID"],
                    "USER_PROPS" => "Y", "ACTIVE" => "Y", "UTIL" => "N"
                ),
                false,
                false,
                array("ID", "PERSON_TYPE_ID", "NAME", "TYPE", "REQUIED", "DEFAULT_VALUE", "SORT", "USER_PROPS",
                    "IS_LOCATION", "PROPS_GROUP_ID", "SIZE1", "SIZE2", "DESCRIPTION", "IS_EMAIL", "IS_PROFILE_NAME",
                    "IS_PAYER", "IS_LOCATION4TAX", "CODE", "SORT", "MULTIPLE")
            );
            while ($orderProperty = $orderPropertiesList->GetNext())
            {

                $val = $this->arResult['PROP'][$orderProperty['ID']];
                if($val) {
                    if(empty($val['VALUE']) && !empty($val['COMPANY_PROPERTY_DEFAULT_VALUE'])) {
                        $val["VALUE"] = $val['COMPANY_PROPERTY_DEFAULT_VALUE'];
                    }

                    $val['TYPE'] = $val['COMPANY_PROPERTY_TYPE'];
                    if($val['TYPE'] === "STRING")
                        $val['TYPE'] = "TEXT";

                    if($val['TYPE'] == "ENUM"){
                        $dbEnum = CSaleOrderPropsVariant::GetList([], ["ORDER_PROPS_ID"=>"33"], false, false, []);
                        while($variant = $dbEnum->fetch()){
                            $val["VARIANTS"][] = $variant;
                        }
                    }

                    $val['REQUIRED'] = $val['COMPANY_PROPERTY_REQUIRED'];
                    $val['CODE'] = $val['COMPANY_PROPERTY_CODE'];

                    $arrayTmp[$orderPropertyGroup["ID"]]["PROPS"][] = $val;
                    $orderPropertyList[$val['COMPANY_PROPERTY_ID']] = $val;


                    /*if ($val["TYPE"] === "LOCATION" && $this->arParams['COMPATIBLE_LOCATION_MODE'] === 'Y')
                    {
                        $val["VALUES"] = $locationValue;
                    }

                    if ($val['TYPE'] === 'LOCATION')
                    {
                        $locationMap = array();
                        $locationData = Sale\Location\LocationTable::getList(
                            array(
                                'filter' => array('=CODE' => $value),
                                'select' => array('ID', 'CODE')
                            )
                        );
                        while ($location = $locationData->fetch())
                        {
                            $locationMap[] = $location['ID'];
                        }
                        $val["VALUE"] = ($orderPropertyList[$propertyId]['MULTIPLE'] === 'Y') ? $locationMap : $locationMap[0];
                    }*/
                }
            }

			$this->arResult["ORDER_PROPS"] = $arrayTmp;
		}



		// get prop values
		$propertiesValueList = Array();

		$htmlConvector = \Bitrix\Main\Text\Converter::getHtmlConverter();

		/*$profileData = Sale\OrderUserProperties::getProfileValues((int)($this->idCompany));
		if (!empty($profileData))
		{
			foreach ($profileData as $propertyId => $value)
			{


				if (is_array($value))
				{
					foreach ($value as &$elementValue)
					{
						if (!is_array($elementValue))
							$elementValue = $htmlConvector->encode($elementValue);
						else
							$elementValue = htmlspecialcharsEx($elementValue);
					}
				}
				else
				{
					$value = $htmlConvector->encode($value);
				}
				$propertiesValueList["ORDER_PROP_" . $propertyId] = $value;
			}
		}*/

		$this->arResult["ORDER_PROPS_VALUES"] = $propertiesValueList;
	}

	/**
	 * Move all errors to $this->arResult, if there were any
	 * @return void
	 */
	protected function formatResultErrors()
	{
		if (!$this->errorCollection->isEmpty())
		{
			/** @var Main\Error $error */
			foreach ($this->errorCollection->toArray() as $error)
			{
				$this->arResult['ERROR_MESSAGE'] .= $error->getMessage();

				if ($error->getCode())
					$this->arResult['ERRORS'][$error->getCode()] = $error->getMessage();
				else
					$this->arResult['ERRORS'][] = $error->getMessage();
			}
		}
	}

	/**
	 * Check value required params of property
	 * @param $property
	 * @param $currentValue
	 * @return bool
	 */
	protected function checkProperty($property, $currentValue)
	{
		if ($property["TYPE"] == "LOCATION" && $property["IS_LOCATION"] == "Y")
		{
			if ((int)($currentValue) <= 0)
				return false;
		}
		elseif ($property["IS_PROFILE_NAME"] == "Y")
		{
			if (trim($currentValue) == '')
				return false;
		}
		elseif ($property["IS_PAYER"] == "Y")
		{
			if (trim($currentValue) == '')
				return false;
		}
		elseif ($property["IS_EMAIL"] == "Y")
		{
			if (trim($currentValue) == '' || !check_email(trim($currentValue)))
				return false;
		}
		elseif ($property["REQUIED"] == "Y")
		{
			if ($property["TYPE"] == "LOCATION")
			{
				if ((int)($currentValue) <= 0)
					return false;
			}
			elseif ($property["TYPE"] == "MULTISELECT" || $property["MULTIPLE"] == "Y")
			{
				if (!is_array($currentValue) || count($currentValue) <= 0)
					return false;
			}
			else
			{
				if ($currentValue == '')
					return false;
			}
		}

		return true;
	}
}