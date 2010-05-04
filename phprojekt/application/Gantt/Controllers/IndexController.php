<?php
/**
 * Gantt Module Controller.
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
 * @subpackage Gantt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Gantt Module Controller.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Gantt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Gantt_IndexController extends IndexController
{
    /**
     * Return a list of projects with the necessary info to make the gantt chart.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>nodeId</b> List all the items with projectId == nodeId.
     * </pre>
     *
     * The return have:
     * <pre>
     *  - projects => A list of projects.
     *  - rights   => Write access only if all the projects have write access.
     *  - min      => First startDate of all the projects.
     *  - max      => Last endDate of all the projects.
     *  - step     => Number of days in the year of the min value.
     * </pre>
     *
     * For each project in the list, the data have:
     * <pre>
     *  - id      => id of the project.
     *  - level   => Child level * 10.
     *  - parent  => id of the parent project.
     *  - childs  => Number of children.
     *  - caption => Title of the project.
     *  - start   => Timestamp of the startDate.
     *  - end     => Timestamp of the endDate.
     *  - startD  => Day of startDate.
     *  - startM  => Month of startDate.
     *  - startY  => Year of startDate.
     *  - endD    => Day of endDate.
     *  - endM    => Month of endDate.
     *  - endY    => Year of endDate.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetProjectsAction()
    {
        $projectId    = (int) $this->getRequest()->getParam('nodeId', null);
        $data['data'] = array();
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, $projectId);
        $tree         = $tree->setup();
        $min          = gmmktime(0, 0, 0, 12, 31, 2030);
        $max          = gmmktime(0, 0, 0, 1, 1, 1970);
        $ids          = array();

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
                    $key                        = (int) $key;
                    $ids[]                      = $key;
                    $data['data']["projects"][$key] = array('id'      => $key,
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
                                                            'endM'    => $endMonth,
                                                            'endY'    => $endYear);
                }
            }
        }

        // Define right access for each project
        // Also define the general write access for display the save button
        // (only if at least one project different than the parent have write or hight access)
        $data['data']['rights']["currentUser"]["write"] = false;
        if (count($ids) > 0) {
            $rights = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');
            $where  = sprintf('user_id = %d AND item_id IN (%s) AND module_id = 1', Phprojekt_Auth::getUserId(),
                implode(", ", $ids));
            $access = $rights->fetchAll($where)->toArray();
            foreach ($access as $right) {
                $itemRights = Phprojekt_Acl::convertBitmaskToArray($right['access']);
                $itemRight  = ($itemRights['write'] === true);

                // Mix the item_right with the role
                if ($itemRight) {
                    $roleRights = new Phprojekt_RoleRights($data['data']["projects"][$right['item_id']]['parent'], 1,
                        $right['item_id']);

                    $roleRightWrite  = $roleRights->hasRight('write');
                    $roleRightCreate = $roleRights->hasRight('create');
                    $roleRightAdmin  = $roleRights->hasRight('admin');

                    $mixedRight = ($roleRightWrite || $roleRightCreate || $roleRightAdmin);
                } else {
                    $mixedRight = false;
                }
                $data['data']['rights']["currentUser"][$right['item_id']] = $mixedRight;

                if ($data['data']['rights']["currentUser"]["write"] === false &&
                    $projectId != $right['item_id'] && $mixedRight) {
                    $data['data']['rights']["currentUser"]["write"] = true;
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

        // Remove index for the json data
        $data['data']["projects"] = array_values($data['data']["projects"]);

        Phprojekt_Converter_Json::echoConvert($data);
    }

    /**
     * Saves the new values of the projects dates.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - array <b>projects</b> Array with projectId,startDate and endDate by comma separated
     * </pre>
     *
     * If there is an error, the save will return a Phprojekt_PublishedException,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - code    => 0.
     *  - id      => 0.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On error in the action save or wrong parameters.
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $projects     = (array) $this->getRequest()->getParam('projects', array());
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $rights       = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');
        $userId       = Phprojekt_Auth::getUserId();
        $this->setCurrentProjectId();

        // Error check: no project received
        if (empty($projects)) {
            $label   = Phprojekt::getInstance()->translate('Projects');
            $message = Phprojekt::getInstance()->translate('No project info was received');
            throw new Phprojekt_PublishedException($label . ': ' . $message);
        }

        foreach ($projects as $project) {
            list($id, $startDate, $endDate) = explode(",", $project);

            // Check: are the three values available?
            if (empty($id) || empty($startDate) || empty($endDate)) {
                $label   = Phprojekt::getInstance()->translate('Projects');
                $message = Phprojekt::getInstance()->translate('Incomplete data received');
                throw new Phprojekt_PublishedException($label . ': ' . $message);
            }

            $id = (int) $id;
            $activeRecord->find($id);
            // Check: project id exists?
            if (empty($activeRecord->id)) {
                $label   = Phprojekt::getInstance()->translate('Project');
                $message = Phprojekt::getInstance()->translate('Id not found #') . $id;
                throw new Phprojekt_PublishedException($label . ': ' . $message);
            }

            // Check: dates are valid?
            $validStart = Cleaner::validate('date', $startDate, false);
            $validEnd   = Cleaner::validate('date', $endDate, false);
            if (!$validStart || !$validEnd) {
                $label = Phprojekt::getInstance()->translate('Project id #') . $id;
                if (!$validStart) {
                    $message = Phprojekt::getInstance()->translate('Start date invalid');
                } else {
                    $message = Phprojekt::getInstance()->translate('End date invalid');
                }
                throw new Phprojekt_PublishedException($label . ': ' . $message);
            }

            // Check: start date after end date?
            $startDateTemp = strtotime($startDate);
            $endDateTemp   = strtotime($endDate);
            if ($startDateTemp > $endDateTemp) {
                $label   = Phprojekt::getInstance()->translate('Project id #') . $id;
                $message = Phprojekt::getInstance()->translate('Start date can not be after End date');
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
