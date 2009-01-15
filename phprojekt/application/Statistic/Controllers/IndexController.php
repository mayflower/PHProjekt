<?php
/**
 * Statistic Module Controller for PHProjekt 6.0
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
 * @version    $Id:$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Statistic Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Statistic_IndexController extends IndexController
{
   /**
     * Returns an array with the statistics data
     *
     * @requestparam integer startDate Start date for the query
     * @requestparam integer endDate   End date for the query
     * @requestparam integer nodeId    Current project Id
     *
     * @return void
     */
    public function jsonGetStatisticAction()
    {
        $startDate = Cleaner::sanitize('date', $this->getRequest()->getParam('startDate', date("Y-m-d")));
        $endDate   = Cleaner::sanitize('date', $this->getRequest()->getParam('endDate', date("Y-m-d")));
        $projectId = (int) $this->getRequest()->getParam('nodeId', null);

        $data = $this->getModelObject()->getStatistics($startDate, $endDate, $projectId);

        echo Phprojekt_Converter_Json::convert($data);
    }

    /**
     * Returns the data for export
     *
     * @return void
     */
    public function csvListAction()
    {
        $startDate = Cleaner::sanitize('date', $this->getRequest()->getParam('startDate', date("Y-m-d")));
        $endDate   = Cleaner::sanitize('date', $this->getRequest()->getParam('endDate', date("Y-m-d")));
        $projectId = (int) $this->getRequest()->getParam('nodeId', null);

        $data = $this->getModelObject()->getStatistics($startDate, $endDate, $projectId);
        $data = $data['data'];

        $rows       = array();
        $sumPerUser = array();
        $index      = 0;

        $rows[$index][] = 'Project';
        foreach ($data['users'] as $name) {
            $rows[$index][] = $name;
        }
        $rows[$index][] = 'Total';
        $index++;

        $converter = Phprojekt_Loader::getLibraryClass('Phprojekt_Date_Converter');
        foreach ($data['projects'] as $projectId => $title) {
            $userData      = array();
            $sumPerProject = 0;
            $rows[$index][] = $title;
            foreach (array_keys($data['users']) as $userId) {
                if (!isset($data['rows'][$projectId][$userId])) {
                    $rows[$index][] = $converter->convertMinutesToHours(0);
                } else {
                    $rows[$index][] = $converter->convertMinutesToHours($data['rows'][$projectId][$userId]);
                    $sumPerProject  = $sumPerProject + $data['rows'][$projectId][$userId];
                    if (!isset($sumPerUser[$userId])) {
                        $sumPerUser[$userId] = 0;
                    }
                    $sumPerUser[$userId] = $sumPerUser[$userId] + $data['rows'][$projectId][$userId];
                }
            }
            $rows[$index][] = $converter->convertMinutesToHours($sumPerProject);
            $index++;
        }

        $rows[$index][] = 'Total';
        $total          = 0;
        $totalPerUser   = array();
        foreach (array_keys($data['users']) as $userId) {
            if (!isset($sumPerUser[$userId])) {
                $rows[$index][] = $converter->convertMinutesToHours(0);
            } else {
                $rows[$index][] = $converter->convertMinutesToHours($sumPerUser[$userId]);
                $total = $total + $sumPerUser[$userId];
            }
        }
        $rows[$index][] = $converter->convertMinutesToHours($total);

        Phprojekt_Converter_Csv::convert($rows);
    }
}
