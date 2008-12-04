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
 * Tests for Search Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_SearchController_Test extends FrontInit
{
    /**
     * Test of json get a results
     */
    public function testJsonSeacrchAction()
    {
        $this->request->setParams(array('action' => 'jsonGetTags', 'controller' => 'Search', 'module' => 'Default'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Default/Search/jsonSearch/words/note');
        $this->request->setPathInfo('/Default/Search/jsonSearch/words/note');
        $this->request->setRequestUri('/Default/Search/jsonSearch/words/note');
        $response = $this->getResponse();        
        $this->assertTrue(strpos($response, 
            '"id":"1","moduleId":"1","moduleName":"Project","moduleLabel":"Project","firstDisplay":"test"') > 0);
    }
}
