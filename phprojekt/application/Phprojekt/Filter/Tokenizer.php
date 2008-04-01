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
    private $token = array(
        self::T_OPEN_BRACE  => '/^(\()(.)*?$/',
        self::T_CLOSE_BRACE => '/^(\))(.)*?$/',
        self::T_CONNECTOR   => '/^(or|and)(.)*$/i',
        self::T_OPERATOR    => '/^(=|!=|\<=|\<|\>|=\>)(.)*?$/',
        self::T_COLUMN      => '/^(((\'|\")(\w| )+(\"|\'))|(\w)+)( )*?(=|!=|\<=|\<|\>|=\>)(.)*?$/i',
        self::T_VALUE       => '/^((\'|\")(\w| )+(\'|\")|(\d){1,})( )*?(((and|or)(.)*)|(\)(.)*?|( )*?))/i',
        );
    
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
    public $type = null;

    /**
     * value of token
     *
     * @var string
     */
    public $value = '';

    public function __construct($string = '')
    {
        $this->data         = trim($string);
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
        $token       = $next->parseString(false);
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
        if ($this->data == '') {
            return false;
        }
        $tok = substr($this->data, -1);
        
        // we only need type information T_CLOSE_BRACE, else UNDEFINED is working as well
        $tok = (')' === $tok) 
        ? array(self::T_CLOSE_BRACE, $tok) 
        : array(self::T_UNDEFINED, $tok);
        
        $last = new Phprojekt_Filter_Tokenizer();
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
     	$this->currentToken = $this->parseString();
     	$this->type         = $this->currentToken[0];
     	$this->value        = $this->currentToken[1];
    }

    /**
     * Returns string
     *
     * @return string
     */
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
        foreach ($this->token as $key => $regex) {
        	if (preg_match($regex, $this->data, $matches)) {
        		$found_token = array($key, $matches[1]);  
        	    if ($remove) {
        	        $this->data = substr($this->data, strlen($matches[1]), strlen($this->data));
                }
        		break;      	    
        	} 
        }
        if (isset($found_token)) {
        	return $found_token;
        } else {
            return NULL;
        }
    }
}