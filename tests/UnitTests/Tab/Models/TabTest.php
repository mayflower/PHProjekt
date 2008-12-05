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
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests Tab Model class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
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
            array(0 => array('field'=> 'Label', 'message' => 'Is a required field')));
        $this->assertEquals($tabModel->__toString(), '');
    }
}
