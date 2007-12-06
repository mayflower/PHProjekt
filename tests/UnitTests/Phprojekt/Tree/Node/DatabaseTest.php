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
 * Tests for Database Nodes
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_Tree_Node_DatabaseTest extends PHPUnit_Framework_TestCase
{
    private $_treeModel;

    /**
     * setUp method for PHPUnit. We use a shared db connection
     *
     */
    public function setUp()
    {
        $this->_treeModel = new Phprojekt_Project($this->sharedFixture);
    }

    /**
     * Test the getModel functionality
     *
     */
    public function testSetup()
    {
        $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 1);
        $tree->setup();

        $this->assertEquals('/', $tree->path);
        $this->assertEquals('Invisible Root', $tree->title);
        $this->assertNotNull($tree->id);
        $this->assertEquals(1, count($tree->getChildren()));
    }

    /**
     * getNodeByID test
     *
     */
    public function testMove()
    {
        $this->_treeModel->getAdapter()->beginTransaction();
        try {
            $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 1);
            $tree->setup();

            $child1 = $tree->getNodeById(4);
            $child2 = $tree->getNodeById(5);
            $child1->setParentNode($child2);

            $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 1);
            $tree->setup();
            $this->assertEquals(5, $tree->getNodeById(4)->parentNode->id);

        } catch (Exception $e) {
            $this->_treeModel->getAdapter()->rollBack();
            throw $e;
        }

        $this->_treeModel->getAdapter()->rollBack();
    }

    /**
     * Test append
     *
     */
    public function testAppend()
    {
        $this->_treeModel->getAdapter()->beginTransaction();

        try {
            $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 1);
            $tree->setup();

            $new = new Phprojekt_Tree_Node_Database($this->_treeModel);

            $new->title = 'Hello World';

            $tree->getNodeById(4)->appendNode($new);
            $this->assertEquals('/1/2/4/', $new->path);
            $this->assertEquals(4, $new->parent);
        } catch (Exception $e) {
            $this->sharedFixture->rollBack();
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
        $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 1);
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
        $this->assertEquals('Sub Project', $tree->title);

        $this->assertTrue($tree->isRootNodeForCurrentTree());
        $this->assertFalse($tree->isRootNode());
    }

    /**
     * delete test
     */
    public function testDeleteNode()
    {
        $this->_treeModel->getAdapter()->beginTransaction();

        try {
            $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, 1);
            $tree->setup();
            $tree->delete();
            $this->assertNull($tree->id);
            $this->setExpectedException('Phprojekt_Tree_Node_Exception');
            $tree = new Phprojekt_Tree_Node_Database($this->_treeModel, $tree->id);
            $tree->setup();
        } catch (Exception $e) {
            $this->sharedFixture->beginTransaction();
            throw $e;
        }

        $this->_treeModel->getAdapter()->beginTransaction();
    }
}