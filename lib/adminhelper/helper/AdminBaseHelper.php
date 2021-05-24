<?php

namespace DigitalWand\AdminHelper\Helper;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use DigitalWand\AdminHelper\EntityManager;
use DigitalWand\AdminHelper\Widget\HelperWidget;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Context;

Loader::includeModule('highloadblock');
Loc::loadMessages(__FILE__);

/**
 * ������ ������ ��������� ������ MVC ��� �������� ����������������� ����������.
 *
 * ����������� ���������� ����������������� ���������� ���������� ��������� ������� ������� API ��� CRUD-���������� ���
 * ����������. ������� ���������� �����. ���������� ���������� ������� ������ �������� ������ ��� �������, �����������
 * API ORM �������. ��� ������� ������������ ������ ������ ��� ���������, �� ������������ ORM �������, �����
 * ����������� ��� ����� ��������� �����-������, ����������� ����������� �������.
 *
 * �������� ������� ������:
 * <ul>
 * <li>�����: "model" � �������� MVC. �����, �������������� �� DataManager ��� ����������� ����������� API.</li>
 * <li>������: "view" � �������� MVC. �����, ����������� ��������� ���������� ������ ��� ��������� ��������.</li>
 * <li>������: "controller" � �������� MVC. ����, ����������� ��� ������� � ������� ������� ������, ��������� ������
 * ������� � ������� �����������. � ��� �������� �������� �� �������.</li>
 * <li>�������: "delegate" � �������� MVC. ������, ���������� �� ��������� ��������� ���������� ��� ��������� �����
 * ���������. � ������ � �� ���������.</li>
 * </ul>
 *
 * ����� ������ � ������� ���������:
 * <ul>
 * <li>���������� ������ AdminListHelper - ��� ���������� ��������� ������ ���������</li>
 * <li>���������� ������ AdminEditHelper - ��� ���������� ��������� ���������/�������������� ��������</li>
 * <li>���������� ������ AdminInterface - ��� �������� ������������ ����� ������� � ������ �����������</li>
 * <li>���������� ������ AdminSectionListHelper - ��� �������� �������� ������ ��������(���� ��� ������������)</li>
 * <li>���������� ������ AdminSectionEditHelper - ��� ���������� ��������� ���������/�������������� �������(���� ��� ������������)</li>
 * <li>���� �� ������� ������������ ��������, ������ � �������, ����� ����������� ���� ������, �������������� �� ������
 * ������� �������� ������� ��� �� ������������ ������ HelperWidget</li>
 * </ul>
 *
 * ���������� ����������:
 * <ul>
 * <li>���� Interface.php � ������� AdminBaseHelper::setInterfaceSettings(), � ������� ����������
 * ������������ ����� ������� � ������.</li>
 *
 * ������������� �������� ��������� ��� �������, ������������ ������ ����������:
 * <ul>
 * <li>������� <b>admin</b>. ���������� ��������� � ���� ���� menu.php, ��������� ����� ��� ������ � ���������
 * ��������� �� ���� ��������� ������� ��������.</li>
 * <li>������� <b>classes</b> (��� lib): �������� ������ �����, ������������� � ���������.</li>
 * <li> -- <b>classes/admininterface</b>: �������, ���������� ������ "view", �������������� �� AdminListHelper,
 * AdminEditHelper, AdminInterface, AdminSectionListHelper � AdminSectionEditHelper.</li>
 * <li> -- <b>classes/widget</b>: �������, ���������� ������� ("delegate"), ���� ��� ������ �������� ���������
 * ����.</li>
 * <li> -- <b>classes/model</b>: ������� � ��������, ���� �������� �������������� ��������� ����������� ������� getList
 * � �.�.</li>
 * </ul>
 *
 * ������������ ������ ��������� �� �����������, ��� ���� ������������, ���������� �� �������� ����� ���������� ������
 * � ���� ��������.
 *
 * ������������ <b>������������</b> ������� - ������������  ���� ����������� ������� ����� �������� � ����� �����������
 * � ����� ����������
 *
 * ��� ������������� �������� ����� ����������� ��������� � ������ ��������� �������� � ������ ��������, ��������:
 *
 * ```php
 * <?php
 * class ElementModel
 * {
 * 		public static function getMap()
 *  	{
 * 			return [
 * 				'CATEGORY' => [
 *					'data_type' => 'Vendor\Module\CategoryTable',
 *					'reference' => ['=this.CATEGORY_ID' => 'ref.ID'],
 *				]
 * 			];
 * 		}
 * ```
 *
 * @see AdminInterface::fields()
 * @package AdminHelper
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 * @author Artem Yarygin <artx19@yandex.ru>
 */
