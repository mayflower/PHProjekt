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
 * @author     Gustavo Solt <solt@mayflower.de>
 */


/**
 * Tests for Tab Class
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      phprojekt
 * @group      tabs
 * @group      phprojekt-tabs
 */
class Phprojekt_TabsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test getId
     */
    public function testSaveTab()
    {
        $tab = new Phprojekt_Tabs();
        $this->assertEquals(2, $tab->saveTab('TEST TAB 1'));
        $this->assertEquals(2, $tab->saveTab('CHANGE TEST TAB 1', 2));
        $this->assertEquals(3, $tab->saveTab('TEST TAB 2'));
    }

    /**
     * Test getModuleName
     */
    public function testGetTabs()
    {
        $tab = new Phprojekt_Tabs();
        $result = array(array('id' => 1,
                              'label' => 'Basic Data'),
                        array('id' => 2,
                              'label' => 'CHANGE TEST TAB 1'),
                        array('id' => 3,
                              'label' => 'TEST TAB 2'));
        $this->assertEquals($result, $tab->getTabs());
    }

    public function testSaveModuleTabRelation()
    {
        $tab = new Phprojekt_Tabs();
        $tab->saveModuleTabRelation(1, 1);
        $result = array(array('id' => 1,
                              'label' => 'Basic Data'));
        $this->assertEquals($result, $tab->getTabsByModule(1));
    }
}
