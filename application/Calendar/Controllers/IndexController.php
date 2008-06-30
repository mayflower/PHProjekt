<?php
/**
 * Calendar Module Controller for PHProjekt 6.0
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
 * Default Calendar Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Calendar_IndexController extends IndexController
{
    /**
     * Saves the current item
     * Save if you are add one or edit one.
     * Use the model module for get the data
     *
     * If there is an error, the save will return a Phprojekt_PublishedException
     * If not, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $translate  = Zend_Registry::get('translate');
        $message    = $translate->translate('The Item was added correctly');
        $id         = (int)$this->getRequest()->getParam('id');

        // getting the main row if the group if an id is provided
        if (!empty($id)) {
            $message = $translate->translate('The Item was edited correctly');
        }

        $id = (int)Calendar_Models_Calendar::saveEvent($this->getRequest());

        $return    = array('type'    => 'success',
        'message' => $message,
        'code'    => 0,
        'id'      => $id);

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Returns the detail for a calendar in JSON.
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            $record = $this->getModelObject();
        } else {
            $record = $this->getModelObject();
            $record->find($id);
            $record->getAllParticipants();
        }

        echo Phprojekt_Converter_Json::convert($record, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Deletes an event, it includes all related events to this parent event
     * 
     * requestparam integer id ...
     * 
     * @return void
     */
    public function jsonDeleteAction()
    {
        $translate = Zend_Registry::get('translate');
        $id        = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException('ID parameter required');
        }

        $model = $this->getModelObject()->find($id);

        if ($model instanceof Phprojekt_Model_Interface) {
            $model->deleteRelatedEvents();
            $model->delete();
            $message = $translate->translate('The Item was deleted correctly');
            $return  = array('type'    => 'success',
            'message' => $message,
            'code'    => 0,
            'id'      => $id);

            echo Phprojekt_Converter_Json::convert($return);
        } else {
            throw new Phprojekt_PublishedException('Item not found');
        }
    }

}