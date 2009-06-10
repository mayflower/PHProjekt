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
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests Dispatcher class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
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
