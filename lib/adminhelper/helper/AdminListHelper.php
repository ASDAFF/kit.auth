<?php

namespace DigitalWand\AdminHelper\Helper;

use Bitrix\Main\Context;
use Bitrix\Main\DB\Result;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use DigitalWand\AdminHelper\EntityManager;

Loc::loadMessages(__FILE__);

/**
 * ������� ����� ��� ���������� �������� ������ �������.
 * ��� �������� ������ ������ ���������� �������������� ��������� ����������:
 * <ul>
 * <li> static protected $model </Li>
 * </ul>
 *
 * ����� ����� ���������� ��� ��������� ����������� ����������������
 * ����� ������ ����� ����� �������������� ��� ����������� ����������� ���� � ������������ ������ �������� �� ������
 *
 * @see AdminBaseHelper::$model
 * @see AdminBaseHelper::$module
 * @see AdminBaseHelper::$editViewName
 * @see AdminBaseHelper::$viewName
 * @package AdminHelper
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 * @author Artem Yarygin <artx19@yandex.ru>
 */
abstract class AdminListHelper extends AdminBaseHelper
{
	const OP_GROUP_ACTION = 'AdminListHelper::__construct_groupAction';
	const OP_ADMIN_VARIABLES_FILTER = 'AdminListHelper::prepareAdminVariables_filter';
	const OP_ADMIN_VARIABLES_HEADER = 'AdminListHelper::prepareAdminVariables_header';
	const OP_GET_DATA_BEFORE = 'AdminListHelper::getData_before';
	const OP_ADD_ROW_CELL = 'AdminListHelper::addRowCell';
	const OP_CREATE_FILTER_FORM = 'AdminListHelper::createFilterForm';
	const OP_CHECK_FILTER = 'AdminListHelper::checkFilter';
	const OP_EDIT_ACTION = 'AdminListHelper::editAction';
	/**
	 * @var bool
	 * �������� ������ �������� � Excel
	 * @api
	 */
	protected $exportExcel = true;
	/**
	 * @var bool
	 * �������� � ������ ���-�� ��������� ����� ���
	 */
	protected $showAll = true;
	/**
	 * @var bool
	 * �������� �� ������ ����������� ����� ��� ������ ��������� �� ������.
	 * � ���� ������ �� ������ ���� �������� ��������/�������� � ��������������.
	 */
	protected $isPopup = false;
	/**
	 * @var string
	 * �������� ����, � ������� �������� ��������� ������ �� ����������� ����
	 */
	protected $fieldPopupResultName = '';
	/**
	 * @var string
	 * ���������� ������ ����, � ������� �������� ��������� ������ �� ����������� ����
	 */
	protected $fieldPopupResultIndex = '';
	protected $sectionFields         = [];
	/**
	 * @var string
	 * �������� �������, � ������� �������� �������� ��������
	 */
	protected $fieldPopupResultElTitle = '';
	/**
	 * @var string
	 * �������� �������, ���������� ��� ��������� �� ������ ������, � ������, ���� ������ ��������� � ������
	 *     ������������ ����
	 */
	protected $popupClickFunctionName = 'selectRow';
	/**
	 * @var string
	 * ��� �������, ���������� ��� ����� �� ������ ������
	 * @see AdminListHelper::genPipupActionJS()
	 */
	protected $popupClickFunctionCode;
	/**
	 * @var array
	 * ������ � ����������� �������
	 * @see \CAdminList::AddHeaders()
	 */
	protected $arHeader = [];
	/**
	 * @var array
	 * ��������� ���������� ������ � ������������ ����������� �������
	 */
	protected $arFilter = [];
	/**
	 * @var array
	 * ������, �������� ��� ������� ��� ������� ����. ��������� �������� ������� �������� �����.
	 */
	protected $filterTypes = [];
	/**
	 * @var array
	 * ����, ��������������� ��� ����������
	 * @see \CAdminList::InitFilter();
	 */
	protected $arFilterFields = [];
	/**
	 * ������ �����, ��� ������� �������� ����������
	 * @var array
	 * @see \CAdminFilter::__construct();
	 */
	protected $arFilterOpts = [];
	/**
	 * @var \CAdminList
	 */
	protected $list;
	/**
	 * @var string
	 * ������� �������. �����, ����� ���������� ������������ ������������ ������ �����. �����������.
	 * ��� ��� ���������� � ������������ ������� ���������� �����������, ��� ��������� �������� � �������� �� �������
	 * ����������������� ����������, � ���������� ���� ����������� ����� �������� ����������, ����������. ��������
	 * ������ �������� � ��.
	 */
	static protected $tablePrefix = "digitalwand_admin_helper_";
	/**
	 * @var array
	 * @see \CAdminList::AddGroupActionTable()
	 */
	protected $groupActionsParams = [];
	/**
	 * ������� ��������� ���������,
	 * ��������� ��� ����������� ���������� ������ �������� � ���������
	 * @var array
	 */
	protected $navParams = [];
	/**
	 * ���������� ��������� ��������� ������
	 * @see AdminListHelper::CustomNavStart
	 * @var int
	 */
	protected $totalRowsCount = 0;
	/**
	 * ������ ��� ������� �������� ��������� � ��������
	 * @var array
	 */
	protected $tableColumnsMap = [];
	/**
	 * @var string
	 * HTML ������� ����� �������
	 * @api
	 */
	public $prologHtml;
	/**
	 * @var string
	 * HTML ������ ����� �������
	 * @api
	 */
	public $epilogHtml;

