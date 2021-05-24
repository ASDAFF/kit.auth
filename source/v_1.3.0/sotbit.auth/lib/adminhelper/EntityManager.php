<?php

namespace DigitalWand\AdminHelper;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity;
use DigitalWand\AdminHelper\Helper\AdminBaseHelper;
use DigitalWand\AdminHelper\Widget\HelperWidget;

/**
 * �������� ��� ���������� �������, ��������� ������������� � ����� � ��������� ������ � ��������� �������� ��
 * ��������� ���������� ������ �� ��������.
 *
 * ������ �������� ��������:
 * ```
 * $filmManager = new EntityManager('\Vendor\Module\FilmTable', array(
 *        // ������ ��������
 *        'TITLE' => '������� �� ��������� 2',
 *        'YEAR' => 2015,
 *        // � �������� FilmTable ���� ����� � RelatedLinksTable ����� ���� RELATED_LINKS.
 *        // ���� �������� �� ������, �� ��� ����� ����������
 *        // ����������, ��� � �������� RelatedLinksTable ���� ���� ID � VALUE (� ���� ���� �������� ������), FILM_ID
 *        // � ����������� �������, ������ ������������ ������ ������������ �������������� ���������
 *        'RELATED_LINKS' => array(
 *            // ���������� ���� ������ ����� ��������� ���������� ���� RelatedLinksTable::add(array('VALUE' =>
 * 'yandex.ru')); array('VALUE' => 'yandex.ru'),
 *            // ���� � ������ �������� ID, �� ������ ���������: RelatedLinksTable::update(3, array('ID' => 3, 'VALUE'
 * => 'google.com')); array('ID' => 3, 'VALUE' => 'google.com'),
 *            // ��������: ������ ����� ��������������� ���������: ��� �������� ��� �����, �� ���������� ��� ���������,
 * ��� �� ��������, ����� �������
 *            // �� ����, ���� � ���� ����� RELATED_LINKS �������� ������ ������, �� ��� �������� ����� ����� �������
 *        )
 * ));
 * $filmManager->save();
 * ```
 *
 * ������ �������� ��������
 * ```
 * $articleManager = new EntityManager('\Vendor\Module\ArticlesTable', array(), 7, $adminHelper);
 * $articleManager->delete();
 * ```
 *
 * ��� �������� ���������� ������ ? �������������� ������
 * ��������, ��� ���� ������ NewsTable (�������) � NewsLinksTable (������ �� �������������� ���������� � �������)
 *
 * � ������ NewsTable ���� ����� � ������� NewsLinksTable ����� ���� NEWS_LINKS:
 * ```
 * DataManager::getMap() {
 * ...
 * new Entity\ReferenceField(
 *        'NEWS_LINKS',
 *        '\Vendor\Module\NewsLinksTable',
 *        array('=this.ID' => 'ref.NEWS_ID'),
 *        'ref.FIELD' => new DB\SqlExpression('?s', 'NEWS_LINKS'),
 *        'ref.ENTITY' => new DB\SqlExpression('?s', 'news'),
 * ),
 * ...
 * }
 * ```
 *
 * ��������� ���������
 * ```
 * $newsManager = new EntityManager(
 *        '\Vendor\Module\NewsTable',
 *        array(
 *            'TITLE' => 'News title',
 *            'CONTENT' => 'News content',
 *            'NEWS_LINKS' => array(
 *                array('LINK' => 'test.ru'),
 *                array('LINK' => 'test2.ru'),
 *                array('ID' => 'id ������', 'LINK' => 'test3.ru'),
 *            )
 *        ),
 *        null,
 *        $adminHelper
 * );
 * $newsManager->save();
 * ```
 *
 * � ������ ������� ���������� ������ ��� ������� (��������� � ����������) � ������ ��� ����-����� NEWS_LINKS.
 *
 * ����� EntityManager:
 * 1. �������� ������, ������� ������������� ������
 * 2. ����������� � ��� ������ �� �������� ������ �� ������ ������� �����
 * �������� ��� ����� � ����� NEWS_LINKS ����������� ������:
 *
 * ```
 * NewsLinksTable::ENTITY_ID => NewsTable::ID,
 * NewsLinksTable::FIELD => 'NEWS_LINKS',
 * NewsLinksTable::ENTITY => 'news'
 * ```
 *
 * 3. ����� ����������� ������ ��� ����� �������� ������ ����� ������� ���� ����:
 *
 * ```
 * NewsLinksTable::add(array('ENTITY' => 'news', 'FIELD' => 'NEWS_LINKS', 'ENTITY_ID' => 'id ��������, ��������
 * �������', 'LINK' => 'test.ru')); NewsLinksTable::add(array('ENTITY' => 'news', 'FIELD' => 'NEWS_LINKS', 'ENTITY_ID'
 * => 'id ��������', 'LINK' => 'test2.ru')); NewsLinksTable::update('id ������', array('ENTITY' => 'news', 'FIELD' =>
 * 'NEWS_LINKS', 'ENTITY_ID' => 'id ��������', 'LINK' => 'test3.ru'));
 * ```
 *
 * �������� ��������, ��� � ����� EntityManager::save() ���� ���������� �������� ������ ���� LINK, ���� ENTITY,
 * ENTITY_ID � FIELD ���� ����������� ������� EntityManager ������������� (���������� �����) � ��� �� �����, ��� ���
 * ������� ������ ��� ������� �������������, ������� ���������� NewsLinksTable::update, � �� NewsLinksTable::add
 *
 * 4. ����� `EntityManager` ������� ������ ��������� ������ `NewsLinksTable`, ������� �� ���� ��������� ��� ���������.
 *
 * <b>��� �������� ��������?</b>
 *
 * 1. EntityManager �������� �� `NewsTable::getMap()` ����-�����
 * 2. �������� ���� ��������� � ���������� ���������� �������
 * 3. ������� �������� ��� �����-������, ������� ��������� � ����������
 *
 * <i>����������.</i>
 * EntityManager ��������� ������ �������, ������� �������� ��� ������ ����� ������������ ����������� ��������.
 * ��������, ��� �������� NewsTable ����� ������� ������ NewsLinksTable, ���:
 *
 * ```
 * NewsLinksTable::ENTITY_ID => NewsTable::ID,
 * NewsLinksTable::FIELD => 'NEWS_LINKS',
 * NewsLinksTable::ENTITY => 'news'
 * ```
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 * @author Dmitriy Baibuhtin <dmitriy.baibuhtin@ya.ru>
 */
