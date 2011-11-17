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
 * @subpackage Selenium
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */


/**
 * Init Selenium Class
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Selenium
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Selenium_SeleniumInit extends PHPUnit_Extensions_SeleniumTestCase
{
    /**
     * Handle for the current configuration
     *
     * @var Zend_Config_Ini
     */
    public $config;

    /**
     * List of the used Browser
     *
     * @var array the used test browser and selenium-rc hosts
     */
    public static $browsers = array(
      array(
        'name'    => 'Firefox on Linux',
        'browser' => '*chrome',
        'host'    => 'localhost',
        'port'    => 4444,
        'timeout' => 30000,
      ),

      /*
        array(
            'name'    => 'Internet Explorer on Windows Vista',
            'browser' => '*iexplore',
            'host'    => 'vistatest.mf-muc.nop',
            'port'    => 4444,
            'timeout' => 30000,
      ),
      */
    );

    /**
     * Collect coverage data at this url
     *
     * @var string Url of the coverage php file
     */
    protected $_coverageScriptUrl = 'http://cruisecontrol.mf-muc.nop/phpunit_coverage_phprojekt6.php';

    /**
     * setup the unit test. Use firefox as a browser and the document
     * root from the configuration file
     *
     * @return void
     */
    function setUp()
    {
        $this->setAutoStop(false);
        $this->config = Phprojekt::getInstance()->getConfig();
        $this->verificationErrors = array();
        $this->setBrowserUrl($this->config->webpath);
    }

    function login()
    {
        $this->open("/phprojekt/htdocs/index.php/Login/index");
        $this->type("username", "david");
        $this->type("password", "test");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
    }
}