	/**
	 * ������������ ������������� ����������, ��������� �������� �� ��������������
	 *
	 * @param array $fields
	 * @param bool $isPopup
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function __construct(
		array $fields,
		$isPopup = false
	)
	{
		$this->isPopup = $isPopup;
		if($this->isPopup)
		{
			$this->fieldPopupResultName = preg_replace("/[^a-zA-Z0-9_:\\[\\]]/", "", $_REQUEST['n']);
			$this->fieldPopupResultIndex = preg_replace("/[^a-zA-Z0-9_:]/", "", $_REQUEST['k']);
			$this->fieldPopupResultElTitle = $_REQUEST['eltitle'];
		}
		parent::__construct($fields);

//		I not understand why this need
//		$this->restoreLastGetQuery();

		$this->prepareAdminVariables();
		$className = static::getModel();
		$oSort = $this->initSortingParameters(Context::getCurrent()->getRequest());
		$this->list = new \CAdminList($this->getListTableID(), $oSort);
		$this->list->InitFilter($this->arFilterFields);
		if($this->list->EditAction() AND $this->hasWriteRights())
		{
			global $FIELDS;
			foreach ($FIELDS as $id => $fields)
			{
				if(!$this->list->IsUpdated($id))
				{
					continue;
				}
				$this->editAction($id, $fields);
			}
		}
		if($IDs = $this->list->GroupAction() AND $this->hasWriteRights())
		{
			if($_REQUEST['action_target'] == 'selected')
			{
				$this->setContext(AdminListHelper::OP_GROUP_ACTION);
				$IDs = [];
				//������� ������ ������ ���� ������������� ��������
				//��� ������������ ����������� ���������� ����, ��� ����� ������������ � ����������.
				$raw = [
					'SELECT' => $this->pk(),
					'FILTER' => $this->arFilter,
					'SORT' => []
				];
				foreach ($this->fields as $code => $settings)
				{
					$widget = $this->createWidgetForField($code);
					$widget->changeGetListOptions($this->arFilter, $raw['SELECT'], $raw['SORT'], $raw);
				}
				$res = $className::getList([
					'filter' => $this->arFilter,
					'select' => [$this->pk()],
				]);
				while ($el = $res->Fetch())
				{
					$IDs[] = $el[$this->pk()];
				}
			}
			$filteredIDs = [];
			foreach ($IDs as $id)
			{
				if(strlen($id) <= 0)
				{
					continue;
				}
				$filteredIDs[] = IntVal($id);
			}
			$this->groupActions($IDs, $_REQUEST['action']);
		}
		if(isset($_REQUEST['action']) || isset($_REQUEST['action_button']) && count($this->getErrors()) == 0)
		{
			$listHelperClass = $this->getHelperClass(AdminListHelper::className());
			$id = isset($_GET['ID']) ? $_GET['ID'] : null;
			$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : $_REQUEST['action_button'];
			if($action != 'edit' && $_REQUEST['cancel'] != 'Y')
			{
				$params = $_GET;
				unset($params['action']);
				unset($params['action_button']);
				$this->customActions($action, $id);
				LocalRedirect($listHelperClass::getUrl($params));
			}
		}
		if($this->isPopup())
		{
			$this->genPopupActionJS();
		}
		// �������� ��������� ���������
		$navUniqSettings = [
			'nPageSize' => 20,
			'sNavID' => $this->getListTableID()
		];
		$this->navParams = [
			'nPageSize' => \CAdminResult::GetNavSize($this->getListTableID(), $navUniqSettings),
			'navParams' => \CAdminResult::GetNavParams($navUniqSettings)
		];
	}

	/**
	 * �������������� ��������� ���������� �� ��������� �������
	 * @return \CAdminSorting
	 */
	protected function initSortingParameters(HttpRequest $request)
	{
		$sortByParameter = 'by';
		$sortOrderParameter = 'order';
		$sortBy = $request->get($sortByParameter);
		$sortBy = $sortBy ?: static::pk();
		$sortOrder = $request->get($sortOrderParameter);
		$sortOrder = $sortOrder ?: 'desc';

		return new \CAdminSorting($this->getListTableID(), $sortBy, $sortOrder, $sortByParameter, $sortOrderParameter);
	}

	/**
	 * �������������� ����������, ������������ ��� ������������� ������.
	 *
	 * - ��������� ���� � ������ ������� ������ ���� FILTER �� ������ false �� ��������� ��� ������� � ���� �� ��������
	 * ����� ����� ��������� ��������
	 */
	protected function prepareAdminVariables()
	{
		$this->arHeader = [];
		$this->arFilter = [];
		$this->arFilterFields = [];
		$arFilter = [];
		$this->filterTypes = [];
		$this->arFilterOpts = [];
		$sectionField = static::getSectionField();
		foreach ($this->fields as $code => $settings)
		{
			$widget = $this->createWidgetForField($code);
			if(
				($sectionField != $code && $widget->getSettings('FILTER') !== false)
				&&
				((isset($settings['FILTER']) AND $settings['FILTER'] != false) OR !isset($settings['FILTER']))
			)
			{
				$this->setContext(AdminListHelper::OP_ADMIN_VARIABLES_FILTER);
				$filterVarName = 'find_' . $code;
				$this->arFilterFields[] = $filterVarName;
				$filterType = '';
				if(is_string($settings['FILTER']))
				{
					$filterType = $settings['FILTER'];
				}
				if(isset($_REQUEST[$filterVarName])
					AND !isset($_REQUEST['del_filter'])
					AND $_REQUEST['del_filter'] != 'Y'
				)
				{
					$arFilter[$filterType . $code] = $_REQUEST[$filterVarName];
					$this->filterTypes[$code] = $filterType;
				}
				$this->arFilterOpts[$code] = $widget->getSettings('TITLE');
			}
			if(!isset($settings['LIST']) || $settings['LIST'] === true)
			{
				$this->setContext(AdminListHelper::OP_ADMIN_VARIABLES_HEADER);
				$mergedColumn = false;
				// ��������� ���� �� ������� ������� � ����� ���������
				if($widget->getSettings('LIST_TITLE'))
				{
					$sectionHeader = $this->getSectionsHeader();
					foreach ($sectionHeader as $sectionColumn)
					{
						if($sectionColumn['content'] == $widget->getSettings('LIST_TITLE'))
						{
							// ��������� ������� ��������� � ����� ��������
							$this->tableColumnsMap[$code] = $sectionColumn['id'];
							$mergedColumn = true;
							break;
						}
					}
				}
				if(!$mergedColumn)
				{
					$this->arHeader[] = [
						"id" => $code,
						"content" => $widget->getSettings('LIST_TITLE') ? $widget->getSettings('LIST_TITLE') : $widget->getSettings('TITLE'),
						"sort" => $code,
						"default" => !isset($settings['HEADER']) || $settings['HEADER'] === true,
						'admin_list_helper_sort' => $widget->getSettings('LIST_COLUMN_SORT') ? $widget->getSettings('LIST_COLUMN_SORT') : 100
					];
				}
			}
		}
		if($this->checkFilter($arFilter))
		{
			$this->arFilter = $arFilter;
		}
		if(static::getHelperClass(AdminSectionEditHelper::className()))
		{
			$this->arFilter[static::getSectionField()] = $_GET['ID'];
		}
	}

	/**
	 * ���������� ������ �������� ��� ��������
	 * @return array
	 */
	public function getSectionsHeader()
	{
		$arSectionsHeaders = [];
		$sectionHelper = static::getHelperClass(AdminSectionEditHelper::className());
		$sectionsInterfaceSettings = static::getInterfaceSettings($sectionHelper::getViewName());
		$this->sectionFields = $sectionsInterfaceSettings['FIELDS'];
		foreach ($sectionsInterfaceSettings['FIELDS'] as $code => $settings)
		{
			if(!isset($settings['LIST']) || $settings['LIST'] === true)
			{
				$arSectionsHeaders[] = [
					"id" => $code,
					"content" => isset($settings['LIST_TITLE']) ? $settings['LIST_TITLE'] : $settings['TITLE'],
					"sort" => $code,
					"default" => !isset($settings['HEADER']) || $settings['HEADER'] === true,
					'admin_list_helper_sort' => isset($settings['LIST_COLUMN_SORT']) ? $settings['LIST_COLUMN_SORT'] : 100
				];
			}
			unset($settings['WIDGET']);
			foreach ($settings as $c => $v)
			{
				$sectionsInterfaceSettings['FIELDS'][$code]['WIDGET']->setSetting($c, $v);
			}
		}

		return $arSectionsHeaders;
	}

