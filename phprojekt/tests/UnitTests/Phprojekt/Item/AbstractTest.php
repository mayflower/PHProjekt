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
 */


class Customized_Project extends Project_Models_Project
{
    public function validatePriority($value)
    {
        if ($value > 0) {
            return null;
        } else {
            return 'Bad priority';
        }
    }
}

/**
 * Tests for items
 *
 * @group      phprojekt
 * @group      item
 * @group      phprojekt-item
 * @group      activerecord
 */
class Phprojekt_Item_AbstractTest extends DatabaseTest
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml');
    }

    /**
     * setUp method for PHPUnit. We use a shared db connection
     */
    public function setUp()
    {
        parent::setUp();
        $this->_emptyResult = array();

        $this->_formResult = array(
            'projectId' => array(
                'id'                => '',
                'tableName'         => '',
                'tablefield'        => '',
                'formTab'           => '',
                'formLabel'         => '',
                'formType'          => '',
                'formPosition'      => '',
                'formColumns'       => '',
                'formRegexp'        => '',
                'formRange'         => '',
                'defaultValue'      => '',
                'listPosition'      => '',
                'listAlign'         => '',
                'listUseFilter'     => '',
                'altPosition'       => '',
                'status'            => '',
                'isInteger'         => '',
                'isRequired'        => '',
                'isUnique'          => ''),
            'title' => array(
                'id'                => '',
                'tableName'         => '',
                'tablefield'        => '',
                'formTab'           => '',
                'formLabel'         => '',
                'formType'          => '',
                'formPosition'      => '',
                'formColumns'       => '',
                'formRegexp'        => '',
                'formRange'         => '',
                'defaultValue'      => '',
                'listPosition'      => '',
                'listAlign'         => '',
                'listUseFilter'     => '',
                'altPosition'       => '',
                'status'            => '',
                'isInteger'         => '',
                'isRequired'        => '',
                'isUnique'          => ''),
            'notes'     => array(
                'id'                => '',
                'tableName'         => '',
                'tablefield'        => '',
                'formTab'           => '',
                'formLabel'         => '',
                'formType'          => '',
                'formPosition'      => '',
                'formColumns'       => '',
                'formRegexp'        => '',
                'formRange'         => '',
                'defaultValue'      => '',
                'listPosition'      => '',
                'listAlign'         => '',
                'listUseFilter'     => '',
                'altPosition'       => '',
                'status'            => '',
                'isInteger'         => '',
                'isRequired'        => '',
                'isUnique'          => ''),
            'startDate' => array(
                'id'                => '',
                'tableName'         => '',
                'tablefield'        => '',
                'formTab'           => '',
                'formLabel'         => '',
                'formType'          => '',
                'formPosition'      => '',
                'formColumns'       => '',
                'formRegexp'        => '',
                'formRange'         => '',
                'defaultValue'      => '',
                'listPosition'      => '',
                'listAlign'         => '',
                'listUseFilter'     => '',
                'altPosition'       => '',
                'status'            => '',
                'isInteger'         => '',
                'isRequired'        => '',
                'isUnique'          => ''),
            'endDate' => array(
                'id'                => '',
                'tableName'         => '',
                'tablefield'        => '',
                'formTab'           => '',
                'formLabel'         => '',
                'formType'          => '',
                'formPosition'      => '',
                'formColumns'       => '',
                'formRegexp'        => '',
                'formRange'         => '',
                'defaultValue'      => '',
                'listPosition'      => '',
                'listAlign'         => '',
                'listUseFilter'     => '',
                'altPosition'       => '',
                'status'            => '',
                'isInteger'         => '',
                'isRequired'        => '',
                'isUnique'          => ''),
            'priority'  => array(
                'id'                => '',
                'tableName'         => '',
                'tablefield'        => '',
                'formTab'           => '',
                'formLabel'         => '',
                'formType'          => '',
                'formPosition'      => '',
                'formColumns'       => '',
                'formRegexp'        => '',
                'formRange'         => '',
                'defaultValue'      => '',
                'listPosition'      => '',
                'listAlign'         => '',
                'listUseFilter'     => '',
                'altPosition'       => '',
                'status'            => '',
                'isInteger'         => '',
                'isRequired'        => '',
                'isUnique'          => ''),
            'currentStatus' => array(
                'id'                => '',
                'tableName'         => '',
                'tablefield'        => '',
                'formTab'           => '',
                'formLabel'         => '',
                'formType'          => '',
                'formPosition'      => '',
                'formColumns'       => '',
                'formRegexp'        => '',
                'formRange'         => '',
                'defaultValue'      => '',
                'listPosition'      => '',
                'listAlign'         => '',
                'listUseFilter'     => '',
                'altPosition'       => '',
                'status'            => '',
                'isInteger'         => '',
                'isRequired'        => '',
                'isUnique'          => ''),
            'completePercent' => array(
                'id'                => '',
                'tableName'         => '',
                'tablefield'        => '',
                'formTab'           => '',
                'formLabel'         => '',
                'formType'          => '',
                'formPosition'      => '',
                'formColumns'       => '',
                'formRegexp'        => '',
                'formRange'         => '',
                'defaultValue'      => '',
                'listPosition'      => '',
                'listAlign'         => '',
                'listUseFilter'     => '',
                'altPosition'       => '',
                'status'            => '',
                'isInteger'         => '',
                'isRequired'        => '',
                'isUnique'          => ''),
            'budget' => array(
                'id'                => '',
                'tableName'         => '',
                'tablefield'        => '',
                'formTab'           => '',
                'formLabel'         => '',
                'formType'          => '',
                'formPosition'      => '',
                'formColumns'       => '',
                'formRegexp'        => '',
                'formRange'         => '',
                'defaultValue'      => '',
                'listPosition'      => '',
                'listAlign'         => '',
                'listUseFilter'     => '',
                'altPosition'       => '',
                'status'            => '',
                'isInteger'         => '',
                'isRequired'        => '',
                'isUnique'          => '')
        );

        $this->_listResult = array(
            'title'           => $this->_formResult['title'],
            'startDate'       => $this->_formResult['startDate'],
            'endDate'         => $this->_formResult['endDate'],
            'priority'        => $this->_formResult['priority'],
            'currentStatus'   => $this->_formResult['currentStatus'],
            'completePercent' => $this->_formResult['completePercent']
        );

        $this->_filterResult = array(
            'title'            => $this->_formResult['title'],
            'start_date'       => $this->_formResult['startDate'],
            'end_date'         => $this->_formResult['endDate'],
            'priority'         => $this->_formResult['priority'],
            'current_status'   => $this->_formResult['currentStatus'],
            'complete_percent' => $this->_formResult['completePercent']
        );
        $this->sharedFixture = Phprojekt::getInstance()->getDb();
    }

    /**
     * Test set
     */
    public function testWrongSet()
    {
        $item = new Project_Models_Project(array('db' => $this->sharedFixture));
        $this->setExpectedException('Phprojekt_ActiveRecord_Exception');
        $item->wrongAttribute = 'Hello World';
    }

    /**
     * Test set for required fields
     */
    public function testRequiredFieldSet()
    {
        $item            = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->projectId = 1;
        $item->title     = '';
        $item->notes     = 'TEST';
        $item->startDate = '1981-05-12';
        $item->priority  = 1;
        $item->recordValidate();
        $this->assertEquals(1, count($item->getError()));
    }

    /**
     * Test set for integer fields
     */
    public function testIntegerFieldSet()
    {
        $item           = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->priority = 'AA';
        $this->assertEquals(0, $item->priority);

        $item->priority = 7;
        $this->assertEquals(7, $item->priority);
    }

    /**
     * Test for get errors
     */
    public function testGetError()
    {
        $result   = array();
        $result[] = array('field'    => 'title',
                          'label'    => 'Title',
                          'message'  => 'Is a required field');
        $item = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->getError();
        $this->assertEquals(array(), $item->getError());

        $item->projectId = 1;
        $item->title     = '';
        $item->notes     = 'TEST';
        $item->startDate = '20-';
        $item->endDate   = '1981-05-12';
        $item->priority  = 1;
        $item->recordValidate();
        $this->assertEquals($result, $item->getError());
    }

    /**
     * Test for validations
     */
    public function testRecordValidate()
    {
        $item        = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->title = '';
        $this->assertFalse($item->recordValidate());

        $item->projectId = 1;
        $item->title     = 'TEST';
        $item->notes     = 'TEST';
        $item->startDate = '1981-05-12';
        $item->endDate   = '1981-05-12';
        $item->priority  = 1;
        $this->assertTrue($item->recordValidate());

        $item     = new Project_Models_Project(array('db' => $this->sharedFixture));
        $result = array(
            array(
                'field'   => 'currentStatus',
                'label'   => 'Current status',
                'message' => 'Value out of range'
            )
        );
        $item->projectId     = 1;
        $item->title         = 'TEST';
        $item->notes         = 'TEST';
        $item->startDate     = '1981-05-12';
        $item->endDate       = '1981-05-12';
        $item->priority      = 1;
        $item->currentStatus = 10;
        $this->assertFalse($item->recordValidate());
        $this->assertEquals($result, $item->getError());

        $item            = new Customized_Project(array('db' => $this->sharedFixture));
        $item->projectId = 1;
        $item->title     = 'TEST';
        $item->notes     = 'TEST';
        $item->startDate = '1981-05-12';
        $item->endDate   = '1981-05-12';
        $item->priority  = 0;
        $this->assertFalse($item->recordValidate());
    }

    /**
     * Test date field
     */
    public function testDate()
    {
        $item            = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->startDate = 'aaaaaaaaaa';
        $this->assertEquals($item->startDate, null);

        $item->startDate = '1981-05-12';
        $this->assertEquals(array(), $item->getError());
    }

    /**
     * Test float values
     */
    public function testFloat()
    {
        $locale = new Zend_Locale();
        $locale->setLocale('es_AR');
        $item         = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->budget = '1000,30';
        $item->budget;
    }

    /**
     * Test empty float values
     */
    public function testEmptyFloat()
    {
        $locale = new Zend_Locale();
        $locale->setLocale('es_AR');
        $item         = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->budget = '';
    }

    /**
     * Test time
     */
    public function testTime()
    {
        $this->markTestSkipped('Do not use Minute model outside of Minutes test');
        $item          = new Minutes_Models_Minutes(array('db' => $this->sharedFixture));
        $item->endTime = '12:00:00';
        $this->assertEquals(array(), $item->getError());
        $this->assertEquals('12:00:00', $item->endTime);
    }

    /**
     * Test html
     */
    public function testHtml()
    {
        $this->markTestSkipped('Do not use Note model outside of Minutes test');
        $item           = new Note_Models_Note(array('db' => $this->sharedFixture));
        $item->comments = '<b>HELLO</b>';
        $this->assertEquals(array(), $item->getError());
        $this->assertEquals('<b>HELLO</b>', $item->comments);
    }

    /**
     * Test multipleValues
     */
    public function testArray()
    {
        $this->markTestSkipped('Do not use Minute model outside of Minutes test');
        $item                      = new Minutes_Models_Minutes(array('db' => $this->sharedFixture));
        $item->participantsInvited = array(1,2,3);
        $this->assertEquals(array(), $item->getError());
        $this->assertEquals('1,2,3', $item->participantsInvited);
    }

    /**
     * Test filters data
     */
    public function testGetFieldsForFilter()
    {
        $module = new Project_Models_Project(array('db' => $this->sharedFixture));
        $array  = $module->getFieldsForFilter();
        $this->assertEquals(array_keys($this->_filterResult), $array);
    }

    /**
     * Test getRights function
     */
    public function testGetUsersRights()
    {
        $module = new Project_Models_Project(array('db' => $this->sharedFixture));
        $module->find(2);

        $rights = $module->getUsersRights();
        $this->assertArrayHasKey(3, $rights);
        $this->assertArrayHasKey('itemId', $rights[3]);
        $this->assertEquals($rights[3]['itemId'], 2);
        $this->assertArrayHasKey('write', $rights[3]);
        $this->assertEquals($rights[3]['write'], true);

        $module = new Todo_Models_Todo(array('db' => $this->sharedFixture));
        $this->assertEquals(array(), $module->getUsersRights());
    }

    /**
     * Test delete function (with upload file)
     */
    public function testDelete()
    {
        $this->markTestSkipped('Do not use Helpdesk model outside of Helpdesk tests');
        $model              = new Helpdesk_Models_Helpdesk(array('db' => $this->sharedFixture));
        $model->title       = 'test';
        $model->projectId   = 1;
        $model->ownerId     = 1;
        $model->attachments = '3bc3369dd33d3ab9c03bd76262cff633|LICENSE';
        $model->status      = 3;
        $model->author      = 1;
        $model->save();
        $this->assertNotNull($model->id);

        $id = $model->id;
        $model->delete();
        $model->find($id);
        $this->assertNull($model->title);
    }

    public function testSaveRights()
    {
        $this->markTestSkipped('Do not use Helpdesk model outside of Helpdesk tests');
        $model = new Helpdesk_Models_Helpdesk(array('db' => $this->sharedFixture));
        $model->title       = 'test';
        $model->projectId   = 1;
        $model->ownerId     = 1;
        $model->attachments = '3bc3369dd33d3ab9c03bd76262cff633|LICENSE';
        $model->status      = 3;
        $model->author      = 1;
        $model->save();
        $model->saveRights(array(1 => 255));
        $rights = new Phprojekt_Item_Rights();
        $this->assertEquals(255, $rights->getItemRight(10, $model->id, 1));

        $this->assertEquals(0, $rights->getItemRight(10, $model->id, 10));
    }

    /**
     * Test the current function
     */
    public function testCurrent()
    {
        $model = new Project_Models_Project(array('db' => $this->sharedFixture));
        $model->find(1);
        foreach ($model as $key => $field) {
            if ($key == 'id') {
                $this->assertEquals('1', $field->value);
            }
            if ($key == 'title') {
                $this->assertEquals('PHProjekt', $field->value);
            }
        }
    }
}