class EntityManager
{
	/**
	 * @var string ����� ������.
	 */
	protected $modelClass;
	/**
	 * @var Entity\Base �������� ������.
	 */
	protected $model;
	/**
	 * @var array ������ ��� ���������.
	 */
	protected $data;
	/**
	 * @var integer ������������� ������.
	 */
	protected $itemId = null;
	/**
	 * @var string ���� ������, � ������� �������� ������������� ������.
	 */
	protected $modelPk = null;
	/**
	 * @var array ������ ��� ������.
	 */
	protected $referencesData = array();
	/**
	 * @var AdminBaseHelper ������.
	 */
	protected $helper;
	/**
	 * @var array ��������������.
	 */
	protected $notes = array();

	/**
	 * @param string $modelClass ����� �������� ������, ���������� DataManager.
	 * @param array $data ������ � ������������ �������.
	 * @param integer $itemId ������������� ����������� ������.
	 * @param AdminBaseHelper $helper ������, ������������ ���������� ������.
	 */
	public function __construct($modelClass, array $data = array(), $itemId = null, AdminBaseHelper $helper)
	{
		Loc::loadMessages(__FILE__);
		
		$this->modelClass = $modelClass;
		$this->model = $modelClass::getEntity();
		$this->data = $data;
		$this->modelPk = $this->model->getPrimary();
		$this->helper = $helper;

		if (!empty($itemId)) {
			$this->setItemId($itemId);
		}
	}

    /**
     * ��������� ������ � ������ ������.
     *
     * @return Entity\AddResult|Entity\UpdateResult
     */
    public function save()
    {
        $this->collectReferencesData();

        /**
         * @var DataManager $modelClass
         */
        $modelClass = $this->modelClass;
		$db = $this->model->getConnection();
		$db->startTransaction(); // ������ ����������

		if (empty($this->itemId)) {
			$result = $modelClass::add($this->data);

			if ($result->isSuccess()) {
				$this->setItemId($result->getId());
			}
		}
		else {
			$result = $modelClass::update($this->itemId, $this->data);
		}

        if ($result->isSuccess()) {
			$referencesDataResult = $this->processReferencesData();
			if($referencesDataResult->isSuccess()){
				$db->commitTransaction(); // ������ ��� - ��������� ���������
			}else{
				$result = $referencesDataResult; // ���������� ReferencesResult ��� �� ������� ������
				$db->rollbackTransaction(); // ���-�� ����� �� ��� - ���������� ��� ��� ����
			}
		} else {
			$db->rollbackTransaction();
		}

		return $result;
	}

