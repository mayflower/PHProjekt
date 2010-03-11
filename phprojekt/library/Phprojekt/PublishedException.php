<?php
/**
 * A exception that can be and will be forwarded to the frontend when thrown.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

/**
 * A exception that can be and will be forwarded to the frontend when thrown.
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
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_PublishedException extends Exception
{
    /**
     * Message to display.
     *
     * @var string
     */
    public $message;

    /**
     * Special error codes.
     *
     * 0   Normal meesage.
     * 500 Expired login -> must Logout.
     *
     * @var integer
     */
    public $code = 0;

    /**
     * Initialize.
     *
     * @param string  $message A short description what's wrong.
     * @param integer $code    An optional error code.
     *
     * @return void
     */
    public function __construct($message, $code = 0)
    {
        $this->message = Phprojekt::getInstance()->translate($message);
        $this->code    = $code;
    }
}