abstract class AdminBaseHelper
{
	/**
	 * @internal
	 * @var string ����� ����������� �������� � �����. ����������.
	 */
	static protected $routerUrl = '/bitrix/admin/kit_admin_helper_route.php';

	/**
	 * @var string
	 * ��� ������ ������������ ������. ������������ ��� ���������� CRUD-��������.
	 * ��� ������������ ������ ���������� �������������� ��� ����������, ������ ������ ��� ������ ������.
	 *
	 * @see DataManager
	 * @api
	 */
	static protected $model;

	/**
	 * @var string
	 * ��� ������ ������������� ��������� ���������. ������������ ��� ���������� CRUD-��������.
	 *
	 * @see DataManager
	 * @api
	 */
	static protected $entityManager = '\DigitalWand\AdminHelper\EntityManager';

	/**
	 * @var string
	 * ������� ������ ������ ������.
	 * ��� ������������ ������ ���������� ������� ������� ������, � ������� �� ���������.
	 * � ����� � �� ���������, � ����� ������ �� ����������� ������������� �� namespace ������
	 * ������������ ��� ��������� ���������� ����� ������� �������������.
	 *
	 * @api
	 */
	static public $module = array();

	/**
	 * @var string[]
	 * �������� �������������.
	 * ��� ������������ ������ ���������� ������� �������� �������������.
	 * � ����� � �� ���������, � ����� ������ ��� ����������� ������������� �� namespace ������.
	 * ��� ����� ������������ ��� ���������� URL � ������� ������� �������.
	 * �� ������ ��������� �������� � ������ ��������, ��������� �������������� ���
	 * �������� ������ ��������.
	 *
	 * @api
	 */
	static protected $viewName = array();

	/**
	 * @var array
	 * ��������� ����������
	 * @see AdminBaseHelper::setInterfaceSettings()
	 * @internal
	 */
	static protected $interfaceSettings = array();

	/**
	 * @var array
	 * �������� ������ ��������� � ������ �������
	 */
	static protected $interfaceClass = array();

	/**
	 * @var array
	 * ������ ������ ������������ ����� � ��������� �� �����������
	 * @see AdminBaseHelper::setInterfaceSettings()
	 */
	protected $fields = array();

	/**
	 * @var \CMain
	 * ������ global $APPLICATION;
	 */
	protected $app;
	protected $validationErrors = array();

	/**
	 * @var string
	 * ��������� ��������������� ������� ����� �������� ������. �������, � ������, ���� ����� ������� ����������� ���
	 * ������������� ������� ������. � ������, ���� ���� ���������� ��� ������, ������� �� ������������.
	 *
	 * @see AdminBaseHelper::getListPageUrl
	 * @api
	 */
	static protected $listPageUrl;

	/**
	 * @var string
	 * $viewName �������������, ����������� �� �������� ������. ���������� ��������� ������ ��� �������, �������������
	 * �� AdminEditHelper.
	 * ��������������, ������������� ������������� ���� �� ����������
	 *
	 * @see AdminBaseHelper::getViewName()
	 * @see AdminBaseHelper::getListPageUrl
	 * @see AdminEditHelper
	 * @api
	 */
	static protected $listViewName;

	/**
	 * @var string
	 * ��������� ��������������� ������� ����� �������� ���������/�������������� ��������. �������, � ������, ����
	 * ����� ������� ����������� ��� ������������� ������� ������. � ������, ���� ���� ���������� ��� ������,
	 * ������� �� ������������.
	 *
	 * @see AdminBaseHelper::getEditPageUrl
	 * @api
	 */
	static protected $editPageUrl;

	/**
	 * @var string
	 * $viewName �������������, ����������� �� �������� ��������������/��������� ��������. ���������� ��������� ������
	 *     ��� �������, ������������� �� AdminListHelper.
	 *
	 * @see AdminBaseHelper::getViewName()
	 * @see AdminBaseHelper::getEditPageUrl
	 * @see AdminListHelper
	 * @api
	 */
	static protected $editViewName;

