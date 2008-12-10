<?php
/**
 * Tokenizer class
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
 * @copyright  2007-2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id$
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Sarah Hermann <sarah.hermann@mayflower.de>
 */

/**
 * Tokenizer with inherit object methods for iteration
 *
 * @copyright  2007-2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Sarah Hermann <sarah.hermann@mayflower.de>
 */
class Phprojekt_Filter_Tokenizer
{
    const T_UNDEFINED   = 0;
    const T_OPEN_BRACE  = 1;
    const T_CLOSE_BRACE = 2;
    const T_CONNECTOR   = 3;
    const T_OPERATOR    = 4;
    const T_COLUMN      = 5;
    const T_VALUE       = 6;

    /**
     * regex for tokens
     *
     * @var array
     */
    private $_token = array(
        self::T_OPEN_BRACE  => '/^(\()(.)*?$/',
        self::T_CLOSE_BRACE => '/^(\))(.)*?$/',
        self::T_CONNECTOR   => '/^(or|and)(.)*$/i',
        self::T_OPERATOR    => '/^(=|!=|\<=|\<|\>=|\>)(.)*?$/',
        self::T_COLUMN      => '/^(((\'|\")(\w| )+(\"|\'))|(\w)+)( )*?(=|!=|\<=|\<|\>=|\>)(.)*?$/i',
        self::T_VALUE       => '/^((\'|\")(\w| )+(\'|\")|(([0-9]+)(\.)([0-9]+))|(\d){1,})( )*?(((and|or)(.)*)|(\)(.)*?|( )*?))/i',
        );

    /**
     * string to tokenize
     *
     * @var string
     */
    private $_data = '';

    /**
     * array with current token
     *
     * @var array
     */
    private $_currentToken = array();

    /**
     * type of token
     *
     * @var int
     */
    public $type = null;

    /**
     * value of token
     *
     * @var string
     */
    public $value = '';

    public function __construct($string = '')
    {
        $this->_data         = trim($string);
        $this->_currentToken = $this->parseString();
        $this->type          = $this->_currentToken[0];
        $this->value         = $this->_currentToken[1];
    }

    /**
     * Returns current token
     *
     * @return array
     */
    public function getCurrent()
    {
        if (null === $this->_currentToken) {
            return false;
        }
        return $this;
    }

    /**
     * Returns next token
     *
     * @return array
     */
    public function getNext()
    {
        if ('' == $this->_data) {
            return false;
        }

        $next        = new Phprojekt_Filter_Tokenizer($this->_data);
        $token       = $this->parseString(false);
        $next->type  = $token[0];
        $next->value = $token[1];
        return $next;
    }

    /**
     * Returns last token
     *
     * @return array
     */
    public function getLast()
    {
        if ('' == $this->_data) {
            return false;
        }
        $tok = substr($this->_data, -1);

        // we only need type information T_CLOSE_BRACE, else UNDEFINED is working as well
        if (')' === $tok) {
            $tok = array(self::T_CLOSE_BRACE, $tok);
        } else {
            $tok = array(self::T_UNDEFINED, $tok);
        }

        $last        = new Phprojekt_Filter_Tokenizer();
        $last->type  = $tok[0];
        $last->value = $tok[1];
        return $last;
    }

    /**
     * go to next token
     *
     * @return void
     */
    public function next()
    {
        $this->_currentToken = $this->parseString();
        $this->type          = $this->_currentToken[0];
        $this->value         = $this->_currentToken[1];
    }

    /**
     * Returns string
     *
     * @return string
     */
    public function getRest()
    {
        return $this->_data;
    }

    /**
     * parse string, removes token from string and returns first token
     *
     * @param  bool
     * @return array
     */
    private function parseString($remove = true)
    {
        if ('' == $this->_data) {
            return null;
        }

        $this->_data = trim($this->_data);
        foreach ($this->_token as $key => $regex) {
            if (preg_match($regex, $this->_data, $matches)) {
                $foundToken = array($key, $matches[1]);
                if ($remove) {
                    $this->_data = substr($this->_data, strlen($matches[1]), strlen($this->_data));
                }
                break;
            }
        }
        if (isset($foundToken)) {
            return $foundToken;
        } else {
            return null;
        }
    }
}
