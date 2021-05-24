<?php
namespace DigitalWand\AdminHelper\Widget;

use DigitalWand\AdminHelper\Helper\AdminBaseHelper;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\EntityError;
use Bitrix\Main\Entity\Result;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Localization\Loc;
use DigitalWand\AdminHelper\Helper\AdminEditHelper;
use DigitalWand\AdminHelper\Helper\AdminListHelper;

Loc::loadMessages(__FILE__);

/**
 * ������, ������������ ����������� ����, ����������� � HL-��������� � �������.
 *
 * ���������:
 * <ul>
 * <li><b>MODEL</b> - �������� ������, �� ������� ����� ������������� ������� ������. ��-��������� - ������ ��������
 * �������</li>
 * </ul>
 * Class HLIBlockFieldWidget
 * @package DigitalWand\AdminHelper\Widget
 */
class HLIBlockFieldWidget extends HelperWidget
{
    static protected $userFieldsCache = array();
    static protected $defaults = array(
        'USE_BX_API' => true
    );

    /**
     * ���������� HTML ��� �������������� ����
     *
     * @see \CAdminForm::ShowUserFieldsWithReadyData
     * @return mixed
     */
    protected function getEditHtml()
    {
        $info = $this->getUserFieldInfo();
        if ($info) {

            /** @var \CAllUserTypeManager $USER_FIELD_MANAGER */
            global $USER_FIELD_MANAGER;
            $GLOBALS[$this->getCode()] = isset($GLOBALS[$this->getCode()]) ? $GLOBALS[$this->getCode()] : $this->data[$this->getCode()];
            $bVarsFromForm = false;

            $info["VALUE_ID"] = intval($this->data['ID']);
            $info['EDIT_FORM_LABEL'] = $this->getSettings('TITLE');

            if (isset($_REQUEST['def_' . $this->getCode()])) {
                $info['SETTINGS']['DEFAULT_VALUE'] = $_REQUEST['def_' . $this->getCode()];
            }
            print $USER_FIELD_MANAGER->GetEditFormHTML($bVarsFromForm, $GLOBALS[$this->getCode()], $info);

        }
    }

    /**
     * ������������ ������ ��� ���������� ���, ��� ��� ������ �� ���������������� �������� ��������.
     * ��������� ��������� � ������� CheckFields() ���������������� �����.
     *
     * @see Bitrix\Highloadblock\DataManager
     * @see /bitrix/modules/highloadblock/admin/highloadblock_row_edit.php
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function processEditAction()
    {
        /** @var \CAllUserTypeManager $USER_FIELD_MANAGER */
        global $USER_FIELD_MANAGER;
        $iblockId = 'HLBLOCK_' . $this->getHLId();

        //����� �� �������� ������ ������
        if (!isset($this->data[$this->getCode()]) AND isset($_REQUEST[$this->getCode() . '_old_id'])) {
            $this->data[$this->getCode()] = $_REQUEST[$this->getCode() . '_old_id'];
        }

        //������� �������� ��� ���� �����, ��� ��� ��������� � ������ ���� ���, ��������� ��������.
        static $data = array();
        if (empty($data)) {
            $data = $this->data;
            $USER_FIELD_MANAGER->EditFormAddFields($iblockId, $data);
        }

        $value = $data[$this->getCode()];

        $entity_data_class = AdminBaseHelper::getHLEntity($this->getSettings('MODEL'));

        $oldData = $this->getOldFieldData($entity_data_class);
        $fieldsInfo = $USER_FIELD_MANAGER->getUserFieldsWithReadyData($iblockId, $oldData, LANGUAGE_ID, false, 'ID');
        $fieldInfo = $fieldsInfo[$this->getCode()];

        $className = $fieldInfo['USER_TYPE']['CLASS_NAME'];
        if (is_callable(array($className, 'CheckFields'))) {
            $errors = $className::CheckFields($fieldInfo, $value);
            if (!empty($errors)) {
                $this->addError($errors);
                return;
            }
        }

        // use save modifiers
        $field = $entity_data_class::getEntity()->getField($this->getCode());
        $value = $field->modifyValueBeforeSave($value, $data);

