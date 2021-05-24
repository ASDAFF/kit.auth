<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;

$_1677613774 = __DIR__ . '/lang/' . LANGUAGE_ID . '/install/index.php';
if (file_exists($_1677613774)) {
    global $MESS;
    $MESS = [];
    include($_1677613774);
}

class kit_auth extends CModule
{
    const MODULE_ID = 'kit.auth';
    var $MODULE_ID = 'kit.auth';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $_1108552007 = '';

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = GetMessage('kit.auth_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('kit.auth_MODULE_DESC');
        $this->PARTNER_NAME = GetMessage('kit.auth_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('kit.auth_PARTNER_URI');
    }

    function DoInstall()
    {
        global $APPLICATION;
        $this->InstallFiles();
        $this->InstallEvents();
        $this->InstallDB();
        $this->InstallDefaultRoles();
        $this->addDefaultData();
        RegisterModule(self::MODULE_ID);
    }

    function InstallFiles($_264990955 = array())
    {
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/', true, true);
        if (is_dir($_1632215364 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components/')) {
            if ($_57556996 = opendir($_1632215364)) {
                while (false !== $_1925301341 = readdir($_57556996)) {
                    if ($_1925301341 == '..' || $_1925301341 == '.') continue;
                    CopyDirFiles($_1632215364 . $_1925301341, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/' . $_1925301341, $_1211855957 = True, $_502024538 = True);
                }
                closedir($_57556996);
            }
        }
        if (is_dir($_1632215364 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/local/templates/.default/components/bitrix/')) {
            if ($_57556996 = opendir($_1632215364)) {
                while (false !== $_1925301341 = readdir($_57556996)) {
                    if ($_1925301341 == '..' || $_1925301341 == '.') continue;
                    CopyDirFiles($_1632215364 . $_1925301341, $_SERVER['DOCUMENT_ROOT'] . '/local/templates/.default/components/bitrix/' . $_1925301341, $_1211855957 = True, $_502024538 = True);
                }
                closedir($_57556996);
            }
        }
        return true;
    }

    function InstallEvents()
    {
        $_1808237876 = array();
        $_1196005621 = \Bitrix\Main\SiteTable::getList(array('filter' => array('ACTIVE' => 'Y')));
        while ($_1940889013 = $_1196005621->Fetch()) {
            $_1808237876[] = $_1940889013['LID'];
        }
        Option::set(self::MODULE_ID, 'LOGIN_EQ_EMAIL_IN_ADMIN', 'N');
        foreach ($_1808237876 as $_965449096) {
            Option::set(self::MODULE_ID, 'LOGIN_EQ_EMAIL', 'N', $_965449096);
        }
        $_998041908 = new CEventType();
        $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_SUCCESS_CHANGE_PASSWORD', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_SUCCESS_CHANGE_PASSWORD_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_SUCCESS_CHANGE_PASSWORD_DESCRIPTION'),));
        $_998041908 = new CEventType();
        $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_NEW_USER_PASSWORD', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_NEW_USER_PASSWORD_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_NEW_USER_PASSWORD_DESCRIPTION'),));
        $_998041908 = new CEventType();
        $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_SEND', 'NAME' => GetMessage(self::MODULE_ID . 'KIT_AUTH_SEND_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_SEND_DESCRIPTION'),));
        $_328441982 = new CEventMessage();
        $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_SUCCESS_CHANGE_PASSWORD', 'LID' => $_1808237876, 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_SUCCESS_CHANGE_PASSWORD_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_SUCCESS_CHANGE_PASSWORD_MESSAGE'), 'BODY_TYPE' => 'html'));
        $_328441982 = new CEventMessage();
        $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_NEW_USER_PASSWORD', 'LID' => $_1808237876, 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_NEW_USER_PASSWORD_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_NEW_USER_PASSWORD_MESSAGE'), 'BODY_TYPE' => 'html'));
        $_328441982 = new CEventMessage();
        $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_SEND', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL_TO#', 'SUBJECT' => '#SUBJECT#', 'MESSAGE' => '#MESSAGE#', 'BODY_TYPE' => 'html'));
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_COMPANY_REGISTER'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_COMPANY_REGISTER', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_REGISTER_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_REGISTER_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_REGISTER', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_REGISTER_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_REGISTER_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_COMPANY_MODERATION'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_COMPANY_MODERATION', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_MODERATION_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_MODERATION_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_MODERATION', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_MODERATION_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_MODERATION_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_COMPANY_CONFIRM'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_COMPANY_CONFIRM', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_CONFIRM_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_CONFIRM_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_CONFIRM', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_CONFIRM_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_CONFIRM_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_COMPANY_REJECTED'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_COMPANY_REJECTED', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_REJECTED_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_REJECTED_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_REJECTED', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_REJECTED_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_REJECTED_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_ADMIN_CONFIRM'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_ADMIN_CONFIRM', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_ADMIN_CONFIRM_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_ADMIN_CONFIRM_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_ADMIN_CONFIRM', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_ADMIN_CONFIRM_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_ADMIN_CONFIRM_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_ADMIN_REJECTED'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_ADMIN_REJECTED', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_ADMIN_REJECTED_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_ADMIN_REJECTED_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_ADMIN_REJECTED', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_ADMIN_REJECTED_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_ADMIN_REJECTED_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_COMPANY_CHANGES_REJECTED'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_COMPANY_CHANGES_REJECTED', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_CHANGES_REJECTED_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_CHANGES_REJECTED_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_CHANGES_REJECTED', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_CHANGES_REJECTED_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_CHANGES_REJECTED_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_STAFF_INVITE'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_STAFF_INVITE', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_INVITE_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_INVITE_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_STAFF_INVITE', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_INVITE_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_INVITE_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_COMPANY_JOIN_REQUEST'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_COMPANY_JOIN_REQUEST', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_JOIN_REQUEST_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_JOIN_REQUEST_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_JOIN_REQUEST', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_JOIN_REQUEST_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_JOIN_REQUEST_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_STAFF_REMOVED'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_STAFF_REMOVED', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_REMOVED_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_REMOVED_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_STAFF_REMOVED', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_REMOVED_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_REMOVED_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        if (!\CEventType::GetList(['TYPE_ID' => 'KIT_AUTH_STAFF_CONFIRM'])->fetch()) {
            $_998041908 = new \CEventType();
            $_998041908->Add(array('EVENT_NAME' => 'KIT_AUTH_STAFF_CONFIRM', 'NAME' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_CONFIRM_NAME'), 'LID' => LANGUAGE_ID, 'DESCRIPTION' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_CONFIRM_DESCRIPTION'),));
            $_328441982 = new \CEventMessage();
            $_328441982->Add(array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_STAFF_CONFIRM', 'LID' => $_1808237876, 'BCC' => '#BCC#', 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL#', 'SUBJECT' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_CONFIRM_SUBJECT'), 'MESSAGE' => GetMessage(self::MODULE_ID . '_KIT_AUTH_STAFF_CONFIRM_MESSAGE'), 'BODY_TYPE' => 'html'));
        }
        EventManager::getInstance()->registerEventHandler('main', 'OnBeforeUserAdd', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeUserAddHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBeforeUserRegister', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeUserRegisterHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBeforeUserUpdate', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeUserUpdateHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBeforeUserLogin', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeUserLoginHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBeforeUserSendPassword', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeUserSendPasswordHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBeforeEventSend', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeEventSendHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnProlog', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnPrologHandler');
        EventManager::getInstance()->registerEventHandler('main', 'OnBuildGlobalMenu', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'onBuildGlobalMenuHandler');
        EventManager::getInstance()->registerEventHandler('sale', 'OnSaleComponentOrderOneStepPersonType', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'setCurrentProfileValue');
        EventManager::getInstance()->registerEventHandler('sale', 'OnSaleComponentOrderOneStepOrderProps', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'setOrderPropertyValues');
        EventManager::getInstance()->registerEventHandler(self::MODULE_ID, 'OnAfterAddCompany', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onAfterAddCompany');
        EventManager::getInstance()->registerEventHandler(self::MODULE_ID, 'OnAfterCompanyModerate', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onAfterCompanyModerate');
        EventManager::getInstance()->registerEventHandler(self::MODULE_ID, 'OnBeforeCompanyRejection', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeCompanyRejection');
        EventManager::getInstance()->registerEventHandler(self::MODULE_ID, 'OnBeforeCompanyChangesRejection', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeCompanyChangesRejection');
        EventManager::getInstance()->registerEventHandler(self::MODULE_ID, 'OnBeforeAdminModerate', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeAdminModerate');
        EventManager::getInstance()->registerEventHandler(self::MODULE_ID, 'onBeforeStaffRemoved', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeStaffRemoved');
        EventManager::getInstance()->registerEventHandler(self::MODULE_ID, 'OnBeforeStaffRejected', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeStaffRejected');
        EventManager::getInstance()->registerEventHandler(self::MODULE_ID, 'OnBeforeJoinRequest', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeJoinRequest');
        EventManager::getInstance()->registerEventHandler(self::MODULE_ID, 'OnBeforeStaffInvite', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeStaffInvite');
        EventManager::getInstance()->registerEventHandler(self::MODULE_ID, 'OnBeforeAdminRejected', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeAdminRejected');
        EventManager::getInstance()->registerEventHandler(self::MODULE_ID, 'OnBeforeStaffUpdate', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeStaffUpdate');
        return true;
    }

    function InstallDB($_264990955 = array())
    {
        global $DB, $APPLICATION;
        $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/db/' . strtolower($DB->type) . '/install.sql');
        return true;
    }

    function InstallDefaultRoles($_264990955 = array())
    {
        Bitrix\Main\Application::getConnection()->query("INSERT INTO kit_auth_roles (CODE,NAME) VALUES ('ADMIN', '" . GetMessage(self::MODULE_ID . "_STAFF_ADMIN_ROLE") . "');");
        Bitrix\Main\Application::getConnection()->query("INSERT INTO kit_auth_roles (CODE,NAME) VALUES ('STAFF', '" . GetMessage(self::MODULE_ID . "_STAFF_EMPLOYEE_ROLE") . "');");
    }

    function addDefaultData()
    {
        $_1808237876 = array();
        $_1196005621 = \Bitrix\Main\SiteTable::getList(array('filter' => array('ACTIVE' => 'Y')));
        while ($_1940889013 = $_1196005621->Fetch()) {
            $_1808237876[] = $_1940889013['LID'];
        }
        foreach ($_1808237876 as $_965449096) {
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_REGISTER', serialize(['KIT_AUTH_COMPANY_REGISTER']), $_965449096);
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_SENT_MODERATION', serialize(['KIT_AUTH_COMPANY_MODERATION']), $_965449096);
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_CONFIRM', serialize(['KIT_AUTH_COMPANY_CONFIRM']), $_965449096);
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_REJECTED', serialize(['KIT_AUTH_COMPANY_REJECTED']), $_965449096);
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_ADMIN_CONFIRM', serialize(['KIT_AUTH_ADMIN_CONFIRM']), $_965449096);
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_ADMIN_REJECTED', serialize(['KIT_AUTH_ADMIN_REJECTED']), $_965449096);
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_CHANGES_REJECTED', serialize(['KIT_AUTH_COMPANY_CHANGES_REJECTED']), $_965449096);
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_STAFF_INVITE', serialize(['KIT_AUTH_STAFF_INVITE']), $_965449096);
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_JOIN_REQUEST', serialize(['KIT_AUTH_COMPANY_JOIN_REQUEST']), $_965449096);
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_JOIN_REQUEST_CONFIRM', serialize(['KIT_AUTH_STAFF_CONFIRM']), $_965449096);
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_JOIN_REQUEST_REJECTED', serialize(['KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED']), $_965449096);
            Option::set(self::MODULE_ID, 'MAIL_EVENT_COMPANY_STAFF_REMOVED', serialize(['KIT_AUTH_STAFF_REMOVED']), $_965449096);
        }
    }

    function DoUninstall()
    {
        global $APPLICATION;
        UnRegisterModule(self::MODULE_ID);
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
    }

    function UnInstallDB($_264990955 = array())
    {
        global $DB, $APPLICATION;
        $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/db/' . strtolower($DB->type) . '/uninstall.sql');
        return true;
    }

    function UnInstallEvents()
    {
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_SUCCESS_CHANGE_PASSWORD');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_NEW_USER_PASSWORD');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_SEND');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_REGISTER');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_MODERATION');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_CONFIRM');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_REJECTED');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_ADMIN_CONFIRM');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_ADMIN_REJECTED');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_CHANGES_REJECTED');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_STAFF_INVITE');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_JOIN_REQUEST');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_STAFF_REMOVED');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_1230043452 = array('ACTIVE' => 'Y', 'EVENT_NAME' => 'KIT_AUTH_STAFF_CONFIRM');
        $_1182172668 = CEventMessage::GetList($_1439179935 = $_965449096, $_519778630 = 'desc', $_1230043452);
        if ($_1517929945 = $_1182172668->fetch()) {
            $_328441982 = new CEventMessage();
            $_328441982->Delete(intval($_1517929945['ID']));
        }
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_SUCCESS_CHANGE_PASSWORD');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_NEW_USER_PASSWORD');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_SEND');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_STAFF_CONFIRM');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_STAFF_REMOVED');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_COMPANY_JOIN_REQUEST');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_STAFF_INVITE');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_COMPANY_CHANGES_REJECTED');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_ADMIN_REJECTED');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_ADMIN_CONFIRM');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_COMPANY_REJECTED');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_COMPANY_CONFIRM');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_COMPANY_MODERATION');
        $_313883075 = new CEventType();
        $_313883075->Delete('KIT_AUTH_COMPANY_REGISTER');
        EventManager::getInstance()->unregisterEventHandler('main', 'OnBeforeUserAdd', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeUserAddHandler');
        EventManager::getInstance()->unregisterEventHandler('main', 'OnBeforeUserRegister', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeUserRegisterHandler');
        EventManager::getInstance()->unregisterEventHandler('main', 'OnBeforeUserUpdate', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeUserUpdateHandler');
        EventManager::getInstance()->unregisterEventHandler('main', 'OnBeforeUserLogin', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeUserLoginHandler');
        EventManager::getInstance()->unregisterEventHandler('main', 'OnBeforeUserSendPassword', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeUserSendPasswordHandler');
        EventManager::getInstance()->unregisterEventHandler('main', 'OnBeforeEventSend', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnBeforeEventSendHandler');
        EventManager::getInstance()->unregisterEventHandler('main', 'OnProlog', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'OnPrologHandler');
        EventManager::getInstance()->unregisterEventHandler('main', 'OnBuildGlobalMenu', self::MODULE_ID, '\Kit\Auth\EventHandlers', 'onBuildGlobalMenuHandler');
        EventManager::getInstance()->registerEventHandler('sale', 'OnSaleComponentOrderOneStepPersonType', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'setCurrentProfileValue');
        EventManager::getInstance()->registerEventHandler('sale', 'OnSaleComponentOrderOneStepOrderProps', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'setOrderPropertyValues');
        EventManager::getInstance()->unregisterEventHandler(self::MODULE_ID, 'OnAfterAddCompany', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onAfterAddCompany');
        EventManager::getInstance()->unregisterEventHandler(self::MODULE_ID, 'OnAfterCompanyModerate', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onAfterCompanyModerate');
        EventManager::getInstance()->unregisterEventHandler(self::MODULE_ID, 'OnBeforeCompanyRejection', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeCompanyRejection');
        EventManager::getInstance()->unregisterEventHandler(self::MODULE_ID, 'OnBeforeCompanyChangesRejection', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeCompanyChangesRejection');
        EventManager::getInstance()->unregisterEventHandler(self::MODULE_ID, 'OnBeforeAdminModerate', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeAdminModerate');
        EventManager::getInstance()->unregisterEventHandler(self::MODULE_ID, 'onBeforeStaffRemoved', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeStaffRemoved');
        EventManager::getInstance()->unregisterEventHandler(self::MODULE_ID, 'OnBeforeStaffRejected', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeStaffRejected');
        EventManager::getInstance()->unregisterEventHandler(self::MODULE_ID, 'OnBeforeJoinRequest', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeJoinRequest');
        EventManager::getInstance()->unregisterEventHandler(self::MODULE_ID, 'OnBeforeStaffInvite', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeStaffInvite');
        EventManager::getInstance()->unregisterEventHandler(self::MODULE_ID, 'OnBeforeAdminRejected', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeAdminRejected');
        EventManager::getInstance()->unregisterEventHandler(self::MODULE_ID, 'OnBeforeStaffUpdate', self::MODULE_ID, '\Kit\Auth\Company\CompanyEventHandlers', 'onBeforeStaffUpdate');
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default/icons/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/icons');
        if (is_dir($_1632215364 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components/kit/')) {
            if ($_57556996 = opendir($_1632215364)) {
                while (false !== $_1925301341 = readdir($_57556996)) {
                    if ($_1925301341 == '..' || $_1925301341 == '.' || !is_dir($_356606878 = $_1632215364 . $_1925301341)) continue;
                    $_1105370498 = opendir($_356606878);
                    while (false !== $_1453474204 = readdir($_1105370498)) {
                        if ($_1453474204 == '..' || $_1453474204 == '.') continue;
                        DeleteDirFilesEx('/bitrix/components/kit/' . $_1925301341 . '/' . $_1453474204);
                    }
                    closedir($_1105370498);
                }
                closedir($_57556996);
            }
        }
        if (is_dir($_1632215364 = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/local/templates/.default/components/bitrix/')) {
            if ($_57556996 = opendir($_1632215364)) {
                while (false !== $_1925301341 = readdir($_57556996)) {
                    if ($_1925301341 == '..' || $_1925301341 == '.' || !is_dir($_356606878 = $_1632215364 . $_1925301341)) continue;
                    $_1105370498 = opendir($_356606878);
                    while (false !== $_1453474204 = readdir($_1105370498)) {
                        if ($_1453474204 == '..' || $_1453474204 == '.') continue;
                        DeleteDirFilesEx('/local/templates/.default/components/bitrix/' . $_1925301341 . '/' . $_1453474204);
                    }
                    closedir($_1105370498);
                }
                closedir($_57556996);
            }
        }
        return true;
    }
}