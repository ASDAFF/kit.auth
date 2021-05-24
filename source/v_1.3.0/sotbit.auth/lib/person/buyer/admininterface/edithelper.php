<?php

namespace Sotbit\Auth\Person\Buyer\AdminInterface;

use Bitrix\Main\Localization\Loc;
use DigitalWand\AdminHelper\Helper\AdminEditHelper;
use DigitalWand\AdminHelper\Helper\AdminListHelper;
use Sotbit\Auth\Internals\BuyerConfirmTable;

Loc::loadMessages(__FILE__);

class EditHelper extends AdminEditHelper
{
	protected static $model = '\Sotbit\Auth\Internals\BuyerConfirmTable';

	protected function getMenu($showDeleteButton = true)
	{
		$listHelper = static::getHelperClass(AdminListHelper::className());

		$menu = [
			$this->getButton('RETURN_TO_LIST', [
				'LINK' => $listHelper::getUrl(array_merge($this->additionalUrlParams,
					['restore_query' => 'Y']
				)),
				'ICON' => 'btn_list',
			])
		];

		$arSubMenu = [];

		$arSubMenu[] = $this->getButton('CONFIRM_ELEMENT', [
			'LINK' => static::getUrl(array_merge($this->additionalUrlParams,
				[
					'ID' => $this->data[$this->pk()],
					'action' => 'confirm',
					'lang' => LANGUAGE_ID,
				]
			)),
		]);
		$arSubMenu[] = $this->getButton('UNCONFIRM_ELEMENT', [
			'LINK' => static::getUrl(array_merge($this->additionalUrlParams,
				[
					'ID' => $this->data[$this->pk()],
					'action' => 'unconfirm',
					'lang' => LANGUAGE_ID,
				]
			)),
		]);

		if($showDeleteButton && isset($this->data[$this->pk()]) && $this->hasDeleteRights())
		{
			$arSubMenu[] = $this->getButton('DELETE_ELEMENT', [
				'ONCLICK' => "if(confirm('" . Loc::getMessage('DIGITALWAND_ADMIN_HELPER_EDIT_DELETE_CONFIRM') . "')) location.href='" .
					static::getUrl(array_merge($this->additionalUrlParams,
						[
							'ID' => $this->data[$this->pk()],
							'action' => 'delete',
							'lang' => LANGUAGE_ID,
							'restore_query' => 'Y',
						])) . "'",
				'ICON' => 'delete',
			]);
		}

		if(count($arSubMenu))
		{
			$menu[] = $this->getButton('ACTIONS', [
				'MENU' => $arSubMenu,
				'ICON' => 'btn_new'
			]);
		}

		return $menu;
	}

	public function show()
	{
		if(!$this->hasReadRights())
		{
			$this->addErrors(Loc::getMessage('DIGITALWAND_ADMIN_HELPER_ACCESS_FORBIDDEN'));
			$this->showMessages();

			return false;
		}

		$context = new \CAdminContextMenu($this->getMenu());
		$context->Show();

		$this->tabControl->BeginPrologContent();
		$this->showMessages();
		$this->showProlog();
		$this->tabControl->EndPrologContent();

		$this->tabControl->BeginEpilogContent();
		$this->showEpilog();
		$this->tabControl->EndEpilogContent();

		$query = $this->additionalUrlParams;

		if(isset($_REQUEST[$this->pk()]))
		{
			$query[$this->pk()] = $_REQUEST[$this->pk()];
		}
		elseif(isset($_REQUEST['SECTION_ID']) && $_REQUEST['SECTION_ID'])
		{
			$this->data[static::getSectionField()] = $_REQUEST['SECTION_ID'];
		}

		$this->tabControl->Begin([
			'FORM_ACTION' => static::getUrl($query)
		]);

		foreach ($this->tabs as $tabSettings)
		{
			if($tabSettings['VISIBLE'])
			{
				$this->showTabElements($tabSettings);
			}
		}

		$this->showEditPageButtons();
		$this->tabControl->ShowWarnings('editform', []);
		$this->tabControl->Show();
	}

