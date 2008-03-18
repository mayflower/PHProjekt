<?php
/**
 * Tokenizer class
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007-2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
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
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Sarah Hermann <sarah.hermann@mayflower.de>
 */
class Phprojekt_Filter_Tokenizer
{
    const T_OPEN_BRACE  = 1;
    const T_CLOSE_BRACE = 2;
    const T_CONNECTOR   = 3;
    const T_OPERATOR    = 4;
    const T_COLUMN      = 5;
    const T_VALUE       = 6;

    /**
     * string to tokenize
     *
     * @var string
     */
    private $data = '';

    /**
     * array with current token
     *
     * @var array
     */
    private $currentToken = array();

    /**
     * type of token
     *
     * @var int
     */
    public $type  = null;

    /**
     * value of token
     *
     * @var string
     */
    public $value = '';

    public function __construct($string = '')
    {
        $this->data         = $string;
        $this->currentToken = $this->parseString();
        $this->type         = $this->currentToken[0];
        $this->value        = $this->currentToken[1];
    }

    /**
     * Returns current token
     *
     * @return array
     */
    public function getCurrent()
    {
        if ($this->currentToken === NULL) {
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
        if ($this->data == '') {
            return false;
        }
        $next        = new Phprojekt_Filter_Tokenizer($this->data);
        $token       = $this->parseString(false);
        $next->type  = $token[0];
        $next->value = $token[1];
        return $next;
    }

    public function next()
    {
     $this->currentToken = $this->parseString();
     $this->type         = $this->currentToken[0];
     $this->value        = $this->currentToken[1];
    }

    public function getRest()
    {
        return $this->data;
    }

    /**
     * parse string, removes token from string and returns first token
     *
     * @param  bool
     * @return array
     */
    private function parseString($remove = true)
    {
        $this->data = trim($this->data);
        if (strpos($this->data, '(') !== false && strpos($this->data, '(') < 1) {
            //open brace
            $token = array(self::T_OPEN_BRACE, '(');
            if ($remove) {
             $this->data = preg_replace('[\(]', '', $this->data, 1);
            }
            return $token;
        } elseif (strpos($this->data, ')') !== false && strpos($this->data, ')') < 1) {
            //closing brace
            $token = array(self::T_CLOSE_BRACE, ')');
            if ($remove) {
                $this->data = preg_replace('[\)]', '', $this->data, 1);
            }
            return $token;
        } elseif (stripos($this->data, 'and') !== false && stripos($this->data, 'and') < 1) {
            // and
            $token = array(self::T_CONNECTOR, 'and');
            if ($remove) {
                $this->data = preg_replace('/and/i', '', $this->data, 1);
            }
            return $token;
        } elseif (stripos($this->data, 'or') !== false && stripos($this->data, 'or') < 1) {
            //or
            $token = array(self::T_CONNECTOR, 'or');
            if ($remove) {
                $this->data = preg_replace('/or/i', '', $this->data, 1);
            }
            return $token;
        } else {
            $splitted = preg_split('/[\s]/', $this->data);
            if (!empty($this->currentToken)) {
                if ($this->currentToken[0] === self::T_CONNECTOR || $this->currentToken[0] === self::T_OPEN_BRACE) {
                    $token = array(self::T_COLUMN, $splitted[0]);
                }
                if ($this->currentToken[0] === self::T_COLUMN) {
                    $token = array(self::T_OPERATOR, $splitted[0]);
                }
                if ($this->currentToken[0] === self::T_OPERATOR) {
                    $token = array(self::T_VALUE, $splitted[0]);
                }
            } else {
             $token = array(self::T_COLUMN, $splitted[0]);;
            }

            if (isset($token)) {
                if ($remove) {
                    $this->data = preg_replace('/'.$splitted[0].'/', '', $this->data, 1);
                }
                return $token;
            }
        }
    }
}