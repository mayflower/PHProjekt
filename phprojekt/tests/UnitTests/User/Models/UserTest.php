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
 * Tests User Model class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class User_User_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test valid method
     *
     */
    public function testUserModelsUser()
    {
        $userModel = new Phprojekt_User_User();
        $userModel->find(1);
        $this->assertEquals($userModel->saveRights(), null);
        $this->assertEquals($userModel->recordValidate(), false);
        $this->assertEquals($userModel->getError(), array(0 => array('field' => 'firstname', 'message' => 'Is a required field'),
                                                          1 => array('field' => 'lastname', 'message' => 'Is a required field')));
    }
}