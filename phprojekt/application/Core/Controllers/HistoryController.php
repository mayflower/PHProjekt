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
 * History Module Controller.
 */
class Core_HistoryController extends Core_IndexController
{
    /**
     * Returns the list of actions done in one item.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>moduleId</b> id of the module (if moduleName is sent, this is not necessary).
     *  - integer <b>itemId</b>   id of the item.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>userId</b>     To filter by user id.
     *  - string  <b>moduleName</b> Name of the module (if moduleId is sent, this is not necessary).
     *  - date    <b>startDate</b>  To filter by start date.
     *  - date    <b>endDate</b>    To filter by end date.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @throws Zend_Controller_Action_Exception On missing or wrong moduleId or itemId.
     *
     * @return void
     */
    public function jsonListAction()
    {
        $moduleId   = (int) $this->getRequest()->getParam('moduleId', null);
        $itemId     = (int) $this->getRequest()->getParam('itemId', null);
        $userId     = (int) $this->getRequest()->getParam('userId', null);
        $moduleName = Cleaner::sanitize('alnum', $this->getRequest()->getParam('moduleName', 'Default'));
        $startDate  = Cleaner::sanitize('date', $this->getRequest()->getParam('startDate', null));
        $endDate    = Cleaner::sanitize('date', $this->getRequest()->getParam('endDate', null));
        $this->setCurrentProjectId();

        if (empty($moduleId)) {
            $moduleId = Phprojekt_Module::getId($moduleName);
        }

        if (empty($itemId) || empty($moduleId)) {
            throw new Zend_Controller_Action_Exception("Invalid module or item", 400);
        } else {
            $history = new Phprojekt_History();
            $data    = $history->getHistoryData(null, $itemId, $moduleId, $startDate, $endDate, $userId);
            $data    = array('data' => $data);

            Phprojekt_Converter_Json::echoConvert($data);
        }
    }
}
