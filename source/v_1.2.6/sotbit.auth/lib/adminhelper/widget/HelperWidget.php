<?php

namespace DigitalWand\AdminHelper\Widget;

use Bitrix\Main\Localization\Loc;
use DigitalWand\AdminHelper\Helper\AdminBaseHelper;
use DigitalWand\AdminHelper\Helper\AdminEditHelper;
use DigitalWand\AdminHelper\Helper\AdminListHelper;
use Bitrix\Main\Entity\DataManager;

/**
 * ������ - �����, ���������� �� ������� ��� �������� ������� ���� ��������. ���� ������ �������� ��:
 * <ul>
 * <li>����������� ���� �� �������� ��������������</li>
 * <li>����������� ������ ���� � ������� ������ - ��� ��������� � ��������������</li>
 * <li>����������� ������� �� ������� ����</li>
 * <li>��������� �������� ����</li>
 * </ul>
 *
 * ����� ��������� �������������� ��������������� ��������� ������:
 * <ul>
 * <li>����� ����������� �������� ���� � ��</li>
 * <li>����� ��������� �������� ���� �� ��</li>
 * <li>����������� ������� ����� �����������</li>
 * <li>����������� ������� ����� �������� ������</li>
 * </ul>
 *
 * ��� ��������� ����������� ���������������� ���������� �������������� �������� ������, ���������� �� �����������
 * ������� � ������ � �� ���������.
 *
 * ������ ������ ����� ��� ������������� ��������, ��������� �� ������� ����������� ��� ����������. ���������
 * ������������ �� ���������� ����� ������ � ������������ � ����������� �������. ��������� ����� ���� �������� �
 * ������ ��� ��� �������� ����� ���������� � ����� Interface.php, ��� � ��������������� �� ����� ����������,
 * ������ Helper-�������.
 *
 * ��� �������� �������� ���� "��"/"���", ������ ������������ ��������� ����������� "Y"/"N":
 * ��� ����� ���� ������ true � false.
 *
 * ��������� �������� ������:
 * <ul>
 * <li><b>HIDE_WHEN_CREATE</b> - �������� ���� � ����� ��������������, ���� �������� ����� �������, � �� ������
 *     ������������ �� ��������������.</li>
 * <li><b>TITLE</b> - �������� ����. ���� �� ������ �� ��������� �������� title �� DataManager::getMap()
 *       ����� getField($code)->getTitle(). ����� ������������ � �������, ��������� ������� � � �������� ������� ����
 *     ��
 *     �������� ��������������.</li>
 * <li><b>REQUIRED</b> - �������� �� ���� ������������.</li>
 * <li><b>READONLY</b> - ���� ������ �������������, ������������� ������ ��� ������</li>
 * <li><b>FILTER</b> - ��������� ������� ������ ���������� �� ����. � ������� ������ �������� ������ ������� "BETWEEN"
 *     ��� "><". � � ��� � � ������ ������ ��� ����� �������� ���������� �� ��������� ��������. ���������� ���������
 *     ��������� ����� ��������� ����� ���� ��������� � ����������� �������</li>
 * <li><b>UNIQUE</b> - ���� ������ ��������� ������ ���������� ��������</li>
 * <li><b>VIRTUAL</b> - ������ ���������, ���������� ��� �� ��������� �������, ��� � �� ��������� ��������. ����,
 *     ����������� �����������, ������������ � ����������� ����������, ������ �� ���������� � �������� � ��. �����
 *     ����� ���� �������� ��� ���������� ������������� ������, �����, � �������, ��� ������ ������ ���� �����
 *     ���������� ������ �� ���������� ����� �����. </li>
 * <li><b>EDIT_IN_LIST</b> - �������� �� �������������� ��������������� ��������, ������ ������������ ��������.
 *     ���������, ����� �� ������������� ������ ���� � �������</li>
 * <li><b>MULTIPLE</b> - bool �������� �� ���� �������������</li>
 * <li><b>MULTIPLE_FIELDS</b> - bool ���� ������������ � ��������� ������������� �������� � �� ������</li>
 * <li><b>LIST</b> - ���������� �� ���� � ������ ��������� � ���������� �������� ������� (��-��������� true)</li>
 * <li><b>HEADER</b> - �������� �� ������� ������������ ��-���������, ���� ����� �������� ������� �� �������� (��-��������� true)</li>
 * </ul>
 *
 * ��� ������� ������ �������������?
 * <ul>
 * <li>���������� ����� genMultipleEditHTML(). ����� ������ �������� ������������� ����� �����. ��� ���������� �����
 * ����� ���� JS ������ HelperWidget::jsHelper()</li>
 * <li>������� ����, ������� ����� �������� ����� � EntityManager. ���� ����������� � ��������� "MULTIPLE_FIELDS"
 *     �������. �� ��������� ������������� ������ ���������� ���� ID, ENTITY_ID, VALUE</li>
 * <li>���������� �� ������� ������ ����� �������� � EntityManager � ��������� ��� ��������� ������</li>
 * </ul>
 * ������ ���������� ����� ������� � ������� StringWidget
 *
 * ��� ������������ ������������� ������?
 * <ul>
 * <li>
 * �������� ������� � ������, ������� ����� ������� ������ ����
 * - ������� ����������� ������ ����� ����, ������� ������� ������.
 * ������������ ���� ������� �� ��������� ������� �: HelperWidget::$settings['MULTIPLE_FIELDS']
 * ���� � ������� ������������� ����� �����, �� ��� �������� �: SomeWidget::$settings['MULTIPLE_FIELDS']
 * - ���� ����, ������� ������� ������ ���� � ����� �������, �� ��� ����� ������ ��������,
 * ����� ��������� ������ ��� ������ � ������ ������.
 * ��� ����� �������������� ��������� MULTIPLE_FIELDS ��� ���������� ���� � ���������� ��������� ��������:
 * ```
 * 'RELATED_LINKS' => array(
 *        'WIDGET' => new StringWidget(),
 *        'TITLE' => '������',
 *        'MULTIPLE' => true,
 *        // �������� ��������, ������ ��� ���������������� ���� �������
 *        'MULTIPLE_FIELDS' => array(
 *            'ID', // ������ ���� ��������� ��� ����, ���� ��, ������� �� ����� ��������������
 *            'ENTITY_ID' => 'NEWS_ID', // ENTITY_ID - ����, ������� ������� ������, NEWS_ID - ������ ����, �������
 *     ����� �������������� ������ ENTITY_ID
 *            'VALUE' => 'LINK', // VALUE - ����, ������� ������� ������, LINK - ������ ����, ������� �����
 *     �������������� ������ VALUE
 *        )
 *    ),
 * ```
 * </li>
 *
 * <li>
 * ����� � �������� ������ (��, ������� ������� � AdminBaseHelper::$model) ����� ��������� ����� � �������,
 * � ������� �� ������ ������� ������ ����
 * ������ ���������� �����:
 * ```
 * new Entity\ReferenceField(
 *        'RELATED_LINKS',
 *        'namespace\NewsLinksTable',
 *        array('=this.ID' => 'ref.NEWS_ID'),
 *          // ������� FIELD � ENTITY �� �����������, ����������� �������� � ������������ � ������ @see EntityManager
 *        'ref.FIELD' => new DB\SqlExpression('?s', 'NEWS_LINKS'),
 *        'ref.ENTITY' => new DB\SqlExpression('?s', 'news'),
 * ),
 * ```
 * </li>
 *
 * <li>
 * ��� �� ������ ������� �� ������������� ������, ����� ��� �������� ���������� ���� ������� �������� MULTIPLE => true
 * ```
 * 'RELATED_LINKS' => array(
 *        'WIDGET' => new StringWidget(),
 *        'TITLE' => '������',
 *        // �������� ����� �������������� �����
 *        'MULTIPLE' => true,
 * )
 * ```
 * </li>
 *
 * <li>
 * ������ :)
 * </li>
 * </ul>
 *
 * � ��� ��� ����������� ������ ������������� �������� ����� ������ �� ������������ 
 * ������ \DigitalWand\AdminHelper\EntityManager.
 *
 * @see EntityManager
 * @see HelperWidget::getEditHtml()
 * @see HelperWidget::generateRow()
 * @see showFilterHtml::showFilterHTML()
 * @see HelperWidget::setSetting()
 * 
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 * @author Dmitriy Baibuhtin <dmitriy.baibuhtin@ya.ru>
 */
