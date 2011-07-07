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
 * Tests Tab Model class
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
 * @group      tab-model
 */
class Phprojekt_TabModelTab_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test valid method
     *
     */
    public function testTabModelsTab()
    {
        $tabModel = new Phprojekt_Tab_Tab();
        $expected = new Phprojekt_Tab_Information();
        $this->assertEquals($tabModel->getInformation(), $expected);
        $this->assertEquals($tabModel->getRights(), array());
        $this->assertEquals($tabModel->saveRights(), null);
        $this->assertEquals($tabModel->recordValidate(), false);
        $this->assertEquals($tabModel->getError(),
            array(0 => array('field'   => 'label',
                             'label'   => 'Label',
                             'message' => 'Is a required field')));
        $this->assertEquals($tabModel->__toString(), '');
    }
}