	/**
	 * @var string
	 * ��������� ��������������� ������� ����� �������� ���������/�������������� �������. �������, � ������, ����
	 * ����� ������� ����������� ��� ������������� ������� ������. � ������, ���� ���� ���������� ��� ������,
	 * ������� �� ������������.
	 *
	 * @see AdminBaseHelper::getEditPageUrl
	 * @api
	 */
	static protected $sectionsEditPageUrl;

	/**
	 * @var string
	 * $viewName �������������, ����������� �� �������� ��������������/��������� �������. ���������� ��������� ������
	 * ��� �������, ������������� �� AdminListHelper.
	 * ��������������, ������������� ������������� ���� �� ����������
	 *
	 * @see AdminBaseHelper::getViewName()
	 * @see AdminBaseHelper::getEditPageUrl
	 * @see AdminListHelper
	 * @api
	 */
	static protected $sectionsEditViewName;

	/**
	 * @var array
	 * �������������� ��������� URL, ������� ����� ��������� � ���������� ��-���������, ������������ �������������
	 * @api
	 */
	protected $additionalUrlParams = array();

	/**
	 * @var string �������� ����������. ������� ��� �������������� �������� � ���, ����� �������� � ��������� ������
	 *     ������������.
	 */
	protected $context = '';

	/**
	 * ���� ������������� ��������, ���������� �������������� � �������� ������
	 * @var bool
	 */
	static protected $useSections = false;

	/**
	 * ������� ���������� �������� ��� �������� �� ���������
	 * @var string
	 */
	static protected $sectionSuffix = 'Sections';

	/**
	 * @param array $fields ������ ������������ ����� � �������� ��� ���
	 * @param array $tabs ������ ������� ��� ��������� ��������
	 * @param string $module �������� ������
	 */
	public function __construct(array $fields, array $tabs = array(), $module = "")
	{
		global $APPLICATION;

		$this->app = $APPLICATION;

		$settings = array(
			'FIELDS' => $fields,
			'TABS' => $tabs
		);
		if (static::setInterfaceSettings($settings)) {
			$this->fields = $fields;
		}
		else {
			$settings = static::getInterfaceSettings();
			$this->fields = $settings['FIELDS'];
		}
	}

	/**
	 * @param string $viewName ��� �����, ��� ������� �� ����� �������� ���������
	 *
	 * @return array ���������� ��������� ���������� ��� ������� ������.
	 *
	 * @see AdminBaseHelper::setInterfaceSettings()
	 * @api
	 */
	public static function getInterfaceSettings($viewName = '')
	{
		if (empty($viewName)) {
			$viewName = static::getViewName();
		}

		return self::$interfaceSettings[static::getModule()][$viewName]['interface'];
	}

	/**
	 * �������� ������� ��� ������������ ����� ����������������� ����������.
	 *
	 * @param array $settings ��������� ����� � �������
	 * @param array $helpers ������ �������-��������, ������������ ��� ��������� �������
	 * @param string $module �������� ������
	 *
	 * @return bool false, ���� ��� ������� ������ ��� ���� ���������� ���������
	 *
	 * @api
	 */
	public static function setInterfaceSettings(array $settings, array $helpers = array(), $module = '')
	{
		foreach ($helpers as $helperClass => $helperSettings) {
			if (!is_array($helperSettings)) { // ��������� ������� ������� �������� ��������
				$helperClass = $helperSettings; // � �������� ���������� ����� ������� � �� ���������
				$helperSettings = array(); // �������� � ������ ������� ���
			}
			$success = $helperClass::registerInterfaceSettings($module, array_merge($settings, $helperSettings));
			if (!$success) return false;
		}

		return true;
	}

	/**
	 * ����������� ������ ������� �� �������� ���������� � ���������, ������������ ��� ���������
	 * ������ �� ��������� ���������� �� ����������.
	 *
     * @param $class
	 */
	public static function setInterfaceClass($class)
	{
		static::$interfaceClass[get_called_class()] = $class;
	}

	/**
	 * ���������� ����� ���������� � �������� �������� ������ �� �������� ������ �����.
     *
	 * @return array
	 */
	public static function getInterfaceClass()
	{
		return isset(static::$interfaceClass[get_called_class()]) ? static::$interfaceClass[get_called_class()] : false;
	}

