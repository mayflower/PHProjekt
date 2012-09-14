<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */


/**
 * Tests for Database Nodes
 *
 * @group      phprojekt
 * @group      treenode
 * @group      phprojekt-treenode
 */
class Phprojekt_Tree_Node_DatabaseTest extends DatabaseTest
{
    private $_tree;
    private $_model;

    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../../common.xml');
    }

    /**
     * initialite
     */
    public function setUp()
    {
        parent::setUp();
        $this->_model = new Project_Models_Project();
        $this->_tree = new Phprojekt_Tree_Node_Database($this->_model, 1);
        $this->_tree = $this->_tree->setup();
    }

    /**
     * setup
     */
    public function testSetup()
    {
        $this->assertEquals('/', $this->_tree->path);
        $this->assertEquals('PHProjekt', $this->_tree->title);
        $this->assertNotNull($this->_tree->id);
        $this->assertEquals(1, count($this->_tree->getChildren()));
        $this->assertNotNull($this->_tree->isSetup());
    }

    /**
     * iterator
     */
    public function testIterator()
    {
        $iterator = $this->_tree->getIterator();
        $this->assertTrue($iterator instanceof RecursiveIteratorIterator);
    }

    /**
     * depth
     */
    public function testGetDepth()
    {
        $this->assertEquals(0, $this->_tree->getDepth());
        $node = $this->_tree->getNodeById(2);
        $this->assertEquals(1, $node->getDepth());
        $node = $this->_tree->getNodeById(5);
        $this->assertEquals(2, $node->getDepth());
    }

    /**
     * Tree list formatter
     */
    public function testGetDepthDisplay()
    {
        $node = $this->_tree->getNodeById(2);
        $this->assertEquals('Test Project', $node->getDepthDisplay('title'));
        $node = $this->_tree->getNodeById(5);
        $this->assertEquals('Sub Project', $node->getDepthDisplay('title'));
        $this->assertEquals('2009-06-02', $node->getDepthDisplay('startDate'));
    }

    /**
     * childrens
     */
    public function testGetFirstChild()
    {
        $child = $this->_tree->getFirstChild();
        $this->assertEquals('Test Project', $child->title);

        $node  = $this->_tree->getNodeById(2);
        $child = $node->getFirstChild();
        $this->assertEquals('Sub Project', $child->title);

        $node  = $this->_tree->getNodeById(5);
        $child = $node->getFirstChild();
        $this->assertEquals('Sub Sub Project 1', $child->title);

        $node  = $this->_tree->getNodeById(7);
        $child = $node->getFirstChild();
        $this->assertNull($child);
    }

    /**
     * move tree
     */
    public function testMove()
    {
        $child1 = $this->_tree->getNodeById(5);
        $child2 = $this->_tree->getNodeById(2);
        $child1->setParentNode($child2);

        $tree = new Phprojekt_Tree_Node_Database($this->_model, 1);
        $tree = $tree->setup();
        $this->assertEquals(2, $tree->getNodeById(5)->parentNode->id);
    }

    /**
     * append
     */
    public function testAppend()
    {
        $new = new Phprojekt_Tree_Node_Database($this->_model);
        $new->title = 'Hello World';

        $this->_tree->getNodeById(5)->appendNode($new);
        $this->assertEquals('/1/2/5/', $new->path);
        $this->assertEquals(5, $new->projectId);
    }

    /**
     * rootNode
     */
    public function testRootNode()
    {
        $this->assertEquals($this->_tree->id, $this->_tree->getRootNode()->id);
    }

    /**
     * subtree test
     */
    public function testGetSubtree()
    {
        $tree = new Phprojekt_Tree_Node_Database($this->_model, 5);
        $tree = $tree->setup();

        $this->assertEquals(2, count($tree->getChildren()));
        $this->assertNull($tree->getNodeById(4));
        $this->assertEquals('Sub Project', $tree->title);

        $this->assertTrue($tree->isRootNodeForCurrentTree());
        $this->assertFalse($tree->isRootNode());
    }

    /**
     * delete test node
     */
    public function testDeleteNode()
    {
        $tree = new Phprojekt_Tree_Node_Database($this->_model, 5);
        $tree = $tree->setup();
        $this->assertEquals(2, count($tree->getChildren()));
        $tree->delete();
        $this->assertNull($tree->id);
        $this->assertEquals(0, count($tree->getChildren()));

        $this->setExpectedException('Phprojekt_Tree_Node_Exception');
        $tree->delete();
    }

    /**
     * delete test root node
     */
    public function testDeleteRoot()
    {
        $this->setExpectedException('Phprojekt_Tree_Node_Exception');
        $this->_tree->delete();
    }
}
