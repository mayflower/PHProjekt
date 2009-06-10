<?php
/**
 * Unit test
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests Module Model class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @group      module
 * @group      model
 * @group      module-model
 */
class Phprojekt_ModuleModelModule_Test extends PHPUnit_Framework_TestCase
{
    public function testGetInformation()
    {
        $module    = new Phprojekt_Module_Module();
        $converted = array();

        // name
        $data = array();
        $data['key']      = 'name';
        $data['label']    = Phprojekt::getInstance()->translate('Name');
        $data['type']     = 'hidden';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('name');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;

        $converted[] = $data;


        // label
        $data = array();
        $data['key']      = 'label';
        $data['label']    = Phprojekt::getInstance()->translate('Label');
        $data['type']     = 'text';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('label');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;

        $converted[] = $data;

        // active
        $data = array();
        $data['key']      = 'active';
        $data['label']    = Phprojekt::getInstance()->translate('Active');
        $data['type']     = 'selectbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('active');
        $data['order']    = 0;
        $data['position'] = 3;
        $data['fieldset'] = '';
        $data['range'][]  = array('id'   => '0',
                                  'name' => Phprojekt::getInstance()->translate('No'));
        $data['range'][]  = array('id'   => '1',
                                  'name' => Phprojekt::getInstance()->translate('Yes'));
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = true;

        $converted[] = $data;

        $this->assertEquals($converted, $module->getInformation()->getFieldDefinition());
    }

    public function testRecordValidate()
    {
        $module = new Phprojekt_Module_Module();
        $this->assertFalse($module->recordValidate());
        $this->assertEquals(2, count($module->getError()));

        $module->name     = 'Test';
        $module->label    = 'Test';
        $module->active   = 1;
        $module->saveType = 0;
        $this->assertTrue($module->recordValidate());
    }

    /**
     * Test for mock function
     */
    public function testMocks()
    {
        $module = new Phprojekt_Module_Module();
        $this->assertEquals(array(), $module->getRights());

        $this->assertEquals(array(), $module->getInformation()->getTitles());

        $module->name     = 'Test';
        $module->label    = 'Test';
        $module->active   = 1;
        $module->saveType = 0;
        $module->saveRights();
        $this->assertEquals(array(), $module->getRights());
    }
}