	/**
	 * ������������ ��������� ���������� ��� �������� �������
	 *
	 * @param string $module ��� �������� ������
	 * @param $interfaceSettings
     *
	 * @return bool
	 * @internal
	 */
	public static function registerInterfaceSettings($module, $interfaceSettings)
	{
		if (isset(self::$interfaceSettings[$module][static::getViewName()]) || empty($module)
			|| empty($interfaceSettings)
		) {
			return false;
		}

		self::$interfaceSettings[$module][static::getViewName()] = array(
			'helper' => get_called_class(),
			'interface' => $interfaceSettings
		);

		return true;
	}

	/**
	 * �������� ��������� ���������� ��� ������� ������ � �������������. ������������ ��� ��������.
	 * ������������ ������ �� ���������� �������:
	 *
	 * <ul>
	 * <li> helper - �������� ������-�������, ������� ����� �������� ��������</li>
	 * <li> interface - ��������� ���������� ��� �������</li>
	 * </ul>
	 *
	 * @param string $module ������, ��� �������� ����� �������� ���������.
	 * @param string $view �������� �������������.
     *
	 * @return array
	 * @internal
	 */
	public static function getGlobalInterfaceSettings($module, $view)
	{
		if (!isset(self::$interfaceSettings[$module][$view])) {
			return false;
		}

		return array(
			self::$interfaceSettings[$module][$view]['helper'],
			self::$interfaceSettings[$module][$view]['interface'],
		);
	}

	/**
     * ���������� ��� �������� �������������.
     *
	 * @return string
	 * @api
	 */
	public static function getViewName()
	{
		if (!is_array(static::$viewName)) {
			return static::$viewName;
		}

		$className = get_called_class();

		if (!isset(static::$viewName[$className])) {
			$classNameParts = explode('\\', trim($className, '\\'));

			if (count($classNameParts) > 2) {
				$classCaption = array_pop($classNameParts); // �������� ������ ��� namespace
				preg_match_all('/((?:^|[A-Z])[a-z]+)/', $classCaption, $matches);
				$classCaptionParts = $matches[0];

				if (end($classCaptionParts) == 'Helper') {
					array_pop($classCaptionParts);
				}

				static::$viewName[$className] = strtolower(implode('_', $classCaptionParts));
			}
		}

		return static::$viewName[$className];
	}

	/**
	 * ���������� ���� ������ ������� ������������ ��� �������� � ������� �� ���� � ����� ����������� � ������� ������
	 * �������.
	 * @return string
	 * @throws Exception
	 */
	public static function getSectionField()
	{
		$sectionListHelper = static::getHelperClass(AdminSectionListHelper::className());

		if (empty($sectionListHelper))
		{
			return null;
		}

		$sectionModelClass = $sectionListHelper::getModel();
		$modelClass = static::getModel();

		foreach ($modelClass::getMap() as $field => $data) {
			if ($data instanceof ReferenceField && $data->getDataType() . 'Table' === $sectionModelClass) {
				return str_replace('=this.', '', reset($data->getReference()));
			}
			if (is_array($data) && $data['data_type'] === $sectionModelClass) {
				return str_replace('=this.', '', key($data['reference']));
			}
		}

		throw new Exception('References to section model not found');
	}

	/**
     * ���������� ��� ������ ������������ ������.
     *
	 * @return \Bitrix\Main\Entity\DataManager|string
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 * @api
	 */
	public static function getModel()
	{
		if (static::$model) {
			return static::getHLEntity(static::$model);
		}

		return null;
	}

	/**
	 * ���������� ��� ������. ���� ��� �� ������, �� ���������� ������������� �� namespace ������.
     *
	 * @return string
     *
	 * @throws LoaderException
	 * @api
	 */
	public static function getModule()
	{
		if (!is_array(static::$module)) {
			return static::$module;
		}

		$className = get_called_class();

		if (!isset(static::$module[$className])) {
			$classNameParts = explode('\\', trim($className, '\\'));

			$moduleNameParts = array();
			$moduleName = false;

			while (count($classNameParts)) {
				$moduleNameParts[] = strtolower(array_shift($classNameParts));
				$moduleName = implode('.', $moduleNameParts);

				if (ModuleManager::isModuleInstalled($moduleName)) {
					static::$module[$className] = $moduleName;
					break;
				}
			}

			if (empty($moduleName)) {
				throw new LoaderException('Module name not found');
			}
		}

		return static::$module[$className];
	}

