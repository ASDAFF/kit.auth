<?php
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('kit.auth',
    array(
        'DigitalWand\AdminHelper\EventHandlers' => 'lib/adminhelper/EventHandlers.php',
        'DigitalWand\AdminHelper\Helper\Exception' => 'lib/adminhelper/helper/Exception.php',
        'DigitalWand\AdminHelper\Helper\AdminInterface' => 'lib/adminhelper/helper/AdminInterface.php',
        'DigitalWand\AdminHelper\Helper\AdminBaseHelper' => 'lib/adminhelper/helper/AdminBaseHelper.php',
        'DigitalWand\AdminHelper\Helper\AdminListHelper' => 'lib/adminhelper/helper/AdminListHelper.php',
        'DigitalWand\AdminHelper\Helper\AdminSectionListHelper' => 'lib/adminhelper/helper/AdminSectionListHelper.php',
        'DigitalWand\AdminHelper\Helper\AdminEditHelper' => 'lib/adminhelper/helper/AdminEditHelper.php',
        'DigitalWand\AdminHelper\Helper\AdminSectionEditHelper' => 'lib/adminhelper/helper/AdminSectionEditHelper.php',
        'DigitalWand\AdminHelper\EntityManager' => 'lib/adminhelper/EntityManager.php',
        'DigitalWand\AdminHelper\Widget\HelperWidget' => 'lib/adminhelper/widget/HelperWidget.php',
        'DigitalWand\AdminHelper\Widget\CheckboxWidget' => 'lib/adminhelper/widget/CheckboxWidget.php',
        'DigitalWand\AdminHelper\Widget\ComboBoxWidget' => 'lib/adminhelper/widget/ComboBoxWidget.php',
        'DigitalWand\AdminHelper\Widget\StringWidget' => 'lib/adminhelper/widget/StringWidget.php',
        'DigitalWand\AdminHelper\Widget\NumberWidget' => 'lib/adminhelper/widget/NumberWidget.php',
        'DigitalWand\AdminHelper\Widget\FileWidget' => 'lib/adminhelper/widget/FileWidget.php',
        'DigitalWand\AdminHelper\Widget\TextAreaWidget' => 'lib/adminhelper/widget/TextAreaWidget.php',
        'DigitalWand\AdminHelper\Widget\HLIBlockFieldWidget' => 'lib/adminhelper/widget/HLIBlockFieldWidget.php',
        'DigitalWand\AdminHelper\Widget\DateTimeWidget' => 'lib/adminhelper/widget/DateTimeWidget.php',
        'DigitalWand\AdminHelper\Widget\IblockElementWidget' => 'lib/adminhelper/widget/IblockElementWidget.php',
        'DigitalWand\AdminHelper\Widget\UrlWidget' => 'lib/adminhelper/widget/UrlWidget.php',
        'DigitalWand\AdminHelper\Widget\VisualEditorWidget' => 'lib/adminhelper/widget/VisualEditorWidget.php',
        'DigitalWand\AdminHelper\Widget\UserWidget' => 'lib/adminhelper/widget/UserWidget.php',
        'DigitalWand\AdminHelper\Widget\OrmElementWidget' => 'lib/adminhelper/widget/OrmElementWidget.php'
    )
);

class KitAuth
{
    const idModule = 'kit.auth';
    const SEND_FORGOT_NEW_PASSWORD_EVENT = 'KIT_AUTH_SUCCESS_CHANGE_PASSWORD';
    const SEND_NEW_USER_PASSWORD_EVENT = 'KIT_AUTH_NEW_USER_PASSWORD';
    const KIT_AUTH_SEND_EVENT = 'KIT_AUTH_SEND';
    private static $_747550465 = false;
    protected $_876471220;
    protected $_1846516450;
    protected $_942734349;

    public function __construct()
    {
    }

    public function getDemo()
    {
        if (self::$_747550465 === false) self::__422952952();
        return !(static::$_747550465 == 0 || static::$_747550465 == 3);
    }

    private static function __422952952()
    {
        static::$_747550465 = CModule::IncludeModuleEx(self::idModule);
    }

    public function ReturnDemo()
    {
        if (self::$_747550465 === false) self::__422952952();
        return static::$_747550465;
    }

    public function getField($_1717141349 = '')
    {
        return $this->_876471220[$_1717141349];
    }

    public function getFields()
    {
        return $this->_876471220;
    }

    public function setFields(array $_1623123929)
    {
        foreach ($_1623123929 as $_1994500907 => $_487402116) {
            $this->setField($_1994500907, $_487402116);
        }
    }

    public function setField($_1717141349 = '', $_487402116 = '')
    {
        $this->_876471220[$_1717141349] = $_487402116;
    }

    protected function setError($_1846516450 = '')
    {
        $this->_1846516450 = $_1846516450;
        unset($_1846516450);
    }

    protected function getError()
    {
        return $this->_1846516450;
    }

    protected function validateEmail($_2041534598 = '')
    {
        $_389451061 = false;
        if (!preg_match('/^[a-zA-Z0-9_\-.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', trim($_2041534598))) {
            $_389451061 = false;
        } else {
            $_389451061 = true;
        }
        unset($_2041534598);
        return $_389451061;
    }
}

class DataManagerEx_Auth extends Bitrix\Main\Entity\DataManager
{
    public static function getList(array $_501887963 = array())
    {
        if (!KitAuth::getDemo()) return new Bitrix\Main\ORM\Query\Result(parent::query(), new \Bitrix\Main\DB\ArrayResult(array())); else return parent::getList($_501887963);
    }

    public static function getById($_2018094269 = "")
    {
        if (!KitAuth::getDemo()) return new \CDBResult; else return parent::getById($_2018094269);
    }

    public static function add(array $_126901937 = array())
    {
        if (!KitAuth::getDemo()) return new \Bitrix\Main\Entity\AddResult(); else return parent::add($_126901937);
    }

    public static function update($_2018094269 = "", array $_126901937 = array())
    {
        if (!KitAuth::getDemo()) return new \Bitrix\Main\Entity\UpdateResult(); else return parent::update($_2018094269, $_126901937);
    }
}