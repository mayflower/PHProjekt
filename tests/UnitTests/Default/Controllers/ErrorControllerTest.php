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
 * Tests for Error Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_ErrorController_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test Error action 
     *
     */

    public function testErrorErrorAction()
    {
        
        // creating request and response for action
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');
        
        
        // The config needs to be open as writable.
        // Changing the debug value to force the debug
        $oldDebugValue = $config->debug;
        $config->debug = true;

        $request->setModuleName('Default');

        $request->setActionName('error');
        
        // setting the path, controller and action 
        $request->setBaseUrl($config->webpath.'index.php/');
        $request->setPathInfo('error/error');
        $request->setRequestUri('/error/error');

        $front = Zend_Controller_Front::getInstance();

        // prevent oupput
        ob_start();
        try {
            throw new Zend_Exception('This is a test', 1);
        }
        catch (Exception $e) {
            $error            = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
            $error->exception = $e;
            $request->setParam('error_handler', $error);

            $front->dispatch($request, $response);
            $response = ob_get_contents();

            ob_end_clean();
            
            // restoring the debug value
            $config->debug = $oldDebugValue;
        }             
    }
}