	/**
	 * ���������� ��������������� ������ � ��������� �������� ���������� �� ��� ����. ����� �������� � ���������
     * �� �����-����������, ���� ��� �� ������ � ������������ �������� �� ���������.
     *
     * ���� ������� ���������� ������ � �����-����������, �� ��������� ��������� � ��������� � ������ ����������
     * ����� ��������� (��������).
     *
	 * @param $code
	 * @param $params
	 * @param array $keys
     *
	 * @return array|bool
	 */
	protected function getButton($code, $params, $keys = array('name', 'TEXT'))
	{
		$interfaceClass = static::getInterfaceClass();
		$interfaceSettings = static::getInterfaceSettings();
		if ($interfaceClass && !empty($interfaceSettings['BUTTONS'])) {
			$buttons = $interfaceSettings['BUTTONS'];

			if (is_array($buttons) && isset($buttons[$code])) {
				if ($buttons[$code]['VISIBLE'] == 'N') {
					return false;
				}
				$params = array_merge($params, $buttons[$code]);

				return $params;
			}
		}
		$text = Loc::getMessage('DIGITALWAND_ADMIN_HELPER_' . $code);

		foreach ($keys as $key) {
			$params[$key] = $text;
		}

		return $params;
	}

	/**
	 * ���������� ������ ����� ����������.
     *
	 * @see AdminBaseHelper::setInterfaceSettings()
     *
	 * @return array
     *
	 * @api
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * ������������ ������� ���������������� ��������.
	 */
	abstract public function show();

	/**
	 * �������� �������� ������� ������������ ������.
     *
	 * @return mixed
	 */
	public function table()
	{
		/**
         * @var DataManager $className
         */
		$className = static::getModel();

		return $className::getTableName();
	}

	/**
	 * ���������� ��������� ���� ������� ������������ ������
	 * ��� HL-���������� ������� - ������ ID. �� ����� ���������� ��� �����-���� ������ ��������.
	 * @return string
	 * @api
	 */
	public function pk()
	{
		return 'ID';
	}

	/**
	 * ���������� �������� ���������� ����� ������� ������������ ������
	 * @return array|int|null
	 * 
	 * @api
	 */
	public function getPk()
	{
		return isset($_REQUEST['FIELDS'][$this->pk()]) ? $_REQUEST['FIELDS'][$this->pk()] : $_REQUEST[$this->pk()];
	}

	/**
	 * ���������� ��������� ���� ������� ������������ ������ ��������. ��� HL-���������� ������� - ������ ID.
     * �� ����� ���������� ��� �����-���� ������ ��������.
     *
	 * @return string
	 *
     * @api
	 */
	public function sectionPk()
	{
		return 'ID';
	}

	/**
	 * ������������� ��������� ������� � �������.
     *
	 * @param string $title
	 *
     * @api
	 */
	public function setTitle($title)
	{
		$this->app->SetTitle($title);
	}

	/**
	 * ������� ��� ��������� �������������� �������� ��� ���������� � �������. ��� �������, ������ ������������
     * LocalRedirect ����� �������� ���������.
	 *
	 * @param string $action �������� ��������.
	 * @param null|int $id ID ��������.
     *
	 * @api
	 */
	protected function customActions($action, $id = null)
	{
		return;
	}

	/**
	 * ����������� �������� ���� �� ������ � ��������.
     *
	 * @return bool
	 *
     * @api
	 */
	protected function hasRights()
	{
		return true;
	}

	/**
	 * ����������� �������� ���� �� ���������� �������� ������ ���������.
     *
	 * @return bool
	 *
     * @api
	 */
	protected function hasReadRights()
	{
		return true;
	}

	/**
	 * ����������� �������� ���� �� ���������� �������� �������������� ���������.
	 *
     * @return bool
     *
	 * @api
	 */
	protected function hasWriteRights()
	{
		return true;
	}

	/**
	 * �������� ���� �� ��������� ������������� ��������.
     *
	 * @param array $element ������ ������ ��������.
     *
	 * @return bool
     *
     * @api
	 */
	protected function hasWriteRightsElement($element = array())
	{
		if (!$this->hasWriteRights()) {
			return false;
		}

		return true;
	}

	/**
	 * ����������� �������� ���� �� ���������� �������� �������� ���������.
     *
	 * @return bool
     *
	 * @api
	 */
	protected function hasDeleteRights()
	{
		return true;
	}

