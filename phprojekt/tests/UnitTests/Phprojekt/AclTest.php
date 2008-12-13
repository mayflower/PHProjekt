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
 * @version    $Id:$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Acls
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Nina Schmitt <nina.schmitt@mayflower.de>
 */

class Phprojekt_AclTest extends PHPUnit_Framework_TestCase
{
    /**
     * setUp method for PHPUnit. We use a shared db connection
     *
     */
    public function setUp()
    {

    }

    /**
     * This function constructs the Acl list and checks whether all Rights are
     * registered and returned correctly
     */
    public function testRegisterRights()
    {
        $acl = Phprojekt_Acl::getInstance();
        $this->assertTrue($acl->has(2));
        $this->assertFalse($acl->has(4));
        $this->assertTrue($acl->has(1));
        $this->assertTrue($acl->has(3));
        $this->assertTrue($acl->isAllowed('1', 1, 'write'));
        $this->assertTrue($acl->isAllowed('1', 2, 'write'));
        $this->assertTrue($acl->isAllowed('1', 3, 'write'));
    }
}
