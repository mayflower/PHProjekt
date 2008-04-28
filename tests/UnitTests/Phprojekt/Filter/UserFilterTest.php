<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Filter
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_Filter_UserFilterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the filtering
     *
     */
    public function testFilter ()
    {
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $authNamespace->userId = 1;

        $record = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $filter = new Phprojekt_Filter_UserFilter($record, 'title', 'Invisible Root');
        $tree   = new Phprojekt_Tree_Node_Database($record, 1);
        $tree->setup($filter);
        $this->assertEquals(1, count($tree->getRootNode()->getChildren()));
    }

    public function testSaveToFilter()
    {
        $user = Phprojekt_Loader::getModel('User','User',array('db' => $this->sharedFixture));
        $user->find(1);

        $record = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $filter = new Phprojekt_Filter_UserFilter($record, 'title', 'Invisble Root');
        $tree   = new Phprojekt_Tree_Node_Database($record, 1);
        $tree->setup($filter);

        $filter->saveToBackingStore($user, 'Project');
    }
}