	/**
	 * ������� ��������� �� �������.
     *
	 * @internal
	 */
	protected function showMessages()
	{
		$allErrors = $this->getErrors();
		$notes = $this->getNotes();

		if (!empty($allErrors)) {
			$errorList[] = implode("\n", $allErrors);
		}
		if ($e = $this->getLastException()) {
			$errorList[] = trim($e->GetString());
		}

		if (!empty($errorList)) {
			$errorText = implode("\n\n", $errorList);
			\CAdminMessage::ShowOldStyleError($errorText);
		}
		else {
			if (!empty($notes)) {
				$noteText = implode("\n\n", $notes);
				\CAdminMessage::ShowNote($noteText);
			}
		}
	}

	/**
	 * @return bool|\CApplicationException
     *
	 * @internal
	 */
	protected function getLastException()
	{
		if (isset($_SESSION['APPLICATION_EXCEPTION']) AND !empty($_SESSION['APPLICATION_EXCEPTION'])) {
			/** @var CApplicationException $e */
			$e = $_SESSION['APPLICATION_EXCEPTION'];
			unset($_SESSION['APPLICATION_EXCEPTION']);

			return $e;
		}
		else {
			return false;
		}
	}

	/**
	 * @param $e
	 */
	protected function setAppException($e)
	{
		$_SESSION['APPLICATION_EXCEPTION'] = $e;
	}

	/**
	 * ��������� ������ ��� ������ ������ ��� ������ ������������.
     *
	 * @param array|string $errors
	 *
     * @api
	 */
	public function addErrors($errors)
	{
		if (!is_array($errors)) {
			$errors = array($errors);
		}

		if (isset($_SESSION['ELEMENT_SAVE_ERRORS']) AND !empty($_SESSION['ELEMENT_SAVE_ERRORS'])) {
			$_SESSION['ELEMENT_SAVE_ERRORS'] = array_merge($_SESSION['ELEMENT_SAVE_ERRORS'], $errors);
		}
		else {
			$_SESSION['ELEMENT_SAVE_ERRORS'] = $errors;
		}
	}

	/**
	 * ��������� ����������� ��� ������ ����������� ��� ������ ������������.
     *
	 * @param array|string $notes
	 *
     * @api
	 */
	public function addNotes($notes)
	{
		if (!is_array($notes)) {
			$notes = array($notes);
		}

		if (isset($_SESSION['ELEMENT_SAVE_NOTES']) AND !empty($_SESSION['ELEMENT_SAVE_NOTES'])) {
			$_SESSION['ELEMENT_SAVE_NOTES'] = array_merge($_SESSION['ELEMENT_SAVE_NOTES'],
				$notes);
		}
		else {
			$_SESSION['ELEMENT_SAVE_NOTES'] = $notes;
		}
	}

	/**
	 * @return bool|array
     *
	 * @api
	 */
	protected function getErrors()
	{
		if (isset($_SESSION['ELEMENT_SAVE_ERRORS']) AND !empty($_SESSION['ELEMENT_SAVE_ERRORS'])) {
			$errors = $_SESSION['ELEMENT_SAVE_ERRORS'];
			unset($_SESSION['ELEMENT_SAVE_ERRORS']);

			return $errors;
		}
		else {
			return false;
		}
	}

	/**
	 * @return bool
     *
	 * @api
	 */
	protected function getNotes()
	{
		if (isset($_SESSION['ELEMENT_SAVE_NOTES']) AND !empty($_SESSION['ELEMENT_SAVE_NOTES'])) {
			$notes = $_SESSION['ELEMENT_SAVE_NOTES'];
			unset($_SESSION['ELEMENT_SAVE_NOTES']);

			return $notes;
		}
		else {
			return false;
		}
	}

