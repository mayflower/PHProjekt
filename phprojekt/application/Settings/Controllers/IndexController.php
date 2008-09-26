<?php
/**
 * Settings Module Controller for PHProjekt 6.0
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
 * Default Settings Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Settings_IndexController extends IndexController
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
        $message    = $translate->translate(self::ADD_TRUE_TEXT);
        $id         = (int)$this->getRequest()->getParam('id');
        $value      = $this->getRequest()->getParam('value');

        $setting    = new Phprojekt_User_UserSetting();

        $setting->find($id);

        if ($setting->validateSetting($setting->keyValue, $value)) {

            if ($setting->keyValue != 'password') {
                $setting->setSetting($setting->keyValue, $value);
                $message = $translate->translate(self::EDIT_TRUE_TEXT);
                $type = "success";
                if ($setting->keyValue == 'language') {
                    $message .= ". ".$translate->translate("Please, logout and login again on application to apply the changes.");
                }

            } else {
                $confirmValue = $this->getRequest()->getParam('confirmValue');
                $oldValue = $this->getRequest()->getParam('oldValue', null);
                
                if ($value == $confirmValue && !empty($value)) {
                    if ($setting->getSetting('password') == Phprojekt_Auth::cryptString($oldValue)) {
                        Phprojekt_Auth::setPassword($value);
                        $message = $translate->translate(self::EDIT_TRUE_TEXT);
                        $type = "success";
                    }
                    else {
                        $message = $translate->translate("The old password provided is invalid");
                        $type = "error";
                    }
                } else {
                    $message = $translate->translate("The password and confirmation are different or empty");
                    $type = "error";
                }
            }
        } else {
            $message = $translate->translate("The value for the setting is incorrect");
            $type = "error";
        }

        $return = array('type'    => $type,
        'message' => $message,
        'code'    => 0,
        'id'      => $id);

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Returns the detail for a Settings in JSON.
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $translate  = Zend_Registry::get('translate');
        $settings = new Phprojekt_User_UserSetting();
        $id       = $this->getRequest()->getParam("id");

        
        
        if (empty($id)) {
            $records  = $settings->getList();
        } elseif ($settings->getSettingNameById($id) == 'password') {
            $settings->find($id);
            $data = array();
            $data['id'] = -1;
            $data['keyValue'] = "password";
            $data['value'] = "";
            $data['confirmValue'] = "";
            $data['oldValue'] = "";
            $records = array($data);
        } else {
            //$settings->id = (int)$id;
            $settings->find($id);
            $data = array();
            $data['id'] = $settings->id;
            $data['keyValue'] = $settings->keyValue;
            $data['value'] = $settings->value;
            $records = array($data);
        }

        $metadata = $settings->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        $numRows  = array('numRows' => count($records));
        $data     = array("metadata"=> $metadata,
        "data"    => $records,
        "numRows" => count($records));
        echo Phprojekt_Converter_Json::convert($data);

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
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $model = $this->getModelObject()->find($id);

        if ($model instanceof Phprojekt_Model_Interface) {
            $model->deleteRelatedEvents();
            $model->delete();
            $message = $translate->translate(self::DELETE_TRUE_TEXT);
            $return  = array('type'    => 'success',
            'message' => $message,
            'code'    => 0,
            'id'      => $id);

            echo Phprojekt_Converter_Json::convert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

}