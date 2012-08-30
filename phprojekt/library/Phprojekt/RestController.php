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
        sscanf($range, 'items=%d-%d', $start, $end);
        $count     = $end - $start + 1;
        $sort      = $this->getRequest()->getParam('sort', null);
        $recursive = $this->getRequest()->getParam('recursive', 'false');
        $recursive = $recursive === 'true';
        $model     = $this->newModelObject();
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
            $where       = $this->getFilterWhere();
            $records     = $tree->getRecordsFor($model, $count, $start, $where, $sort);
            $recordCount = $tree->getRecordsCount($model, $where);
        } else {
            if (!empty($projectId) && $model->hasField('projectId')) {
                $where  = Phprojekt::getInstance()->getDb()->quoteInto('project_id = ?', (int) $projectId);
            } else {
                $where = null;
            }

            $where       = $this->getFilterWhere($where);
            $records     = $model->fetchAll($where, $sort, $count, $start);
            $recordCount = $model->count($where);
        }

        $end = min($end, $recordCount);
        $this->getResponse()->setHeader('Content-Range', "items {$start}-{$end}/{$recordCount}");
        Phprojekt_CompressedSender::send(
            Zend_Json::encode(Phprojekt_Model_Converter::convertModels($records))
        );
    }

    public function getAction()
    {
        $id = (int) $this->_getParam('id');
        $record = $this->newModelObject();
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
        throw new Zend_Controller_Action_Exception('Not implemented!', 501);
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

        $model = $this->newModelObject()->find($id);
        if (!$model) {
            $this->getResponse()->setHttpResponseCode(404);
            echo "item with id $id not found";
            return;
        }

        foreach ($item as $property => $value) {
            $model->$property = $value;
        }
        $model->save();

        Phprojekt_CompressedSender::send(
            Zend_Json_Encoder::encode($model->toArray())
        );
    }

    public function deleteAction()
    {
        throw new Zend_Controller_Action_Exception('Not implemented!', 501);
    }

    protected function newModelObject()
    {
        $classname = $this->getRequest()->getModuleName() . '_Models_' . $this->getRequest()->getControllerName();
        return new $classname();
    }

    protected function getFilterWhere($where = null)
    {
        $filters = $this->getRequest()->getParam('filters', "[]");

        $filters = Zend_Json_Decoder::decode($filters);

        if (!empty($filters)) {
            $filterClass = new Phprojekt_Filter($this->newModelObject(), $where);
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

        return $where;
    }
}
