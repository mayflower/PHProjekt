<?php
/**
 * History Module Controller for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * History Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class History_IndexController extends IndexController
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
        $count      = (int) $this->getRequest()->getParam('count',     null);
        $offset     = (int) $this->getRequest()->getParam('start',     null);
        // $projectId = (int) $this->getRequest()->getParam('nodeId',    null);
        $moduleId   = (int) $this->getRequest()->getParam('moduleId',  null);
        $itemId     = (int) $this->getRequest()->getParam('itemId',    null);
        $UserId     = (int) $this->getRequest()->getParam('userId',    null);
        $moduleName = (string) $this->getRequest()->getParam('moduleName',  1);
        $startDate  = $this->getRequest()->getParam('startDate', null);
        $endDate    = $this->getRequest()->getParam('endDate',   null);

        $startDate  = Inspector::sanitize('date', $startDate, $messages, false);
        $endDate    = Inspector::sanitize('date', $endDate,   $messages, false);
        
        if (empty($moduleId) && !empty($moduleName)) {
            $moduleId = Phprojekt_Module::getId($moduleName);
        }
        
        $db = Zend_Registry::get('db');
        
        if (empty($itemId) ||empty($moduleId)) {
            echo Phprojekt_Converter_Json::convert("invalid module or item");
        }
        else {
            $history = new Phprojekt_History(array('db' => $db));
            $data = $history->getHistoryData(null, $itemId, $moduleId, $startDate, $endDate, $UserId);
            $data = array('history' => $data);
            echo Phprojekt_Converter_Json::convert($data);
        }
        
    }

}