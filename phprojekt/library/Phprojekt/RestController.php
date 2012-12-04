<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Default REST Controller.
 */
abstract class Phprojekt_RestController extends Zend_Rest_Controller
{
    public function init()
    {
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function preDispatch()
    {
        $projectId = $this->getRequest()->getParam('projectId', null);
        if (!is_null($projectId)) {
            Phprojekt::setCurrentProjectId($projectId);
        }
    }

    public function indexAction()
    {
        $projectId = (int) $this->getRequest()->getParam('projectId', 0);
        $range     = $this->getRequest()->getHeader('range');
        if (!empty($range)) {
            sscanf($range, 'items=%d-%d', $start, $end);
            $count = $end - $start + 1;
        } else {
            $start = null;
            $end   = null;
            $count = null;
        }
        $sort      = $this->_getSorting();
        $recursive = $this->getRequest()->getParam('recursive', 'false');
        $recursive = $recursive === 'true';
        $model     = $this->_newModelObject();
        $moduleId  = Phprojekt_Module::getId($this->getRequest()->getModuleName());
        $isGlobal  = Phprojekt_Module::saveTypeIsGlobal($moduleId);

        if (empty($projectId) && !$isGlobal) {
            throw new Zend_Controller_Action_Exception(
                'projectId not given for non-global module',
                422
            );
        } else if (!empty($projectId) && $isGlobal) {
            throw new Zend_Controller_Action_Exception(
                'projectId given for global module',
                422
            );
        }

        $recursive = $isGlobal ? false : $recursive;

        $records     = array();
        $recordCount = 0;
        if ($recursive) {
            $tree = new Phprojekt_Tree_Node_Database(new Project_Models_Project(), $projectId);
            $tree->setup();
            $where       = $this->_getFilterWhere();
            $records     = $tree->getRecordsFor($model, $count, $start, $where, $sort);
            $recordCount = $tree->getRecordsCount($model, $where);
        } else {
            if (!empty($projectId) && $model->hasField('projectId')) {
                $where  = Phprojekt::getInstance()->getDb()->quoteInto('project_id = ?', (int) $projectId);
            } else {
                $where = null;
            }

            $where       = $this->_getFilterWhere($where);
            $records     = $model->fetchAll($where, $sort, $count, $start);
            $recordCount = $model->count($where);
        }

        $end = is_null($end) ? $recordCount : min($end, $recordCount);
        $this->getResponse()->setHeader('Content-Range', "items {$start}-{$end}/{$recordCount}");
        Phprojekt_CompressedSender::send(
            Zend_Json::encode(Phprojekt_Model_Converter::convertModels($records))
        );
    }

    protected function _getSorting()
    {
        $params = $this->getRequest()->getParams();
        foreach ($params as $key => $value) {
            if (strpos($key, 'sort(') === 0) {
                return $this->_parseSortingQuery($key);
            }
        }
    }

    private function _parseSortingquery($sortString)
    {
        $criteriaStrings = explode(',', substr($sortString, strlen('sort('), -1));
        $criteria = array();

        foreach ($criteriaStrings as $c) {
            $attribute  = substr($c, 1);
            $descending = (substr($c, 0, 1) === '-');
            return $attribute . ($descending ? ' DESC' : ' ASC');
        }
        return $criteria;
    }

    public function getAction()
    {
        $id = (int) $this->_getParam('id');
        $record = $this->_newModelObject();
        if (!empty($id)) {
            $record = $record->find($id);
            Phprojekt::setCurrentProjectId($record->projectId);
        }

        Phprojekt_CompressedSender::send(
            Zend_Json_Encoder::encode(Phprojekt_Model_Converter::convertModel($record))
        );
    }

    public function postAction()
    {
        $item = Zend_Json::decode($this->getRequest()->getRawBody());
        if (!$item) {
            throw new Zend_Controller_Action_Exception('No data was received', 400);
        }

        $model = $this->_newModelObject();

        foreach ($item as $property => $value) {
            $model->$property = $value;
        }

        if ($model->recordValidate()) {
            $model->save();

            Phprojekt_CompressedSender::send(
                Zend_Json_Encoder::encode(
                    Phprojekt_Model_Converter::convertModel($model)
                )
            );
        } else {
            $errors       = $model->getError();
            $errorStrings = array();
            foreach ($errors as $error) {
                $errorStrings[] = $error['label'] . ' : ' . $error['message'];
            }
            throw new Zend_Controller_Action_Exception('Invalid Data: ' . implode(',', $errorStrings), 422);
        }
    }

    public function putAction()
    {
        if (!$id = $this->_getParam('id', false)) {
            throw new Zend_Controller_Action_Exception('No id given', 422);
        }

        $item = Zend_Json::decode($this->getRequest()->getRawBody());
        if (!$item) {
            throw new Zend_Controller_Action_Exception('No data was received', 400);
        }

        if ($item['id'] !== $id) {
            throw new Zend_Controller_Action_Exception('Can not alter the id of existing items', 501);
        }
        unset($item['id']);

        $model = $this->_newModelObject()->find($id);
        if (!$model) {
            throw new Zend_Controller_Action_Exception('Id not found', 404);
        }

        foreach ($item as $property => $value) {
            $model->$property = $value;
        }

        if ($model->recordValidate()) {
            $model->save();

            Phprojekt_CompressedSender::send(
                Zend_Json_Encoder::encode(
                    Phprojekt_Model_Converter::convertModel($model)
                )
            );
        } else {
            $errors       = $model->getError();
            $errorStrings = array();
            foreach ($errors as $error) {
                $errorStrings[] = $error['label'] . ' : ' . $error['message'];
            }
            throw new Zend_Controller_Action_Exception('Invalid Data: ' . implode(',', $errorStrings), 422);
        }
    }

    public function deleteAction()
    {
        if (!$id = $this->_getParam('id', false)) {
            throw new Zend_Controller_Action_Exception('No id given', 422);
        }

        $model = $this->_newModelObject()->find($id);
        if (!$model) {
            throw new Zend_Controller_Action_Exception('Id not found', 404);
        }

        if ($model->delete()) {
            Phprojekt_CompressedSender::send(
                Zend_Json_Encoder::encode(
                    array(
                        'type' => 'info',
                        'message' => 'Delete Successfull'
                    )
                )
            );
        } else {
            throw new Zend_Controller_Action_Exception('Delete not permitted.', 403);
        }
    }

    protected function _newModelObject()
    {
        $classname = $this->getRequest()->getModuleName() . '_Models_' . $this->getRequest()->getControllerName();
        return new $classname();
    }

    protected function _getFilterWhere($where = null)
    {
        $filters = $this->getRequest()->getParam('filters', "[]");

        $filters = Zend_Json_Decoder::decode($filters);

        if (!empty($filters)) {
            $filterClass = new Phprojekt_Filter($this->_newModelObject(), $where);
            foreach ($filters as $filter) {
                list($filterOperator, $filterField, $filterRule, $filterValue) = $filter;
                $filterOperator = Cleaner::sanitize('alpha', $filterOperator, null);
                $filterField    = Cleaner::sanitize('alpha', $filterField, null);
                $filterRule     = Cleaner::sanitize('alpha', $filterRule, null);
                if (isset($filterOperator) && isset($filterField) &&  isset($filterRule) && isset($filterValue)) {
                    $filterClass->addFilter($filterField, $filterRule, $filterValue, $filterOperator);
                }
            }
            $where = $filterClass->getWhere();
        }



        return $this->_getNewFilterWhere($where);
    }

    protected function _getNewFilterWhere($where = null) {
        $filterString = $this->getRequest()->getParam('filter', null);
        if (is_null($filterString)) {
            return $where;
        }

        $db     = Phprojekt::getInstance()->getDb();
        $parts  = array();
        $filter = Zend_Json::decode($filterString);
        foreach ($filter as $field => $filterDef) {
            $dbField = $db->quoteIdentifier(Phprojekt_ActiveRecord_Abstract::convertVarToSql($field));
            foreach ($filterDef as $operator => $value) {
                switch ($operator) {
                case '!ge':
                    $parts[] = $dbField . ' >= ' . $db->quote($this->_getFilterValue($field, $value));
                    break;
                case '!lt':
                    $parts[] = $dbField . ' < ' . $db->quote($this->_getFilterValue($field, $value));
                    break;
                default:
                    throw new Exception("Invalid operator \"$operator\"");
                    break;
                }
            }
        }

        if (!is_null($where)) {
            $where = "($where) AND ";
        } else {
            $where = "";
        }
        $where .= implode(' AND ', $parts);

        return $where;
    }

    protected function _getFilterValue($field, $value)
    {
        $model = $this->_newModelObject();
        $fieldType = $model->getInformation()->getType($field);

        switch ($fieldType) {
            case 'datetime':
                return $this->_getDatetimeFilterValue($value);
            default:
                return $value;
        }
    }

    protected function _getDatetimeFilterValue($value)
    {
        $dt = new Datetime($value);
        return $dt->format('Y-m-d H:i:s');
    }
}
