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
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

require_once 'PHPUnit/Framework.php';

/**
 * Tests for database manager
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @group      phprojekt
 * @group      main
 * @group      phprojekt-main
 */
class Phprojekt_PhprojektTest extends PHPUnit_Framework_TestCase
{
    public function testGetVersion()
    {
        $this->assertEquals("6.0.5-dev", Phprojekt::getVersion());
    }

    public function testCompareVersion()
    {
        $this->assertGreaterThan(0, Phprojekt::compareVersion("6.0.10", Phprojekt::getVersion()));
        $this->assertGreaterThan(0, Phprojekt::compareVersion("6.0.1", "6.0.0"));
        $this->assertLessThan(0, Phprojekt::compareVersion("6.0.1", "6.1.0"));
        $this->assertGreaterThan(0, Phprojekt::compareVersion("6.0.1-RC2", "6.0.1-RC1"));
        $this->assertLessThan(0, Phprojekt::compareVersion("6.0.0-RC1", "6.0.0"));
        $this->assertEquals(0, Phprojekt::compareVersion("6.0.0-RC1", "6.0.0-RC1"));
        $this->assertEquals(0, Phprojekt::compareVersion("6.0.1", "6.0.1"));
    }

    public function testCheckExtensionsAndSettings()
    {
        $spected = array(
            'requirements' => array(
                'extension' => array(
                    'mbstring' => array('required' => true, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/mbstring.installation.php'),
                    'iconv' => array('required' => true, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/iconv.installation.php'),
                    'ctype' => array('required' => true, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/ctype.installation.php'),
                    'gd' => array('required' => true, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/image.installation.php'),
                    'pcre' => array('required' => true, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/pcre.installation.php'),
                    'pdo' => array('required' => true, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/pdo.installation.php'),
                    'Reflection' => array('required' => true, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/reflection.installation.php'),
                    'session' => array('required' => true, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/session.installation.php'),
                    'SPL' => array('required' => true, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/spl.installation.php'),
                    'zlib' => array('required' => true, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/zlib.installation.php'),
                    'pdo_mysql | pdo_sqlite2 | pdo_pgsql' => array('required' => true, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/pdo.installation.php')
                ),
                'settings' => array(
                    'magic_quotes_gpc' => array('required' => 0, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/info.configuration.php#ini.magic-quotes-gpc'),
                    'magic_quotes_runtime' => array('required' => 0, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/info.configuration.php#ini.magic-quotes-runtime'),
                    'magic_quotes_sybase'  => array('required' => 0, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/sybase.configuration.php#ini.magic-quotes-sybase'),
                ),
                'php' => array('required' => '5.2.4', 'checked' => true, 'help' => 'http://us.php.net/')
            ),
            'recommendations' => array(
                'settings' => array(
                    'register_globals' => array('required' => 0, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/ini.core.php#ini.register-globals'),
                    'safe_mode' => array('required' => 0, 'checked' => true,
                        'help' => 'http://us.php.net/manual/en/features.safe-mode.php')
                )
            )
        );

        $this->assertEquals($spected, Phprojekt::checkExtensionsAndSettings());
    }
}