	/**
	 * ���������� �������� ������������ ������ (� ������� $_REQUEST), ���������� � ������
	 * @TODO: ����� ������� ����� ��������� �� ������ ����������.
	 * @param $arFilter
	 * @return bool
	 */
	protected function checkFilter($arFilter)
	{
		$this->setContext(AdminListHelper::OP_CHECK_FILTER);
		$filterValidationErrors = [];
		foreach ($this->filterTypes as $code => $type)
		{
			$widget = $this->createWidgetForField($code);
			$value = $arFilter[$type . $code];
			if(!$widget->checkFilter($type, $value))
			{
				$filterValidationErrors = array_merge($filterValidationErrors,
					$widget->getValidationErrors());
			}
		}

		return empty($filterValidationErrors);
	}

	/**
	 * �������������� ������ � ����������� ������������ ����. ��-��������� ��������� ������ "������� �������".
	 *
	 * @see $contextMenu
	 *
	 * @api
	 */
	protected function getContextMenu()
	{
		$contextMenu = [];
		/** @var AdminSectionEditHelper $sectionEditHelper */
		$sectionEditHelper = static::getHelperClass(AdminSectionEditHelper::className());
		if($sectionEditHelper)
		{
			$sectionId = $_GET['SECTION_ID'] ?: $_GET['ID'] ?: null;
			$this->additionalUrlParams['SECTION_ID'] = $sectionId = $sectionId > 0 ? (int)$sectionId : null;
		}
		/**
		 * ���� ����� ��� �������� ��������� ������ ������� ������ �
		 * ������ �� ������� ����� ���� ��� �� �������� ������
		 */
		if(isset($sectionId))
		{
			$params = $this->additionalUrlParams;
			$sectionModel = $sectionEditHelper::getModel();
			$sectionField = $sectionEditHelper::getSectionField();
			$section = $sectionModel::getById(
				$this->getCommonPrimaryFilterById($sectionModel, null, $sectionId)
			)->Fetch();
			if($this->isPopup())
			{
				$params = array_merge($_GET);
			}
			if($section[$sectionField])
			{
				$params['ID'] = $section[$sectionField];
			}
			else
			{
				unset($params['ID']);
			}
			unset($params['SECTION_ID']);
			$contextMenu[] = $this->getButton('LIST_SECTION_UP', [
				'LINK' => static::getUrl($params),
				'ICON' => 'btn_list'
			]);
		}
		/**
		 * ��������� ������ ������� ������� � ������� ������
		 */
		if(!$this->isPopup() && $this->hasWriteRights())
		{
			$editHelperClass = static::getHelperClass(AdminEditHelper::className());
			if($editHelperClass)
			{
				$contextMenu[] = $this->getButton('LIST_CREATE_NEW', [
					'LINK' => $editHelperClass::getUrl($this->additionalUrlParams),
					'ICON' => 'btn_new'
				]);
			}
			$sectionsHelperClass = static::getHelperClass(AdminSectionEditHelper::className());
			if($sectionsHelperClass)
			{
				$contextMenu[] = $this->getButton('LIST_CREATE_NEW_SECTION', [
					'LINK' => $sectionsHelperClass::getUrl($this->additionalUrlParams),
					'ICON' => 'btn_new'
				]);
			}
		}

		return $contextMenu;
	}

	/**
	 * ���������� ������ � ����������� ��������� �������� ��� �������.
	 *
	 * @return array
	 *
	 * @api
	 */
	protected function getGroupActions()
	{
		$result = [];
		if(!$this->isPopup())
		{
			if($this->hasDeleteRights())
			{
				$result = ['delete' => Loc::getMessage("DIGITALWAND_ADMIN_HELPER_LIST_DELETE")];
			}
		}

		return $result;
	}

	/**
	 * ���������� ��������� ��������. ��-��������� ��������� �������� ��������� / ����������� � ��������.
	 *
	 * @param array $IDs
	 * @param string $action
	 *
	 * @api
	 */
	protected function groupActions(
		$IDs,
		$action
	)
	{
		$sectionEditHelperClass = $this->getHelperClass(AdminSectionEditHelper::className());
		$listHelperClass = $this->getHelperClass(AdminListHelper::className());
		$className = static::getModel();
		if(isset($_REQUEST['model']))
		{
			$className = $_REQUEST['model'];
		}
		if($sectionEditHelperClass && !isset($_REQUEST['model-section']))
		{
			$sectionClassName = $sectionEditHelperClass::getModel();
		}
		else
		{
			$sectionClassName = $_REQUEST['model-section'];
		}
		if($action == 'delete')
		{
			if($this->hasDeleteRights())
			{
				$complexPrimaryKey = is_array($className::getEntity()->getPrimary());
				if($complexPrimaryKey)
				{
					$IDs = $this->getIds();
				}
				// ���� ���������� ��� ��� ��������
				if(!empty($IDs[0]))
				{
					$id = $complexPrimaryKey ? $IDs[0][$this->pk()] : $IDs[0];
					$model = $className;
					if(strpos($id, 's') === 0)
					{
						$model = $sectionClassName;
						$listHelper = $this->getHelperClass(AdminSectionListHelper::className());
						if(!$listHelper)
						{
							$this->addErrors(Loc::getMessage('DIGITALWAND_ADMIN_HELPER_LIST_SECTION_HELPER_NOT_FOUND'));
							unset($_GET['ID']);

							return;
						}
						$id = substr($id, 1);
					}
					else
					{
						$listHelper = $listHelperClass;
					}
					if($listHelper)
					{
						$id = $this->getCommonPrimaryFilterById($model, null, $id);
						$element = $model::getById($id)->Fetch();
						$sectionField = $listHelper::getSectionField();
						if($element[$sectionField])
						{
							$_GET[$this->pk()] = $element[$sectionField];
						}
						else
						{
							unset($_GET['ID']);
						}
					}
				}
				foreach ($IDs as $id)
				{
					$model = $className;
					$id = $complexPrimaryKey ? $id[$this->pk()] : $id;
					if(strpos($id, 's') === 0)
					{
						$model = $sectionClassName;
						$id = substr($id, 1);
					}
					/** @var EntityManager $entityManager */
					$entityManager = new static::$entityManager($model, empty($this->data) ? [] : $this->data, $id,
						$this);
					$result = $entityManager->delete();
					$this->addNotes($entityManager->getNotes());
					if(!$result->isSuccess())
					{
						$this->addErrors($result->getErrorMessages());
						break;
					}
				}
			}
			else
			{
				$this->addErrors(Loc::getMessage('DIGITALWAND_ADMIN_HELPER_LIST_DELETE_FORBIDDEN'));
			}
		}
		if($action == 'delete-section')
		{
			if($this->hasDeleteRights())
			{
				// ���� ���������� ��� ��� ��������
				if(!empty($IDs[0]))
				{
					$id = $this->getCommonPrimaryFilterById($sectionClassName, null, $IDs[0]);
					$sectionListHelperClass = $this->getHelperClass(AdminSectionListHelper::className());
					if($sectionListHelperClass)
					{
						$element = $sectionClassName::getById($id)->Fetch();
						$sectionField = $sectionListHelperClass::getSectionField();
						if($element[$sectionField])
						{
							$_GET[$this->pk()] = $element[$sectionField];
						}
						else
						{
							unset($_GET['ID']);
						}
					}
					else
					{
						unset($_GET['ID']);
						$this->addErrors(Loc::getMessage('DIGITALWAND_ADMIN_HELPER_LIST_SECTION_HELPER_NOT_FOUND'));

						return;
					}
				}
				foreach ($IDs as $id)
				{
					$id = $this->getCommonPrimaryFilterById($sectionClassName, null, $id);
					$entityManager = new static::$entityManager($sectionClassName, [], $id, $this);
					$result = $entityManager->delete();
					$this->addNotes($entityManager->getNotes());
					if(!$result->isSuccess())
					{
						$this->addErrors($result->getErrorMessages());
						break;
					}
				}
			}
			else
			{
				$this->addErrors(Loc::getMessage('DIGITALWAND_ADMIN_HELPER_LIST_DELETE_FORBIDDEN'));
			}
		}
	}

