<?php
/**
 * Default REST Controller.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * Default REST Controller.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
abstract class Phprojekt_RestController extends Zend_Rest_Controller
{
    public function init()
    {
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function indexAction()
    {
        $projectId = (int) $this->getRequest()->getParam('projectId', null);
        $range     = $this->getRequest()->getHeader('range');
        sscanf($range, 'items=%d-%d', $start, $end);
        $count     = $end - $start + 1;
        $sort      = $this->getRequest()->getParam('sort', null);
        $recursive = $this->getRequest()->getParam('recursive', 'false');
        $recursive = $recursive === 'true' ? true : false;
        $model     = $this->newModelObject();

        if (empty($projectId) && $model->hasField('projectId')) {
            throw new Phprojekt_PublishedException('projectId not given for non-global module');
        } else if (!empty($projectId) && !$model->hasField('projectId')) {
            throw new Phprojekt_PublishedException('projectId given for global module');
        }

        if ($recursive && !$model->hasField('projectId')) {
            throw new Phprojekt_PublishedException('recursive listing is only supported on non-global modules');
        }

        $records     = array();
        $recordCount = 0;
        if ($recursive) {
            $tree = new Phprojekt_Tree_Node_Database(new Project_Models_Project(), $projectId);
            $tree->setup();
            $records     = $tree->getRecordsFor($model, $sort, $count, $start, $this->getFilterWhere());
            $recordCount = $tree->getRecordsCount($model);
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

        foreach ($records as $k => $v) {
            $records[$k] = $v->toArray();
        }

        $end = min($end, $recordCount);
        $this->getResponse()->setHeader('Content-Range', "items {$start}-{$end}/{$recordCount}");
        echo Zend_Json_Encoder::encode($records);
    }

    public function getAction()
    {
        $id = (int) $this->_getParam('id');
        $record = $this->newModelObject();
        if (!empty($id)) {
            $record = $record->find($id);
        }

        echo Zend_Json_Encoder::encode($record->toArray());
    }

    public function postAction()
    {
        throw new Exception('Not implemented!');
    }

    public function putAction()
    {
        throw new Exception('Not implemented!');
    }

    public function deleteAction()
    {
        throw new Exception('Not implemented!');
    }

    protected function newModelObject()
    {
        $classname = $this->getRequest()->getModuleName() . '_Models_' . $this->getRequest()->getControllerName();
        return new $classname();
    }

    protected function getFilterWhere($where = null)
    {
        $filters = $this->getRequest()->getParam('filters', "[]");

        $filters = json_decode($filters);

        if (!empty($filters)) {
            $filterClass = new Phprojekt_Filter($this->getModelObject(), $where);
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
