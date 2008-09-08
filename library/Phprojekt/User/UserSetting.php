<?php
/**
 * User setting model
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <eduardo.polidor@mayflower.de>
 */

/**
 * Settings on a per user base
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <eduardo.polidor@mayflower.de>
 */
class Phprojekt_User_UserSetting extends Phprojekt_ActiveRecord_Abstract
{

    /**
     * User Id related with settings - by default the session userId
     *
     * @var int
     */
    private $_userId = null;

    /**
     * module Id related with settings - by default the id of Project module
     *
     * @var int
     */
    private $_moduleId = null;

    /**
     * Default settings to be created for each user
     *
     * @var array
     */
    private $_defaultSettings = array('timeZone', 'language');

    /**
     * Config for inicializes children objects
     *
     * @var array
     */
    protected $_config = null;


    /**
     * User Setting constructor
     *
     * @param int $userId - if a different user of the current user is needed
     */
    public function __construct($userId = null, $moduleId = null) {

        parent::__construct();

        $this->_config = Zend_Registry::get('config');

        if (empty($userId)) {
            $userId = Phprojekt_Auth::getUserId();
        }

        if (empty($moduleId)) {
            $moduleId = Phprojekt_Module::getId('Project');
        }

        $this->_userId = (int)$userId;

        $this->_moduleId = (int)$moduleId;



    }

    /**
     * Creates the default settings to be set for each user
     *
     * @return void
     */
    public function checkDefaultSettings() {

        foreach ($this->_defaultSettings as $oneSettingKey) {
            $value = '';
            $tmp = $this->getSetting($oneSettingKey);

            if (empty($tmp)) {

                if (!empty($this->_config->$oneSettingKey)) {
                    $value = $this->_config->$oneSettingKey;
                }
                $this->setSetting($oneSettingKey, $value);
            }
        }
    }

    /**
     * Gets the content of one setting key
     *
     * @param string $settingName name of the setting to be found
     * @return string value of the setting
     */
    public function getSetting($settingName) {
     
    	// Cache the settings for this user
        $userSettingsNamespace = new Zend_Session_Namespace('UserSetting'.$this->_userId);
        if (isset($userSettingsNamespace->$settingName)) {
        	$toReturn = $userSettingsNamespace->$settingName;
        } else {
            $toReturn = '';
            $record = $this->fetchAll("userId = ". $this->_db->quote($this->_userId) .
                                      " AND keyValue = ".$this->_db->quote($settingName) .
                                      " AND moduleId = ".$this->_db->quote($this->_moduleId));
            if (!empty($record)) {
                $toReturn = $record[0]->value;
                $userSettingsNamespace->$settingName = $toReturn;
            }
        }
        return $toReturn;
    }

    /**
     * Gets the name of a setting based on a setting label
     *
     * @param integer $id name of the setting to be found
     * @return string name of the setting
     */
    public function getSettingNameById($id) {
        $toReturn = '';
        $record = $this->fetchAll("userId = ". $this->_db->quote($this->_userId) .
                                  " AND id = ".$this->_db->quote($id));
        if (!empty($record)) {
            $toReturn = $record[0]->keyValue;
        }
        return $toReturn;
    }

    /**
     * Sets the content of a setting. If it doesn't exist it will be added
     *
     * @param string $settingName the key of the setting
     * @param string $settingValue the value to be stored
     * @return boolean if it was saved or not.
     */
    public function setSetting($settingName, $settingValue) {
        $record = $this->fetchAll("userId = ". $this->_db->quote($this->_userId) .
                                  " AND keyValue = ".$this->_db->quote($settingName) .
                                  " AND moduleId = ".$this->_db->quote($this->_moduleId));
        if (!empty($record)) {
            $record = $record[0];
        } else {
            $record = new Phprojekt_User_UserSetting();
            $record->userId = $this->_userId;
            $record->keyValue = $settingName;
            $record->moduleId = $this->_moduleId;
            $record->identifier = '';
        }

        $record->value = $settingValue;
           
        $userSettingsNamespace = new Zend_Session_Namespace('UserSetting'.$this->_userId);
        $userSettingsNamespace->$settingName = $settingValue;
            
        return $record->save();
    }

    /**
     * Gets a list of all settings with its values
     *
     * @return array with names => values
     */
    public function getList() {

        $settingsArray = array();

        // check default settings
        $this->checkDefaultSettings();

        $record = $this->fetchAll("userId = ". $this->_db->quote($this->_userId) .
        " AND moduleId = ".$this->_db->quote($this->_moduleId));
        foreach ($record as $oneSetting) {

            $data = array();
            $data['id'] = $oneSetting->id;
            $data['keyValue'] = $oneSetting->keyValue;
            $data['value'] = $oneSetting->value;


            $settingsArray[] = $data;

        }

        return $settingsArray;
    }

    /**
     * Deletes a setting value
     *
     * @param boolean $settingName the name of the setting to be deleted
     * @return boolean it it was deleted sucessfull
     */
    public function deleteSetting($settingName) {

        $record = $this->fetchAll("userId = ". $this->_db->quote($this->_userId) .
        " AND keyValue = ".$this->_db->quote($settingName) .
        " AND moduleId = ".$this->_db->quote($this->_moduleId));
        if (!empty($record)) {
            $record = $record[0];
            $record->delete();
            $return = true;
        } else {
            $return = false;
        }

        return $return;
    }

    /**
     * Return an array of field information.
     *
     * @param integer $ordering An ordering constant
     *
     * @return array
     */
    public function getFieldDefinition($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $converted = array();
        $translate = Zend_Registry::get('translate');

        // username
        $data = array();
        $data['key']      = 'keyValue';
        $data['label']    = $translate->translate('keyValue');
        $data['type']     = 'text';
        $data['hint']     = $translate->translate('keyValue');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
        'name' => '');
        $data['required'] = true;
        $data['readOnly'] = true;
        $converted[] = $data;

        // value
        $data = array();
        $data['key']      = 'value';
        $data['label']    = $translate->translate('value');
        $data['type']     = 'text';
        $data['hint']     = $translate->translate('value');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
        'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $converted[] = $data;

        return $converted;
    }

}