abstract class HelperWidget
{
    const LIST_HELPER = 1;
    const EDIT_HELPER = 2;

    /**
     * @var string ��� ����.
     */
    protected $code;
    /**
     * @var array $settings ��������� ������� ��� ������ ������.
     */
    protected $settings = array(
        // ���� �������������� ������� �� ��������� (array('������������ ��������', '������������ ��������' => '�����'))
        'MULTIPLE_FIELDS' => array('ID', 'VALUE', 'ENTITY_ID')
    );
    /**
     * @var array ��������� "��-���������" ��� ������.
     */
    static protected $defaults;
    /**
     * @var DataManager �������� ������ ������.
     */
    protected $entityName;
    /**
     * @var array ������ ������.
     */
    protected $data;
    /** @var  AdminBaseHelper|AdminListHelper|AdminEditHelper $helper ��������� �������, ���������� ������ ������.
     */
    protected $helper;
    /**
     * @var bool ������ ����������� JS �������. ������������ ��� ���������� ������������ JS-����.
     */
    protected $jsHelper = false;
    /**
     * @var array $validationErrors ������ ��������� ����.
     */
    protected $validationErrors = array();
    /**
     * @var string ������, ����������� � ���� name ����� �������.
     */
    protected $filterFieldPrefix = 'find_';