    /**
     * �������� ������ � ������ ������.
     *
     * @return Entity\DeleteResult
     */
    public function delete()
    {
        // �������� ������ ������������
		$db = $this->model->getConnection();
		$db->startTransaction(); // ������ ����������

		$result = $this->deleteReferencesData(); // ������� ��������� ��������

		if(!$result->isSuccess()){ // ���� ���� �� ���� �� ��� �� ���������
			$db->rollbackTransaction(); // �� ��������������� ���
			return $result; // ���������� ������
		}

		$model = $this->modelClass;

		//���� ���������� ������, �� �������� �� ������
        if (!is_null($this->itemId)) {
            $result = $model::delete($this->itemId); // ������� �������� ��������
        } elseif (!is_array($this->helper->getPk())) {
            $result = $model::delete($this->helper->getPk()); // ������� �������� ��������
        } else {
            $result = new Entity\DeleteResult();
            $error = new Entity\EntityError('Can\'t find element\'s ID');
            $result->addError($error);
        }

		if(!$result->isSuccess()){  // ���� �� ���������
			$db->rollbackTransaction(); // �� ��������������� ��������� ��������
			return $result; // ���������� ������
		}

		$db->commitTransaction(); // ��� ������ ��� ������ ��������� ���������
		return $result;
    }

	/**
	 * �������� ������ ��������������
	 * @return array
	 */
	public function getNotes()
	{
		return $this->notes;
	}

	/**
	 * �������� ��������������
	 *
	 * @param $note
	 * @param string $key ���� ��� ��������� ������������ ���������
	 *
	 * @return bool
	 */
	protected function addNote($note, $key = null)
	{
		if ($key) {
			$this->notes[$key] = $note;
		}
		else {
			$this->notes[] = $note;
		}

		return true;
	}

	/**
	 * ��������� �������� �������������� ������.
	 *
	 * @param integer $itemId ������������� ������.
	 */
	protected function setItemId($itemId)
	{
		$this->itemId = $itemId;
		$this->data[$this->modelPk] = $this->itemId;
	}

	/**
	 * ��������� ������
	 *
	 * @return array
	 */
	protected function getReferences()
	{
		/**
		 * @var DataManager $modelClass
		 */
		$modelClass = $this->modelClass;
		$entity = $modelClass::getEntity();
		$fields = $entity->getFields();
		$references = array();

		foreach ($fields as $fieldName => $field) {
			if ($field instanceof Entity\ReferenceField) {
				$references[$fieldName] = $field;
			}
		}

		return $references;
	}

	/**
	 * ���������� ������ ��� ������
	 */
	protected function collectReferencesData()
	{
		$result = new Entity\Result();
		$references = $this->getReferences();
		// ���������� ������ ����������� ������
		foreach ($references as $fieldName => $reference) {
			if (array_key_exists($fieldName, $this->data)) {
				if (!is_array($this->data[$fieldName])) {
					$result->addError(new Entity\EntityError(Loc::getMessage('DIGITALWAND_AH_RELATION_SHOULD_BE_MULTIPLE_FIELD')));

					return $result;
				}
				// ���������� ������ ��� �����
				$this->referencesData[$fieldName] = $this->data[$fieldName];
				unset($this->data[$fieldName]);
			}
		}

		return $result;
	}

