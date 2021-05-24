<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Sotbit\Auth\Company\Company;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class AuthCompanyChoose extends CBitrixComponent implements Controllerable
{
    private $currentCompany;
    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'choose' => [
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

    private function getCompaniesByUserID($userID = false) {
        if (!$userID) return;
        $company = new Company();
        $this->currentCompany = $company->getCompaniesByUserID($userID, ["COMPANY_NAME"=>"asc"]);
    }

    private function getCurrentCompany ($userID = false) {
        if (isset($_SESSION['AUTH_COMPANY_CURRENT_ID'])
            && !empty($_SESSION['AUTH_COMPANY_CURRENT_ID'])
            && array_key_exists($_SESSION['AUTH_COMPANY_CURRENT_ID'], $this->currentCompany)
        ) {
            $currentCompanyID = $_SESSION['AUTH_COMPANY_CURRENT_ID'];
        } else {
            $currentCompanyID = current($this->currentCompany)['ID_COMPANY'];
        }

        return $this->currentCompany[$currentCompanyID];
    }

    function executeComponent()
    {
        global $USER;
        define("EXTENDED_VERSION_COMPANIES", "Y");
        $this->getCompaniesByUserID($USER->getID());
        $currentCompany = $this->getCurrentCompany($USER->getID());
        $this->arResult['CURRENT_COMPANY'] = $currentCompany["COMPANY_NAME"];
        if($USER->IsAuthorized()) {
            $_SESSION['AUTH_COMPANY_CURRENT_ID'] = $currentCompany["ID_COMPANY"];
//            if(!empty($_SESSION['AUTH_COMPANY_CURRENT_ID'])) {
//                LocalRedirect($_SERVER['REQUEST_URI'], false, 302);
//            }
        }
        $this->arResult['COMPANIES'] = $this->currentCompany;
        unset($currentCompany);
        
        $this->includeComponentTemplate();
    }

    /**
     * @param string $companyID
     * @return array
     */
    public function changeCompanyAction($companyID = false)
    {
        global $USER;
        $this->getCompaniesByUserID($USER->getID());
        if (array_key_exists($companyID, $this->currentCompany)) {
            $_SESSION['AUTH_COMPANY_CURRENT_ID'] = $companyID;
            return [
                'companyId' => $companyID,
                'error' => false
            ];
        } elseif( $currentCompany = $this->getCurrentCompany($USER->getID())) {
            $_SESSION['AUTH_COMPANY_CURRENT_ID'] = $currentCompany["RELATED_COMPANY_ID"];
            return [
                'error' => true,
                'companyId' => $companyID,
                'errorMessage' => Loc::getMessage('SOTBIT_AUTH_COMPANY_CHOOSE_ERROR_MESSAGE_COMPANY_NOT_FOUND')
            ];
        }
        else{
            $USER->Logout();
            return [
                'error' => false,
                'errorMessage' => Loc::getMessage('SOTBIT_AUTH_COMPANY_CHOOSE_ERROR_MESSAGE_NO_RELATED_COMPANIES')
            ];
        }

    }


}