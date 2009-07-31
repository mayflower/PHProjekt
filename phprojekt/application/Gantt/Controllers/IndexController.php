<?php
/**
 * Gantt Module Controller for PHProjekt 6.0
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Gantt Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Gantt_IndexController extends IndexController
{
    /**
     * Return a list of projects with the necessary info to make the gantt chart
     *
     * @requestparam integer nodeId
     *
     * @return void
     */
    public function jsonGetProjectsAction()
    {
        $projectId    = (int) $this->getRequest()->getParam('nodeId', null);
        $data['data'] = array();
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, $projectId);
        $tree->setup();
        $min = gmmktime(0, 0, 0, 12, 31, 2030);
        $max = gmmktime(0, 0, 0, 1, 1, 1970);
        $ids = array();

        foreach ($tree as $node) {
            if ($node->id != self::INVISIBLE_ROOT) {
                $key    = $node->id;
                $parent = ($node->getParentNode()) ? $node->getParentNode()->id : 0;

                if (strstr($node->startDate, '-') && strstr($node->endDate, '-')) {
                    list($startYear, $startMonth, $startDay) = explode("-", $node->startDate);
                    list($endYear, $endMonth, $endDay)       = explode("-", $node->endDate);

                    $start = gmmktime(10, 0, 0, $startMonth, $startDay, $startYear);
                    $end   = gmmktime(0, 0, 0, $endMonth, $endDay, $endYear);

                    if ($start < $min) {
                        $min = $start;
                    }
                    if ($end > $max) {
                        $max = $end;
                    }
                    $ids[]                      = (int) $key;
                    $data['data']["projects"][] = array('id'      => (int) $key,
                                                        'level'   => (int) $node->getDepth() * 10,
                                                        'parent'  => (int) $parent,
                                                        'childs'  => (int) count($node->getChildren()),
                                                        'caption' => $node->title,
                                                        'start'   => (int) $start,
                                                        'end'     => (int) $end,
                                                        'startD'  => $startDay,
                                                        'startM'  => $startMonth,
                                                        'startY'  => $startYear,
                                                        'endD'    => $endDay,
                                                        'startD'  => $startDay,
                                                        'endM'    => $endMonth,
                                                        'endY'    => $endYear);
                }
            }
        }

        // Only allow write if all the projects have write or hight access
        $rights = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');
        $where  = sprintf('user_id = %d AND item_id IN (%s) AND module_id = 1 AND access < %d',
            Phprojekt_Auth::getUserId(), implode(", ", $ids), Phprojekt_Acl::WRITE);
        if (count($rights->fetchAll($where)) > 0) {
            $data['data']['rights']["currentUser"]["write"] = false;
        } else {
            $data['data']['rights']["currentUser"]["write"] = true;
        }

        $data['data']['min']  = gmmktime(0, 0, 0, 1, 1, date("Y", $min));
        $data['data']['max']  = gmmktime(0, 0, 0, 12, 31, date("Y", $max));
        $data['data']['step'] = (date("L", $min)) ? 366 : 365;

        if (date("Y", $min) < date("Y", $max)) {
            while (date("Y", $min) != date("Y", $max)) {
                $data['data']['step'] += (date("L", $max)) ? 366 : 365;
                $max = gmmktime(0, 0, 0, 5, 5, date("Y", $max) - 1);
            }
        }

        Phprojekt_Converter_Json::echoConvert($data);
    }

    /**
     * Save the new values of the projects dates
     *
     * @requestparam array projects
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $projects     = (array) $this->getRequest()->getParam('projects', array());
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $rights       = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');
        $userId       = Phprojekt_Auth::getUserId();

        // Error check: no project received
        if (empty($projects)) {
            $label   = Phprojekt::getInstance()->translate("Projects");
            $message = Phprojekt::getInstance()->translate("No project info was received");
            throw new Phprojekt_PublishedException($label . ': ' . $message);
        }

        foreach ($projects as $project) {
            list($id, $startDate, $endDate) = explode(",", $project);

            // Check: are the three values available?
            if (empty($id) || empty($startDate) || empty($endDate)) {
                $label   = Phprojekt::getInstance()->translate("Projects");
                $message = Phprojekt::getInstance()->translate("Incomplete data received");
                throw new Phprojekt_PublishedException($label . ': ' . $message);
            }

            $id = (int) $id;
            $activeRecord->find($id);
            // Check: project id exists?
            if (empty($activeRecord->id)) {
                $label   = Phprojekt::getInstance()->translate("Project");
                $message = Phprojekt::getInstance()->translate("Id not found #") . $id;
                throw new Phprojekt_PublishedException($label . ': ' . $message);
            }

            // Check: dates are valid?
            $validStart = Cleaner::validate('date', $startDate, false);
            $validEnd   = Cleaner::validate('date', $endDate, false);
            if (!$validStart || !$validEnd) {
                $label = Phprojekt::getInstance()->translate("Project id #") . $id;
                if (!$validStart) {
                    $message = Phprojekt::getInstance()->translate("Start date invalid");
                } else {
                    $message = Phprojekt::getInstance()->translate("End date invalid");
                }
                throw new Phprojekt_PublishedException($label . ': ' . $message);
            }

            // Check: start date after end date?
            $startDateTemp = strtotime($startDate);
            $endDateTemp   = strtotime($endDate);
            if ($startDateTemp > $endDateTemp) {
                $label   = Phprojekt::getInstance()->translate("Project id #") . $id;
                $message = Phprojekt::getInstance()->translate("Start date can not be after End date");
                throw new Phprojekt_PublishedException($label . ': ' . $message);
            }

            $activeRecord->startDate = $startDate;
            $activeRecord->endDate   = $endDate;

            if ($rights->getItemRight(1, $id, $userId) >= Phprojekt_Acl::WRITE) {
                $activeRecord->parentSave();
            }
        }

        $message = Phprojekt::getInstance()->translate(self::EDIT_MULTIPLE_TRUE_TEXT);

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => 0);

        Phprojekt_Converter_Json::echoConvert($return);
    }
}
