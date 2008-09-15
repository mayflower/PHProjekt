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
class Core_ModuleController extends Core_IndexController
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
        } else {
            $record = $this->getModelObject()->find($id);
            $record->tabs->create();
            $tabs = Phprojekt_Tabs::getTabsByModule($id);
            $record->tabs = ",";
            foreach ($tabs as $tabData) {
                $record->tabs .= $tabData['id'].",";
            }
        }

        echo Phprojekt_Converter_Json::convert($record, Phprojekt_ModelInformation_Default::ORDERING_FORM);
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
            $message = $translate->translate(self::ADD_TRUE_TEXT);
        } else {
            $model   = $this->getModelObject()->find($id);

            $message = $translate->translate(self::EDIT_TRUE_TEXT);
        }

        $model->saveModule($this->getRequest()->getParams());

        $return    = array('type'    => 'success',
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $model->id);

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Return all global modules
     *
     * @return array
     */
    function jsonGetGlobalModulesAction()
    {
        $modules = array();
        $model   = new Phprojekt_Module_Module();
        foreach ($model->fetchAll(' active = 1 AND (saveType = 1 OR saveType = 2) ', ' name ASC ') as $module) {
            $modules['data'][$module->id] = array();
            $modules['data'][$module->id]['id']        = $module->id;
            $modules['data'][$module->id]['name']      = $module->name;
            $modules['data'][$module->id]['label']     = $module->name;
        }
        echo Phprojekt_Converter_Json::convert($modules);
    }
}