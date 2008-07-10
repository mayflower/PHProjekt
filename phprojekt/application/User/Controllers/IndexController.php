<?php
/**
 * User Module Controller for PHProjekt 6.0
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
 * User Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class User_IndexController extends IndexController
{
    /**
     * Return a list of all the users except the current user
     *
     * @return void
     */
    public function jsonGetUsersAction()
    {
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $where         = array();
        $where         = "status = 'A' AND id != ". (int)$authNamespace->userId;
        $object        = Phprojekt_Loader::getModel('User', 'User');
        $records       = $object->fetchAll($where);

        echo Phprojekt_Converter_Json::convert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Gets the content of a setting
     * 
     * @uses name parameter as setting key
     * 
     * @return void
     *
     */
    public function jsonGetSettingAction() {

        $value = '';

        $settingName = (string) $this->getRequest()->getParam('name', null);

        if (!empty($settingName)) {
            $setting = Phprojekt_Loader::getModel('User', 'UserModuleSetting');

            $value = $setting->getSetting($settingName);
        }
        echo Phprojekt_Converter_Json::convert($value);

    }

    /**
     * Sets the value for a setting
     * 
     * @uses name and value parameters
     * 
     * @return void
     *
     */
    public function jsonSetSettingAction() {

        $message = '';

        $settingName = (string) $this->getRequest()->getParam('name', null);
        $settingValue = (string) $this->getRequest()->getParam('value', null);

        if (!empty($settingName)) {
            $setting = Phprojekt_Loader::getModel('User', 'UserModuleSetting');

            if ($setting->setSetting($settingName, $settingValue)) {
                $return = 'Value saved successful';
            } else {
                $return = 'Value not saved. Error at saving.';
            }
        } else {
            $return = 'A key value needs to be provided';
        }

        echo Phprojekt_Converter_Json::convert($return);

    }

    /**
     * Gets the list of all settings and it is returned as an array
     * 
     * @return void
     *
     */
    public function jsonGetSettingListAction() {

        $tmp = Phprojekt_Loader::getModel('User', 'UserModuleSetting');


        echo Phprojekt_Converter_Json::convert($tmp->getList());

    }

    /**
     * Deletes the indicated setting
     * 
     * @uses name parameter 
     *
     * @return boolean
     */
    public function jsonDeleteSettingAction() {

        $message = '';

        $settingName = (string) $this->getRequest()->getParam('name', null);

        if (!empty($settingName)) {
            $setting = Phprojekt_Loader::getModel('User', 'UserModuleSetting');

            if ($setting->deleteSetting($settingName)) {
                $return = 'Value deleted successful';
            } else {
                $return = 'Value not found.';
            }
        } else {
            $return = 'A key value needs to be provided';
        }

        echo Phprojekt_Converter_Json::convert($return);

    }

}