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
require_once 'PHPUnit/Extensions/ExceptionTestCase.php';

/**
 * Tests for Language Adapter
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_LanguageAdapterTest extends PHPUnit_Extensions_ExceptionTestCase
{
    /**
     * Test the load function
     *
     * @return void
     */
    public function testIsLoaded()
    {
        /* The adapter do not load the file for it self */
        $adapter = new Phprojekt_LanguageAdapter('es');
        $this->assertFalse($adapter->isLoaded('es'));
        $this->assertFalse($adapter->isLoaded('de'));

        /* The language class must load the files needed */
        $lang = new Phprojekt_Language('es');
        $this->assertTrue($lang->_adapter->isLoaded('es'));
        $this->assertFalse($lang->_adapter->isLoaded('de'));
    }

    /**
     * Test name of the class
     *
     */
    public function testToString()
    {
        $lang   = new Phprojekt_LanguageAdapter('es');
        $string = $lang->toString();
        $this->assertEquals('Phprojekt',$string);
    }

    /**
     * Test all the lang files using the const defined
     */
    public function testAllFiles()
    {
        $reflect = new ReflectionClass('Phprojekt_LanguageAdapter');
        $constants = $reflect->getConstants();
        foreach ($constants as $key => $value) {
            $value = ereg_replace('.inc.php','',$value);
            $lang = new Phprojekt_LanguageAdapter($value);
        }
    }
}