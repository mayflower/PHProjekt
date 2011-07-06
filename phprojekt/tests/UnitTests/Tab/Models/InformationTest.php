<?php
/**
 * Unit test
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Tab
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */


/**
 * Tests Information Tab Model class
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Tab
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @group      tab
 * @group      model
 * @group      information
 * @group      tab-model
 * @group      tab-model-information
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

        // name
        $data                  = array();
        $data['key']           = 'label';
        $data['label']         = Phprojekt::getInstance()->translate('Label');
        $data['originalLabel'] = 'Label';
        $data['type']          = 'text';
        $data['hint']          = Phprojekt::getInstance()->getTooltip('label');
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

        $expected[] = $data;

        $this->assertEquals($tabModel->getFieldDefinition(), $expected);
    }
}