    /**
     * ��������� ������� �������� ����� ���� ���, ��� �������� �������� ����������. ��� �������� ���� �����������
     * ����� ������� ��� ���� ����������� ���������.
     * 
     * @param array $settings
     */
    public function __construct(array $settings = array())
    {
        Loc::loadMessages(__FILE__);
        
        $this->settings = $settings;
    }

    /**
     * ���������� HTML ��� �������������� ����.
     *
     * @return string
     * 
     * @api
     */
    abstract protected function getEditHtml();

    /**
     * ���������� HTML ��� �������������� ���� � ������-������.
     *
     * @return string
     * 
     * @api
     */
    protected function getMultipleEditHtml()
    {
        return Loc::getMessage('DIGITALWAND_AH_MULTI_NOT_SUPPORT');
    }

    /**
     * ����������� ���� � HTML ���, ������� � ����������� ������� ������ �� ��������. ����� ���������� 
     * ��������������� �����.
     *
     * @param bool $isPKField �������� �� ���� ��������� ������ ������.
     *
     * @see HelperWidget::getEditHtml();
     */
    public function showBasicEditField($isPKField)
    {
        if ($this->getSettings('HIDE_WHEN_CREATE') AND !isset($this->data['ID'])) {
            return;
        }

        // JS �������
        $this->jsHelper();

        if ($this->getSettings('USE_BX_API')) {
            $this->getEditHtml();
        } else {
            print '<tr>';
            $title = $this->getSettings('TITLE');
            if ($this->getSettings('REQUIRED') === true) {
                $title = '<b>' . $title . '</b>';
            }
            print '<td width="40%" style="vertical-align: top;">' . $title . ':</td>';

            $field = $this->getValue();
            
            if (is_null($field)) {
                $field = '';
            }

            $readOnly = $this->getSettings('READONLY');

            if (!$readOnly AND !$isPKField) {
                if ($this->getSettings('MULTIPLE')) {
                    $field = $this->getMultipleEditHtml();
                } else {
                    $field = $this->getEditHtml();
                }
            } else {
                if ($readOnly) {
                    if ($this->getSettings('MULTIPLE')) {
                        $field = $this->getMultipleValueReadonly();
                    } else {
                        $field = $this->getValueReadonly();
                    }
                }
            }

            print '<td width="60%">' . $field . '</td>';
            print '</tr>';
        }
    }

