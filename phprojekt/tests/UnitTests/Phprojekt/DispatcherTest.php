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
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */


/**
 * Tests Dispatcher class
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @group      phprojekt
 * @group      dispatcher
 * @group      phprojekt-dispatcher
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
        $this->assertEquals($controllerName, 'PhprojektNotification');

        /* Check name with irregular chars */
        $controllerName = $dispatcher->formatControllerName('Phprojekt_Notification()');
        $this->assertEquals($controllerName, 'PhprojektNotification');
    }
}
