<?php
/**
 * Timecard setting model
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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Settings for the Timecard module
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Timecard_Models_TimecardSetting
{
    /**
     * Return an array of field information.
     *
     * @return array
     */
    public function getFieldDefinition()
    {
        $converted = array();
        $translate = Zend_Registry::get('translate');

        // Amount
        $data = array();
        $data['key']      = 'amount';
        $data['label']    = $translate->translate('Max Number of favorites projects');
        $data['type']     = 'text';
        $data['hint']     = $translate->translate('Max Number of favorites projects');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
                
        $converted[] = $data;
    
        // Favorites
        $data = array();
        $data['key']      = 'favorites';
        $data['label']    = $translate->translate('Favorite projects');
        $data['type']     = 'multipleselectbox';
        $data['hint']     = $translate->translate('Favorite projectst');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $result       = $activeRecord->fetchAll();
        $data['range'] = array();
        foreach ($result as $item) {
            $data['range'][] = array('id'   => $item->id,
                                     'name' => $item->title);
        }
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
                
        $converted[] = $data;
             
        return $converted;
    }     

    /**
     * getter for the "favorites" field
     *
     * @param string $value Serialized array of Ids
     * 
     * @return array
     */
    public function getFavorites($value)
    {
        return implode(",",unserialize($value));
    }
        
    /**
     * Save the settings for the timecard
     *
     * @param array $params $_POST values
     * 
     * @return void
     */
    public function setSettings($params)
    {            
        $fields = $this->getFieldDefinition();          
        foreach ($fields as $data) {
            foreach ($params as $key => $value) {
                if ($key == $data['key']) {
                    $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
                    $setting->setModule('Timecard');                    
                    if (($key == 'favorites')) {
                        $value = serialize($value);
                    }                    
                    $record = $setting->fetchAll("userId = ". Phprojekt_Auth::getUserId() .
                                                 " AND keyValue = ". $setting->_db->quote($key) .
                                                 " AND moduleId = ". Phprojekt_Module::getId('Timecard'));                        
                    if (isset($record[0])) {
                        $record[0]->keyValue = $key;
                        $record[0]->value    = $value;
                        $record[0]->save();                        
                    } else {                        
                        $setting->userId     = Phprojekt_Auth::getUserId();
                        $setting->moduleId   = Phprojekt_Module::getId('Timecard');
                        $setting->keyValue   = $key;
                        $setting->value      = $value;
                        $setting->identifier = 'Timecard';
                        $setting->save();
                    }
                }
            }
        }
    }    
}