    /**
     * ���������� �������� ���� � ����� "������ ��� ������" ��� �� ������������� �������.
     *
     * @return mixed
     */
    protected function getValueReadonly()
    {
        return static::prepareToOutput($this->getValue());
    }

    /**
     * ���������� �������� �������������� ����.
     * 
     * @return array
     */
    protected function getMultipleValue()
    {
        $rsEntityData = null;
        $values = array();
        if (!empty($this->data['ID'])) {
            $entityName = $this->entityName;
            $rsEntityData = $entityName::getList(array(
                'select' => array('REFERENCE_' => $this->getCode() . '.*'),
                'filter' => array('=ID' => $this->data['ID'])
            ));

            if ($rsEntityData) {
                while ($referenceData = $rsEntityData->fetch()) {
                    if (empty($referenceData['REFERENCE_' . $this->getMultipleField('ID')])) {
                        continue;
                    }
                    $values[] = $referenceData['REFERENCE_' . $this->getMultipleField('VALUE')];
                }
            }
        } else {
            if ($this->data[$this->code]) {
                $values = $this->data[$this->code];
            }
        }

        return $values;
    }

    /**
     * ���������� �������� ���� � ����� "������ ��� ������" ��� ������������� �������.
     *
     * @return string
     */
    protected function getMultipleValueReadonly()
    {
        $values = $this->getMultipleValue();

        foreach ($values as &$value) {
            $value = static::prepareToOutput($value);
        }

        return join('<br/>', $values);
    }

    /**
     * ��������� ������ ��� ����������� �����������. ���� ����� ���������� ����� ��� �������� ����, 
     * ����������� static::prepareToTag().
     *
     * @param string $string
     * @param bool $hideTags ������ ����:
     * 
     * - true - �������� ���� ������� ����������. ��������� ���������: <b>text</b> = text
     * 
     * - false - ����������� ���� � ���� ������. ��������� ���������: <b>text</b> = &lt;b&gt;text&lt;/b&gt;
     *
     * @return string
     */
    public static function prepareToOutput($string, $hideTags = true)
    {
        if ($hideTags) {
            return preg_replace('/<.+>/mU', '', $string);
        } else {
            return htmlspecialchars($string, ENT_QUOTES, SITE_CHARSET);
        }
    }
    
    /**
     * ���������� ������ ��� ������������� � ���������� �����. ��������:
     * ```
     * <input name="test" value="<?= HelperWidget::prepareToTagAttr($value) ?>"/>
     * ```
     * 
     * @param string $string
     *
     * @return string
     */
    public static function prepareToTagAttr($string)
    {
        // �� ����������� addcslashes � ���� ������, ����� � ����� ����� ����� �������� ������
        return htmlspecialchars($string, ENT_QUOTES, SITE_CHARSET);
    }

    /**
     * ���������� ������ ��� ������������� � JS.
     *
     * @param string $string
     *
     * @return string
     */
    public static function prepareToJs($string)
    {
        $string = htmlspecialchars($string, ENT_QUOTES, SITE_CHARSET);
        $string = addcslashes($string, "\r\n\"\\");

        return $string;
    }

    /**
     * ���������� HTML ��� ���� � ������.
     *
     * @param \CAdminListRow $row
     * @param array $data ������ ������� ������.
     *
     * @return void
     *
     * @see AdminListHelper::addRowCell()
     * 
     * @api
     */
    abstract public function generateRow(&$row, $data);

    /**
     * ���������� HTML ��� ���� ����������.
     *
     * @return void
     *
     * @see AdminListHelper::createFilterForm()
     * 
     * @api
     */
    abstract public function showFilterHtml();

