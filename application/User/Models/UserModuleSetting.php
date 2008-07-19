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
class User_Models_UserModuleSetting extends Phprojekt_ActiveRecord_Abstract
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
     * User Setting constructor
     *
     * @param int $userId - if a different user of the current user is needed
     */
    public function __construct($userId = null, $moduleId = null) {

        parent::__construct();

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
     * Gets the content of one setting key
     *
     * @param string $settingName name of the setting to be found
     * @return string value of the setting
     */
    public function getSetting($settingName) {

        $toReturn = '';

        $record = $this->fetchAll("userId = ". $this->_db->quote($this->_userId) . 
                                  " AND keyValue = ".$this->_db->quote($settingName) . 
                                  " AND moduleId = ".$this->_db->quote($this->_moduleId));
        if (!empty($record)) {
            $toReturn = $record[0]->value;
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
            $record = Phprojekt_Loader::getModel('User', 'UserModuleSetting');
            $record->userId = $this->_userId;
            $record->keyValue = $settingName;
            $record->moduleId = $this->_moduleId;
            $record->identifier = '';
        }

        $record->value = $settingValue;
        return $record->save();
    }
    
    /**
     * Gets a list of all settings with its values
     *
     * @return array with names => values
     */
    public function getList() {

        $settingsArray = array();          
        
        $record = $this->fetchAll("userId = ". $this->_db->quote($this->_userId) . 
                                  " AND moduleId = ".$this->_db->quote($this->_moduleId));
        foreach ($record as $oneSetting) {
            
            $settingsArray[$oneSetting->keyValue] = $oneSetting->value;
            
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

}