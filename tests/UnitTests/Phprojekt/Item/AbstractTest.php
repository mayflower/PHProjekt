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
require_once 'PHPUnit/Extensions/ExceptionTestCase.php';

/**
 * Tests for items
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Item_AbstractTest extends PHPUnit_Extensions_ExceptionTestCase
{
    /**
     * Test set
     * Should throw an exception
     *
     * @return void
     */
    public function testWrongSet()
    {
        $item = new PHprojekt_Project(array('db' => $this->sharedFixture));
        $this->setExpectedException('Exception');
        $item->wrongAttribute = 'Hello World';
    }

    /**
     * Test set for required fields
     * Should throw an exception
     *
     * @return void
     */
    public function testRequiredFieldSet()
    {
        $item = new PHprojekt_Project(array('db' => $this->sharedFixture));
        $this->setExpectedException('Exception');
        $item->title = '';
    }

    /**
     * Test set for integer fields
     *
     * @return void
     */
    public function testIntegerFieldSet()
    {
        $item = new PHprojekt_Project(array('db' => $this->sharedFixture));
        $item->priority = 'AA';
        $this->assertEquals(0, $item->priority);

        $item = new PHprojekt_Project(array('db' => $this->sharedFixture));
        $item->priority = '7';
        $this->assertEquals(7, $item->priority);
    }
}