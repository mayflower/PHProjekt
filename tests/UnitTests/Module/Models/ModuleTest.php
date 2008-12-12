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
 * @version    CVS: $Id:
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
 */
class Phprojekt_ModuleModelModule_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test valid method
     *
     */
    public function testModuleModelsModule()
    {
        $moduleModel = new Phprojekt_Module_Module();
        $expected    = new Phprojekt_Module_Information();
        $this->assertEquals($moduleModel->getInformation(), $expected);
        $this->assertEquals($moduleModel->getRights(), array());
        $this->assertEquals($moduleModel->saveModule(array('name' => 'test', 'saveType' => 0, 'active' => 1)), 7);
        $moduleModel->find(7);
        $this->assertEquals($moduleModel->recordValidate(), true);
        $this->assertEquals($moduleModel->delete(), null);
        $this->assertEquals($moduleModel->getError(), array());
        $this->assertEquals($moduleModel->delete(), null);
    }
}
