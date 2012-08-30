<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    Release: 6.1.0
 */


/**
 * Tests Module Model class
 *
 * @version    Release: 6.1.0
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
        $data                  = array();
        $data['key']           = 'name';
        $data['label']         = Phprojekt::getInstance()->translate('Name');
        $data['originalLabel'] = 'Name';
        $data['type']          = 'hidden';
        $data['hint']          = Phprojekt::getInstance()->getTooltip('name');
        $data['listPosition']  = 1;
        $data['formPosition']  = 1;
        $data['fieldset']      = '';
        $data['range']         = array('id'   => '',
                                       'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 255;
        $data['default']  = null;

        $converted[] = $data;


        // label
        $data                  = array();
        $data['key']           = 'label';
        $data['label']         = Phprojekt::getInstance()->translate('Label');
        $data['originalLabel'] = 'Label';
        $data['type']          = 'text';
        $data['hint']          = Phprojekt::getInstance()->getTooltip('label');
        $data['listPosition']  = 2;
        $data['formPosition']  = 2;
        $data['fieldset']      = '';
        $data['range']         = array('id'   => '',
                                       'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 255;
        $data['default']  = null;

        $converted[] = $data;

        // saveType
        $data                  = array();
        $data['key']           = 'saveType';
        $data['label']         = Phprojekt::getInstance()->translate('Type');
        $data['originalLabel'] = 'Type';
        $data['type']          = 'selectbox';
        $data['hint']          = Phprojekt::getInstance()->getTooltip('saveType');
        $data['listPosition']  = 3;
        $data['formPosition']  = 3;
        $data['fieldset']      = '';
        $data['range'][]       = array('id'          => '0',
                                      'name'         => Phprojekt::getInstance()->translate('Normal'),
                                      'originalName' => 'Normal');
        $data['range'][]       = array('id'          => '1',
                                      'name'         => Phprojekt::getInstance()->translate('Global'),
                                      'originalName' => 'Global');
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = true;
        $data['length']   = 0;
        $data['default']  = 0;

        $converted[] = $data;

        // active
        $data                  = array();
        $data['key']           = 'active';
        $data['label']         = Phprojekt::getInstance()->translate('Active');
        $data['originalLabel'] = 'Active';
        $data['type']          = 'selectbox';
        $data['hint']          = Phprojekt::getInstance()->getTooltip('active');
        $data['listPosition']  = 4;
        $data['formPosition']  = 4;
        $data['fieldset']      = '';
        $data['range'][]       = array('id'           => '0',
                                       'name'         => Phprojekt::getInstance()->translate('No'),
                                       'originalName' => 'No');
        $data['range'][]       = array('id'           => '1',
                                       'name'         => Phprojekt::getInstance()->translate('Yes'),
                                       'originalName' => 'Yes');
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = true;
        $data['length']   = 0;
        $data['default']  = 1;

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
}
