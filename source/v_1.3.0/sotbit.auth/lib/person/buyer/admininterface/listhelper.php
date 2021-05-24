<?php

namespace Sotbit\Auth\Person\Buyer\AdminInterface;

use Bitrix\Main\Localization\Loc;
use DigitalWand\AdminHelper\Helper\AdminListHelper;
use DigitalWand\AdminHelper\Helper\AdminSectionEditHelper;
use Sotbit\Auth\Internals\BuyerConfirmTable;

\CModule::IncludeModule('sale');

class ListHelper extends AdminListHelper
{
	protected static $model = '\Sotbit\Auth\Internals\BuyerConfirmTable';

	protected function getGroupActions()
	{
	}

	protected function getElementsFilter($filter)
	{
        // show only unprocessed data for moderation
        $filter['==STATUS'] = NULL;

		return $filter;
	}

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
			if((isset($settings['VIRTUAL']) and $settings['VIRTUAL'] == true))
			{
				unset($visibleColumns[$key]);
				unset($this->arFilter[$name]);
				unset($sort[$name]);
			}
			if(isset($settings['LIST']) && $settings['LIST'] === false)
			{
				unset($visibleColumns[$key]);
			}
			if(isset($settings['FORCE_SELECT']) and $settings['FORCE_SELECT'] == true)
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
			$res = new \CDbResult();
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
					list ($link, $name) = $this->getRow($data, $this->getHelperClass(AdminSectionEditHelper::className()));
					$row = $this->list->AddRow('s' . $data[$this->pk()], $data, $link, $name);
					foreach ($this->sectionFields as $code => $settings)
					{
						if(in_array($code, $sectionsVisibleColumns))
						{
							$this->addRowSectionCell($row, $code, $data);
						}
					}
//                  Delete action menu (edit, delete)
//					$row->AddActions($this->getRowActions($data, true));
				}
				else
				{
					$this->modifyRowData($data);
					list ($link, $name) = $this->getRow($data);

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
							$this->addRowCell($row, $code, $data, isset($this->tableColumnsMap[$code]) ? $this->tableColumnsMap[$code] : false);
						}
					}
//                  Delete action menu (edit, delete)
//					$row->AddActions($this->getRowActions($data));
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
				list ($link, $name) = $this->getRow($data);
				$row = $this->list->AddRow($data[$this->pk()], $data, '', $name);
				foreach ($this->fields as $code => $settings)
				{
					if(in_array($code, $listSelect))
					{
						$this->addRowCell($row, $code, $data);
					}
				}
//                  Delete action menu (edit, delete)
//				$row->AddActions($this->getRowActions($data));
			}
		}
		$this->list->AddFooter($this->getFooter($res));

		$Moderation = [
			"approve" => Loc::getMessage("SOTBIT_AUTH_APPROVE"),
			"unapprove" => Loc::getMessage("SOTBIT_AUTH_UNAPPROVE")
		];


		$this->list->AddGroupActionTable($Moderation);
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

	protected function editAction(
		$id,
		$fields
	)
	{
		$this->setContext(AdminListHelper::OP_EDIT_ACTION);
		if(strpos($id, 's') === 0)
		{
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
			$_REQUEST = [
				$this->pk() => $id
			];
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
			{
				$widget = $sectionsInterfaceSettings['FIELDS'][$key]['WIDGET'];
			}
			else
			{
				$widget = $this->createWidgetForField($key, $fields);
			}
			$widget->processEditAction();
			$this->validationErrors = array_merge($this->validationErrors, $widget->getValidationErrors());
			$allWidgets[] = $widget;
		}
		$this->addErrors($this->validationErrors);

		if($fields['STATUS'])
		{
			$row = UserConfirmTable::getList([
				'filter' => ['ID' => $id],
				'limit' => 1,
				'select' => ['FIELDS']
			])->fetch();

			$oUser = new \CUser();
			$idUser = $oUser->Add($row['FIELDS']);

			if($idUser > 0)
			{
				UserConfirmTable::update($id, [
					'ID_USER' => $idUser,
					'DATE_UPDATE' => new \Bitrix\Main\Type\DateTime(),
					'STATUS' => true
				]);
			}
			else
			{
				$this->addErrors($oUser->LAST_ERROR);
			}
		}

		if(empty($this->validationErrors) and !empty($errors))
		{
			$fieldList = implode("\n", $errors);
			$this->list->AddGroupError(Loc::getMessage("MAIN_ADMIN_SAVE_ERROR") . " " . $fieldList, $idForLog);
		}
		if(!empty($errors))
		{
			foreach ($allWidgets as $widget)
			{
				$widget->setData($fields);
				$widget->processAfterSaveAction();
			}
		}
	}

	protected function getContextMenu()
	{
		$contextMenu = [];

		$sectionEditHelper = static::getHelperClass(AdminSectionEditHelper::className());
		if($sectionEditHelper)
		{
			$sectionId = $_GET['SECTION_ID'] ?: $_GET['ID'] ?: null;
			$this->additionalUrlParams['SECTION_ID'] = $sectionId = $sectionId > 0 ? (int)$sectionId : null;
		}

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

		return $contextMenu;
	}

	protected function groupActions(
		$IDs,
		$action
	)
	{
		if($action == 'approve')
		{
			$ids = [];
			$rs = BuyerConfirmTable::getList([
				'filter' => [
					'ID' => $IDs,
				],
				'select' => [
					'ID',
					'FIELDS'
				]
			]);
			while ($row = $rs->fetch())
			{
				if($row['STATUS'] == 1)
				{
					continue;
				}
				$ids[$row['ID']] = $row['FIELDS'];
			}

			foreach ($IDs as $id)
			{
				$rs = BuyerConfirmTable::update($id, [
					'STATUS' => true
				]);
				if(!$rs->isSuccess())
				{
					$this->addErrors($rs->getErrorMessages());
				}
			}
		}
		if($action == 'unapprove')
		{
			foreach ($IDs as $id)
			{
				BuyerConfirmTable::delete($id);
			}
		}
		if($action == 'delete')
		{
			foreach ($IDs as $id)
			{
				BuyerConfirmTable::delete($id);
			}
		}
	}
}