	private function showTabElements($tabSettings)
	{
		$this->setContext(AdminEditHelper::OP_SHOW_TAB_ELEMENTS);
		$this->tabControl->BeginNextFormTab();

		foreach ($this->getFields() as $code => $fieldSettings)
		{
			$widget = $this->createWidgetForField($code, $this->data);
			$fieldTab = $widget->getSettings('TAB');
			$fieldOnCurrentTab = ($fieldTab == $tabSettings['DIV'] OR $tabSettings['DIV'] == 'DEFAULT_TAB');

			if(!$fieldOnCurrentTab)
			{
				continue;
			}

			$fieldSettings = $widget->getSettings();
			if(isset($fieldSettings['VISIBLE']) && $fieldSettings['VISIBLE'] === false)
			{
				continue;
			}

			$this->tabControl->BeginCustomField($code, $widget->getSettings('TITLE'));
			$pkField = ($code == $this->pk());
			$widget->showBasicEditField($pkField);
			$this->tabControl->EndCustomField($code);
		}
		if($this->data['FIELDS']) ;
		{
			$this->tabControl->BeginCustomField('NAME', Loc::getMessage('SOTBIT_AUTH_EDIT_NAME'));
			echo '<tr>';
			print '<td width="40%" style="vertical-align: top;">' . Loc::getMessage('SOTBIT_AUTH_EDIT_NAME') . ':</td>';
			echo '<td width="60%">' . $this->data['FIELDS']['NAME'] . '</td>';
			echo '</tr>';
			$this->tabControl->EndCustomField('NAME');
			$this->tabControl->BeginCustomField('LAST_NAME', Loc::getMessage('SOTBIT_AUTH_EDIT_LAST_NAME'));
			echo '<tr>';
			print '<td width="40%" style="vertical-align: top;">' . Loc::getMessage('SOTBIT_AUTH_EDIT_LAST_NAME') . ':</td>';
			echo '<td width="60%">' . $this->data['FIELDS']['LAST_NAME'] . '</td>';
			echo '</tr>';
			$this->tabControl->EndCustomField('LAST_NAME');
		}
	}

	protected function editAction()
	{
		$this->setContext(AdminEditHelper::OP_EDIT_ACTION_BEFORE);

		if(!$this->hasWriteRights())
		{
			$this->addErrors(Loc::getMessage('DIGITALWAND_ADMIN_HELPER_EDIT_WRITE_FORBIDDEN'));

			return false;
		}

		$allWidgets = [];

		foreach ($this->getFields() as $code => $settings)
		{
			if($settings['READONLY'] && $code !== $this->pk())
			{
				unset($this->data[$code]);
			}
		}

		foreach ($this->getFields() as $code => $settings)
		{
			$widget = $this->createWidgetForField($code, $this->data);
			$widget->processEditAction();
			$this->validationErrors = array_merge($this->validationErrors, $widget->getValidationErrors());
			$allWidgets[] = $widget;

			if($widget->getSettings('READONLY') || empty($this->data[$this->pk()])
				&& $widget->getSettings('HIDE_WHEN_CREATE'))
			{
				unset($this->data[$code]);
			}
		}
		$this->addErrors($this->validationErrors);
		$success = empty($this->validationErrors);

		if($success)
		{
			$this->setContext(AdminEditHelper::OP_EDIT_ACTION_AFTER);
			$existing = false;
			$id = $this->getPk();

			if($id)
			{
				$className = static::getModel();
				$existing = $className::getById($id)->fetch();
			}

			if($existing)
			{
				$result = $this->saveElement($id);
			}
			else
			{
				$result = $this->saveElement();
			}

			if($result)
			{
				if(!$result->isSuccess())
				{
					$this->addErrors($result->getErrorMessages());

					return false;
				}
			}
			else
			{
				return false;
			}

			$this->data[$this->pk()] = $result->getId();

			foreach ($allWidgets as $widget)
			{
				$widget->setData($this->data);
				$widget->processAfterSaveAction();
			}

			return true;
		}
		return false;
	}
	protected function customActions($action, $id = null)
	{
		if ($action == 'delete' AND !is_null($id))
		{
			$result = $this->deleteElement($id);

			if(!$result->isSuccess()){
				$this->addErrors($result->getErrorMessages());
			}

			$listHelper = static::getHelperClass(AdminListHelper::className());
			$redirectUrl = $listHelper::getUrl(array_merge(
				$this->additionalUrlParams,
				array('restore_query' => 'Y')
			));

			LocalRedirect($redirectUrl);
		}
		if($action == 'confirm')
		{
			$result = BuyerConfirmTable::update($id,['STATUS' => true]);
			if(!$result->isSuccess()){
				$this->addErrors($result->getErrorMessages());
			}
		}
		if($action == 'unconfirm')
		{
			$result = BuyerConfirmTable::delete($id);
			if(!$result->isSuccess()){
				$this->addErrors($result->getErrorMessages());
			}
		}
	}
}