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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */


/**
 * Tests Default Model class
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @group      default
 * @group      model
 * @group      default-model
 */
class Phprojekt_DefaultModelDefault_Test extends DatabaseTest
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml');
    }

    /**
     * Test valid method
     *
     */
    public function testDefaultModelsDefault()
    {
        $defaultModel = new Default_Models_Default();
        $this->assertEquals($defaultModel->valid(), false);
        $this->assertEquals($defaultModel->save(), false);
        $this->assertEquals($defaultModel->getRights(), array());
        $this->assertEquals($defaultModel->recordValidate(), true);
        $this->assertEquals($defaultModel->getFieldsForFilter(), array());
        $this->assertEquals($defaultModel->find(), null);
        $this->assertEquals($defaultModel->fetchAll(), null);
        $this->assertEquals($defaultModel->current(), null);
        $this->assertEquals($defaultModel->rewind(), null);
        $this->assertEquals($defaultModel->next(), null);
        $this->assertEquals($defaultModel->getInformation(), null);
        $this->assertEquals($defaultModel->key(), null);
    }
}
