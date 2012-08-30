<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Statistic model class.
 */
class Statistic_Models_Statistic
{
    /**
     * Get all the values for the current project and sub-projects and return 3 array:
     * 1. With Projects names.
     * 2. With users names.
     * 3. Relations Projects-User-Bookings.
     *
     * @param string  $startDate Start date for make the query.
     * @param string  $endDate   End date for make the query.
     * @param integer $projectId Current Project ID.
     *
     * @return array Array with 'users', 'projects' and 'rows'.
     */
    public function getStatistics($startDate, $endDate, $projectId)
    {
        $data['data']             = array();
        $data['data']['users']    = array();
        $data['data']['projects'] = array();
        $data['data']['rows']     = array();

        // Get Sub-Projects
        $activeRecord = new Project_Models_Project();
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, $projectId);
        $tree         = $tree->setup();
        $projectsId   = array(0);
        foreach ($tree as $node) {
            if ($node->id) {
                $projectsId[]                        = (int) $node->id;
                $data['data']['projects'][$node->id] = $node->getDepthDisplay('title');
            }
        }

        // Get Timecard
        $model = new Timecard_Models_Timecard();
        $where = sprintf('(DATE(start_datetime) >= %s AND DATE(start_datetime) <= %s AND project_id IN (%s))',
            $model->_db->quote($startDate), $model->_db->quote($endDate), implode(", ", $projectsId));
        $records = $model->fetchAll($where);

        $users = new Phprojekt_User_User();

        foreach ($records as $record) {
            if (!isset($data['data']['users'][$record->ownerId])) {
                $user = $users->findUserById($record->ownerId);
                $data['data']['users'][$record->ownerId] = $user->username;
            }
            if (!isset($data['data']['rows'][$record->projectId][$record->ownerId])) {
                $data['data']['rows'][$record->projectId][$record->ownerId] = 0;
            }
            $data['data']['rows'][$record->projectId][$record->ownerId] += $record->minutes;
        }

        return $data;
    }
}
