<?php
/**
 * Module Controller for PHProjekt 6.0
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
 * Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Module_IndexController extends IndexController
{
    /**
     * Returns the detail for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
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
            $ordering = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        } else {
            $record = $this->getModelObject()->find($id);
            $record->tabs->create();
            $tabs = Phprojekt_Tabs::getTabsByModule($id);
            $record->tabs = ",";
            foreach ($tabs as $tabData) {
                $record->tabs .= $tabData['id'].",";
            }
            $ordering = Phprojekt_ModelInformation_Default::ORDERING_FORM_UPDATE;
        }
        
        

        echo Phprojekt_Converter_Json::convert($record, $ordering);
    }

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
        $translate = Zend_Registry::get('translate');
        $id        = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            $model   = $this->getModelObject();
            $message = $translate->translate('The Item was added correctly');
        } else {
            $model   = $this->getModelObject()->find($id);
            
            // prevent internal name change
            // $this->getRequest()->setParam('internalName', $model->internalName);
            
            $message = $translate->translate('The Item was edited correctly');
        }

        $model->saveModule($this->getRequest()->getParams());

        $return    = array('type'    => 'success',
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $model->id);

        echo Phprojekt_Converter_Json::convert($return);
    }
}