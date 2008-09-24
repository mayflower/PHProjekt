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
    private $_defaultSettings = array('password', 'email', 'timeZone', 'language');

    /**
     * Range of dates language setting
     *
     * @var array
     */
    private $_languageRange = array('de', 'en', 'es');

    /**
     * Range of available timezones
     *
     * @var array
     */
    private $_timeZoneRange = array(-12, -11, -10, -9, -8, -7, -6, -5, -4, -3, -2, -1, 0,
    1, 2, 3, 4, 5, 6, 7, 8, 99, 10, 11, 12);

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
    public function __construct($userId = null)
    {
        parent::__construct();
        $this->_config   = Zend_Registry::get('config');

        if (empty($userId)) {
            $this->_userId   = Phprojekt_Auth::getUserId();
        } else {
            $this->_userId = (int)$userId;
        }
        $this->_moduleId = Phprojekt_Module::getId('Project');
    }

    /**
     * Creates the default settings to be set for each user
     *
     * @return void
     */
    public function checkDefaultSettings()
    {
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
    public function getSetting($settingName)
    {
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
    public function getSettingNameById($id)
    {
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
    public function setSetting($settingName, $settingValue)
    {
        if ($this->validateSetting($settingName, $settingValue)) {

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
        } else {
            return false;
        }
    }

    /**
     * Gets a list of all settings with its values
     *
     * @param $getAll boolean get all settings instead of only default settings
     * 
     * @return array with names => values
     */
    public function getList($getAll = true)
    {
        $settingsArray = array();

        // check default settings
        $this->checkDefaultSettings();

        $record = $this->fetchAll("userId = ". $this->_db->quote($this->_userId) .
                                  " AND moduleId = ".$this->_db->quote($this->_moduleId));
        
        foreach ($record as $oneSetting) {
            
            if ($getAll || in_array($oneSetting->keyValue, $this->_defaultSettings)) {
                $data = array();
                $data['id'] = $oneSetting->id;
                $data['keyValue'] = $oneSetting->keyValue;
                if ($oneSetting->keyValue != 'password') {
                    $data['value'] = $oneSetting->value;
                } else {
                    $data['value'] = "";
                }
                $settingsArray[] = $data;
            }
        }
        return $settingsArray;
    }

    /**
     * Deletes a setting value
     *
     * @param boolean $settingName the name of the setting to be deleted
     * @return boolean it it was deleted sucessfull
     */
    public function deleteSetting($settingName)
    {
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

        if (isset($this->keyValue) && in_array($this->keyValue, $this->_defaultSettings)) {
            $tmp = '_'.$this->keyValue.'Range';
            if (!empty($this->$tmp)) {
                $data['type'] = 'selectbox';
                $data['range'] = array();

                switch ($this->keyValue) {
                    case 'timeZone':
                        $values = $this->_timeZoneRange;
                        break;
                    case 'language':
                        $values = $this->_languageRange;
                        break;
                }
                if (is_array($values)) {
                    foreach ($values as $value) {
                        $data['range'][] = array('id'=> $value, 'name' => $value);
                    }
                }
            } elseif (isset($this->keyValue) && $this->keyValue == 'password') {
                $data['type'] = 'password';
            }
        }
        $converted[] = $data;
                       
        if (isset($this->keyValue) && $this->keyValue == 'password') {
            $data = array();
            $data['key']      = 'confirmValue';
            $data['label']    = $translate->translate('Confirm Password');
            $data['type']     = 'password';
            $data['hint']     = $translate->translate('Confirm Password');
            $data['order']    = 0;
            $data['position'] = 3;
            $data['fieldset'] = '';
            $data['range']    = array('id'   => '',
                                      'name' => '');
            $data['required'] = true;
            $data['readOnly'] = false;
            $converted[] = $data;
            
            $data = array();
            $data['key']      = 'oldValue';
            $data['label']    = $translate->translate('Old Password');
            $data['type']     = 'password';
            $data['hint']     = $translate->translate('Old Password');
            $data['order']    = 0;
            $data['position'] = 4;
            $data['fieldset'] = '';
            $data['range']    = array('id'   => '',
                                      'name' => '');
            $data['required'] = true;
            $data['readOnly'] = false;
            $converted[] = $data;            
        }

        return $converted;
    }

    /**
     * Validates the settings if there is any range. Any setting not defined into a range will be valid.
     *
     * @param string $key The name of the setting to be validated
     * @param string $value the value to be validated
     *
     * @return boolean true if the value is valid for the provied key.
     */
    public function validateSetting($key, $value)
    {
        $return = true;

        switch ($key) {
            case 'timeZone':
                $return = in_array($value, $this->_timeZoneRange);
                break;
            case 'language':
                $return = in_array($value, $this->_languageRange);
                break;
            case 'password':
                $return = !empty($value);
                break;
        }
        return $return;
    }
}
