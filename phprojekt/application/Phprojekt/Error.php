<?php
/**
 * Simple error class for collect errors and return it
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
 * The class collect a number of errors in an array and the return it all together.
 *
 * For use with templates, you can assign array like:
 * addError(array('field' => 'title',
 *                'message' => 'Hello');
 * So the template can get the array and use the fields "field" and "message".
 * You can use the class with the array that you want.
 *
 * When you get the errors, the array that contain it is deleted.
 * So you can add errors the times that you want,
 * but only get the errors one time.
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
     * Use like addError(array('field' => 'title','message' => 'Hello');
     * So the template can get the array and use each field of them
     *
     * @param array $data Array contain the fields for show
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