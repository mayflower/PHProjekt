<?php
/**
 * History Module Controller for PHProjekt 6.0
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
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * History Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Core_HistoryController extends Core_IndexController
{
    /**
     * Returns the list for a Timecard in JSON.
     *
     * @requestparam integer count ...
     * @requestparam integer start ...
     * @requestparam date startDate to limit the list start date
     * @requestparam date endDate to limit the list end date
     *
     * @return void
     */
    public function jsonListAction()
    {
        // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
        // so lets apply this to the query set. This is also used for loading a
        // grid on demand (initially only a part is shown, scrolling down loads what is needed).
        $messages   = null;
        $moduleId   = (int) $this->getRequest()->getParam('moduleId', null);
        $itemId     = (int) $this->getRequest()->getParam('itemId', null);
        $userId     = (int) $this->getRequest()->getParam('userId', null);
        $moduleName = (string) $this->getRequest()->getParam('moduleName', 'Default');
        $startDate  = $this->getRequest()->getParam('startDate', null);
        $endDate    = $this->getRequest()->getParam('endDate', null);

        $startDate  = Cleaner::sanitize('date', $startDate, $messages, false);
        $endDate    = Cleaner::sanitize('date', $endDate, $messages, false);

        if (empty($moduleId)) {
            $moduleId = Phprojekt_Module::getId($moduleName);
        }

        if (empty($itemId) || empty($moduleId)) {
            throw new Phprojekt_PublishedException("Invalid module or item");
        } else {
            $db      = Zend_Registry::get('db');
            $history = new Phprojekt_History(array('db' => $db));
            $data    = $history->getHistoryData(null, $itemId, $moduleId, $startDate, $endDate, $userId);
            $data    = array('data' => $data);
            echo Phprojekt_Converter_Json::convert($data);
        }
    }
}
