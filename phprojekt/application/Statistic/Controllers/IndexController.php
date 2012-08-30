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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Statistic Module Controller.
 */
class Statistic_IndexController extends IndexController
{
   /**
     * Returns the statistics data.
     *
     * The return have
     * <pre>
     *  - users    => id and display of all the users involved
     *  - projects => id and display of all the projects involved.
     *  - rows     => pair projectId => userId - Booked minutes.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - date    <b>startDate</b> ISO start date for filter.
     *  - date    <b>endDate</b>   ISO end date for filter.
     *  - integer <b>nodeId</b>    List all the projects under nodeId.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @throws Zend_Controller_Action_Exception On error in the parameters.
     *
     * @return void
     */
    public function jsonGetStatisticAction()
    {
        $startDate = Cleaner::sanitize('date', $this->getRequest()->getParam('startDate', date("Y-m-d")));
        $endDate   = Cleaner::sanitize('date', $this->getRequest()->getParam('endDate', date("Y-m-d")));
        $projectId = (int) $this->getRequest()->getParam('nodeId', null);

        if ($startDate <= $endDate) {
            $data = $this->getModelObject()->getStatistics($startDate, $endDate, $projectId);
            Phprojekt_Converter_Json::echoConvert($data);
        } else {
            $messageTitle = Phprojekt::getInstance()->translate('Period');
            $messageDesc  = Phprojekt::getInstance()->translate('End time can not be before Start time');
            throw new Zend_Controller_Action_Exception($messageTitle . ': ' . $messageDesc, 400);
        }
    }

    /**
     * Returns the statistics data.
     *
     * Also return the Total per rows.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - date    <b>startDate</b> ISO start date for filter.
     *  - date    <b>endDate</b>   ISO end date for filter.
     *  - integer <b>nodeId</b>    List all the projects under nodeId.
     * </pre>
     *
     * The return is in CSV format.
     *
     * @return void
     */
    public function csvListAction()
    {
        $startDate = Cleaner::sanitize('date', $this->getRequest()->getParam('startDate', date("Y-m-d")));
        $endDate   = Cleaner::sanitize('date', $this->getRequest()->getParam('endDate', date("Y-m-d")));
        $projectId = (int) $this->getRequest()->getParam('nodeId', null);
        $this->setCurrentProjectId();

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

        $converter = new Phprojekt_Converter_Time();
        foreach ($data['projects'] as $projectId => $title) {
            $sumPerProject  = 0;
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
        foreach (array_keys($data['users']) as $userId) {
            if (!isset($sumPerUser[$userId])) {
                $rows[$index][] = $converter->convertMinutesToHours(0);
            } else {
                $rows[$index][] = $converter->convertMinutesToHours($sumPerUser[$userId]);
                $total = $total + $sumPerUser[$userId];
            }
        }
        $rows[$index][] = $converter->convertMinutesToHours($total);

        Phprojekt_Converter_Csv::echoConvert($rows);
    }
}
