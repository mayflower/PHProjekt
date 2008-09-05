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
     * Returns the list for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * Only return data for the current user.
     * User the params for set the month and year
     *
     * @requestparam integer count ...
     * @requestparam integer start ...
     * @requestparam integer year  Year for the list view
     * @requestparam integer month Month for the list view
     * @requestparam string  view  Type of the view for the list
     *
     * @return void
     */
    public function jsonListAction()
    {
        // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
        // so lets apply this to the query set. This is also used for loading a
        // grid on demand (initially only a part is shown, scrolling down loads what is needed).
        $count  = (int) $this->getRequest()->getParam('count', null);
        $offset = (int) $this->getRequest()->getParam('start', null);
        $year   = (int) $this->getRequest()->getParam('year', date("Y"));
        $month  = (int) $this->getRequest()->getParam('month', date("m"));
        $view   = $this->getRequest()->getParam('view', 'month');
                
        $records = $this->getModelObject()->getRecords($view, $year, $month, $count, $offset);

        echo Phprojekt_Converter_Json::convert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the detail for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam string date
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $date          = $this->getRequest()->getParam('date');
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        
        $where = sprintf('(ownerId = %d AND date = "%s")', $authNamespace->userId, $date);

        $records = $this->getModelObject()->fetchAll($where);

        echo Phprojekt_Converter_Json::convert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Returns the detail for the bookings in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam string date
     *
     * @return void
     */
    public function jsonBookingDetailAction()
    {
        $date  = $this->getRequest()->getParam('date');
        $model = Phprojekt_Loader::getModel('Timecard','Timeproj');
        
        $records = $model->getRecords($date);

        echo Phprojekt_Converter_Json::convert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }
        
   /**
     * Deletes a certain item
     *
     * If the item are already deleted or do not exist
     * return a Phprojekt_PublishedException
     * If the item is deleted, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonBookingDeleteAction()
    {
        $translate = Zend_Registry::get('translate');
        $id        = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException('ID parameter required');
        }

        $model = Phprojekt_Loader::getModel('Timecard','Timeproj')->find($id);

        if ($model instanceof Phprojekt_Model_Interface) {
            $tmp = $model->delete();
            if ($tmp === false) {
                $message = $translate->translate("The Item can't be deleted");
            } else {
                $message = $translate->translate('The Item was deleted correctly');
            }
            $return  = array('type'    => 'success',
                             'message' => $message,
                             'code'    => 0,
                             'id'      => $id);

            echo Phprojekt_Converter_Json::convert($return);
        } else {
            throw new Phprojekt_PublishedException('Item not found');
        }
    }
        
    /**
     * Creates a new timecard record with the current date and time.
     *
     * @return void
     */
    public function jsonStartAction()
    {
        $translate = Zend_Registry::get('translate');
        $model     = $this->getModelObject();
        $message   = $translate->translate('The Item was added correctly');

        $this->getRequest()->setParam('date', date("Y-m-d"));
        $this->getRequest()->setParam('startTime', date("H:i:s"));

        Default_Helpers_Save::save($model, $this->getRequest()->getParams());

        $return    = array('type'    => 'success',
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $model->id);

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Closes the first timecard record of the current date.
     *
     * @return void
     */
    public function jsonStopAction()
    {
        $translate = Zend_Registry::get('translate');
        $offset    = (int) $this->getRequest()->getParam('start', null);

        // Date filter to find the open register
        $dateFilter = array();

        $dateFilter[] = 'date = "'.date("Y-m-d").'"';
        $dateFilter[] = '(endTime = "" OR endTime is null)';
        $dateFilter = implode($dateFilter, " AND ");

        $this->getRequest()->setParam('endTime', date("H:i:s"));

        $records = $this->getModelObject()->fetchAll($dateFilter, null, 1, $offset);

        if (isset($records[0])) {
            $model = $records[0];
            Default_Helpers_Save::save($model, $this->getRequest()->getParams());
            $type    = 'success';
            $message = $translate->translate('The Item was saved correctly');
            $showId  = $model->id;
        } else {
            $type    = 'error';
            $message = $translate->translate('The Item was not found');
            $showId  = null;
        }

        $return    = array('type'    => $type,
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $showId);

        echo Phprojekt_Converter_Json::convert($return);
    }
    
    /**
     * Save a booking project
     *
     * @return void
     */
    public function jsonBookingSaveAction()
    {
        $translate = Zend_Registry::get('translate');
        $model     = Phprojekt_Loader::getModel('Timecard','Timeproj');
        $message   = $translate->translate('The Item was added correctly');

        Default_Helpers_Save::save($model, $this->getRequest()->getParams());

        $return    = array('type'    => 'success',
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $model->id);

        echo Phprojekt_Converter_Json::convert($return);
    }    
}