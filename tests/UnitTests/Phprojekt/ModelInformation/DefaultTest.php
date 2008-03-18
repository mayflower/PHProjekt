<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests Phprojekt Model Information Default class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_ModelInformation_DefaultTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test get field definition
     *
     */
    public function testGetFieldDefinition()
    {
        
        // empty values
        $default_form = array (0 => array (
                                'key'      => '',
                                'label'    => '',
                                'type'     => 'string',
                                'hint'     => '',
                                'order'    => 0,
                                'position' => 0,
                                'fieldset' => null,
                                'range'    => '',
                                'required' => false,
                                'right'    => 'write',
                                'readOnly' => false),
                                );
        
        $object = new Phprojekt_ModelInformation_Default();
        
        $records = $object->getFormFields(); 

        $this->assertEquals($records, $default_form);
        
        $records = $object->getListFields(); 
        
        $this->assertEquals($records, $default_form);
        
        $records = $object->getTitles();
        
        $this->assertEquals($records, '');
        
        $records = $object->getTitles(MODELINFO_ORD_FORM);
        
        $this->assertEquals($records, '');
    }

}