    /**
     * ��������� ������ ��� ������.
     *
     * @throws ArgumentException
     */
    protected function processReferencesData()
    {
        /**
         * @var DataManager $modelClass
         */
        $modelClass = $this->modelClass;
        $entity = $modelClass::getEntity();
        $fields = $entity->getFields();
		$result = new Entity\Result(); // ������ Result � �������� isSuccess ������ true

		foreach ($this->referencesData as $fieldName => $referenceDataSet) {
			if (!is_array($referenceDataSet)) {
				continue;
			}

			/**
			 * @var Entity\ReferenceField $reference
			 */
			$reference = $fields[$fieldName];
			$referenceDataSet = $this->linkDataSet($reference, $referenceDataSet);
			$referenceStaleDataSet = $this->getReferenceDataSet($reference);
			$fieldWidget = $this->getFieldWidget($fieldName);

			// �������� � ���������� ����������� ������
			$processedDataIds = array();
			foreach ($referenceDataSet as $referenceData) {
				if (empty($referenceData[$fieldWidget->getMultipleField('ID')])) {
					// �������� ��������� ������
					if (!empty($referenceData[$fieldWidget->getMultipleField('VALUE')])) {
						$result = $this->createReferenceData($reference, $referenceData);

                        if ($result->isSuccess()) {
                            $processedDataIds[] = $result->getId();
                        } else {
							break; // ������, ��������� ��������� ������
						}
                    }
                } else {
                    // ���������� ��������� ������
					$result = $this->updateReferenceData($reference, $referenceData, $referenceStaleDataSet);

                    if ($result->isSuccess()) {
                        $processedDataIds[] = $referenceData[$fieldWidget->getMultipleField('ID')];
                    } else {
						break; // ������, ��������� ��������� ������
					}
                }
            }

			if($result->isSuccess()){ // �������� �������, ������� �� ���� ������� ��� ���������
				foreach ($referenceStaleDataSet as $referenceData) {
					if (!in_array($referenceData[$fieldWidget->getMultipleField('ID')], $processedDataIds)) {
						$result = $this->deleteReferenceData($reference,
							$referenceData[$fieldWidget->getMultipleField('ID')]);
						if(!$result->isSuccess()) {
							break; // ������, ��������� �������� ������
						}
					}
				}
			}
        }

        $this->referencesData = array();
		return $result;
    }

    /**
     * �������� ������ ���� ������, ������� ������� � ����� ���������� �������.
     */
    protected function deleteReferencesData()
    {
        $references = $this->getReferences();
        $fields = $this->helper->getFields();
		$result = new Entity\Result();
        /**
         * @var string $fieldName
         * @var Entity\ReferenceField $reference
         */
        foreach ($references as $fieldName => $reference) {
            // ��������� ������ ������ ������, ������� ��������� � ����������
            if (!isset($fields[$fieldName])) {
                continue;
            }

			$fieldWidget = $this->getFieldWidget($reference->getName());
			$referenceStaleDataSet = $this->getReferenceDataSet($reference);

            foreach ($referenceStaleDataSet as $referenceData) {
                $result = $this->deleteReferenceData($reference, $referenceData[$fieldWidget->getMultipleField('ID')]);
				if(!$result->isSuccess()){
					return $result;
				}
            }
        }
		return $result;
    }

	/**
	 * �������� ��������� ������.
	 *
	 * @param Entity\ReferenceField $reference
	 * @param array $referenceData
	 *
	 * @return \Bitrix\Main\Entity\AddResult
	 * @throws ArgumentException
	 */
	protected function createReferenceData(Entity\ReferenceField $reference, array $referenceData)
	{
		$referenceName = $reference->getName();
		$fieldParams = $this->getFieldParams($referenceName);
		$fieldWidget = $this->getFieldWidget($referenceName);

		if (!empty($referenceData[$fieldWidget->getMultipleField('ID')])) {
			throw new ArgumentException(Loc::getMessage('DIGITALWAND_AH_ARGUMENT_CANT_CONTAIN_ID', array('%A%' => 'referenceData')), 'referenceData');
		}

		$refClass = $reference->getRefEntity()->getDataClass();

		$createResult = $refClass::add($referenceData);

		if (!$createResult->isSuccess()) {
			$this->addNote(Loc::getMessage('DIGITALWAND_ADMIN_HELPER_RELATION_SAVE_ERROR',
				array('#FIELD#' => $fieldParams['TITLE'])), 'CREATE_' . $referenceName);
		}

		return $createResult;
	}