	/**
	 * ���������� ����� ��� ����� ������, ����������������� � ������.
	 * �����:
	 * <ul>
	 * <li> ������� �������� �� ID, ����� ��������������, ��� �� ����������. � ��������� ������  ������������
	 * ������</li>
	 * <li> �������� ������� ��� ������ ������, ��������� �������� ����</li>
	 * <li> TODO: ����� ������ ���������</li>
	 * <li> ���������� ������</li>
	 * <li> ����� ������ ����������, ���� ������� ���������</li>
	 * <li> ����������� ������ ����� ���������.</li>
	 * </ul>
	 *
	 * @param int $id ID ������ � ��
	 * @param array $fields ���� � �����������
	 *
	 * @see HelperWidget::processEditAction();
	 * @see HelperWidget::processAfterSaveAction();
	 */
	protected function editAction(
		$id,
		$fields
	)
	{
		$this->setContext(AdminListHelper::OP_EDIT_ACTION);
		if(strpos($id, 's') === 0)
		{ // ��� ������� ������ ����� ������
			$editHelperClass = $this->getHelperClass(AdminSectionEditHelper::className());
			$sectionsInterfaceSettings = static::getInterfaceSettings($editHelperClass::getViewName());
			$className = $editHelperClass::getModel();
			$id = str_replace('s', '', $id);
		}
		else
		{
			$className = static::getModel();
			$sectionsInterfaceSettings = false;
		}
		$idForLog = $id;
		$complexPrimaryKey = is_array($className::getEntity()->getPrimary());
		if($complexPrimaryKey)
		{
			$oldRequest = $_REQUEST;
			$_REQUEST = [$this->pk() => $id];
			$id = $this->getCommonPrimaryFilterById($className, null, $id);
			$idForLog = json_encode($id);
			$_REQUEST = $oldRequest;
		}
		$el = $className::getById($id);
		if($el->getSelectedRowsCount() == 0)
		{
			$this->list->AddGroupError(Loc::getMessage("MAIN_ADMIN_SAVE_ERROR"), $idForLog);

			return;
		}
		// ������ ����� ��� �������� ��������� ����������� �� ��������� ��������
		if($sectionsInterfaceSettings == false)
		{
			$tableColumnsMap = array_flip($this->tableColumnsMap);
			$replacedFields = [];
			foreach ($fields as $key => $value)
			{
				if(!empty($tableColumnsMap[$key]))
				{
					$key = $tableColumnsMap[$key];
				}
				$replacedFields[$key] = $value;
			}
			$fields = $replacedFields;
		}
		$allWidgets = [];
		foreach ($fields as $key => $value)
		{
			if($sectionsInterfaceSettings !== false)
			{ // ��� �������� ���� �������
				$widget = $sectionsInterfaceSettings['FIELDS'][$key]['WIDGET'];
			}
			else
			{
				$widget = $this->createWidgetForField($key, $fields); // ��� ��������� ����
			}
			$widget->processEditAction();
			$this->validationErrors = array_merge($this->validationErrors, $widget->getValidationErrors());
			$allWidgets[] = $widget;
		}
		//FIXME: �����, ���� �������� ����� ������ �� ����������?..
		$this->addErrors($this->validationErrors);
		$result = $className::update($id, $fields);
		$errors = $result->getErrorMessages();
		if(empty($this->validationErrors) AND !empty($errors))
		{
			$fieldList = implode("\n", $errors);
			$this->list->AddGroupError(Loc::getMessage("MAIN_ADMIN_SAVE_ERROR") . " " . $fieldList, $idForLog);
		}
		if(!empty($errors))
		{
			foreach ($allWidgets as $widget)
			{
				/** @var \DigitalWand\AdminHelper\Widget\HelperWidget $widget */
				$widget->setData($fields);
				$widget->processAfterSaveAction();
			}
		}
	}

	/**
	 * �������� �� ������ ����������� ����� ��� ������ ��������� �� ������.
	 * � ���� ������ �� ������ ���� �������� ��������/�������� � ��������������.
	 *
	 * @return boolean
	 */
	public function isPopup()
	{
		return $this->isPopup;
	}

	/**
	 * ������� ���������� js-������� ��� �������� ����� �� ������.
	 * ���������� � ��� ������, ���� ���� ������� � ������ ������.
	 *
	 * @api
	 */
	protected function genPopupActionJS()
	{
		$this->popupClickFunctionCode = '<script>
			function ' . $this->popupClickFunctionName . '(data){
				var input = window.opener.document.getElementById("' . $this->fieldPopupResultName . '[' . $this->fieldPopupResultIndex . ']");
				if(!input)
					input = window.opener.document.getElementById("' . $this->fieldPopupResultName . '");
				if(input)
				{
					input.value = data.ID;
					if (window.opener.BX)
						window.opener.BX.fireEvent(input, "change");
				}
				var span = window.opener.document.getElementById("sp_' . md5($this->fieldPopupResultName) . '_' . $this->fieldPopupResultIndex . '");
				if(!span)
					span = window.opener.document.getElementById("sp_' . $this->fieldPopupResultName . '");
				if(!span)
					span = window.opener.document.getElementById("' . $this->fieldPopupResultName . '_link");
				if(span)
					span.innerHTML = data["' . $this->fieldPopupResultElTitle . '"];
				window.close();
			}
		</script>';
	}

