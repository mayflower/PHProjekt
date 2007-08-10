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
require_once 'PHPUnit/Extensions/ExceptionTestCase.php';

class Phprojekt_Tree extends Phprojekt_Item_Abstract
{
}

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
class Phprojekt_Filter_UserFilterTest extends PHPUnit_Extensions_ExceptionTestCase
{
    /**
     * Test the filtering
     *
     */
    public function testFilter ()
    {
        $record = new Phprojekt_Tree(array('db' => $this->sharedFixture));
        $filter = new Phprojekt_Filter_UserFilter($record, 'name', 'Root');
        $tree   = new Phprojekt_Tree_Node_Database($record, 1);
        $tree->setup($filter);
        $this->assertEquals(0, count($tree->getRootNode()->getChildren()));
    }

    public function testSaveToFilter()
    {
        $user = Phprojekt_Loader::getModel('Users','User',array('db' => $this->sharedFixture));
        $user->find(1);

        $record = new Phprojekt_Tree(array('db' => $this->sharedFixture));
        $filter = new Phprojekt_Filter_UserFilter($record, 'name', 'Root');
        $tree   = new Phprojekt_Tree_Node_Database($record, 1);
        $tree->setup($filter);

        $filter->saveToBackingStore($user, 'Test');
    }
}