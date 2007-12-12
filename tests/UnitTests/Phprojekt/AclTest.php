<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Acls
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Nina Schmitt <nina.schmitt@mayflower.de>
 */

class Phprojekt_AclTest extends PHPUnit_Framework_TestCase
{
    /**
     * setUp method for PHPUnit. We use a shared db connection
     *
     */
    public function setUp()
    {

    }
    
    /**
     * This function constructs the Acl list and checks whether all Rights are
     * registered and returned correctly
     */
    public function testRegisterRights()
    {
        $acl  = Phprojekt_Acl::getInstance();
        $this ->assertTrue($acl->has('Todo'));
        $this ->assertFalse($acl->has('Timecard'));
        $this ->assertTrue($acl->has('Project'));
        $this ->assertTrue($acl->has('Note'));
        $this ->assertTrue($acl->isAllowed('1', 'Project', 'write'));
        $this ->assertTrue($acl->isAllowed('1', 'Todo', 'write'));
        $this ->assertTrue($acl->isAllowed('1', 'Note', 'write'));
    }
}