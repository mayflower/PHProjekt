<?php
/**
 * Parser class
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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Elger Thiele <elger.thiele@mayflower.de>
 */

/**
 * Parses a given String / Tree
 *
 * This class parses the usercreated filter to mysql conform statement
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Elger Thiele <elger.thiele@mayflower.de>
 */
class Phprojekt_Filter_ParseTree
{
    /**
     * Counter for braces, if null all open braces are closed again
     *
     * @var int
     */
    private $_bracketGroup = 0;

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
        $string = $this->_checkString($string);

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
            if (isset($tmpTree) && false === $next) {
                return $tmpTree;
            } else {
                $tmpTree = null;
            }

            if (!is_object($next)) {
                break;
            }

            // Parser error, never the same token twice except braces
            if ($current->type === $next->type &&
            Phprojekt_Filter_Tokenizer::T_OPEN_BRACE !== $current->type &&
            Phprojekt_Filter_Tokenizer::T_CLOSE_BRACE !== $current->type) {
                throw new Phprojekt_ParseException(sprintf("Parser error near %s %s %s",
                $last->value, $current->value, $next->value), null, $string);
            }

            switch ($current->type) {
                case Phprojekt_Filter_Tokenizer::T_OPEN_BRACE :
                    $braces++;

                    if (Phprojekt_Filter_Tokenizer::T_OPEN_BRACE != $next->type
                    && Phprojekt_Filter_Tokenizer::T_COLUMN != $next->type) {
                        throw new Phprojekt_ParseException(sprintf("Parser error near %s %s",
                        $current->value, $next->value), null, $string);
                    }
                    break;
                case Phprojekt_Filter_Tokenizer::T_CLOSE_BRACE:
                    $braces--;

                    if (Phprojekt_Filter_Tokenizer::T_CLOSE_BRACE != $next->type
                    && Phprojekt_Filter_Tokenizer::T_CONNECTOR != $next->type) {
                        throw new Phprojekt_ParseException(sprintf("Parser error near %s %s",
                        $current->value, $next->value), null, $string);
                    }
                    break;
                case Phprojekt_Filter_Tokenizer::T_CONNECTOR:
                    if (Phprojekt_Filter_Tokenizer::T_OPEN_BRACE != $next->type
                    && Phprojekt_Filter_Tokenizer::T_COLUMN != $next->type) {
                        throw new Phprojekt_ParseException(sprintf("Parser error near %s %s",
                        $current->value, $next->value), null, $string);
                    }

                    if (0 == $braces) {
                        $tree = new Phprojekt_Tree_Binary($current->value, $current->type);
                        $tree->addChild($this->stringToTree($parsed),
                        $this->stringToTree($tokenizer->getRest()));
                        return $tree;
                    }
                    break;
                case Phprojekt_Filter_Tokenizer::T_OPERATOR:
                    if (Phprojekt_Filter_Tokenizer::T_VALUE != $next->type) {
                        throw new Phprojekt_ParseException(sprintf("Parser error near %s %s",
                        $current->value, $next->value), null, $string);
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
                    if (Phprojekt_Filter_Tokenizer::T_OPERATOR != $next->type) {
                        throw new Phprojekt_ParseException(sprintf("Parser error near %s %s",
                        $current->value, $next->value), null, $string);
                    }
                    break;
                case Phprojekt_Filter_Tokenizer::T_VALUE:
                    if (Phprojekt_Filter_Tokenizer::T_CLOSE_BRACE != $next->type
                    && Phprojekt_Filter_Tokenizer::T_CONNECTOR != $next->type) {
                        throw new Phprojekt_ParseException(sprintf("Parser error near %s %s",
                        $current->value, $next->value), null, $string);
                    }
                    break;
                default:
                    echo "default";

            }

            // remember the parsed string to call it recursivly
            $parsed .= " " . $current->value;

            // we need the last token to create a tree (last) <- (current) -> (next) if current is
            // a node
            $last = clone $current;
            $tokenizer->next();
        }

        if (Phprojekt_Filter_Tokenizer::T_CLOSE_BRACE === $current->type) {
            $braces--;
        }

        if ($braces != 0) {
            throw new Phprojekt_ParseException(sprintf("Parser error: Braces are set incorrectly!"), null, $string);
        }
    }

    /**
     * Gernerates a string from given Phprojekt_Tree_Binary
     *
     * @param Phprojekt_Tree_Binary $tree the tree that should be converted
     *
     * @return mixed
     */
    public function treeToString(Phprojekt_Tree_Binary $tree)
    {
        if ($tree->isLeaf()) {
            return $tree->getNode();
        }

        $left  = $this->treeToString($tree->getLeftChild());
        $right = $this->treeToString($tree->getRightChild());

        if (Phprojekt_Filter_Tokenizer::T_CONNECTOR === $tree->getNodeType()) {
            $right = " (" . $right . ") ";
            $left  = " (" . $left . ") ";
        }

        return $left . $tree->getNode() . $right;
    }

    /**
     * Check the given string
     *
     * Checking for braces are implemented. If surrounding braces exist remove them,
     * for example:
     *  ( string ) => string
     *  ( string ) string => ( string ) string
     *
     * But this function may be upgraded for further validations.
     *
     * @param string $string the string that should be checked
     *
     * @return string
     */
    protected function _checkString($string)
    {
        $string       = trim($string);
        $tokenizer    = new Phprojekt_Filter_Tokenizer($string);
        $braces       = 0;
        $bracesCount  = 0;
        $lastToken    = $tokenizer->getLast();
        $isValid      = false;

        // if last token is not a brace string is valid
        if (Phprojekt_Filter_Tokenizer::T_CLOSE_BRACE !== $lastToken->type) {
            return $string;
        }

        while ($current = $tokenizer->getCurrent()) {
            if (Phprojekt_Filter_Tokenizer::T_OPEN_BRACE === $current->type) {
                $braces++;
                $bracesCount++;
            }

            if (Phprojekt_Filter_Tokenizer::T_CLOSE_BRACE === $current->type) {
                $braces--;
                $bracesCount++;
            }

            // if in between braces sum +(-) is zero and braces exist, end next token not null
            // e.g. (string) string (string), this is valid, but (string) not
            $next = $tokenizer->getNext();
            if (0 < $bracesCount && 0 === $braces && false !== $next) {
                $isValid = true;
                break;
            }
            $tokenizer->next();
        }

        if (!$isValid) {
            return substr($string, 1, -1);
        }

        return $string;
    }
}
