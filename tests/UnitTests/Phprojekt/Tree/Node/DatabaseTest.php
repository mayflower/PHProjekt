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

class Phprojekt_Model_Tree extends Phprojekt_ActiveRecord_Abstract
{ }

/**
 * Tests for Database Nodes
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_Tree_Node_DatabaseTest extends PHPUnit_Extensions_ExceptionTestCase
{
    private $_treeModel;

    /**
     * setUp method for PHPUnit. We use a shared db connection
     *
     */
    public function setUp()
    {
        $config = new Zend_Config_Ini('../../configuration.ini', 'testing');
        $this->_treeModel = new Phprojekt_Model_Tree($this->sharedFixture);
    }

    /**
     * Test the getModel functionality
     *
     */
    public function testSetup()
    {
        $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 2);
        $tree->setup();

        $this->assertEquals('/', $tree->path);
        $this->assertEquals('Root', $tree->name);
        $this->assertNotNull($tree->id);
        $this->assertEquals(3, count($tree->getChildren()));
    }

    /**
     * getNodeByID test
     *
     */
    public function testGetNodeById()
    {
        $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 2);
        $tree->setup();
        $this->assertEquals('Sub Child 1', $tree->getNodeById(5)->name);
    }

    /**
     * Test append
     *
     */
    public function testAppend()
    {
        $this->_treeModel->getAdapter()->beginTransaction();

        try {
            $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 2);
            $tree->setup();

            $new = new Phprojekt_Tree_Node_Database($this->_treeModel);

            $new->name = 'Hello World';

            $tree->getNodeById(4)->appendNode($new);
            $this->assertEquals('/2/4/', $new->path);
            $this->assertEquals(4, $new->parent);
        } catch (Exception $e) {
            $this->_treeModel->getAdapter()->rollBack();
            throw $e;
        }

        $this->_treeModel->getAdapter()->rollBack();
    }

    /**
     * delete test
     */
    public function testDeleteNode()
    {
        $this->_treeModel->getAdapter()->beginTransaction();

        try {
            $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 2);
            $tree->setup();
            $tree->delete();
            $this->assertNull($tree->id);
            $this->setExpectedException('Phprojekt_Tree_Node_Exception');
            $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 2);
            $tree->setup();
        } catch (Exception $e) {
            $this->_treeModel->getAdapter()->rollBack();
            throw $e;
        }

        $this->_treeModel->getAdapter()->rollBack();
    }

    /**
     * Test the getModel functionality
     *
     */
    public function testRootNode()
    {
        $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 2);
        $tree->setup();
        $this->assertEquals($tree->id, $tree->getRootNode()->id);
    }

    /**
     * Enter description here...
     *
     */
    public function testGetSubtree()
    {
        $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 4);
        $tree->setup();

        $this->assertEquals(2, count($tree->getChildren()));
        $this->assertNull($tree->getNodeById(2));
        $this->assertEquals('Child 2', $tree->name);

        $this->assertTrue($tree->isRootNodeForCurrentTree());
        $this->assertFalse($tree->isRootNode());
    }
}