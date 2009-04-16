<?php
/**
 * Statistic model class
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     Gustavo Solt <solt@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Statistic model class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Statistic_Models_Statistic
{
    /**
     * Get all the values for the current project and sub-projects
     * and return 3 array
     * 1. With Projects names
     * 2. With users names
     * 3. Relations Projects-User-Bookings
     *
     * @param string  $startDate Start date for make the query
     * @param string  $endDate   End date for make the query
     * @param integer $projectId Current Project
     *
     * @return array
     */
    public function getStatistics($startDate, $endDate, $projectId)
    {
        $data['data']             = array();
        $data['data']['users']    = array();
        $data['data']['projects'] = array();
        $data['data']['rows']     = array();

        // Get Sub-Projects
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, $projectId);
        $tree->setup();
        $projectsId = array(0);
        foreach ($tree as $node) {
            if ($node->id) {
                $projectsId[] = $node->id;
                $data['data']['projects'][$node->id] = str_repeat('....', $node->getDepth()) . $node->title;
            }
        }

        // Get TimeProj
        $model   = Phprojekt_Loader::getModel('Timecard', 'Timeproj');
        $records = $model->fetchAll(sprintf('(date >= "%s" AND date <= "%s") AND project_id IN (%s)',
            $startDate, $endDate, implode(",", $projectsId)));

        $users = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');

        foreach ($records as $record) {
            if (!isset($data['data']['users'][$record->ownerId])) {
                $user = $users->findUserById($record->ownerId);
                $data['data']['users'][$record->ownerId] = $user->username;
            }
            if (!isset($data['data']['rows'][$record->projectId][$record->ownerId])) {
                $data['data']['rows'][$record->projectId][$record->ownerId] = 0;
            }
            $amount = Timecard_Models_Timecard::getDiffTime($record->amount, '00:00:00');
            $data['data']['rows'][$record->projectId][$record->ownerId] += $amount;
        }

        return $data;
    }

    /**
     * Implement fetchAll for delete projects
     *
     * @param string $where The SQL where
     *
     * @return array
     */
    public function fetchAll($where)
    {
        return array();
    }
}
