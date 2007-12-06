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
 * Tests Dispatcher class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_DispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test formatControllerName
     */
    public function testFormatControllerName()
    {
        /*  Initialization */

        $dispatcher = new Phprojekt_Dispatcher();

        /* Check regular name */
        $controllerName = $dispatcher->formatControllerName('Phprojekt_Notification');
        $this->assertEquals($controllerName,'PhprojektNotification');

        /* Check name with irregular chars */
        $controllerName = $dispatcher->formatControllerName('Phprojekt_Notification()');
        $this->assertEquals($controllerName,'PhprojektNotification');

    }

}