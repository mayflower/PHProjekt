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
 * Tests for Eglers Parse Tree
 *
 * @group      phprojekt
 * @group      filter
 * @group      parsetree
 * @group      phprojekt-filter
 * @group      phprojekt-filter-parsetree
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

    /**
     * Test Exception
     */
    public function testException()
    {
        $tree = new Phprojekt_Filter_ParseTree();

        try {
            $tree->stringToTree('(name = "Muster\"man" and id = 3) or id != 5');
        } catch (Exception $error) {
            $this->assertEquals('(name = "Muster\"man" and id = 3) or id != 5', $error->getParsedString());
        }
    }
}
