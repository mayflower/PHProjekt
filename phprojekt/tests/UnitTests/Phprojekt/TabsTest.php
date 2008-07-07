<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Tab Class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_TabsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test getId
     */
    public function testSaveTab()
    {
        $tab = new Phprojekt_Tabs();
        $this->assertEquals(1, $tab->saveTab('TEST TAB 1'));
        $this->assertEquals(1, $tab->saveTab('CHANGE TEST TAB 1',1));
        $this->assertEquals(2, $tab->saveTab('TEST TAB 2'));
    }

    /**
     * Test getModuleName
     */
    public function testGetTabs()
    {
        $tab = new Phprojekt_Tabs();
        $result = array(array('id' => 1,
                              'label' => 'CHANGE TEST TAB 1'),
                        array('id' => 2,
                              'label' => 'TEST TAB 2'));
        $this->assertEquals($result, $tab->getTabs());
    }

    public function testSaveModuleTabRelation()
    {
        $tab = new Phprojekt_Tabs();
        $tab->saveModuleTabRelation(1,1);
        $result = array(array('id' => 1,
                              'label' => 'CHANGE TEST TAB 1'));
        $this->assertEquals($result, $tab->getTabsByModule(1));
    }
}