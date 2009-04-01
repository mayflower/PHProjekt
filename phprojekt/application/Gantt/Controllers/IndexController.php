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
     * Return a list of projects with the info nessesary for make the gantt chart
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
        $tree = new Phprojekt_Tree_Node_Database($activeRecord, $projectId);
        $tree->setup();
        $min = gmmktime(0, 0, 0, 12, 31, 2030);
        $max = gmmktime(0, 0, 0, 1, 1, 1970);

        $rights = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');

        $data['data']['rights']["currentUser"]["write"] = true;
        foreach ($tree as $node) {
            if ($node->id != self::INVISIBLE_ROOT) {
                $key    = $node->id;
                $parent = ($node->getParentNode()) ? $node->getParentNode()->id : 0;

                if (strstr($node->startDate, '-') && strstr($node->endDate, '-')) {
                    list($startYear, $startMonth, $startDay) = split("-", $node->startDate);
                    list($endYear, $endMonth, $endDay)       = split("-", $node->endDate);

                    $start = gmmktime(10, 0, 0, $startMonth, $startDay, $startYear);
                    $end   = gmmktime(0, 0, 0, $endMonth, $endDay, $endYear);

                    if ($start < $min) {
                        $min = $start;
                    }
                    if ($end > $max) {
                        $max = $end;
                    }
                    $data['data']["projects"][] = array('id'      => $key,
                                                        'level'   => $node->getDepth() * 10,
                                                        'parent'  => $parent,
                                                        'childs'  => count($node->getChildren()),
                                                        'caption' => $node->title,
                                                        'start'   => $start,
                                                        'end'     => $end,
                                                        'startD'  => $startDay,
                                                        'startM'  => $startMonth,
                                                        'startY'  => $startYear,
                                                        'endD'    => $endDay,                                                        'startD'  => $startDay,
                                                        'endM'    => $endMonth,
                                                        'endY'    => $endYear);
                    // Only allow write if all the projects have write or hight access
                    if ($data['data']['rights']["currentUser"]["write"]) {
                        if ($rights->getItemRight(1, $node->id, Phprojekt_Auth::getUserId()) < Phprojekt_Acl::WRITE) {
                            $data['data']['rights']["currentUser"]["write"] = false;
                        }
                    }
                }
            }
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
        $projects = (array) $this->getRequest()->getParam('projects', array());
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        foreach ($projects as $project) {
            list($id, $startDate, $endDate) = split(",", $project);
            $activeRecord->find((int) $id);
            $activeRecord->startDate = $startDate;
            $activeRecord->endDate   = $endDate;
            if ($activeRecord->recordValidate()) {
                $activeRecord->save();
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
