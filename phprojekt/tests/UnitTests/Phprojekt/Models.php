<?php
/**
 * Unit test for modules
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
require_once 'PHPUnit/Extensions/ExceptionTestCase.php';

/**
 * Module Test
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Models extends PHPUnit_Extensions_ExceptionTestCase
{
    /**
     * All the modules to test
     *
     * @var array
     */
    protected $_modules = array();

    /**
     * Add all the modules for check
     *
     */
    public function setUp()
    {
        $this->_modules[] = 'Default';
        $this->_modules[] = 'Project';
        $this->_modules[] = 'Todo';
    }

    /**
     * Check if exists the getListData function
     *
     */
    public function testGetListData()
    {
        foreach ($this->_modules as $key => $moduleName) {
            $module = Phprojekt_Loader::getModel($moduleName, $moduleName, array('db' => $this->sharedFixture));
            if (in_array("getListData", get_class_methods($module))) {
                $module->getListData();
            } else {
                $this->fail($moduleNAme .
                            " not have the getListData function defined");
            }
        }
    }

    /**
     * Check if exists the getFormData function
     *
     */
    public function testGetFormData()
    {
        foreach ($this->_modules as $key => $moduleName) {
            $module = Phprojekt_Loader::getModel($moduleName, $moduleName, array('db' => $this->sharedFixture));
            if (in_array("getFormData", get_class_methods($module))) {
                $module->getFormData();
                $module->getFormData(1);
            } else {
                $this->fail($moduleName .
                            " not have the getFormData function defined");
            }
        }
    }

    /**
     * Check if exists the getSubModules function
     *
     */
    public function testSubModules()
    {
        foreach ($this->_modules as $key => $moduleName) {
            $module = Phprojekt_Loader::getModel($moduleName, $moduleName, array('db' => $this->sharedFixture));
            if (in_array("getSubModules", get_class_methods($module))) {
                $module->getSubModules();
            } else {
                $this->fail($moduleName .
                            " not have the getSubModules function defined");
            }
        }
    }
}