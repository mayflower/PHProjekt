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
 * Tests Information Tab Model class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_TabModelInformation_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test valid method
     *
     */
    public function testTabModelsTab()
    {
        $tabModel  = new Phprojekt_Tab_Information();
        $expected  = array();
        $translate = Zend_Registry::get('translate');

        // name
        $data = array();
        $data['key']      = 'label';
        $data['label']    = $translate->translate('label');
        $data['type']     = 'text';
        $data['hint']     = $translate->translate('label');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $expected[] = $data;

        $this->assertEquals($tabModel->getFieldDefinition(), $expected);
        $this->assertEquals($tabModel->getTitles(), array());
    }
}