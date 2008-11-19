<?php
/**
 * Convert a model into a json structure.
 * This is usually done by a controller to send data to the
 * client
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @version    CVS: $Id: Interface.php 635 2008-04-02 19:32:05Z david $
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Convert a model into a json structure.
 * This is usally done by a controller to send data to the client.
 * The Phprojekt_Convert_Json takes care that a apporpriate structure
 * is made from the given model.
 *
 * The fields are hardcore.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Phprojekt_User_Information extends EmptyIterator implements Phprojekt_ModelInformation_Interface
{
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
        $data['key']      = 'username';
        $data['label']    = $translate->translate('username');
        $data['type']     = 'text';
        $data['hint']     = $translate->translate('username');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
                
        $converted[] = $data;
        
        if ($ordering == Phprojekt_ModelInformation_Default::ORDERING_FORM) {
            // password
            $data = array();
            $data['key']      = 'password';
            $data['label']    = $translate->translate('password');
            $data['type']     = 'password';
            $data['hint']     = $translate->translate('password');
            $data['order']    = 0;
            $data['position'] = 2;
            $data['fieldset'] = '';
            $data['range']    = array('id'   => '',
                                      'name' => '');
            $data['required'] = true;
            $data['readOnly'] = false;
            $data['tab']      = 1;
            
            $converted[] = $data;
        }

        // firstname
        $data = array();
        $data['key']      = 'firstname';
        $data['label']    = $translate->translate('firstname');
        $data['type']     = 'text';
        $data['hint']     = $translate->translate('firstname');
        $data['order']    = 0;
        $data['position'] = 3;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        
        $converted[] = $data;

        // lastname
        $data = array();
        $data['key']      = 'lastname';
        $data['label']    = $translate->translate('lastname');
        $data['type']     = 'text';
        $data['hint']     = $translate->translate('lastname');
        $data['order']    = 0;
        $data['position'] = 4;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
                
        $converted[] = $data;

        // email
        if ($ordering == Phprojekt_ModelInformation_Default::ORDERING_FORM) {
            $data = array();
            $data['key']      = 'email';
            $data['label']    = $translate->translate('email');
            $data['type']     = 'text';
            $data['hint']     = $translate->translate('email');
            $data['order']    = 0;
            $data['position'] = 5;
            $data['fieldset'] = '';
            $data['range']    = array('id'   => '',
                                      'name' => '');
            $data['required'] = true;
            $data['readOnly'] = false;
            $data['tab']      = 1;            
            
            $converted[] = $data;
    
            // language
            $data = array();
            $data['key']      = 'language';
            $data['label']    = $translate->translate('language');
            $data['type']     = 'selectbox';
            $data['hint']     = $translate->translate('language');
            $data['order']    = 0;
            $data['position'] = 6;
            $data['fieldset'] = '';
            $data['range']    = array(array('id'   => 'es',
                                            'name' => 'Spanish'),
                                      array('id'   => 'en',
                                            'name' => 'English'),
                                            array('id'   => 'de',
                                            'name' => 'German'));
            $data['required'] = true;
            $data['readOnly'] = false;
            $data['tab']      = 1;            
            
            $converted[] = $data;
            
            // timeZone
            $data = array();
            $data['key']      = 'timeZone';
            $data['label']    = $translate->translate('timeZone');
            $data['type']     = 'selectbox';
            $data['hint']     = $translate->translate('timeZone');
            $data['order']    = 0;
            $data['position'] = 7;
            $data['fieldset'] = '';
            $data['range'] = array();
            for ($i = -12; $i <= 12; $i++) {
                $tmp = array();
                $tmp['id'] = $i;
                $tmp['name'] = $i;
                $data['range'][] = $tmp;
            }
            $data['required'] = true;
            $data['readOnly'] = false;
            $data['tab']      = 1;            
            
            $converted[] = $data;
        }

        // status
        $data = array();
        $data['key']      = 'status';
        $data['label']    = $translate->translate('status');
        $data['type']     = 'selectbox';
        $data['hint']     = $translate->translate('status');
        $data['order']    = 0;
        $data['position'] = 8;
        $data['fieldset'] = '';
        $data['range']    = array(array('id'   => 'A',
                                        'name' => 'Active'),
                                  array('id'   => 'I',
                                        'name' => 'Inactive'));
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;        
        
        $converted[] = $data;

        return $converted;
    }

    /**
     * Return an array with titles to simplify things
     *
     * @param integer $ordering An ordering constant (ORDERING_DEFAULT, etc)
     *
     * @return array
     */
    public function getTitles($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $result = array();
        return $result;
    }
}