<?php
/**
 * A exception that can be and will be forwarded to the frontend
 * when thrown.
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * A exception that can be and will be forwarded to the frontend
 * when thrown.
 * 
 * This exception only returns a message and a code and hides
 * trace informations to avoid an information disclosure.
 * The informations are converted to the appropriate format in the
 * error controller. Usually only the public attributes are convereted and 
 * forwarded.
 * 
 * We cannot make getTrace, etc private to avoid getting the trace at all
 * as we want to log the backtrace.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Default
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_PublishedException extends Exception
{
	public $message;
	public $code = 0;
	
	/**
	 * Initialize
	 *
	 * @param string  $message  A short description what's wrong 
	 * @param integer $code     An optional error code
	 */
	public function __construct($message, $code = 0) {
		$this->message = $message;
		$this->code    = $code;
	}
}