<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    Release: 6.1.0
 */


/**
 * Tests for Search Controller
 *
 * @version    Release: 6.1.0
 * @group      default
 * @group      controller
 * @group      default-controller
 */
class Phprojekt_SearchController_Test extends FrontInit
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml');
    }

    /**
     * Test of json get a results
     */
    public function testJsonSeacrchAction()
    {
        $this->setRequestUrl('Default/Search/jsonSearch/');
        $this->request->setParam('words', 'note');
        $response = $this->getResponse();
        $expected = '"id":1,"moduleId":1,"moduleName":"Project","moduleLabel":"Project","firstDisplay":"test"';
        $this->assertContains($expected, $response);
    }
}