	/**
	 * �������� ���� ����������� ������. �����:
	 * <ul>
	 * <li> ����� ���������� �������� </li>
	 * <li> ����������� ������ ������� ������� � �������, ����������� � �������. </li>
	 * <li> �������� ������� ��� ������� ���� ������� </li>
	 * <li> ����������� ���������� ������� ������ �� �������� </li>
	 * <li> ������� ������ </li>
	 * <li> ����� ����� �������. �� ����� �������� �� ������� �������� ����������� ������ ������. </li>
	 * <li> ��������� ������ �������, ���������� ������������ ���� </li>
	 * </ul>
	 *
	 * @param array $sort ��������� ����������.
	 *
	 * @see AdminListHelper::getList();
	 * @see AdminListHelper::getMixedData();
	 * @see AdminListHelper::modifyRowData();
	 * @see AdminListHelper::addRowCell();
	 * @see AdminListHelper::addRow();
	 * @see HelperWidget::changeGetListOptions();
	 */
	public function buildList($sort)
	{
		$this->setContext(AdminListHelper::OP_GET_DATA_BEFORE);
		$headers = $this->arHeader;
		$sectionEditHelper = static::getHelperClass(AdminSectionEditHelper::className());
		if($sectionEditHelper)
		{
			$sectionHeaders = $this->getSectionsHeader();
			foreach ($sectionHeaders as $sectionHeader)
			{
				$found = false;
				foreach ($headers as $i => $elementHeader)
				{
					if($sectionHeader['content'] == $elementHeader['content'] || $sectionHeader['id'] == $elementHeader['id'])
					{
						if(!$elementHeader['default'] && $sectionHeader['default'])
						{
							$headers[$i] = $sectionHeader;
						}
						else
						{
							$found = true;
						}
						break;
					}
				}
				if(!$found)
				{
					$headers[] = $sectionHeader;
				}
			}
		}

		$this->mergeSortHeader($headers);
		$this->list->AddHeaders($headers);
		$visibleColumns = $this->list->GetVisibleHeaderColumns();
		$modelClass = $this->getModel();
		$elementFields = array_keys($modelClass::getEntity()->getFields());
		if($sectionEditHelper)
		{
			$sectionsVisibleColumns = [];
			foreach ($visibleColumns as $k => $v)
			{
				if(isset($this->sectionFields[$v]))
				{
					if(!in_array($v, $elementFields))
					{
						unset($visibleColumns[$k]);
					}
					if(!isset($this->sectionFields[$v]['LIST']) || $this->sectionFields[$v]['LIST'] !== false)
					{
						$sectionsVisibleColumns[] = $v;
					}
				}
			}
			$visibleColumns = array_values($visibleColumns);
			$visibleColumns = array_merge($visibleColumns, array_keys($this->tableColumnsMap));
		}
		$className = static::getModel();
		$visibleColumns[] = $this->pk();
		$sectionsVisibleColumns[] = $this->sectionPk();
		$raw = [
			'SELECT' => $visibleColumns,
			'FILTER' => $this->arFilter,
			'SORT' => $sort
		];
		foreach ($this->fields as $name => $settings)
		{
			$key = array_search($name, $visibleColumns);
			if((isset($settings['VIRTUAL']) AND $settings['VIRTUAL'] == true))
			{
				unset($visibleColumns[$key]);
				unset($this->arFilter[$name]);
				unset($sort[$name]);
			}
			if(isset($settings['LIST']) && $settings['LIST'] === false)
			{
				unset($visibleColumns[$key]);
			}
			if(isset($settings['FORCE_SELECT']) AND $settings['FORCE_SELECT'] == true)
			{
				$visibleColumns[] = $name;
			}
		}
		$visibleColumns = array_unique($visibleColumns);
		$sectionsVisibleColumns = array_unique($sectionsVisibleColumns);

		$listSelect = array_flip($visibleColumns);
		foreach ($this->fields as $code => $settings)
		{
			if($_REQUEST['del_filter'] !== 'Y')
			{
				$widget = $this->createWidgetForField($code);
				$widget->changeGetListOptions($this->arFilter, $visibleColumns, $sort, $raw);
			}

			if(!empty($settings['MULTIPLE']))
			{
				unset($listSelect[$code]);
			}
		}

		$listSelect = array_flip($listSelect);
		if($sectionEditHelper)
		{
			$mixedData = $this->getMixedData($sectionsVisibleColumns, $visibleColumns, $sort, $raw);
			$res = new \CDbResult;
			$res->InitFromArray($mixedData);
			$res = new \CAdminResult($res, $this->getListTableID());
			$res->nSelectedCount = $this->totalRowsCount;
			$this->customNavStart($res);
			$this->list->NavText($res->GetNavPrint(Loc::getMessage("PAGES")));
			while ($data = $res->NavNext(false))
			{
				$this->modifyRowData($data);
				if($data['IS_SECTION'])
				{
					list($link, $name) = $this->getRow($data, $this->getHelperClass(AdminSectionEditHelper::className()));
					$row = $this->list->AddRow('s' . $data[$this->pk()], $data, $link, $name);
					foreach ($this->sectionFields as $code => $settings)
					{
						if(in_array($code, $sectionsVisibleColumns))
						{
							$this->addRowSectionCell($row, $code, $data);
						}
					}
					$row->AddActions($this->getRowActions($data, true));
				}
				else
				{
					$this->modifyRowData($data);
					list($link, $name) = $this->getRow($data);

					foreach ($this->tableColumnsMap as $elementCode => $sectionCode)
					{
						if(isset($data[$elementCode]))
						{
							$data[$sectionCode] = $data[$elementCode];
						}
					}
					$row = $this->list->AddRow($data[$this->pk()], $data, $link, $name);
					foreach ($this->fields as $code => $settings)
					{
						if(in_array($code, $listSelect))
						{
							$this->addRowCell($row, $code, $data,
								isset($this->tableColumnsMap[$code]) ? $this->tableColumnsMap[$code] : false);
						}
					}
					$row->AddActions($this->getRowActions($data));
				}
			}
		}
		else
		{
			$this->totalRowsCount = $className::getCount($this->getElementsFilter($this->arFilter));
			$res = $this->getData($className, $this->arFilter, $listSelect, $sort, $raw);
			$res = new \CAdminResult($res, $this->getListTableID());
			$this->customNavStart($res);
			$res->bShowAll = $this->showAll;
			$this->list->NavText($res->GetNavPrint(Loc::getMessage("PAGES")));
			while ($data = $res->NavNext(false))
			{
				$this->modifyRowData($data);
				list($link, $name) = $this->getRow($data);
				$row = $this->list->AddRow($data[$this->pk()], $data, $link, $name);
				foreach ($this->fields as $code => $settings)
				{
					if(in_array($code, $listSelect))
					{
						$this->addRowCell($row, $code, $data);
					}
				}
				$row->AddActions($this->getRowActions($data));
			}
		}
		$this->list->AddFooter($this->getFooter($res));
		$this->list->AddGroupActionTable($this->getGroupActions(), $this->groupActionsParams);
		$this->list->AddAdminContextMenu($this->getContextMenu(), $this->exportExcel);
		$this->list->BeginPrologContent();
		echo $this->prologHtml;
		$this->list->EndPrologContent();
		$this->list->BeginEpilogContent();
		echo $this->epilogHtml;
		$this->list->EndEpilogContent();
		$errors = $this->getErrors();
		if(in_array($_GET['mode'], [
				'list',
				'frame'
			]) && is_array($errors))
		{
			foreach ($errors as $error)
			{
				$this->list->addGroupError($error);
			}
		}
		$this->list->CheckListMode();
	}


	protected function mergeSortHeader(&$array)
	{
		// ��� ���������� ����� ���� �� 2 ��������
		if(count($array) < 2) return;
		// ����� ������ �������
		$halfway = count($array) / 2;
		$array1 = array_slice($array, 0, $halfway);
		$array2 = array_slice($array, $halfway);
		// ���������� ��������� ������ ��������
		$this->mergeSortHeader($array1);
		$this->mergeSortHeader($array2);
		// ���� ��������� ������� ������ �������� ������ ��� ����� ������� ��������
		// ������ ��������, �� ������ ��������� �������
		if($this->mergeSortHeaderCompare(end($array1), $array2[0]) < 1)
		{
			$array = array_merge($array1, $array2);

			return;
		}
		// ��������� 2 ��������������� �������� � ���� ��������������� ������
		$array = [];
		$ptr1 = $ptr2 = 0;
		while ($ptr1 < count($array1) && $ptr2 < count($array2))
		{
			// �������� � 1 ������ ���������������� �������
			// ��������� �� 2-� ��������������� ���������
			if($this->mergeSortHeaderCompare($array1[$ptr1], $array2[$ptr2]) < 1)
			{
				$array[] = $array1[$ptr1++];
			}
			else
			{
				$array[] = $array2[$ptr2++];
			}
		}
		// ���� � �������� �������� ���-�� �������� �������� � �������� ������
		while ($ptr1 < count($array1)) $array[] = $array1[$ptr1++];
		while ($ptr2 < count($array2)) $array[] = $array2[$ptr2++];

		return;
	}


