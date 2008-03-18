<?php
/**
 * Parser class
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007-2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Elger Thiele <elger.thiele@mayflower.de>
 */

/**
 * Parses a given String / Tree
 *
 * This class parses the usercreated filter to mysql conform statement
 *
 * @copyright  2007-2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Elger Thiele <elger.thiele@mayflower.de>
 */
class Phprojekt_Filter_ParseTree
{
    /**
     * Counter for braces, if null all open braces are closed again
     *
     * @var int
     */
    private $bracketgroup = 0;

    /**
     * Convertrs an given string to tree
     *
     * @param string   $string the string that should be formated
     *
     * @return Tree
     *
     * @uses Phprojekt_Filter_Tokenizer
     * @uses Phprojekt_Tree_Binary
     * @throws Exception
     */
    public function stringToTree($string)
    {
        // we use this dertemining left open braces
        $braces = 0;

        // last token
        $last   = null;

        $parsed = '';

        $tokenizer = new Phprojekt_Filter_Tokenizer($string);

        while ($current = $tokenizer->getCurrent()) {

            $next = $tokenizer->getNext();

            /**
             * If a tree [(LEAF)-(NODE)-(LEAF)] was created in last iteration, we have to return
             * if EOF is reached
             */
            if (isset($tmpTree) && $next === false) {
                return $tmpTree;
            } else {
                $tmpTree = null;
            }

            // Parser error, never the same token twice except braces
            if ($current->type == $next->type &&
                $current->type != Phprojekt_Filter_Tokenizer::T_OPEN_BRACE &&
                $current->type != Phprojekt_Filter_Tokenizer::T_CLOSE_BRACE) {
                throw new Exception(sprintf("Parser error near %s %s %s",
                                    $last->value,
                                    $current->value,
                                    $next->value));
            }

            switch ($current->type) {

                case Phprojekt_Filter_Tokenizer::T_OPEN_BRACE :

                    $braces++;

                    // Parser error
                    if (Phprojekt_Filter_Tokenizer::T_OPEN_BRACE != $next->type AND Phprojekt_Filter_Tokenizer::T_COLUMN != $next->type) {
                        throw new Exception(sprintf("Parser error near %s %s",
                                            $current->value,
                                            $next->value));
                    }
                    break;
                case Phprojekt_Filter_Tokenizer::T_CLOSE_BRACE:

                    $braces--;

                    // Parser error
                    if (Phprojekt_Filter_Tokenizer::T_CLOSE_BRACE != $next->type AND Phprojekt_Filter_Tokenizer::T_CONNECTOR != $next->type) {
                        throw new Exception(sprintf("Parser error near %s %s",
                                            $current->value,
                                            $next->value));
                    }
                    break;
                case Phprojekt_Filter_Tokenizer::T_CONNECTOR:

                    // Parser error
                    if (Phprojekt_Filter_Tokenizer::T_OPEN_BRACE != $next->type AND Phprojekt_Filter_Tokenizer::T_COLUMN != $next->type) {
                        throw new Exception(sprintf("Parser error near %s %s",
                                            $current->value,
                                            $next->value));
                    }

                    if (0 == $braces) {
                        #var_dump($parsed);
                        $tree = new Phprojekt_Tree_Binary($current->value, $current->type);
                        $tree->addChild(new Phprojekt_Tree_Binary($this->stringToTree($parsed)),
                                        new Phprojekt_Tree_Binary($this->stringToTree($tokenizer->getRest())));
                        return $tree;
                    }
                    break;
                case Phprojekt_Filter_Tokenizer::T_OPERATOR:

                    // Parser error
                    if (Phprojekt_Filter_Tokenizer::T_VALUE != $next->type) {
                        throw new Exception(sprintf("Parser error near %s %s",
                                            $current->value,
                                            $next->value));
                    }

                    /**
                     * if no errors appear it is assumed the termination is reached and a new (last)
                     * tree is created
                     * following step: checking assumption, add tree in reference to this
                     */
                    if (0 == $braces) {
                        $tmpTree = new Phprojekt_Tree_Binary($current->value, $current->type);
                        $tmpTree->addChild(new Phprojekt_Tree_Binary($last->value, $last->type),
                                           new Phprojekt_Tree_Binary($next->value, $next->type));
                    }
                    break;
                case Phprojekt_Filter_Tokenizer::T_COLUMN:

                    // Parser error
                    if (Phprojekt_Filter_Tokenizer::T_OPERATOR != $next->type) {
                        throw new Exception(sprintf("Parser error near %s %s",
                                            $current->value,
                                            $next->value));
                    }
                    break;
                case Phprojekt_Filter_Tokenizer::T_VALUE:

                    // Parser error
                    if (Phprojekt_Filter_Tokenizer::T_CLOSE_BRACE != $next->type AND Phprojekt_Filter_Tokenizer::T_CONNECTOR != $next->type) {
                        throw new Exception(sprintf("Parser error near %s %s",
                                            $current->value,
                                            $next->value));
                    }
                    break;
                default;
            }

            // remember the parsed string to call it recursivly
            $parsed .= " " . $current->value;

            // we need the last token to create a tree (last) <- (current) -> (next) if current is
            // a node
            $last    = $current;

            $tokenizer->next();
        }

        if ($braces != 0) {
            throw new Exception(sprintf("Parser error: Braces are set incorrectly!"));
        }
    }

    /**
     * Gernerates a string from given Phprojekt_Tree_Binary
     *
     * @param Phprojekt_Tree_Binary $tree the tree that should be converted
     *
     * @return string
     */
    public function treeToString(Phprojekt_Tree_Binary $tree)
    {
        $left   = '';
        $right  = '';

        if ($tree->isLeaf()) {
            return $tree->getNode();
        }
        if ($child = $tree->getLeftChild()) {
            $left = $this->treeToString($child);
        }
        if ($child = $tree->getRightChild()) {
            $right = $this->treeToString($child);
        }

        #var_dump($left);
        #var_dump($right);

        if ($tree->getNodeType() == Phprojekt_Filter_Tokenizer::T_CONNECTOR) {
            $right = " (" . $right . ") ";
            $left  = " (" . $left . ") ";
        }
        return $left . $tree->getNode() . $right;
    }
}
