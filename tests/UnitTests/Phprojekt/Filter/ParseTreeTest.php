<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Eglers Parse Tree
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_Filter_ParseTreeTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @group core
     * @group filter
     */
    public function testStringToTree()
    {
        $tree   = new Phprojekt_Filter_ParseTree();
        $parsed = $tree->stringToTree('(name = "Musterman" and id = 3) or id != 5');
        $this->assertEquals(Phprojekt_Filter_Tokenizer::T_CONNECTOR, $parsed->getNodeType());
        $this->assertEquals(Phprojekt_Filter_Tokenizer::T_OPERATOR, $parsed->getRightChild()->getNodeType());

        try {
            $parsed = $tree->stringToTree('(name = = "Musterman" and id = 3) or id != 5');
        } catch (Phprojekt_ParseException $e) { }

        try {
            $parsed = $tree->stringToTree('(name" = "Musterman" and id = 3) or id != 5');
        } catch (Phprojekt_ParseException $e) { }

        try {
            $parsed = $tree->stringToTree('(name = "Muster\"man" and id = 3) or id != 5');
        } catch (Exception $e) {
            $this->fail($e);
        }
    }
}
?>