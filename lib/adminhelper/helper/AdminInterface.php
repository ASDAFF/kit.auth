<?php

namespace DigitalWand\AdminHelper\Helper;

/**
 * ������� ����� ��� �������� ���������� ����������.
 * �������� � ���� ������ ����������� �������� ����������, �������� ��������, ���� ����� � �.�.
 *
 * ���� 2 ������ ������� ����������� ������ ���� ������� � ����������� �������:
 *
 * getFields()  - ������ ���������� ������ �� ������� ����� � ��������� ����� ��� ������� ����
 * getHelpers() - ������ ����������� ������ �� ������� ������� ��������, ����� ����� ��������
 * �������� �������� ��������� ���������� ��� �������.
 *
 * ��� ���� ��� �� ������ ��� ��������� �������� ���������� ����������� ������� �������������� �� AdminInterface.
 * ��� ����� ������� � include.php ������� ������(�� �������������) ��� AdminInterface ����������������
 * ������������� ���� ��� ��������� ������ �� �������� ���������� ���������� ������������� �����������
 * ����� getLink �� ���������������� ������� (ListHelper ��� ������ ��������� � EditHelper ��� �������� ��������������)
 *
 * ��� ������������� �������� ���������� ��������� AdminInterface ��������� � AdminInterface �������� � �������������
 * ���� �����, ��� �� ������ �� ��� ������������� ������� � ������ ����������� �����������. ��� ����� ���������� ������� ������
 * ��� ������ � ������ getDependencies(), ��� ����� ������� ��� ��� AdminInterface ��������� ��� � ��� AdminInterface ��������.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 * @author Artem Yarygin <artx19@yandex.ru>
 */
abstract class AdminInterface
{
	/**
	 * ������ ������������������ �����������
	 * @var string
	 */
	public static $registeredInterfaces = array();

	/**
	 * �������� ���������� �������: ������ ����� � �����. ����� ������ ������� ������ ����:
	 *
	 * ```
	 * array(
	 *    'TAB_1' => array(
	 *        'NAME' => Loc::getMessage('VENDOR_MODULE_ENTITY_TAB_1_NAME'),
	 *        'FIELDS' => array(
	 *            'FIELD_1' => array(
	 *                'WIDGET' => new StringWidget(),
	 *                'TITLE' => Loc::getMessage('VENDOR_MODULE_ENTITY_FIELD_1_TITLE'),
	 *                ...
	 *            ),
	 *            'FIELD_2' => array(
	 *                'WIDGET' => new NumberWidget(),
	 *                'TITLE' => Loc::getMessage('VENDOR_MODULE_ENTITY_FIELD_2_TITLE'),
	 *                ...
	 *            ),
	 *            ...
	 *        )
	 *    ),
	 *    'TAB_2' => array(
	 *        'NAME' => Loc::getMessage('VENDOR_MODULE_ENTITY_TAB_2_NAME'),
	 *        'FIELDS' => array(
	 *            'FIELD_3' => array(
	 *                'WIDGET' => new DateTimeWidget(),
	 *                'TITLE' => Loc::getMessage('VENDOR_MODULE_ENTITY_FIELD_3_TITLE'),
	 *                ...
	 *            ),
	 *            'FIELD_4' => array(
	 *                'WIDGET' => new UserWidget(),
	 *                'TITLE' => Loc::getMessage('VENDOR_MODULE_ENTITY_FIELD_4_TITLE'),
	 *                ...
	 *            ),
	 *            ...
	 *        )
	 *    ),
	 *  ...
	 * )
	 * ```
	 *
	 * ��� TAB_1..2 - ���������� ���� �����, FIELD_1..4 - �������� �������� � ������� ��������. TITLE ��� ���� ��������
	 * �� �����������, � ����� ������ �� ����� ������������� �� ������.
	 *
	 * ����� ��������� ���������� � ������� �������� �������� �������� ��. � ������ HelperWidget.
	 *
	 * @see DigitalWand\AdminHelper\Widget\HelperWidget
	 *
	 * @return array[]
	 */
	abstract public function fields();