	public function mergeSortHeaderCompare(
		$a,
		$b
	)
	{
		$a = $a['admin_list_helper_sort'];
		$b = $b['admin_list_helper_sort'];
		if($a == $b)
		{
			return 0;
		}

		return ($a < $b) ? -1 : 1;
	}


	protected function getMixedData(
		$sectionsVisibleColumns,
		$elementVisibleColumns,
		$sort,
		$raw
	)
	{
		$sectionEditHelperClass = $this->getHelperClass(AdminSectionEditHelper::className());
		$elementEditHelperClass = $this->getHelperClass(AdminEditHelper::className());
		$sectionField = $sectionEditHelperClass::getSectionField();
		$sectionId = $_GET['SECTION_ID'] ? $_GET['SECTION_ID'] : $_GET['ID'];
		$returnData = [];
		/**
		 * @var DataManager $sectionModel
		 */
		$sectionModel = $sectionEditHelperClass::getModel();
		$sectionFilter = [];
		// ��������� �� ������� �� ���� ������� ���� � ��������
		foreach ($this->arFilter as $field => $value)
		{
			$fieldName = $this->escapeFilterFieldName($field);
			if(!empty($this->tableColumnsMap[$fieldName]))
			{
				$field = str_replace($fieldName, $this->tableColumnsMap[$fieldName], $field);
				$fieldName = $this->tableColumnsMap[$fieldName];
			}
			if(isset($this->sectionFields[$fieldName]))
			{
				$sectionFilter[$field] = $value;
			}
		}
		$sectionFilter[$sectionField] = $sectionId;
		$raw['SELECT'] = array_unique($raw['SELECT']);
		// ��� ������������� � �������� popup ���� ��������� ������ �� �������
		// ��� �� �� ���� ����������� ������� ������ ��������� ������ ����
		if(!empty($_REQUEST['self_id']))
		{
			$sectionFilter['!' . $this->sectionPk()] = $_REQUEST['self_id'];
		}
		$sectionSort = [];
		$limitData = $this->getLimits();
		// ��������� � ������ ���������� ��������� ���������� ��������
		$this->totalRowsCount = $sectionModel::getCount($this->getSectionsFilter($sectionFilter));
		foreach ($sort as $field => $direction)
		{
			if(in_array($field, $sectionsVisibleColumns))
			{
				$sectionSort[$field] = $direction;
			}
		}
		// ��������� � ������� �������
		$rsSections = $sectionModel::getList([
			'filter' => $this->getSectionsFilter($sectionFilter),
			'select' => $sectionsVisibleColumns,
			'order' => $sectionSort,
			'limit' => $limitData[1],
			'offset' => $limitData[0],
		]);
		while ($section = $rsSections->fetch())
		{
			$section['IS_SECTION'] = true;
			$returnData[] = $section;
		}
		// ����������� offset � limit ��� ���������
		if(count($returnData) > 0)
		{
			$elementOffset = 0;
		}
		else
		{
			$elementOffset = $limitData[0] - $this->totalRowsCount;
		}
		// ��� ������ �������� �������� �� �����
		if(static::getHelperClass(AdminSectionListHelper::className()) == static::className())
		{
			return $returnData;
		}
		$elementLimit = $limitData[1] - count($returnData);
		$elementModel = static::$model;
		$elementFilter = $this->arFilter;
		$elementFilter[$elementEditHelperClass::getSectionField()] = $sectionId;
		// ��������� � ������ ���������� ��������� ���������� ���������
		$this->totalRowsCount += $elementModel::getCount($this->getElementsFilter($elementFilter));
		// ��������� ������ ��� ��������� ���� ������� �������� ��� �������� �������
		if(!empty($returnData) && $limitData[0] == 0 && $limitData[1] == $this->totalRowsCount)
		{
			return $returnData;
		}
		$elementSort = [];
		foreach ($sort as $field => $direction)
		{
			if(in_array($field, $elementVisibleColumns))
			{
				$elementSort[$field] = $direction;
			}
		}
		$elementParams = [
			'filter' => $this->getElementsFilter($elementFilter),
			'select' => $elementVisibleColumns,
			'order' => $elementSort,
		];
		if($elementLimit > 0 && $elementOffset >= 0)
		{
			$elementParams['limit'] = $elementLimit;
			$elementParams['offset'] = $elementOffset;
			// ��������� � ������� ��������
			$rsSections = $elementModel::getList($elementParams);
			while ($element = $rsSections->fetch())
			{
				$element['IS_SECTION'] = false;
				$returnData[] = $element;
			}
		}
		/**
		 * ������ ��������� � ������ �������� ���� �� ������� ��� ���������.
		 * ��� ������ ��������� ����������� �������� ���� � $this->getLimits()
		 */
		if(!count($returnData) && $this->totalRowsCount > 0)
		{
			$this->navParams['navParams']['PAGEN'] = 1;

			return $this->getMixedData($sectionsVisibleColumns, $elementVisibleColumns, $sort, $raw);
		}

		return $returnData;
	}

	/**
	 * ���������� ������� �� CAdminResult
	 * @return array
	 */
	protected function getLimits()
	{
		if($this->navParams['navParams']['SHOW_ALL'])
		{
			return [];
		}
		else
		{
			if(!intval($this->navParams['navParams']['PAGEN']) OR !isset($this->navParams['navParams']['PAGEN']))
			{
				$this->navParams['navParams']['PAGEN'] = 1;
			}
			$from = $this->navParams['nPageSize'] * ((int)$this->navParams['navParams']['PAGEN'] - 1);
			/**
			 * ������ ��������� � ������ �������� ���� �� ������� ��� ���������.
			 *
			 * $this->totalRowsCount ��� �� �������� ��� ��������� ����������� ��������� � ��������,
			 * � $this->>getMixedData() ���� ��������� �������� �� ���� ����
			 */
			if($this->totalRowsCount && $from >= $this->totalRowsCount)
			{
				$this->navParams['navParams']['PAGEN'] = 1;
				$from = 0;
			}

			return [
				$from,
				$this->navParams['nPageSize']
			];
		}
	}

	/**
	 * ������� �������� ���� �� ���������� �������
	 * @param string $fieldName �������� ���� �� �������
	 * @return string �������� ���� ��� ��� ���������� �������
	 */
	protected function escapeFilterFieldName($fieldName)
	{
		return str_replace([
			'!',
			'<',
			'<=',
			'>',
			'>=',
			'><',
			'=',
			'%'
		], '', $fieldName);
	}

