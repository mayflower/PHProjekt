<?php
/**
 * Represents an Parse error
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007-2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

/**
 * Represents an Parse error
 *
 * @copyright  2007-2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_ParseException extends Exception
{
    protected $_parsedString;

    /**
     *
     * @param string  $message
     * @param integer $code
     * @param string  $parsedString
     */
    public function __construct($message, $code = null, $parsedString = null)
    {
        parent::__construct($message, $code);
        if (null === $parsedString) {
            $this->_parsedString = $parsedString;
        }
    }

    /**
     *
     * @return string
     */
    public function getParsedString()
    {
        return $this->_parsedString;
    }
}