	/**
	 * ���������� ����� ������� ������� ���� �� ���� ������������������ �������� � ������ � �����������
	 * � ��� �� ���������� ��� ����� ������� �� �������� ������ ���� �����
	 *
	 * ��� ����� ���������� ��������� �������� �� ������ AdminHelper.
	 *
	 * �������� ���� ��� ����� �������� ListHelper ��� ������������ ������ �� ������ �� EditHelper,
	 * �� ��� ����� �������� ��� $listHelperClass = static::getHelperClass(AdminListHelper::getClass())
	 *
	 * @param $class
     *
	 * @return string|bool
	 */
	public function getHelperClass($class)
	{
		$interfaceSettings = self::$interfaceSettings[static::getModule()];

		foreach ($interfaceSettings as $viewName => $settings) {
			$parentClasses = class_parents($settings['helper']);
			array_pop($parentClasses); // AdminBaseHelper

			$parentClass = array_pop($parentClasses);
			$thirdClass = array_pop($parentClasses);

			if (in_array($thirdClass, array(AdminSectionListHelper::className(), AdminSectionEditHelper::className()))) {
				$parentClass = $thirdClass;
			}

			if ($parentClass == $class && class_exists($settings['helper'])) {
				$helperClassParts = explode('\\', $settings['helper']);
				array_pop($helperClassParts);
				$helperNamespace = implode('\\', $helperClassParts);

				$�lassParts = explode('\\', get_called_class());
				array_pop($�lassParts);
				$classNamespace = implode('\\', $�lassParts);

				if ($helperNamespace == $classNamespace) {
					return $settings['helper'];
				}
			}
		}

		return false;
	}

	/**
	 * ���������� ������������� namespace �� �������� � ���� URL ���������.
     *
	 * @return string
	 */
	public static function getEntityCode()
	{
		$namespaceParts = explode('\\', get_called_class());
		array_pop($namespaceParts);
		array_shift($namespaceParts);
		array_shift($namespaceParts);

		if (end($namespaceParts) == 'AdminInterface') {
			array_pop($namespaceParts);
		}

		return str_replace(
			'\\',
			'_',
			implode(
				'\\',
				array_map('lcfirst', $namespaceParts)
			)
		);
	}

	/**
	 * ���������� URL �������� �������������� ������ ������� �������������.
     *
	 * @param array $params
	 *
     * @return string
	 *
     * @api
	 */
	public static function getEditPageURL($params = array())
	{
		$editHelperClass = str_replace('List', 'Edit', get_called_class());
		if (empty(static::$editViewName) && class_exists($editHelperClass)) {
			return $editHelperClass::getViewURL($editHelperClass::getViewName(), static::$editPageUrl, $params);
		}
		else {
			return static::getViewURL(static::$editViewName, static::$editPageUrl, $params);
		}
	}

	/**
	 * ���������� URL �������� �������������� ������ ������� �������������.
     *
	 * @param array $params
	 *
     * @return string
	 *
     * @api
	 */
	public static function getSectionsEditPageURL($params = array())
	{
		$sectionEditHelperClass = str_replace('List', 'SectionsEdit', get_called_class());

        if (empty(static::$sectionsEditViewName) && class_exists($sectionEditHelperClass)) {
			return $sectionEditHelperClass::getViewURL($sectionEditHelperClass::getViewName(), static::$sectionsEditPageUrl, $params);
		}
		else {
			return static::getViewURL(static::$sectionsEditViewName, static::$sectionsEditPageUrl, $params);
		}
	}

	/**
	 * ���������� URL �������� ������ ������ ������� �������������.
     *
	 * @param array $params
	 *
     * @return string
	 *
     * @api
	 */
	public static function getListPageURL($params = array())
	{
		$listHelperClass = str_replace('Edit', 'List', get_called_class());

        if (empty(static::$listViewName) && class_exists($listHelperClass)) {
			return $listHelperClass::getViewURL($listHelperClass::getViewName(), static::$listPageUrl, $params);
		}
		else {
			return static::getViewURL(static::$listViewName, static::$listPageUrl, $params);
		}
	}

	/**
	 * �������� URL ��� ���������� �������������
	 *
	 * @param string $viewName �������� �������������.
	 * @param string $defaultURL ��������� ������� URL ��������. ���� �������, �� ����� ������������ ��� ��������.
	 * @param array $params �������������� query-��������� � URL.
     *
	 * @return string
	 *
     * @internal
	 */
	public static function getViewURL($viewName, $defaultURL, $params = array())
	{
		$params['entity'] = static::getEntityCode();

		if (isset($defaultURL)) {
			$url = $defaultURL . "?lang=" . LANGUAGE_ID;
		}
		else {
			$url = static::getRouterURL() . '?lang=' . LANGUAGE_ID . '&module=' . static::getModule() . '&view=' . $viewName;
		}

		if (!empty($params)) {
			unset($params['lang']);
			unset($params['module']);
			unset($params['view']);

			$query = http_build_query($params);
			$url .= '&' . $query;
		}

		return $url;
	}

