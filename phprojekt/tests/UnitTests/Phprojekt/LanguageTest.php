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
 * Tests for Language
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_LanguageTest extends PHPUnit_Extensions_ExceptionTestCase
{
    /**
     * Test translations
     *
     * @return void
     */
    public function testTranslate()
    {
        $lang = new Phprojekt_Language('es');
        $string = $lang->translate('Saved');
        $this->assertEquals('Grabado', $string);

        $string = $lang->translate('stringNotTranslated');
        $this->assertEquals('stringNotTranslated', $string);

        $string = $lang->translate('Saved','de');
        $this->assertEquals('Gespeichert', $string);
    }
}