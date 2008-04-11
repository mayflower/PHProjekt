<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Errors
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_ErrorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test for get errors
     *
     */
    public function testErrors()
    {
        $data = array(
            'field'   => 'title',
            'message' => 'Is a required field');
        $result = ' | title # Is a required field';
        $error  = new Phprojekt_Error();

        $error->addError();
        $return = $error->getError();
        $this->assertEquals(' | ', $return);

        $error->addError($data);
        $return = $error->getError();
        $this->assertEquals($result, $return);
    }
}