	/**
	 * ��������� CDBResult::NavNext � ��� ��������, ��� ����� ���������� ��������� ������� �� �� count($arResult),
	 * � �� ������ ���������, ����������� �� SQL-�������.
	 * array_slice ����� �� ��������.
	 *
	 * @param \CAdminResult $res
	 */
	protected function customNavStart(&$res)
	{
		$res->NavStart($this->navParams['nPageSize'],
			$this->navParams['navParams']['SHOW_ALL'],
			(int)$this->navParams['navParams']['PAGEN']
		);
		// ��������� ����������� ���� ���������
		$res->bShowAll = $this->showAll;
		$res->NavRecordCount = $this->totalRowsCount;
		if($res->NavRecordCount < 1)
			return;
		if($res->NavShowAll)
			$res->NavPageSize = $res->NavRecordCount;
		$res->NavPageCount = floor($res->NavRecordCount / $res->NavPageSize);
		if($res->NavRecordCount % $res->NavPageSize > 0)
			$res->NavPageCount++;
		$res->NavPageNomer =
			($res->PAGEN < 1 || $res->PAGEN > $res->NavPageCount
				?
				(\CPageOption::GetOptionString("main", "nav_page_in_session", "Y") != "Y"
				|| $_SESSION[$res->SESS_PAGEN] < 1
				|| $_SESSION[$res->SESS_PAGEN] > $res->NavPageCount
					?
					1
					:
					$_SESSION[$res->SESS_PAGEN]
				)
				:
				$res->PAGEN
			);
	}

	/**
	 * ����������� ������ ������, ����� ��� ��� ��������� �� � ������.
	 *
	 * @param $data
	 *
	 * @see AdminListHelper::getList()
	 *
	 * @api
	 */
	protected function modifyRowData(&$data)
	{
	}

	/**
	 * ��������� ������ �������.
	 *
	 * @param array $data ������ ������� ������ ��.
	 * @param bool|string $class ����� ������� ����� ����� getUrl �������� ���� ��������� ������.
	 *
	 * @return array ���������� ������ �� ��������� �������� � � ��������.
	 *
	 * @api
	 */
	protected function getRow(
		$data,
		$class = false
	)
	{
		if(empty($class))
		{
			$class = static::getHelperClass(AdminEditHelper::className());
		}
		if($this->isPopup())
		{
			return [];
		}
		else
		{
			$query = array_merge($this->additionalUrlParams, [
				'lang' => LANGUAGE_ID,
				$this->pk() => $data[$this->pk()]
			]);

			return [$class::getUrl($query)];
		}
	}

	/**
	 * ��� ������ ������(�������) ������� ������ ������ ���������������� ����.
	 * ������ �������������� ����������� HTML ��� ������.
	 *
	 * @param \CAdminListRow $row
	 * @param $code ��������� ��� ����.
	 * @param $data ������ ������� ������.
	 *
	 * @throws Exception
	 *
	 * @see HelperWidget::generateRow()
	 */
	protected function addRowSectionCell(
		$row,
		$code,
		$data
	)
	{
		$sectionEditHelper = $this->getHelperClass(AdminSectionEditHelper::className());
		if(!isset($this->sectionFields[$code]['WIDGET']))
		{
			$error = str_replace('#CODE#', $code, 'Can\'t create widget for the code "#CODE#"');
			throw new Exception($error, Exception::CODE_NO_WIDGET);
		}
		/**
		 * @var \DigitalWand\AdminHelper\Widget\HelperWidget $widget
		 */
		$widget = $this->sectionFields[$code]['WIDGET'];
		$widget->setHelper($this);
		$widget->setCode($code);
		$widget->setData($data);
		$widget->setEntityName($sectionEditHelper::getModel());
		$this->setContext(AdminListHelper::OP_ADD_ROW_CELL);
		$widget->generateRow($row, $data);
	}

	/**
	 * ���������� ������ �� ������� �������� ��� ����� ������ �������� ���� �� ������ �������
	 * ��-���������:
	 * <ul>
	 * <li> ������������� ������� </li>
	 * <li> ������� ������� </li>
	 * <li> ���� ��� ����������� ���� - ��������� ��������� JS-�������. </li>
	 * </ul>
	 *
	 * @param $data ������ ������� ������.
	 * @param $section ������� ������ ��� �������.
	 *
	 * @return array
	 *
	 * @see CAdminListRow::AddActions
	 *
	 * @api
	 */
	protected function getRowActions(
		$data,
		$section = false
	)
	{
		$actions = [];
		if($this->isPopup())
		{
			$jsData = \CUtil::PhpToJSObject($data);
			$actions['select'] = [
				'ICON' => 'select',
				'DEFAULT' => true,
				'TEXT' => Loc::getMessage('DIGITALWAND_ADMIN_HELPER_LIST_SELECT'),
				"ACTION" => 'javascript:' . $this->popupClickFunctionName . '(' . $jsData . ')'
			];
		}
		else
		{
			$viewQueryString = 'module=' . static::getModule() . '&view=' . static::getViewName() . '&entity=' . static::getEntityCode();
			$query = array_merge($this->additionalUrlParams,
				[$this->pk() => $data[$this->pk()]]);
			if($this->hasWriteRights())
			{
				$sectionHelperClass = static::getHelperClass(AdminSectionEditHelper::className());
				$editHelperClass = static::getHelperClass(AdminEditHelper::className());
				$actions['edit'] = [
					'ICON' => 'edit',
					'DEFAULT' => true,
					'TEXT' => Loc::getMessage('DIGITALWAND_ADMIN_HELPER_LIST_EDIT'),
					'ACTION' => $this->list->ActionRedirect($section ? $sectionHelperClass::getUrl($query) : $editHelperClass::getUrl($query))
				];
			}
			if($this->hasDeleteRights())
			{
				$actions['delete'] = [
					'ICON' => 'delete',
					'TEXT' => Loc::getMessage("DIGITALWAND_ADMIN_HELPER_LIST_DELETE"),
					'ACTION' => "if(confirm('" . Loc::getMessage('DIGITALWAND_ADMIN_HELPER_LIST_DELETE_CONFIRM') . "')) " . $this->list->ActionDoGroup($data[$this->pk()],
							$section ? "delete-section" : "delete", $viewQueryString)
				];
			}
		}

		return $actions;
	}

	/**
	 * ��� ������ ������ ������� ������ ������ ���������������� ����. ������ �������������� ����������� HTML-���
	 * ��� ������.
	 *
	 * @param \CAdminListRow $row ������ ������ ������ �������.
	 * @param string $code ��������� ��� ����.
	 * @param array $data ������ ������� ������.
	 * @param bool $virtualCode
	 *
	 * @throws Exception
	 *
	 * @see HelperWidget::generateRow()
	 */
	protected function addRowCell(
		$row,
		$code,
		$data,
		$virtualCode = false
	)
	{
		$widget = $this->createWidgetForField($code, $data);
		$this->setContext(AdminListHelper::OP_ADD_ROW_CELL);
		// ������������� ����������� ��� ������, ������������ ��� ������� ��������
		if($virtualCode)
		{
			$widget->setCode($virtualCode);
		}
		$widget->generateRow($row, $data);
		if($virtualCode)
		{
			$widget->setCode($code);
		}
	}