        //��������������� ����
        if ($unserialized = unserialize($value)) {
            //������ �������� ��������� ���������������
            $this->data[$this->getCode()] = $unserialized;

        } else if ($className == 'CUserTypeFile' AND !is_array($value)) {
            //���� �� ������� intval, �� ��� ���������� � ����� ����������� ������ ����� ����������� ������
            $this->data[$this->getCode()] = intval($value);

        } else {
            //��� ��������� ���� - ��������� ��� ����.
            $this->data[$this->getCode()] = $value;
        }
    }

    /**
     * �������� ���� �������� ����, ������ ��������� � ���� ��� ����� ����������������� ��������.
     * ����� ������������� �������� �� �����.
     * �������� � ���, ��� ���������������� �������� ����� ������� �� ��������� ��������.
     * @param HL\DataManager $entity_data_class
     *
     * @return mixed
     */
    protected function getOldFieldData($entity_data_class)
    {
        if (is_null($this->data) OR !isset($this->data[$this->helper->pk()])) return false;
        return $entity_data_class::getByPrimary($this->data[$this->helper->pk()])->fetch();
    }

    /**
     * ���� ������������� ������, � ���� ������ ���� �� �������, �� ������� ������ �������� �������, ����������� ���
     * ������������ ������������� � ������������ ������������.
     *
     * @param string $name
     * @return array|\Bitrix\Main\Entity\DataManager|mixed|string
     */
    public function getSettings($name = '')
    {
        $value = parent::getSettings($name);
        if (!$value) {
            if ($name == 'MODEL') {
                $value = $this->helper->getModel();
                $this->setSetting($name, $value);

            } else if ($name == 'TITLE') {

                $context = $this->helper->getContext();
                $info = $this->getUserFieldInfo();

                if (($context == AdminListHelper::OP_ADMIN_VARIABLES_FILTER OR $context == AdminListHelper::OP_CREATE_FILTER_FORM)
                    AND (isset($info['LIST_FILTER_LABEL']) AND !empty($info['LIST_FILTER_LABEL']))
                ) {
                    $value = $info['LIST_FILTER_LABEL'];

                } else if ($context == AdminListHelper::OP_ADMIN_VARIABLES_HEADER
                    AND isset($info['LIST_COLUMN_LABEL'])
                    AND !empty($info['LIST_COLUMN_LABEL'])
                ) {
                    $value = $info['LIST_COLUMN_LABEL'];

                } else if ($context == AdminEditHelper::OP_SHOW_TAB_ELEMENTS
                    AND isset($info['EDIT_FORM_LABEL'])
                    AND !empty($info['EDIT_FORM_LABEL'])
                ) {
                    $value = $info['EDIT_FORM_LABEL'];

                } else {
                    $value = $info['FIELD_NAME'];
                }
            }
        }

        return $value;
    }


    /**
     * ���������� HTML ��� ���� � ������
     * �������� �� API ��������, �������������� � ������������...
     *
     * @see AdminListHelper::addRowCell();
     *
     * @param \CAdminListRow $row
     * @param array $data - ������ ������� ������
     *
     * @return mixed
     */
    public function generateRow(&$row, $data)
    {
        $info = $this->getUserFieldInfo();
        if ($info) {

            /** @var \CAllUserTypeManager $USER_FIELD_MANAGER */
            global $USER_FIELD_MANAGER;
            $FIELD_NAME = $this->getCode();
            $GLOBALS[$FIELD_NAME] = isset($GLOBALS[$FIELD_NAME]) ? $GLOBALS[$FIELD_NAME] : $this->data[$this->getCode()];

            $info["VALUE_ID"] = intval($this->data['ID']);

            if (isset($_REQUEST['def_' . $FIELD_NAME])) {
                $info['SETTINGS']['DEFAULT_VALUE'] = $_REQUEST['def_' . $FIELD_NAME];
            }
            $USER_FIELD_MANAGER->AddUserField($info, $data[$this->getCode()], $row);

        }
    }

    /**
     * ���������� HTML ��� ���� ����������
     *
     * @see AdminListHelper::createFilterForm();
     * @return mixed
     */
    public function showFilterHtml()
    {
        $info = $this->getUserFieldInfo();
        if ($info) {
            /** @var \CAllUserTypeManager $USER_FIELD_MANAGER */
            global $USER_FIELD_MANAGER;
            $FIELD_NAME = $this->getCode();
            $GLOBALS[$FIELD_NAME] = isset($GLOBALS[$FIELD_NAME]) ? $GLOBALS[$FIELD_NAME] : $this->data[$this->getCode()];

            $info["VALUE_ID"] = intval($this->data['ID']);
            $info['LIST_FILTER_LABEL'] = $this->getSettings('TITLE');

            print $USER_FIELD_MANAGER->GetFilterHTML($info, $this->getFilterInputName(), $this->getCurrentFilterValue());
        }
    }

    public function getUserFieldInfo()
    {
        $id = $this->getHLId();
        $fields = static::getUserFields($id, $this->data);
        if (isset($fields[$this->getCode()])) {
            return $fields[$this->getCode()];
        }
        return false;
    }

    /**
     * �������� ID HL-��������� �� ����� ��� ������
     * @return mixed
     */
    protected function getHLId()
    {
        static $id = false;

        if ($id === false) {
            $model = $this->getSettings('MODEL');
            $info = AdminBaseHelper::getHLEntityInfo($model);
            if ($info AND isset($info['ID'])) {
                $id = $info['ID'];
            }
        }

        return $id;
    }

    static public function getUserFields($iblockId, $data)
    {
        /** @var \CAllUserTypeManager $USER_FIELD_MANAGER */
        global $USER_FIELD_MANAGER;
        $iblockId = 'HLBLOCK_' . $iblockId;
        if (!isset(static::$userFieldsCache[$iblockId][$data['ID']])) {
            $fields = $USER_FIELD_MANAGER->getUserFieldsWithReadyData($iblockId, $data, LANGUAGE_ID, false, 'ID');
            self::$userFieldsCache[$iblockId][$data['ID']] = $fields;
        }

        return self::$userFieldsCache[$iblockId][$data['ID']];
    }

    /**
     * �������� ������������ �������, �.�. ����� ������ �������� �� ��������, ������ �������� ���� ��� ������-�� ��
     * �����������
     * 
*@param string $messageId
     */
    protected function addError($messageId)
    {
        if (is_array($messageId)) {
            foreach ($messageId as $key => $error) {
                if (isset($error['text'])) {
                    //FIXME: ������-�� ������� �� ������������ ���������� �������� ����, ������� ���������� ��� ����.
                    if (isset($error['id']) AND strpos($error['text'], '""')) {
                        $messageId[$key] = str_replace('""', '"' . $this->getSettings('TITLE') . '"', $error['text']);

                    } else {
                        $messageId[$key] = $error['text'];
                    }
                }
            }
        }

        $messageId = implode("\n", $messageId);
        $this->validationErrors[$this->getCode()] = $messageId;
    }

}