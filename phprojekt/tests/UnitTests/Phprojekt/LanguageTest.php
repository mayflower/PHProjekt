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


/**
 * Tests for Language
 *
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

        $string = $lang->translate('untranslated');
        $this->assertEquals('untranslated', $string);

        $string = $lang->translate('Delete', 'Default', 'en');
        $this->assertEquals('Delete', $string);
    }
}