	/**
	 * ���������� ������� ������. ������� ����� �������������� � ������, ���� ���������� ���� ������, � � ������
	 * ������� � ����� ������.
	 *
	 * @param DataManager $className
	 * @param array $filter
	 * @param array $select
	 * @param array $sort
	 * @param array $raw
	 *
	 * @return Result
	 *
	 * @api
	 */
	protected function getData(
		$className,
		$filter,
		$select,
		$sort,
		$raw
	)
	{
		$limits = $this->getLimits();
		$parameters = [
			'filter' => $this->getElementsFilter($filter),
			'select' => $select,
			'order' => $sort,
			'offset' => $limits[0],
			'limit' => $limits[1],
		];
		/** @var Result $res */
		$res = $className::getList($parameters);
		return $res;
	}

	/**
	 * �������������� ������ � ����������� ������ ������� Bitrix
	 * @param \CAdminResult $res - ��������� ������� ������
	 * @see \CAdminList::AddFooter()
	 * @return array[]
	 */
	protected function getFooter($res)
	{
		return [
			$this->getButton('MAIN_ADMIN_LIST_SELECTED', ["value" => $res->SelectedRowsCount()]),
			$this->getButton('MAIN_ADMIN_LIST_CHECKED', ["value" => $res->SelectedRowsCount()], [
				"counter" => true,
				"value" => "0",
			]),
		];
	}

	/**
	 * ������� ����� ���������� ������
	 */
	public function createFilterForm()
	{
		//����� ������������ �������� popup � �����, ���� ��� �������� �������
		if($this->isPopup())
		{
			$this->additionalUrlParams['popup'] = 'Y';
		}
		$this->setContext(AdminListHelper::OP_CREATE_FILTER_FORM);
		print ' <form name="find_form" method="GET" action="' . static::getUrl($this->additionalUrlParams) . '?">';
		$sectionHelper = $this->getHelperClass(AdminSectionEditHelper::className());
		if($sectionHelper)
		{
			$sectionsInterfaceSettings = static::getInterfaceSettings($sectionHelper::getViewName());
			foreach ($this->arFilterOpts as $code => $name)
			{
				if(!empty($this->tableColumnsMap[$code]))
				{
					$newName = $sectionsInterfaceSettings['FIELDS'][$this->tableColumnsMap[$code]]['WIDGET']
						->getSettings('TITLE');
					$this->arFilterOpts[$code] = $newName;
				}
			}
		}
		$oFilter = new \CAdminFilter($this->getListTableID() . '_filter', $this->arFilterOpts);
		$oFilter->Begin();
		foreach ($this->arFilterOpts as $code => $name)
		{
			$widget = $this->createWidgetForField($code);
			if($widget->getSettings('TITLE') != $this->arFilterOpts[$code])
			{
				$widget->setSetting('TITLE', $this->arFilterOpts[$code]);
			}
			$widget->showFilterHtml();
		}
		$oFilter->Buttons([
			"table_id" => $this->getListTableID(),
			"url" => static::getUrl($this->additionalUrlParams),
			"form" => "find_form",
		]);
		$oFilter->End();
		print '</form>';
	}

	/**
	 * ���������� ID �������, ������� �� ������ ������������� � ID � ������ �������� �������, � ����� ���������
	 * ��������� � JS
	 *
	 * @return string
	 */
	protected function getListTableID()
	{
		return str_replace('.', '', static::$tablePrefix . $this->table());
	}

	/**
	 * ������� �������������� ������.
	 * ��������� ������������ GET-������ � ������
	 */
	public function show()
	{
		if(!$this->hasReadRights())
		{
			$this->addErrors(Loc::getMessage('DIGITALWAND_ADMIN_HELPER_ACCESS_FORBIDDEN'));
			$this->showMessages();

			return false;
		}
		$this->showMessages();
		$this->list->DisplayList();
		if($this->isPopup())
		{
			print $this->popupClickFunctionCode;
		}
		$this->saveGetQuery();
	}

	/**
	 * ��������� ��������� ������� ��� ��������� ������������� ����� �������� � ������ ������� (� �������, �����
	 * �������� � ��������� ������� � ������ - ����� ��������� � �������� � ��� ������, � �������� ����� ����)
	 */
	private function saveGetQuery()
	{
		$_SESSION['LAST_GET_QUERY'][get_called_class()] = $_GET;
	}

	/**
	 * ��������������� ��������� GET-������, ���� � ������� ����� �������� restore_query=Y
	 */
	private function restoreLastGetQuery()
	{
		if(!isset($_SESSION['LAST_GET_QUERY'][get_called_class()])
			OR !isset($_REQUEST['restore_query'])
			OR $_REQUEST['restore_query'] != 'Y'
		)
		{
			return;
		}
		$_GET = array_merge($_GET, $_SESSION['LAST_GET_QUERY'][get_called_class()]);
		$_REQUEST = array_merge($_REQUEST, $_SESSION['LAST_GET_QUERY'][get_called_class()]);
	}

	/**
	 * @inheritdoc
	 */
	public static function getUrl(array $params = [])
	{
		return static::getViewURL(static::getViewName(), static::$listPageUrl, $params);
	}

	/**
	 * ������������ ������� ��������
	 * @param $filter
	 * @return mixed
	 */
	protected function getSectionsFilter(array $filter)
	{
		return $filter;
	}

	/**
	 * ������������ ������� ���������
	 * @param $filter
	 * @return mixed
	 */
	protected function getElementsFilter($filter)
	{
		return $filter;
	}

	/**
	 * ������ ��������������� ��� ��������� ��������
	 *
	 * @return array
	 */
	protected function getIds()
	{
		$className = static::getModel();
		if(isset($_REQUEST['model']))
		{
			$className = $_REQUEST['model'];
		}
		$sectionEditHelperClass = $this->getHelperClass(AdminSectionEditHelper::className());
		if($sectionEditHelperClass && !isset($_REQUEST['model-section']))
		{
			$sectionClassName = $sectionEditHelperClass::getModel();
		}
		else
		{
			$sectionClassName = $_REQUEST['model-section'];
		}
		$pkValue = $this->getPk();
		if(isset($pkValue[$this->pk()]) && is_array($pkValue[$this->pk()]))
		{
			foreach ($pkValue[$this->pk()] as $id)
			{
				$class = strpos($id, 's') === 0 ? $sectionClassName : $className;
				$ids[] = $this->getCommonPrimaryFilterById($class, null, $id);
			}
		}
		else
		{
			$ids = [$this->getPk()];
		}

		return $ids;
	}

	/**
	 * �������� ���������� ����� ���������� ���������� �����
	 *
	 * @param $className
	 * @param null $sectionClassName
	 * @param $id
	 * @return array
	 */
	protected function getCommonPrimaryFilterById(
		$className,
		$sectionClassName = null,
		$id
	)
	{
		if($this->getHelperClass($sectionClassName) && strpos($id, 's') === 0)
		{
			$primary = $sectionClassName::getEntity()->getPrimary();
		}
		else
		{
			$primary = $className::getEntity()->getPrimary();
		}
		if(count($primary) === 1)
		{
			return [$this->pk() => $id];
		}
		$key = $this->getPk();
		$key[$this->pk()] = $id;

		return $key;
	}
}