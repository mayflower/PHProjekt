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
 * Tests User Model class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class User_User_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test valid method
     */
    public function testUserModelsUser()
    {
        $userModel = new Phprojekt_User_User();
        $userModel->find(1);
        $this->assertEquals($userModel->saveRights(), null);
        $this->assertEquals($userModel->recordValidate(), false);
        $this->assertEquals($userModel->getError(), array(0 => array('field' => 'Firstname', 'message' => 'Is a required field'),
                                                          1 => array('field' => 'Lastname', 'message' => 'Is a required field')));
    }
}