	/**
	 * ���������� ��������� ������
	 *
	 * @param Entity\ReferenceField $reference
	 * @param array $referenceData
	 * @param array $referenceStaleDataSet
	 *
	 * @return Entity\UpdateResult|null
	 * @throws ArgumentException
	 */
	protected function updateReferenceData(
		Entity\ReferenceField $reference,
		array $referenceData,
		array $referenceStaleDataSet
	)
	{
		$referenceName = $reference->getName();
		$fieldParams = $this->getFieldParams($referenceName);
		$fieldWidget = $this->getFieldWidget($referenceName);

		if (empty($referenceData[$fieldWidget->getMultipleField('ID')])) {
			throw new ArgumentException(Loc::getMessage('DIGITALWAND_AH_ARGUMENT_SHOULD_CONTAIN_ID', array('%A%' => 'referenceData')), 'referenceData');
		}

		// ��������� ������ ������ � �����, ����������� ������ ��� ���������
		if ($this->isDifferentData($referenceStaleDataSet[$referenceData[$fieldWidget->getMultipleField('ID')]],
			$referenceData)
		) {
			$refClass = $reference->getRefEntity()->getDataClass();
			$updateResult = $refClass::update($referenceData[$fieldWidget->getMultipleField('ID')], $referenceData);

			if (!$updateResult->isSuccess()) {
				$this->addNote(Loc::getMessage('DIGITALWAND_ADMIN_HELPER_RELATION_SAVE_ERROR',
					array('#FIELD#' => $fieldParams['TITLE'])), 'UPDATE_' . $referenceName);
			}

            return $updateResult;
        } else {
            return new Entity\Result(); // ������ Result � �������� isSuccess() ������ true
        }
    }

	/**
	 * �������� ������ �����.
	 *
	 * @param Entity\ReferenceField $reference
	 * @param $referenceId
	 *
	 * @return \Bitrix\Main\Entity\Result
	 * @throws ArgumentException
	 */
	protected function deleteReferenceData(Entity\ReferenceField $reference, $referenceId)
	{
		$fieldParams = $this->getFieldParams($reference->getName());
		$refClass = $reference->getRefEntity()->getDataClass();
		$deleteResult = $refClass::delete($referenceId);

		if (!$deleteResult->isSuccess()) {
			$this->addNote(Loc::getMessage('DIGITALWAND_ADMIN_HELPER_RELATION_DELETE_ERROR',
				array('#FIELD#' => $fieldParams['TITLE'])), 'DELETE_' . $reference->getName());
		}

		return $deleteResult;
	}

	/**
	 * ��������� ������ �����.
	 *
	 * @param $reference
	 *
	 * @return array
	 */
	protected function getReferenceDataSet(Entity\ReferenceField $reference)
	{
		/**
		 * @var DataManager $modelClass
		 */
		$modelClass = $this->modelClass;
		$dataSet = array();
		$fieldWidget = $this->getFieldWidget($reference->getName());

		$rsData = $modelClass::getList(array(
			'select' => array('REF_' => $reference->getName() . '.*'),
			'filter' => array('=' . $this->modelPk => $this->itemId)
		));

		while ($data = $rsData->fetch()) {
			if (empty($data['REF_' . $fieldWidget->getMultipleField('ID')])) {
				continue;
			}

			$row = array();
			foreach ($data as $key => $value) {
				$row[str_replace('REF_', '', $key)] = $value;
			}

			$dataSet[$data['REF_' . $fieldWidget->getMultipleField('ID')]] = $row;
		}

		return $dataSet;
	}

	/**
	 * � ������ ����� ������������� ������ �������� ������ ��������� ������� ����� ������� �� getMap().
	 *
	 * @param Entity\ReferenceField $reference
	 * @param array $referenceData ������ ����������� ������
	 *
	 * @return array
	 */
	protected function linkData(Entity\ReferenceField $reference, array $referenceData)
	{
		// ������ ������� ����� ���� �������
		$referenceConditions = $this->getReferenceConditions($reference);

		foreach ($referenceConditions as $refField => $refValue) {
			// ��� ��� � �������� ����� ����� �������� � �������� ��������� ���� this.field => ref.field ���
			// ref.field => SqlExpression, �� ����� ������������ ��� ��� ����������� ������
			// this.field - ��� ���� �������� ������
			// ref.field - ���� ������ �� �����
			// customValue - ��� ������ ���������� �� new SqlExpression('%s', ...)
			if (empty($refValue['thisField'])) {
				$referenceData[$refField] = $refValue['customValue'];
			}
			else {
				$referenceData[$refField] = $this->data[$refValue['thisField']];
			}
		}

		return $referenceData;
	}

