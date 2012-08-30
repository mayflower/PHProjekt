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
 */


/**
 * Tests Tab Model class
 *
 * @group      tab
 * @group      model
 * @group      tab-model
 */
class Phprojekt_TabModelTab_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test valid method
     */
    public function testTabModelsTab()
    {
        $tabModel = new Phprojekt_Tab_Tab();
        $expected = new Phprojekt_Tab_Information();
        $this->assertEquals($tabModel->getInformation(), $expected);
        $this->assertEquals($tabModel->saveRights(), null);
        $this->assertEquals($tabModel->recordValidate(), false);
        $this->assertEquals($tabModel->getError(),
            array(0 => array('field'   => 'label',
                             'label'   => 'Label',
                             'message' => 'Is a required field')));
        $this->assertEquals($tabModel->__toString(), '');
    }
}
