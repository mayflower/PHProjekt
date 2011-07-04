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
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Nina Schmitt <nina.schmitt@mayflower.de>
 */


/**
 * Tests for Acls
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Nina Schmitt <nina.schmitt@mayflower.de>
 * @group      phprojekt
 * @group      acl
 * @group      phprojekt-acl
 */

class Phprojekt_AclTest extends PHPUnit_Framework_TestCase
{
    /**
     * This function constructs the Acl list and checks whether all Rights are
     * registered and returned correctly
     *
     * @expectedException Zend_Acl_Exception
     */
    public function testRegisterRights()
    {
        $acl = Phprojekt_Acl::getInstance();
        $this->assertTrue($acl->has(2));
        $this->assertFalse($acl->has(4));
        $this->assertTrue($acl->has(1));
        $this->assertFalse($acl->has(3));
        $this->assertTrue($acl->isAllowed('1', 1, 'write'));
        $this->assertTrue($acl->isAllowed('1', 2, 'write'));
        $this->assertTrue($acl->isAllowed('1', 3, 'write'));
    }
}
