<?php
/**
 * Error class
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Error class
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Error
{
    /**
     * Containt all the errors
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Collect all the errors in an array for show it later
     *
     * @param array $data Array contain the fields for show
     * @uses addError(array('field' => 'title','message' => 'Hello');
     *
     * @return void
     */
    public function addError(array $data = array())
    {
        $this->_errors[] = $data;
    }

    /**
     * Return the error data and delete it
     *
     * @return array
     */
    public function getError()
    {
        $error         = $this->_errors;
        $this->_errors = array();
        return $error;
    }
}