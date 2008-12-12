<?php
/**
 * Unit test
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Eglers Parse Tree
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_Filter_ParseTreeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test
     */
    public function testStringToTree()
    {
        $tree   = new Phprojekt_Filter_ParseTree();
        $parsed = $tree->stringToTree('(name = "Musterman" and id = 3) or id != 5');
        $this->assertEquals(Phprojekt_Filter_Tokenizer::T_CONNECTOR, $parsed->getNodeType());
        $this->assertEquals(Phprojekt_Filter_Tokenizer::T_OPERATOR, $parsed->getRightChild()->getNodeType());

        $this->setExpectedException('Phprojekt_ParseException');
        $tree->stringToTree('(name = = "Musterman" and id = 3) or id != 5');

        $this->setExpectedException('Phprojekt_ParseException');
        $tree->stringToTree('(name" = "Musterman" and id = 3) or id != 5');

        $this->setExpectedException('Phprojekt_ParseException');
        $tree->stringToTree('(name = "Muster\"man" and id = 3) or id != 5');
    }
}
