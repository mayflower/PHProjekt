<?php
/**
 * Tree class
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007-2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Elger Thiele <elger.thiele@mayflower.de>
 */

/**
 * Represents an node of a tree and provides children handling
 *
 * @copyright  2007-2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Elger Thiele <elger.thiele@mayflower.de>
 */
class Phprojekt_Tree_Binary
{
    /**
     * The node / leaf of the tree
     *
     * @var mixed
     */
    protected $_node = null;

    /**
     * Type of the current node
     *
     * @var mixed
     */
    protected $_nodeType = null;

    /**
     * Left child of a node, is tree again
     *
     * @var Phprojekt_Tree_Binary
     */
    protected $_leftChild  = null;

    /**
     * Right child of a node, is a tree again
     *
     * @var Phprojekt_Tree_Binary
     */
    protected $_rightChild = null;

    /**
     * Constructor
     *
     * @param mixed $node     the value of the node
     * @param int   $nodeType the type of the node
     *
     * @return void
     */
    public function __construct($node, $tokenType = null)
    {
        $this->_node = $node;

        if (null !== $tokenType) {
            $this->_nodeType = $tokenType;
        }
    }

    /**
     * Returns the current node
     *
     * @return mixed the current node
     */
    public function getNode()
    {
        return $this->_node;
    }

    /**
     * Returns the type of the node
     *
     * @return int
     */
    public function getNodeType()
    {
        return $this->_nodeType;
    }

    /**
     * Checks weather the object is leaf or not
     *
     * @return boolean
     */
    public function isLeaf()
    {
        return is_null($this->_leftChild) && is_null($this->_rightChild);
    }

    /**
     * Add a child to current node. All childrena are trees as well.
     *
     * @param Phprojekt_Tree_Binary $leftChild  left child tree
     * @param Phprojekt_Tree_Binary $rightChild right child tree
     *
     * @return void
     */
    public function addChild(Phprojekt_Tree_Binary $leftChild, Phprojekt_Tree_Binary $rightChild)
    {
        $this->addLeftChild($leftChild);
        $this->addRightChild($rightChild);
    }

    /**
     * Returns the left child of current node
     *
     * @return Tree
     */
    public function getLeftChild()
    {
        return $this->_leftChild;
    }

    /**
     * Returns the right child of current node
     *
     * @return Tree
     */
    public function getRightChild()
    {
        return $this->_rightChild;
    }

    /**
     * Add a new Tree Object to the left side of the node
     *
     * @param Tree $child the tree to add to the left side of node
     *
     * @return void
     */
    private function addLeftChild(Phprojekt_Tree_Binary $child)
    {
        $this->_leftChild = $child;
    }

    /**
     * Add a new Tree Object to the right side of the node
     *
     * @param Tree $child the tree to add to the right side of node
     *
     * @return void
     */
    private function addRightChild(Phprojekt_Tree_Binary $child)
    {
        $this->_rightChild = $child;
    }
}