	/**
	 * ��������� ����� ��������� ������ � �������� ������.
	 *
	 * @param Entity\ReferenceField $reference
	 * @param array $referenceDataSet
	 *
	 * @return array
	 */
	protected function linkDataSet(Entity\ReferenceField $reference, array $referenceDataSet)
	{
		foreach ($referenceDataSet as $key => $referenceData) {
			$referenceDataSet[$key] = $this->linkData($reference, $referenceData);
		}

		return $referenceDataSet;
	}

	/**
	 * ������� ������� ����� ����� ��������.
	 *
	 * ������ �������� ���, ������ ������������ ������������ ����� �������� ������ � ������ �� �����. ��������:
	 *
	 * `FilmLinksTable::FILM_ID => FilmTable::ID (ref.FILM_ID => this.ID)`
	 *
	 * ���, ��������:
	 *
	 * `MediaTable::TYPE => 'FILM' (ref.TYPE => new DB\SqlExpression('?s', 'FILM'))`
	 *
	 * @param Entity\ReferenceField $reference ������ ���� �� getMap().
	 *
	 * @return array ������� ����� ��������������� � ������ ���� $conditions[$refField]['thisField' => $thisField,
	 *     'customValue' => $customValue].
	 *      $customValue - ��� ��������� �������� SqlExpression.
	 *      ���� ������ SqlExpression �� ����� %s, �� ������� ����������� �� ����������.
	 */
	protected function getReferenceConditions(Entity\ReferenceField $reference)
	{
		$conditionsFields = array();

		foreach ($reference->getReference() as $leftCondition => $rightCondition) {
			$thisField = null;
			$refField = null;
			$customValue = null;

			// ����� this.... � ����� �������
			$thisFieldMatch = array();
			$refFieldMatch = array();
			if (preg_match('/=this\.([A-z]+)/', $leftCondition, $thisFieldMatch) == 1) {
				$thisField = $thisFieldMatch[1];
			} // ����� ref.... � ����� �������
			else {
				if (preg_match('/ref\.([A-z]+)/', $leftCondition, $refFieldMatch) == 1) {
					$refField = $refFieldMatch[1];
				}
			}

			// ����� expression value... � ������ �������
			$refFieldMatch = array();
			if ($rightCondition instanceof \Bitrix\Main\DB\SqlExpression) {
				$customValueDirty = $rightCondition->compile();
				$customValue = preg_replace('/^([\'"])(.+)\1$/', '$2', $customValueDirty);
				if ($customValueDirty == $customValue) {
					// ���� �������� ��������� �� ��������� ���������, ������ ��� �� ����� ���
					$customValue = null;
				}
			} // ����� ref.... � ������ �������
			else {
				if (preg_match('/ref\.([A-z]+)/', $rightCondition, $refFieldMatch) > 0) {
					$refField = $refFieldMatch[1];
				}
			}

			// ���� �� ������� ����, ������� ����� ��������� ��� �� ������� ���������� ��� ����, �� ��������� �������
			if (empty($refField) || (empty($thisField) && empty($customValue))) {
				continue;
			}
			else {
				$conditionsFields[$refField] = array(
					'thisField' => $thisField,
					'customValue' => $customValue,
				);
			}
		}

		return $conditionsFields;
	}

	/**
	 * ����������� ������� ��������
	 * ����� �� ��������� ������� ����������, ������������ ������ �������� ����� ����������
	 *
	 * @param array $data1
	 * @param array $data2
	 *
	 * @return bool
	 */
	protected function isDifferentData(array $data1 = null, array $data2 = null)
	{
		foreach ($data1 as $key => $value) {
			if (isset($data2[$key]) && $data2[$key] != $value) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $fieldName
	 *
	 * @return array|bool
	 */
	protected function getFieldParams($fieldName)
	{
		$fields = $this->helper->getFields();

		if (isset($fields[$fieldName]) && isset($fields[$fieldName]['WIDGET'])) {
			return $fields[$fieldName];
		}
		else {
			return false;
		}
	}

	/**
	 * ��������� ������� ������������ � ����.
	 *
	 * @param $fieldName
	 *
	 * @return HelperWidget|bool
	 */
	protected function getFieldWidget($fieldName)
	{
		$field = $this->getFieldParams($fieldName);

		return isset($field['WIDGET']) ? $field['WIDGET'] : null;
	}
}