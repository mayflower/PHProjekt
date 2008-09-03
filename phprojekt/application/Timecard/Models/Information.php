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
class Timecard_Models_Information extends EmptyIterator implements Phprojekt_ModelInformation_Interface
{
    /**
     * Return an array of field information.
     *
     * @param string $ordering Type of view
     *
     * @return array
     */
    public function getFieldDefinition($ordering = 'month')
    {
        $converted = array();
        $translate = Zend_Registry::get('translate');

        switch ($ordering) {
        	case 'today':
        	default:
                // Sum of hours
                $data = array();
                $data['key']      = 'startTime';
                $data['label']    = $translate->translate('startTime');
                $data['type']     = 'time';
                $data['hint']     = $translate->translate('startTime');
                $data['order']    = 0;
                $data['position'] = 2;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = true;
                $data['readOnly'] = false;
                $converted[] = $data;

                // Sum of hours
                $data = array();
                $data['key']      = 'endTime';
                $data['label']    = $translate->translate('endTime');
                $data['type']     = 'time';
                $data['hint']     = $translate->translate('endTime');
                $data['order']    = 0;
                $data['position'] = 3;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = false;
                $data['readOnly'] = false;
                $converted[] = $data;
                break;
        	case 'month':
                // date
                $data = array();
                $data['key']      = 'date';
                $data['label']    = $translate->translate('Date');
                $data['type']     = 'date';
                $data['hint']     = $translate->translate('Date');
                $data['order']    = 0;
                $data['position'] = 1;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                  'name' => '');
                $data['required'] = true;
                $data['readOnly'] = true;
                $converted[] = $data;
        
        		// Sum of hours
                $data = array();
                $data['key']      = 'sum';
                $data['label']    = $translate->translate('Sum');
                $data['type']     = 'time';
                $data['hint']     = $translate->translate('Sum');
                $data['order']    = 0;
                $data['position'] = 2;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = true;
                $data['readOnly'] = true;
                $converted[] = $data;

                // Sum of Bookinks
                $data = array();
                $data['key']      = 'bookings';
                $data['label']    = $translate->translate('Bookings');
                $data['type']     = 'time';
                $data['hint']     = $translate->translate('Bookings');
                $data['order']    = 0;
                $data['position'] = 3;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = true;
                $data['readOnly'] = true;
                $converted[] = $data;                
                break;
        }

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