	/**
	 * ���������� ����� ����������� �������� � �����. ����������.
	 *
     * @return string
	 *
     * @api
	 */
	public static function getRouterURL()
	{
		return static::$routerUrl;
	}

    /**
     * ���������� URL �������� � ��������. ��� �������, ����� ���������� ��� ��������� �����������������
     * ���� (`menu.php`).
     *
     * @param array $params �������������� GET-��������� ��� ����������� � URL.
     *
     * @return string
     */
	public static function getUrl(array $params = array())
	{
		return static::getViewURL(static::getViewName(), null, $params);
	}

	/**
	 * �������� ������ ��� �������� ����, ��������� ������� �������������.
	 *
	 * @param string $code ���� ���� ��� ������� ������� (������ ���� � ������� $data).
	 * @param array $data ������ ������� � ���� �������.
	 *
     * @return bool|\DigitalWand\AdminHelper\Widget\HelperWidget
     *
	 * @throws \DigitalWand\AdminHelper\Helper\Exception
	 *
     * @internal
	 */
	public function createWidgetForField($code, &$data = array())
	{
		if (!isset($this->fields[$code]['WIDGET'])) {
			$error = str_replace('#CODE#', $code, 'Can\'t create widget for the code "#CODE#"');
			throw new Exception($error, Exception::CODE_NO_WIDGET);
		}

		/** @var HelperWidget $widget */
		$widget = $this->fields[$code]['WIDGET'];

		$widget->setHelper($this);
		$widget->setCode($code);
		$widget->setData($data);
		$widget->setEntityName($this->getModel());

		$this->onCreateWidgetForField($widget, $data);

		if (!$this->hasWriteRightsElement($data)) {
			$widget->setSetting('READONLY', true);
		}

		return $widget;
	}

	/**
	 * ����� ���������� ��� �������� ������� ��� �������� ����. ����� ���� ����������� ��� ��������� �������� �������
     * �� ������ ������������ ������.
	 *
	 * @param \DigitalWand\AdminHelper\Widget\HelperWidget $widget
	 * @param array $data
	 */
	protected function onCreateWidgetForField(&$widget, $data = array())
	{
	}

	/**
	 * ���� ����� �� ��������, �� ������� ���������� ����� ����� � ��������. ���� ����� ��� ����, �� ���������� ���
     * ��� ����.
	 *
	 * @param $className
	 * @return \Bitrix\Highloadblock\DataManager
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 * @throws Exception
	 */
	public static function getHLEntity($className)
	{
		if (!class_exists($className)) {
			$info = static::getHLEntityInfo($className);

			if ($info) {
				$entity = HL\HighloadBlockTable::compileEntity($info);

				return $entity->getDataClass();
			}
			else {
				$error = Loc::getMessage('DIGITALWAND_ADMIN_HELPER_GETMODEL_EXCEPTION', array('#CLASS#' => $className));
				$exception = new Exception($error, Exception::CODE_NO_HL_ENTITY_INFORMATION);

				throw $exception;
			}
		}

		return $className;
	}

	/**
	 * �������� ������ �� �� � ����������� �� HL.
	 *
	 * @param string $className �������� ������, ����������� ��� Table � ����� � ��� �������� ����������.
     *
	 * @return array|false
	 *
     * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getHLEntityInfo($className)
	{
		$className = str_replace('\\', '', $className);
		$pos = strripos($className, 'Table', -5);

        if ($pos !== false) {
			$className = substr($className, 0, $pos);
		}

        $parameters = array(
			'filter' => array(
				'NAME' => $className,
			),
			'limit' => 1
		);

		return HL\HighloadBlockTable::getList($parameters)->fetch();
	}

	/**
	 * ���������� �������� 404 ������
	 */
	protected function show404()
	{
		// ������������� ���������� ����������, ����������� ��� ������ �������� ����������������� ������� �
		// ������� ������� ���������
		global $APPLICATION, $adminPage, $adminMenu, $USER;
		\CHTTP::SetStatus(404);
		include $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/admin/404.php';
		die();
	}

	/**
	 * ���������� ������� �������� ����������.
     *
	 * @param $context
	 *
     * @see $context
	 */
	protected function setContext($context)
	{
		$this->context = $context;
	}

	public function getContext()
	{
		return $this->context;
	}

	public static function className()
	{
		return get_called_class();
	}
}