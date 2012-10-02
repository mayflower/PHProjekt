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
 * Tests for Minutes Userlist Helper
 *
 * @version    Release: 6.1.0
 * @group      minutes
 * @group      helpers
 * @group      minutes-helpers
 */
class Minutes_Helpers_Userlist_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test the userlist helper
     */
    public function testUserlistEmptyCall()
    {
        $this->assertEquals(array(), Minutes_Helpers_Userlist::expandIdList());
    }

    public function testUserlistEmptyString()
    {
        $this->assertEquals(array(), Minutes_Helpers_Userlist::expandIdList(''));
    }

    public function testUserlistSingleId()
    {
        $data = Minutes_Helpers_Userlist::expandIdList('1');
        $this->assertEquals(array(0 => array('id' => 1, 'display' => 'Soria Parra, David')), $data);
    }

    public function testUserlistMultiId()
    {
        $data = Minutes_Helpers_Userlist::expandIdList('1,2');
        $this->assertEquals(array(0 => array('id' => 2, 'display' => 'Solt, Gustavo'),
                                  1 => array('id' => 1, 'display' => 'Soria Parra, David')), $data);
    }

    /**
     * The order of the id should not matter on the result
     */
    public function testUserlistMultiIdReverse()
    {
        $data = Minutes_Helpers_Userlist::expandIdList('2,1');
        $this->assertEquals(array(0 => array('id' => 2, 'display' => 'Solt, Gustavo'),
                                  1 => array('id' => 1, 'display' => 'Soria Parra, David')), $data);
    }

    public function testUserlistMultiArguments()
    {
        $data = Minutes_Helpers_Userlist::expandIdList('1', '2');
        $this->assertEquals(array(0 => array('id' => 2, 'display' => 'Solt, Gustavo'),
                                  1 => array('id' => 1, 'display' => 'Soria Parra, David')), $data);
    }

    /**
     * The order of the id should not matter
     */
    public function testUserlistMultiArgumentsReverse()
    {
        $data = Minutes_Helpers_Userlist::expandIdList('2', '1');
        $this->assertEquals(array(0 => array('id' => 2, 'display' => 'Solt, Gustavo'),
                                  1 => array('id' => 1, 'display' => 'Soria Parra, David')), $data);
    }

    /**
     * Duplicate ids should show as single entry in result
     */
    public function testUserlistDuplicateId()
    {
        $data = Minutes_Helpers_Userlist::expandIdList('1,1');
        $this->assertEquals(array(0 => array('id' => 1, 'display' => 'Soria Parra, David')), $data);
    }

    /**
     * Duplicate ids should show as single entry in result
     */
    public function testUserlistDuplicateIdMulti()
    {
        $data = Minutes_Helpers_Userlist::expandIdList('1', '1');
        $this->assertEquals(array(0 => array('id' => 1, 'display' => 'Soria Parra, David')), $data);
    }

    /**
     * Multiple occurances of ids should show as single entry in result
     */
    public function testUserlistStresstest()
    {
        $data = Minutes_Helpers_Userlist::expandIdList('1', '1,2', '2, 1', '2', '', '2,1,1,2');
        $this->assertEquals(array(0 => array('id' => 2, 'display' => 'Solt, Gustavo'),
                                  1 => array('id' => 1, 'display' => 'Soria Parra, David')), $data);
    }

}