    /**
     * ���������� ������ �������� ������� �������, ���� �������� ���������� ���������, ���� ������� ��� ���.
     *
     * @param string $name �������� ����������� ���������.
     *
     * @return array|mixed
     * 
     * @api
     */
    public function getSettings($name = '')
    {
        if (empty($name)) {
            return $this->settings;
        } else {
            if (isset($this->settings[$name])) {
                return $this->settings[$name];
            } else {
                if (isset(static::$defaults[$name])) {
                    return static::$defaults[$name];
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * ������� � ������ ������ �� ���������� ��� ������.
     *
     * @param AdminBaseHelper $helper
     */
    public function setHelper(&$helper)
    {
        $this->helper = $helper;
    }

    /**
     * ���������� �������� �������� ���� ���������� (����. ������� ������������).
     *
     * @return bool|string
     */
    protected function getCurrentFilterValue()
    {
        if (isset($GLOBALS[$this->filterFieldPrefix . $this->code])) {
            return htmlspecialcharsbx($GLOBALS[$this->filterFieldPrefix . $this->code]);
        } else {
            return false;
        }
    }

    /**
     * ��������� ������������ ��������� � ������ ��������
     *
     * @param string $operationType ��� ��������
     * @param mixed $value �������� �������
     *
     * @see AdminListHelper::checkFilter();
     * @return bool
     */
    public function checkFilter($operationType, $value)
    {
        return true;
    }

    /**
     * ��������� �������������� �����, ������������ � getList, ��������������� ����� ��������.
     * ���� � ���������� ���� ������ ������ ����������, �� ��������� ��������������� ������� � $arFilter.
     * ���� ������ BETWEEN, �� ��������� ������� ������ ����������.
     *
     * @param array $filter $arFilter �������
     * @param array $select
     * @param       $sort
     * @param array $raw $arSelect, $arFilter, $arSort �� ����������� � ��� ��������������.
     *
     * @see AdlinListHelper::getData();
     */
    public function changeGetListOptions(&$filter, &$select, &$sort, $raw)
    {
        if ($this->isFilterBetween()) {
            $field = $this->getCode();
            $from = $to = false;

            if (isset($_REQUEST['find_' . $field . '_from'])) {
                $from = $_REQUEST['find_' . $field . '_from'];
                if (is_a($this, 'DateWidget')) {
                    $from = date('Y-m-d H:i:s', strtotime($from));
                }
            }
            if (isset($_REQUEST['find_' . $field . '_to'])) {
                $to = $_REQUEST['find_' . $field . '_to'];
                if (is_a($this, 'DateWidget')) {
                    $to = date('Y-m-d 23:59:59', strtotime($to));
                } else if (
                        is_a($this, '\DigitalWand\AdminHelper\Widget\DateTimeWidget') &&
                        !preg_match('/\d{2}:\d{2}:\d{2}/', $to)
                ) {
                    $to = date('d.m.Y 23:59:59', strtotime($to));
                }
            }

            if ($from !== false AND $to !== false) {
                $filter['><' . $field] = array($from, $to);
            } else {
                if ($from !== false) {
                    $filter['>' . $field] = $from;
                } else {
                    if ($to !== false) {
                        $filter['<' . $field] = $to;
                    }
                }
            }
        } else {
            if ($filterPrefix = $this->getSettings('FILTER') AND $filterPrefix !== true AND isset($filter[$this->getCode()])) {
                $filter[$filterPrefix . $this->getCode()] = $filter[$this->getCode()];
                unset($filter[$this->getCode()]);
            }
        }
    }

    /**
     * ��������� �������� ����������.
     * 
     * @return bool
     */
    protected function isFilterBetween()
    {
        return $this->getSettings('FILTER') === '><' OR $this->getSettings('FILTER') === 'BETWEEN';
    }

    /**
     * ��������, ����������� ��� ����� � �������� �������������� ��������, �� ��� ����������.
     * ��-��������� ����������� �������� ������������ ����� � ������������.
     *
     * @see AdminEditHelper::editAction();
     * @see AdminListHelper::editAction();
     */
    public function processEditAction()
    {
        if (!$this->checkRequired()) {
            $this->addError('DIGITALWAND_AH_REQUIRED_FIELD_ERROR');
        }
        if ($this->getSettings('UNIQUE') && !$this->isUnique()) {
            $this->addError('DIGITALWAND_AH_DUPLICATE_FIELD_ERROR');
        }
    }

    /**
     * � ������ ������������ ������� ����� ������������� ��������������� �������� ���� ��� ����� ��� ����������� � �� -
     * ��� ����������� ��������� �����-���� ������ �������.
     */
    public function processAfterSaveAction()
    {
    }

    /**
     * ��������� ������ ������ � ������ ������.
     *
     * @param string $messageId ��� ��������� �� ������ �� ����-�����. ����������� #FIELD# ����� ������ �� �������� 
     * ��������� TITLE.
     * @param array $replace ������ ��� ������.
     *
     * @see Loc::getMessage()
     */
    protected function addError($messageId, $replace = array())
    {
        $this->validationErrors[$this->getCode()] = Loc::getMessage(
            $messageId,
            array_merge(array('#FIELD#' => $this->getSettings('TITLE')), $replace)
        );
    }

    /**
     * �������� ������������� ������������ �����.
     * �� ������ ���� null ��� ��������� ������ ������.
     *
     * @return bool
     */
    public function checkRequired()
    {
        if ($this->getSettings('REQUIRED') == true) {
            $value = $this->getValue();

            return !is_null($value) && !empty($value);
        } else {
            return true;
        }
    }

    /**
     * ���������� ��� ��� ������� ������� ��� �������������. ����������� ���������.
     * 
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
        $this->loadSettings();
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * ������������� ��������� ���������� ��� �������� ����.
     *
     * @param string $code
     *
     * @return bool
     * 
     * @see AdminBaseHelper::getInterfaceSettings()
     * @see AdminBaseHelper::setFields()
     */
    public function loadSettings($code = null)
    {
        $interface = $this->helper->getInterfaceSettings();

        $code = is_null($code) ? $this->code : $code;

        if (!isset($interface['FIELDS'][$code])) {
            return false;
        }
        unset($interface['FIELDS'][$code]['WIDGET']);
        $this->settings = array_merge($this->settings, $interface['FIELDS'][$code]);
        $this->setDefaultValue();

        return true;
    }

    /**
     * ���������� �������� �������� ������ ������.
     * 
     * @return string|DataManager
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
        $this->setDefaultValue();
    }

    /**
     * ������������� �������� ��-��������� ��� ������� ����
     */
    public function setDefaultValue()
    {
        if (isset($this->settings['DEFAULT']) && is_null($this->getValue())) {
            $this->setValue($this->settings['DEFAULT']);
        }
    }

    /**
     * �������� ������ �� ������ �������� � ������
     *
     * @param $data
     */
    public function setData(&$data)
    {
        $this->data = &$data;
        //FIXME: ������� ������� ���� ����, ����� ����� ���� ��������������� ��������������� �������� ��� ������
        $this->setValue($data[$this->getCode()]);
    }

    /**
     * ���������� ������� ��������, �������� � ���� �������
     * ���� ������ ���� ���, ���������� null
     *
     * @return mixed|null
     */
    public function getValue()
    {
        $code = $this->getCode();

        return isset($this->data[$code]) ? $this->data[$code] : null;
    }

    /**
     * ������������� �������� ����
     *
     * @param $value
     *
     * @return bool
     */
    protected function setValue($value)
    {
        $code = $this->getCode();
        $this->data[$code] = $value;

        return true;
    }

    /**
     * ��������� �������� ���� �������, � ������� �������� ������������� ������ ����� �������
     *
     * @param string $fieldName �������� ����
     *
     * @return bool|string
     */
    public function getMultipleField($fieldName)
    {
        $fields = $this->getSettings('MULTIPLE_FIELDS');
        if (empty($fields)) {
            return $fieldName;
        }

        // ����� ������ �������� ����
        if (isset($fields[$fieldName])) {
            return $fields[$fieldName];
        }

        // ����� ������������� �������� ����
        $fieldsFlip = array_flip($fields);

        if (isset($fieldsFlip[$fieldName])) {
            return $fieldsFlip[$fieldName];
        }

        return $fieldName;
    }

    /**
     * ���������� �������� ��������� ���������
     *
     * @param string $name
     * @param mixed $value
     */
    public function setSetting($name, $value)
    {
        $this->settings[$name] = $value;
    }

    /**
     * ���������� ��������� ������ ���������
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * ���������� ����� ��� �������� name ����� �������.
     * ���� ��� ������ BETWEEN, �� ����� ������ � ���������� from � to.
     *
     * @return array|string
     */
    protected function getFilterInputName()
    {
        if ($this->isFilterBetween()) {
            $baseName = $this->filterFieldPrefix . $this->code;;
            $inputNameFrom = $baseName . '_from';
            $inputNameTo = $baseName . '_to';

            return array($inputNameFrom, $inputNameTo);
        } else {
            return $this->filterFieldPrefix . $this->code;
        }
    }

    /**
     * ���������� ����� ��� �������� name ������ ��������������.
     *
     * @param null $suffix ������������ ���������� � �������� ����
     *
     * @return string
     */
    protected function getEditInputName($suffix = null)
    {
        return 'FIELDS[' . $this->getCode() . $suffix . ']';
    }

    /**
     * ���������� ID ��� DOM HTML
     * @return string
     */
    protected function getEditInputHtmlId()
    {
        $htmlId = end(explode('\\', $this->entityName)) . '-' . $this->getCode();

        return strtolower(preg_replace('/[^A-z-]/', '-', $htmlId));
    }

    /**
     * ���������� ����� ��� �������� name ������ �������������� ���� � ������
     * @return string
     */
    protected function getEditableListInputName()
    {
        $id = $this->data['ID'];

        return 'FIELDS[' . $id . '][' . $this->getCode() . ']';
    }

    /**
     * ���������� ��� ����������� �������, �� ���� ����� �������� ��������� �������.
     *
     * @return bool|int
     * @see HelperWidget::EDIT_HELPER
     * @see HelperWidget::LIST_HELPER
     */
    protected function getCurrentViewType()
    {
        if (is_a($this->helper, 'DigitalWand\AdminHelper\Helper\AdminListHelper')) {
            return self::LIST_HELPER;
        } else {
            if (is_a($this->helper, 'DigitalWand\AdminHelper\Helper\AdminEditHelper')) {
                return self::EDIT_HELPER;
            }
        }

        return false;
    }

    /**
     * ��������� �������� ���� �� ������������
     * @return bool
     */
    private function isUnique()
    {
        if ($this->getSettings('VIRTUAL')) {
            return true;
        }

        $value = $this->getValue();
        if (empty($value)) {
            return true;
        }

        /** @var DataManager $class */
        $class = $this->entityName;
        $field = $this->getCode();
        $idField = 'ID';
        $id = $this->data[$idField];

        $filter = array(
            $field => $value,
        );

        if (!empty($id)) {
            $filter["!=" . $idField] = $id;
        }

        $count = $class::getCount($filter);

        if (!$count) {
            return true;
        }

        return false;
    }

    /**
     * ���������, �� �������� �� ������� ������ �������� ��������� ������ � Excel
     * @return bool
     */
    protected function isExcelView()
    {
        if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel') {
            return true;
        }

        return false;
    }

    /**
     * @todo ������� � ������ (\CJSCore::Init()).
     * @todo �������.
     */
    protected function jsHelper()
    {
        if ($this->jsHelper == true) {
            return true;
        }

        $this->jsHelper = true;
        \CJSCore::Init(array("jquery"));
        ?>
        <script>
            /**
             * �������� ������������� �����
             * ��������� ��������� � ������� ����� HTML ��� � ����������� ����������� ������������ ������
             * ����������:
             * - �������� ���������, ��� ����� �������� ������������ ���
             * - �������� ��������� MultipleWidgetHelper
             * ��������: var multiple = MultipleWidgetHelper(�������� ����������, ������)
             * ������ - ��� HTML ���, ������� ����� ����� ��������� � ������� � ����������
             * � ������ ����� ��������� ����������, �� ����� ��������� ��������� ��������. �������� {{entity_id}}
             * ���� � ������� ��������� �����, ���������� {{field_id}} �����������
             * �������� <input type="text" name="image[{{field_id}}][SRC]"><input type="text" name="image[{{field_id}}][DESCRIPTION]">
             * ���� ����������� ���� �� �����, �� ����������� ����������� � addField ���������� field_id � ID ������,
             * ��� ������������� ����� ���������� ���������� �������������
             */
            function MultipleWidgetHelper(container, fieldTemplate) {
                this.$container = $(container);
                if (this.$container.size() == 0) {
                    throw '������� ��������� ����� �� ������ (' + container + ')';
                }
                if (!fieldTemplate) {
                    throw '�� ������� ������������ �������� fieldTemplate';
                }
                this.fieldTemplate = fieldTemplate;
                this._init();
            }

            MultipleWidgetHelper.prototype = {
                /**
                 * �������� ���������
                 */
                $container: null,
                /**
                 * ��������� �����
                 */
                $fieldsContainer: null,
                /**
                 * ������ ����
                 */
                fieldTemplate: null,
                /**
                 * ������� ���������� �����
                 */
                fieldsCounter: 0,
                /**
                 * ���������� ����
                 * @param data object ������ ��� ������� � ���� ����: ��������
                 */
                addField: function (data) {
                    // console.log('���������� ����');
                    this.addFieldHtml(this.fieldTemplate, data);
                },
                addFieldHtml: function (fieldTemplate, data) {
                    this.fieldsCounter++;
                    this.$fieldsContainer.append(this._generateFieldContent(fieldTemplate, data));
                },
                /**
                 * �������� ����
                 * @param field string|object �������� ��� jQuery ������
                 */
                deleteField: function (field) {
                    // console.log('�������� ����');
                    $(field).remove();
                    if (this.$fieldsContainer.find('> *').size() == 0) {
                        this.addField();
                    }
                },
                _init: function () {
                    this.$container.append('<div class="fields-container"></div>');
                    this.$fieldsContainer = this.$container.find('.fields-container');
                    this.$container.append(this._getAddButton());

                    this._trackEvents();
                },
                /**
                 * ��������� �������� ���������� ����
                 * @param data
                 * @returns {string}
                 * @private
                 */
                _generateFieldContent: function (fieldTemplate, data) {
                    return '<div class="field-container" style="margin-bottom: 5px;">'
                        + this._generateFieldTemplate(fieldTemplate, data) + this._getDeleteButton()
                        + '</div>';
                },
                /**
                 * ��������� ������� ����
                 * @param data object ������ ��� �����������
                 * @returns {null}
                 * @private
                 */
                _generateFieldTemplate: function (fieldTemplate, data) {
                    if (!data) {
                        data = {};
                    }

                    if (typeof data.field_id == 'undefined') {
                        data.field_id = 'new_' + this.fieldsCounter;
                    }

                    $.each(data, function (key, value) {
                        // ������������ �������� ����������
                        fieldTemplate = fieldTemplate.replace(new RegExp('\{\{' + key + '\}\}', ['g']), value);
                    });

                    // �������� �� ������� �������������� ����������
                    fieldTemplate = fieldTemplate.replace(/\{\{.+?\}\}/g, '');

                    return fieldTemplate;
                },
                /**
                 * ������ ��������
                 * @returns {string}
                 * @private
                 */
                _getDeleteButton: function () {
                    return '<input type="button" value="-" class="delete-field-button" style="margin-left: 5px;">';
                },
                /**
                 * ������ ����������
                 * @returns {string}
                 * @private
                 */
                _getAddButton: function () {
                    return '<input type="button" value="��������..." class="add-field-button">';
                },
                /**
                 * ������������ �������
                 * @private
                 */
                _trackEvents: function () {
                    var context = this;
                    // ���������� ����
                    this.$container.find('.add-field-button').on('click', function () {
                        context.addField();
                    });
                    // �������� ����
                    this.$container.on('click', '.delete-field-button', function () {
                        context.deleteField($(this).parents('.field-container'));
                    });
                }
            };
        </script>
        <?
    }
}