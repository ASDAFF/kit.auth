<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Kit\Auth\Company\Company;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class CompanyJoin extends CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'joiningCompany' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ]
        ];
    }

    function executeComponent()
    {
        global $USER;

        $currentCompanies = [];
        $obStaff = Kit\Auth\Internals\StaffTable::getList( [
            'filter' => [
                'USER_ID' => $USER->GetID(),
                'COMPANY_STATUS' => 'A'
            ],
            'select' => ['ID_COMPANY_'=>'COMPANY.ID', 'COMPANY_STATUS'=>'COMPANY.STATUS'],
        ] );

        while($result = $obStaff->fetch()){
            $currentCompanies[] = $result["ID_COMPANY_"];
        }

        $parametersCompany = [
            'filter' => ['STATUS'=>"A", '!ID'=>$currentCompanies],
            'select' => ['ID', 'NAME', 'BUYER_TYPE'],
            'order' => ['NAME'=>'asc'],
        ];
        $obCompany = Kit\Auth\Internals\CompanyTable::getList($parametersCompany)->fetchAll();

        $buyerType = [];
        foreach ($obCompany as $company){
            if(!in_array($company["BUYER_TYPE"], $buyerType))
                $buyerType[] = $company["BUYER_TYPE"];
            $companyId[]  = $company["ID"];
            $arrayCompany[$company["ID"]] = $company;
        }
        unset($obCompany);
        unset($currentCompanies);

        if(Loader::includeModule("sale")) {
            $obOrderProps = OrderPropsTable::getList(array(
                'filter' => array(
                    'CODE' => "INN",
                    'PERSON_TYPE_ID' => $buyerType
                ),
                'select' => array('ID')
            ));

            while($orderProp = $obOrderProps->fetch()){
                $idInn[] = $orderProp["ID"];
            }
        }
        $parametersValue = [
            'filter' => ['COMPANY_ID'=>$companyId, 'PROPERTY_ID'=>$idInn],
            'select' => ['ID', 'COMPANY_ID', 'VALUE', 'NAME_COMPANY'=>'COMPANY.NAME'],
            'order' => ['NAME_COMPANY'=>'asc']
        ];
        $obValuesInn = Kit\Auth\Internals\CompanyPropsValueTable::getList($parametersValue)->fetchAll();

        foreach ($obValuesInn as $inn){
            $arrayCompany[$inn["COMPANY_ID"]]['INN'] = $inn["VALUE"];
            $arrayCompany[$inn["COMPANY_ID"]]['PRINT_NAME'] = $arrayCompany[$inn["COMPANY_ID"]]['NAME'] ." ".$inn["VALUE"];
            $arraySelect[$inn["COMPANY_ID"]] = $arrayCompany[$inn["COMPANY_ID"]]['NAME'] ." ".$inn["VALUE"];
        }
        unset($obValuesInn);

        $this->arResult["ITEMS"] = $arrayCompany;
        $this->arResult["SELECT_ITEMS"] = $arraySelect;

        $this->includeComponentTemplate();
    }

    public function joiningCompanyAction()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["JOIN_COMPANY_ID"] <> '' && !empty($_REQUEST["JOIN_COMPANY_ID"])) {
           global $USER;
            $company = new Company(SITE_ID);

            foreach ($_REQUEST["JOIN_COMPANY_ID"] as $companyId){
                $company->addStaff($companyId, $USER->GetID(), "STAFF", "N");
            }
            return [
                'error' => false,
                'companyId' => $_REQUEST["JOIN_COMPANY_ID"]
            ];
        }
        else{
            return [
              'error' => true,
              'errorMessage' => GetMessage("KIT_AUTH_COMPANY_JOIN_ERROR_MESSAGE_COMPANY_NOT_CHOOSE"),
            ];
        }
    }

}