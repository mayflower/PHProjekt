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
 * Tests for Index Controller
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Note_IndexController_Test extends FrontInit
{
    /**
     * Test of json save Note -in fact, default json save
     */
    public function testJsonSave()
    {
        $this->setRequestUrl('Note/index/jsonSave/');
        $this->request->setParam('id', null);
        $this->request->setParam('comments', 'test');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'test');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Note_IndexController::ADD_TRUE_TEXT) > 0);
    }

    /**
     * Test of json save  multiple Note
     */
    public function testJsonSaveMultiple()
    {
        $this->setRequestUrl('Note/index/jsonSaveMultiple/');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('data[1][comments]', 'test save multiple');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Note_IndexController::EDIT_MULTIPLE_TRUE_TEXT) > 0);
    }

    /**
     * Test the note deletion
     */
    public function testJsonDeleteAction()
    {
        $this->setRequestUrl('Note/index/jsonDelete/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Note_IndexController::DELETE_TRUE_TEXT) > 0);
    }
}
