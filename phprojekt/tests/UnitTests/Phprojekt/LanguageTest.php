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

require_once 'PHPUnit/Framework.php';

/**
 * Tests for Language
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
 * @group      language
 * @group      phprojekt-language
 */
class Phprojekt_LanguageTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test translations
     *
     * @return void
     */
    public function testTranslate()
    {
        $lang = new Phprojekt_Language(array('locale' => 'es'));
        $string = $lang->translate('Delete');
        $this->assertEquals('Borrar', $string);

        $string = $lang->translate('stringNotTranslated');
        $this->assertEquals('stringNotTranslated', $string);

        $string = $lang->translate('Delete', 'Default', 'en');
        $this->assertEquals('Delete', $string);
    }
}
