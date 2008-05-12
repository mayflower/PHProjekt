<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Module Class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_ModuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test getId
     */
    public function testGetId()
    {
        $this->assertEquals(1, Phprojekt_Module::getId('Project',1));
        $this->assertEquals(1, Phprojekt_Module::getId('Project'));

        $this->assertEquals(2, Phprojekt_Module::getId('Todo',1));
        $this->assertEquals(0, Phprojekt_Module::getId('Todo',6));
    }


    /**
     * Test getModuleName
     */
    public function testGetModuleName()
    {
        $this->assertEquals('Project', Phprojekt_Module::getModuleName(1,1));
        $this->assertEquals('Project', Phprojekt_Module::getModuleName(1));

        $this->assertEquals('Todo', Phprojekt_Module::getModuleName(2,1));
        $this->assertNull(Phprojekt_Module::getModuleName(2,6));
    }
}