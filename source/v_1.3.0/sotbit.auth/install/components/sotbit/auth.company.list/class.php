<?php

use Bitrix\Main,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    Sotbit\Auth\Company;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class PersonalProfileList extends CBitrixComponent
{
    const E_SALE_MODULE_NOT_INSTALLED 		= 10000;
    const E_NOT_AUTHORIZED					= 10001;

    /** @var  Main\ErrorCollection $errorCollection*/
    protected $errorCollection;


    public function onPrepareComponentParams($params)
    {
        $this->errorCollection = new Main\ErrorCollection();

        $params["PATH_TO_DETAIL"] = trim($params["PATH_TO_DETAIL"]);

        if ($params["PATH_TO_DETAIL"] == '')
        {
            $params["PATH_TO_DETAIL"] = htmlspecialcharsbx(Main\Context::getCurrent()->getRequest()->getRequestedPage()."?ID=#ID#");
        }

        $params["PER_PAGE"] = ((int)($params["PER_PAGE"]) <= 0 ? 20 : (int)($params["PER_PAGE"]));

        return $params;
    }

    public function executeComponent()
    {
        global $APPLICATION, $USER;

        Loc::loadMessages(__FILE__);

        $this->setFrameMode(false);

        $this->checkRequiredModules();

        $this->arResult['ERRORS'] = array();
        if (!$USER->IsAuthorized())
        {
            if(!$this->arParams['AUTH_FORM_IN_TEMPLATE'])
            {
                $this->arResult['USER_IS_NOT_AUTHORIZED'] = 'Y';
                $APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"), false, false, 'N', false);
            }
            else
            {
                $this->arResult['ERRORS'][self::E_NOT_AUTHORIZED] = GetMessage("SALE_ACCESS_DENIED");
            }
        }

        if($this->arParams["SET_TITLE"] == 'Y')
            $APPLICATION->SetTitle(GetMessage("SPPL_DEFAULT_TITLE"));

        $request = Main\Context::getCurrent()->getRequest();

        $errorMessage = "";

        if($errorMessage <> '')
        {
            $this->arResult["ERROR_MESSAGE"] = $errorMessage;
            $this->arResult['ERRORS'][] = $errorMessage;
        }

        $by = ($_REQUEST["by"] <> '' ? $_REQUEST["by"]: "DATE_UPDATE");
        $order = ($_REQUEST["order"] <> '' ? $_REQUEST["order"]: "DESC");

        $company = new Company\Company();

        $dbCompanies = $company->getCompaniesAllByUserID((int)($GLOBALS["USER"]->GetID()), ['RELATED_COMPANY_STATUS' => 'DESC']);

        if(\Sotbit\Auth\Internals\CompanyTable::getList([
            'filter' => ['!ID' => array_keys($dbCompanies), 'ACTIVE' => 'Y', 'STATUS' => 'A'],
            'select' => ['ID']
        ])->fetch()){
            $this->arResult["ÑAN_JOIN"] = "Y";
        }
        $arBuyerTypes = Company\Sale::getBuyerTypes();

        $rs = new CDBResult;
        $rs->InitFromArray($dbCompanies);
        $rs->NavStart($this->arParams["PER_PAGE"]);
        $this->arResult["NAV_STRING"] = $rs->GetPageNavString(GetMessage("SPPL_PAGES"));
        $this->arResult["PROFILES"] = Array();
        while($arCompanyProps = $rs->GetNext())
        {
            $arResultTmp = [
                'ID' => $arCompanyProps['RELATED_COMPANY_ID'],
                'NAME' => $arCompanyProps['RELATED_COMPANY_NAME'],
                'PERSON_TYPE_ID' => $arCompanyProps['RELATED_COMPANY_BUYER_TYPE'],
                'PERSON_TYPE_NAME' => $arBuyerTypes[$arCompanyProps["RELATED_COMPANY_BUYER_TYPE"]],
                'DATE_CREATE' => $arCompanyProps['RELATED_COMPANY_DATE_CREATE'],
                'DATE_UPDATE' => $arCompanyProps['RELATED_COMPANY_DATE_UPDATE'],
                'STATUS' => $arCompanyProps['RELATED_COMPANY_STATUS'],
                'ACTIVE' => $arCompanyProps['RELATED_COMPANY_ACTIVE'],
            ];

            $arResultTmp["URL_TO_DETAIL"] = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_DETAIL"], Array("ID" => $arResultTmp["ID"]));
            if (empty($this->arParams['PATH_TO_DELETE']))
            {
                $arResultTmp["URL_TO_DETELE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?del_id=".$arCompanyProps["ID"]."&".bitrix_sessid_get());
            }
            else
            {
                $arResultTmp["URL_TO_DETELE"] = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_DELETE"], Array("ID" => $arCompanyProps["ID"]))."&".bitrix_sessid_get();
            }
            $this->arResult["PROFILES"][] = $arResultTmp;
        }

        if ($request->get('SECTION'))
        {
            $this->arResult["URL"] = htmlspecialcharsbx($request->getRequestedPage()."?SECTION=".$request->get('SECTION')."&");
        }
        else
        {
            $this->arResult["URL"] = htmlspecialcharsbx($request->getRequestedPage()."?");
        }

        $this->includeComponentTemplate();
    }


    protected function checkRequiredModules()
    {
        if (!Loader::includeModule('sale'))
        {
            throw new Main\SystemException(Loc::getMessage("SALE_MODULE_NOT_INSTALL"), self::E_SALE_MODULE_NOT_INSTALLED);
        }
        if (!Loader::includeModule('sotbit.auth'))
        {
            throw new Main\SystemException(Loc::getMessage("SOTBIT_AUTH_MODULE_NOT_INSTALL"), self::E_SALE_MODULE_NOT_INSTALLED);
        }
    }
}