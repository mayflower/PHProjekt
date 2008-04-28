<?php
/**
 * Timecard Module Controller for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Default Timecard Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Timecard_IndexController extends IndexController
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
        $count     = (int) $this->getRequest()->getParam('count',     null);
        $offset    = (int) $this->getRequest()->getParam('start',     null);
        $projectId = (int) $this->getRequest()->getParam('nodeId',    null);
        $itemId    = (int) $this->getRequest()->getParam('id',        null);
        $startDate = $this->getRequest()->getParam('startDate', null);
        $endDate   = $this->getRequest()->getParam('endDate',   null);
        
        $startDate = Inspector::sanitize('date', $startDate, $messages, false);
        $endDate   = Inspector::sanitize('date', $endDate,   $messages, false);
        
        // Date filter for timecard
        $dateFilter = array();
        
        if (!empty($startDate)) {
            $dateFilter[] = 'date >= "'.$startDate.'"';
        }
        if (!empty($endDate)) {
            $dateFilter[] = 'date <= "'.$endDate.'"';
        }
        if (count($dateFilter)) {
            $dateFilter = implode ($dateFilter, " AND " );
        }
        else {
            $dateFilter = "";
        }

        if (!empty($itemId)) {
            $records = $this->getModelObject()->fetchAll('id = ' . $itemId . $dateFilter, null, $count, $offset);
        } else if (!empty($projectId)) {
            $records = $this->getModelObject()->fetchAll('projectId = ' . $projectId . $dateFilter, null, $count, $offset);
        } else {
            $records = $this->getModelObject()->fetchAll($dateFilter, null, $count, $offset);
        }

        echo Phprojekt_Converter_Json::convert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }
    
}