	/**
	 * ������ ������� �������� � �����������. ����� ������ ������� ������ ����:
	 *
	 * ```
	 * array(
	 *    '\Vendor\Module\Entity\AdminInterface\EntityListHelper' => array(
	 *        'BUTTONS' => array(
	 *            'RETURN_TO_LIST' => array('TEXT' => Loc::getMessage('VENDOR_MODULE_ENTITY_RETURN_TO_LIST')),
	 *            'ADD_ELEMENT' => array('TEXT' => Loc::getMessage('VENDOR_MODULE_ENTITY_ADD_ELEMENT'),
	 *            ...
	 *        )
	 *    ),
	 *    '\Vendor\Module\Entity\AdminInterface\EntityEditHelper' => array(
	 *        'BUTTONS' => array(
	 *            'LIST_CREATE_NEW' => array('TEXT' => Loc::getMessage('VENDOR_MODULE_ENTITY_LIST_CREATE_NEW')),
	 *            'LIST_CREATE_NEW_SECTION' => array('TEXT' => Loc::getMessage('VENDOR_MODULE_ENTITY_LIST_CREATE_NEW_SECTION'),
	 *            ...
	 *        )
	 *    )
	 * )
	 * ```
	 *
	 * ���
	 *
	 * ```
	 * array(
	 *    '\Vendor\Module\Entity\AdminInterface\EntityListHelper',
	 *    '\Vendor\Module\Entity\AdminInterface\EntityEditHelper'
	 * )
	 * ```
	 *
	 * ���:
	 * <ul>
	 * <li> `Vendor\Module\Entity\AdminInterface` - namespace �� ������������� ������� AdminHelper.
	 * <li> `BUTTONS` - ���� ��� ������� � ��������� ��������� ���������� (��������� � ������ getButton()
	 *          ������ AdminBaseHelper).
	 * <li> `LIST_CREATE_NEW`, `LIST_CREATE_NEW_SECTION`, `RETURN_TO_LIST`, `ADD_ELEMENT` - ���������� ��� ���������
	 *          ����������.
	 * <li> `EntityListHelper` � `EntityEditHelper` - ������������� ������ ��������.
	 *
	 * ��� ������� ����� ���������� ���� � ������.
	 *
	 * @see \DigitalWand\AdminHelper\Helper\AdminBaseHelper::getButton()
	 *
	 * @return string[]
	 */
	abstract public function helpers();

	/**
	 * ������ ��������� ��������� �����������, ������� ����� ���������������� ��� ���������� ���������� ����������,
	 * ��������, ��������� ���������� ��������.
	 *
	 * @return string[]
	 */
	public function dependencies()
	{
		return array();
	}

	/**
	 * ������������ ����, ���� � ������.
	 */
	public function registerData()
	{
		$fieldsAndTabs = array('FIELDS' => array(), 'TABS' => array());
		$tabsWithFields = $this->fields();

		// �������� ������ �������� � ������� ����� => ���������
		$helpers = array();

		foreach ($this->helpers() as $key => $value) {
			if (is_array($value)) {
				$helpers[$key] = $value;
			}
			else {
				$helpers[$value] = array();
			}
		}

		$helperClasses = array_keys($helpers);
		/**
		 * @var \Bitrix\Main\Entity\DataManager
		 */
		$model = $helperClasses[0]::getModel();
		foreach ($tabsWithFields as $tabCode => $tab) {
			$fieldsAndTabs['TABS'][$tabCode] = $tab['NAME'];

			foreach ($tab['FIELDS'] as $fieldCode => $field) {
				if (empty($field['TITLE']) && $model) {
					$field['TITLE'] = $model::getEntity()->getField($fieldCode)->getTitle();
				}

				$field['TAB'] = $tabCode;
				$fieldsAndTabs['FIELDS'][$fieldCode] = $field;
			}
		}

		AdminBaseHelper::setInterfaceSettings($fieldsAndTabs, $helpers, $helperClasses[0]::getModule());

		foreach ($helperClasses as $helperClass) {
			/**
			 * @var AdminBaseHelper $helperClass
			 */
			$helperClass::setInterfaceClass(get_called_class());
		}
	}

	/**
	 * ����������� ���������� � ��� ������������.
	 */
	public static function register()
	{
		if (!in_array(get_called_class(), static::$registeredInterfaces)) {
			static::$registeredInterfaces[] = get_called_class();

			$adminInterface = new static();
			$adminInterface->registerData();

			foreach ($adminInterface->dependencies() as $adminInterfaceClass) {
				$adminInterfaceClass::register();
			}
		}
	}
}